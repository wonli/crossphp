<?php
/**
 * @author wonli <wonli@live.com>
 * HTML.php
 */


namespace lib\UI;

use Cross\Exception\CoreException;
use Cross\Lib\Document\HTML;
use Closure;


/**
 * UI控件抽象类,主要管理控件的注册及初始化
 * @author wonli <wonli@live.com>
 *
 * Class UI
 * @package lib\HTML
 */
abstract class UI
{
    /**
     * @var array
     */
    private $js = [];

    /**
     * @var array
     */
    private $css = [];

    /**
     * @var Widget[]
     */
    protected $widget = [];

    /**
     * 控件使用标识
     * 在所有控件调用结束后统一执行init()方法
     *
     * @var array
     */
    protected $usedWidget = [];

    /**
     * 控件属性
     *
     * @var array
     */
    protected $widgetAttributes = [];

    /**
     * 空间默认class
     *
     * @var string
     */
    protected $widgetDefaultClass = 'form-control-static form-control-plaintext';

    /**
     * @var string
     */
    protected $widgetKey = null;

    /**
     * widget运行时配置
     *
     * @var array
     */
    protected $widgetRuntimeConfig = [];

    /**
     * @return mixed
     */
    abstract function render();

    /**
     * 使用匿名函数来处理输出
     * <pre>
     * 回调参数如下:
     * 1. value 当前前字段的值
     * 2. data 当前数据行的值
     * 3. inputName 系统生成的表单名字
     * </pre>
     *
     * @param Closure $action
     * @return UI
     */
    function useClosure(Closure $action): self
    {
        $this->widgetRuntimeConfig[$this->widgetKey] = [
            'name' => $action,
        ];

        return $this;
    }

    /**
     * 注册控件
     *
     * @param string $name
     * @param Widget $widget
     * @param array $attributes
     * @return UI
     */
    function registerWidget(string $name, Widget $widget, $attributes = [])
    {
        $this->widget[$name] = $widget;
        $this->js = array_merge($this->js, $widget->getJs());
        $this->css = array_merge($this->css, $widget->getCss());

        if (!empty($attributes) && isset($this->widgetAttributes[$name])) {
            $this->widgetAttributes[$name] = array_merge($this->widgetAttributes[$name], $attributes);
        } elseif (!empty($attributes)) {
            $this->widgetAttributes[$name] = $attributes;
        }

        return $this;
    }

    /**
     * 使用控件
     *
     * @param string $name 要使用的widget名称
     * @param array $params 运行时参数
     * @return $this
     */
    function useWidget(string $name, $params = []): self
    {
        $this->widgetRuntimeConfig[$this->widgetKey] = [
            'name' => $name,
            'params' => $params,
        ];

        return $this;
    }

    /**
     * 获取Widget的实例
     *
     * @param $name
     * @return Widget|bool
     */
    function getWidget($name)
    {
        if (isset($this->widget[$name])) {
            return $this->widget[$name];
        }

        return false;
    }

    /**
     * 设置Widget的DOM属性
     *
     * @param string $widget
     * @param array $attributes 属性列表
     */
    function setAttributes(string $widget, array $attributes = [])
    {
        if (isset($this->widgetAttributes[$widget])) {
            $this->widgetAttributes[$widget] = array_merge($this->widgetAttributes[$widget], $attributes);
        } else {
            $this->widgetAttributes[$widget] = $attributes;
        }
    }

    /**
     * widget content
     *
     * @param string $key
     * @param string $value
     * @param mixed $token 数据分组标识（主键）
     * @param array $rowData
     * @param string $inputName
     * @param array $a
     * @param array $b
     * @return mixed
     * @throws CoreException
     */
    protected function makeWidgetContent(string $key, string $value, $token, array $rowData, string $inputName = '', &$a = [], &$b = [])
    {
        $widgetConfig = &$this->widgetRuntimeConfig[$key];
        if (!empty($widgetConfig['name'])) {
            $widgetName = $widgetConfig['name'];
        } else {
            $widgetName = '';
        }

        if ($widgetName instanceof Closure) {
            $content = call_user_func_array($widgetName, [$value, $rowData, $inputName]);
            if (empty($content)) {
                throw new CoreException('回调函数不能返回空');
            }

            //回调函数返回结果自动包一层div
            $content = HTML::div([
                '@content' => $content,
                'class' => $this->widgetDefaultClass
            ]);
        } else {
            $params = [];
            if (!empty($widgetConfig['params'])) {
                $params = &$widgetConfig['params'];
            }

            $attributes = [];
            if (isset($this->widgetAttributes[$widgetName])) {
                $attributes = $this->widgetAttributes[$widgetName];
            }

            if (!isset($attributes['class'])) {
                $attributes['class'] = $this->widgetDefaultClass;
            }

            $attributes['name'] = $inputName;
            $attributes['data-id'] = $token;
            $attributes['data-field'] = $key;
            switch ($widgetName) {
                case 'input':
                    $attributes['value'] = $value;
                    $attributes = array_merge($attributes, $params);
                    $content = HTML::input($attributes);
                    break;

                case 'ip':
                    if (false === strpos($value, '.')) {
                        $value = long2ip($value);
                    }
                    $attributes['@content'] = $value;
                    $attributes = array_merge($attributes, $params);
                    $content = HTML::div($attributes);
                    break;

                case 'time':
                case 'date':
                    $format = 'Y-m-d H:i:s';
                    $custom_params = [];
                    if (!empty($params)) {
                        if (is_array($params)) {
                            list($format, $custom_params) = $params;
                        } else {
                            $format = $params;
                        }
                    }

                    if (!empty($value)) {
                        if ($widgetName == 'date') {
                            $value = strtotime($value);
                        }

                        $attributes['@content'] = date($format, $value);
                    } else {
                        $attributes['@content'] = '';
                    }

                    if (!empty($custom_params)) {
                        $attributes = array_merge($attributes, $custom_params);
                    }

                    $content = HTML::div($attributes);
                    break;

                case 'select':
                    $options = '';
                    if (!empty($params)) {
                        foreach ($params as $v => $text) {
                            $option_attributes = [];
                            $option_attributes['value'] = $v;
                            $option_attributes['@content'] = $text;
                            if ($value == $v) {
                                $option_attributes['selected'] = true;
                            }

                            $options .= HTML::option($option_attributes);
                        }

                        $attributes['@content'] = $options;
                        $content = HTML::select($attributes);
                    } else {
                        $content = $value;
                    }
                    break;

                case 'checkbox':
                    $label = null;
                    $trues = [1 => 1, true => 1, 'y' => 1, 'yes' => 1, 'true' => 1];
                    if (!empty($params)) {
                        if (is_array($params)) {
                            list($label, $custom_trues) = $params;
                            if (!empty($custom_trues)) {
                                $trues = &$custom_trues;
                            }
                        } else {
                            $label = $params;
                        }
                    }

                    $attributes['type'] = 'checkbox';
                    $attributes['value'] = $value;
                    if (isset($trues[$value])) {
                        $attributes['checked'] = true;
                    }

                    $label_attributes = $this->widgetAttributes['label'];
                    $label_attributes['@content'] = HTML::input($attributes) . $label;

                    $content = HTML::label($label_attributes);
                    break;

                case 'map':
                case 'dictionary':
                    $content = '-';
                    if (isset($params[$value])) {
                        $content = $params[$value];
                    }

                    $attributes['@content'] = $content;
                    $content = HTML::div($attributes);
                    break;

                case 'txt':
                default:
                    if (isset($this->widget[$widgetName])) {
                        $this->usedWidget[$widgetName] = true;
                        $widgetInstance = $this->widget[$widgetName];
                        $content = $widgetInstance->widget($widgetName, $value, $rowData, $params, $attributes, $a, $b);
                        if (empty($content)) {
                            throw new CoreException($widgetInstance->getNamespace() . "->widget() 不能反回空");
                        }
                    } else {
                        $attributes['@content'] = $value;
                        $content = HTML::div($attributes);
                    }
                    break;
            }
        }

        return $content;
    }

    /**
     * 获取控件的值, 多级数组用冒号分隔
     *
     * @param string $key
     * @param array $data
     * @return mixed|string
     */
    protected function getWidgetValue(string $key, array $data)
    {
        if (false !== strpos($key, ':')) {
            $keys = explode(':', $key);
            while ($k = array_shift($keys)) {
                if (isset($data[$k])) {
                    $nextKey = array_shift($keys);
                    return $this->getWidgetValue($nextKey, $data[$k]);
                } else {
                    return '';
                }
            }
        } elseif (is_array($data) && isset($data[$key])) {
            return $data[$key];
        }

        return '';
    }

    /**
     * 渲染控件
     *
     * @param string $content
     * @return string
     */
    protected function renderWidget(string $content)
    {
        $js = '';
        if (!empty($this->js)) {
            foreach ($this->js as $d) {
                $js .= sprintf('<script src="%s"></script>', $d);
            }
        }

        $css = '';
        if (!empty($this->css)) {
            foreach ($this->css as $d) {
                $css .= sprintf('<link rel="stylesheet" href="%s">', $d);
            }
        }

        ob_start();
        //依次输出UI内容, 所需CSS/JS及调用初始化方法
        echo $content . $css . $js;
        if (!empty($this->usedWidget)) {
            foreach ($this->usedWidget as $widgetName => $value) {
                $widget = $this->getWidget($widgetName);
                if ($widget) {
                    $widget->init();
                }
            }
        }
        $content = ob_get_clean();
        return $content;
    }
}