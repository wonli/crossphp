<?php
/**
 * @author wonli <wonli@live.com>
 * Table.php
 */

namespace lib\UI\Component;

use Cross\Lib\Document\HTML;
use lib\UI\UI;
use Closure;

/**
 * 表格组件
 * <pre>
 * 使用说明:
 *
 * $table = new Table('search'); //初始化表格
 * $table->registerWidget('color', $colorPicker); //注册新控件
 * $table->setGroupKey('id'); //设置数据分组字段名
 * $table->addHead('id', 'ID', '20px', '20px')->useWidget('color'); //设置表头及使用的控件
 * $table->setActionMenu(); //设置操作菜单
 * $table->addAction(); //处理操作
 * $table->setData($data); //设置表格数据
 * $table->render(); //渲染
 * </pre>
 * @author wonli <wonli@live.com>
 *
 * Class Table
 * @package lib\HTML
 */
class Table extends UI
{
    /**
     * 表格头配置数组
     *
     * @var array
     */
    protected $head;

    /**
     * 表格数据中包含的字段数组
     *
     * @var array
     */
    protected $fields;

    /**
     * 表格的默认类名
     *
     * @var string
     */
    protected $tableClass = 'table table-bordered table-hover table-striped';

    /**
     * 是否在表格前加选择框
     *
     * @var bool
     */
    protected $useCheckBox = false;

    /**
     * 多选框配置
     *
     * @var array
     */
    protected $checkBoxConfig = array('type' => 'checkbox');

    /**
     * 多选框包裹器属性配置
     *
     * @var array
     */
    protected $checkBoxWrapConfig = array('class' => 'form-control-static');

    /**
     * 表格中的数据
     *
     * @var array
     */
    protected $data = array();

    /**
     * 操作相关菜单的类名称
     *
     * @var string
     */
    protected $actionClass = 'form-control-static';

    /**
     * 操作相关菜单配置
     *
     * @var array
     */
    protected $hasActionMenu = false;

    /**
     * 操作菜单回调函数
     *
     * @var array
     */
    protected $actionClosure = array();

    /**
     * 表格数据POST时的名称
     *
     * @var null|string
     */
    private $postDataName = 'table';

    /**
     * 表单数据分组的字段名(数据表主键)
     *
     * @var null
     */
    private $groupKey = null;

    function __construct($postDataName = '')
    {
        if ($postDataName) {
            $this->postDataName = $postDataName;
        }
    }

    /**
     * 添加表格头
     *
     * @param string $field
     * @param string $name
     * @param string $width
     * @param string $minWidth
     * @return $this
     */
    function addHead($field, $name, $width, $minWidth = '')
    {
        $this->head[] = array(
            'field' => $field,
            'name' => $name,
            'width' => $width,
            'min-width' => $minWidth
        );

        //默认第一个字段为POST表单数据分组键名
        if (empty($this->groupKey) && empty($this->fields)) {
            $this->groupKey = $field;
        }

        $this->widgetKey = $field;
        $this->fields[$field] = $name;
        $this->useWidget('txt');
        return $this;
    }

    /**
     * 添加表格头部操作菜单
     *
     * @param string $name
     * @param string $width
     * @param string $minWidth
     * @return $this
     */
    function setActionMenu($name, $width, $minWidth = '')
    {
        $this->hasActionMenu = true;
        $this->head[] = array(
            'name' => $name,
            'width' => $width,
            'min-width' => $minWidth
        );

        return $this;
    }

    /**
     * 增加操作菜单
     *
     * @param Closure $action
     */
    function addAction(Closure $action)
    {
        $this->actionClosure[] = $action;
    }

    /**
     * 添加多选框
     *
     * @param string $flag 多选标识
     * @param array $attr checkbox 属性
     * @param array $wrapAttr checkbox 外层label属性
     */
    function addSelectAll($flag = 'select-all', $attr = array(), $wrapAttr = array())
    {
        $this->useCheckBox = $flag;
        if (!empty($attr)) {
            $this->checkBoxConfig = array_merge($this->checkBoxConfig, $attr);
        }

        if (!empty($wrapAttr)) {
            $this->checkBoxWrapConfig = array_merge($this->checkBoxWrapConfig, $wrapAttr);
        }
    }

    /**
     * 生成表格
     *
     * @return mixed
     * @throws \Cross\Exception\CoreException
     */
    function render()
    {
        $th = $this->makeTableHead();
        $body = $this->makeTableBody();

        $table = HTML::table(array(
            '@content' => $th . $body,
            'class' => $this->tableClass
        ));

        $table = $this->renderWidget($table);
        $js = $this->makeTableJS();
        return $table . $js;
    }

    /**
     * 设置表格数据
     *
     * @param $data
     */
    function setData($data)
    {
        $this->data = $data;
    }

    /**
     * 设置数据分组的字段名(一般为数据库表中的主键, 在提交时便于保存和修改数据)
     *
     * @param string $field_name
     */
    function setGroupKey($field_name)
    {
        $this->groupKey = $field_name;
    }

    /**
     * 设置表格CSS类名
     *
     * @param string $name
     */
    function setTableClass($name)
    {
        $this->tableClass = $name;
    }

    /**
     * 设置包裹操作菜单的action的CSS类名
     *
     * @param string $class
     */
    function setActionClass($class)
    {
        $this->actionClass = $class;
    }

    /**
     * 渲染表头
     *
     * @return string
     */
    private function makeTableHead()
    {
        $ths = '';
        if (!empty($this->head)) {
            if ($this->useCheckBox) {
                $ths .= $this->makeCheckBoxSwitch();
            }

            foreach ($this->head as $d) {
                $minWidth = &$d['min-width'];
                if (empty($minWidth)) {
                    $minWidth = $d['width'];
                }

                $style = "width:{$d['width']};min-width:{$minWidth}";
                $ths .= HTML::th(array(
                    '@content' => $d['name'],
                    'style' => $style
                ));
            }
        }

        return HTML::tr($ths);
    }

    /**
     * 生成表单体
     *
     * @return string
     * @throws \Cross\Exception\CoreException
     */
    private function makeTableBody()
    {
        $trs = '';
        if (!empty($this->data)) {
            foreach ($this->data as $d) {
                $td = '';
                $b = array();
                $token = isset($d[$this->groupKey]) ? $d[$this->groupKey] : '';
                foreach ($this->fields as $key => $val) {
                    $value = $this->getWidgetValue($key, $d);
                    if ($this->postDataName) {
                        $inputName = "{$this->postDataName}[{$token}][{$key}]";
                    } else {
                        $inputName = "{$token}[{$key}]";
                    }

                    $a = array();
                    $content = $this->makeWidgetContent($key, $value, $d, $inputName, $a, $b);
                    $a['@content'] = $content;

                    $td .= HTML::td($a);
                }

                //操作菜单
                if ($this->hasActionMenu && !empty($this->actionClosure)) {
                    $actionMenu = '';
                    foreach ($this->actionClosure as $i => $action) {
                        if ($action instanceof Closure) {
                            $actionContent = call_user_func_array($action, array($d));
                            if ($actionContent) {
                                $actionMenu .= HTML::span(array(
                                    '@content' => $actionContent,
                                    'class' => $this->actionClass,
                                    'style' => 'display:inline-block;padding-right:15px'
                                ));
                            }
                        }
                    }

                    if ($actionMenu) {
                        $td .= HTML::td(array(
                            '@content' => $actionMenu
                        ));
                    }
                }

                if ($this->useCheckBox) {
                    $td = $this->makeCheckBox($token) . $td;
                }

                $b['@content'] = $td;
                $trs .= HTML::tr($b);
            }
        } else {
            $cols = count($this->head);
            $trs = "<tr><td colspan='{$cols}'>暂无数据</td></tr>";
        }

        return $trs;
    }

    /**
     * 生成选择框
     *
     * @param string $token
     * @return mixed
     */
    private function makeCheckBox($token)
    {
        $id = "token-{$token}";
        $flagClass = "{$this->useCheckBox}-flag";

        $attr = $this->checkBoxConfig;
        $attr['id'] = $id;
        $attr['token'] = $token;

        $inputName = '.selected';
        if (isset($attr['input_name'])) {
            $inputName = $attr['input_name'];
            unset($attr['input_name']);
        }

        if ($this->postDataName) {
            $attr['name'] = "{$this->postDataName}[{$token}][{$inputName}]";
        } else {
            $attr['name'] = "{$token}[{$inputName}]";
        }

        if (!empty($attr['class'])) {
            $attr['class'] .= " {$flagClass}";
        } else {
            $attr['class'] = $flagClass;
        }

        $labelAttr = &$this->checkBoxWrapConfig;
        $labelAttr['for'] = $id;
        $labelAttr['@content'] = HTML::input($attr);

        return HTML::td(HTML::label($labelAttr));
    }

    /**
     * 全选开关
     *
     * @return mixed
     */
    private function makeCheckBoxSwitch()
    {
        return HTML::th(array(
            'style' => 'width:20px;min-width:20px;max-width:20px',
            '@content' => HTML::input(array(
                'type' => 'checkbox',
                'class' => "{$this->useCheckBox}-switch-flag"
            ))
        ));
    }

    /**
     * @return string
     */
    private function makeTableJS()
    {
        if ($this->useCheckBox) {
            ob_start();
            $config = array(
                'switch' => ".{$this->useCheckBox}-switch-flag",
                'class' => ".{$this->useCheckBox}-flag"
            );
            ?>
            <script>
                var config = <?= json_encode($config) ?>;
                $(function () {
                    $(config.switch).bind('click', function () {
                        var click = $(this).is(':checked');
                        $(config.class).each(function () {
                            $(this).prop("checked", click);
                        })
                    })
                })
            </script>
            <?php
            return ob_get_clean();
        }

        return '';
    }
}