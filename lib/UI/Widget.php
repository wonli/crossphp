<?php
/**
 * @author wonli <wonli@live.com>
 * Widget.php
 */

namespace lib\UI;

use Cross\MVC\View;

/**
 * 控件接口
 *
 * Interface Widget
 * @package lib\HTML
 */
abstract class Widget implements IWidget
{
    /**
     * @var array
     */
    protected $js = array();

    /**
     * @var array
     */
    protected $css = array();

    /**
     * @var View
     */
    protected $view;

    /**
     * 获取JS
     *
     * @return array
     */
    function getJs()
    {
        return $this->js;
    }

    /**
     * 获取CSS
     *
     * @return array
     */
    function getCss()
    {
        return $this->css;
    }

    /**
     * 添加JS
     *
     * @param string $js
     * @return mixed|void
     */
    function addJs($js)
    {
        $this->js[] = $js;
    }

    /**
     * 添加CSS
     *
     * @param string $css
     * @return mixed|void
     */
    function addCss($css)
    {
        $this->css[] = $css;
    }

    /**
     * 设置视图助手
     *
     * @param View $view
     */
    function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * 获取类命名空间名称
     *
     * @return string
     */
    function getNamespace()
    {
        return static::class;
    }

    /**
     * 输出HTML代码
     *
     * @param string $name 表单名
     * @param string $value 当前字段的值
     * @param array $data 当前行数据
     * @param array $params 使用时传入的参数
     * @param array $attributes DOM属性
     * @param array $a 调用widget后改变的外层数据
     * @param array $b
     * @return mixed
     */
    abstract function widget($name, $value, $data, $params, $attributes, &$a = array(), &$b = array());

    /**
     * 初始化控件
     */
    abstract function init();
}