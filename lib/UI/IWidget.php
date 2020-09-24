<?php
/**
 * @author wonli <wonli@live.com>
 * Widget.php
 */

namespace lib\UI;

/**
 * 控件接口
 *
 * Interface Widget
 * @package lib\HTML
 */
interface IWidget
{
    /**
     * 获取JS
     *
     * @return mixed
     */
    function getJs();

    /**
     * 获取CSS
     *
     * @return mixed
     */
    function getCss();

    /**
     * 添加JS
     *
     * @param string $js
     * @return mixed
     */
    function addJs(string $js);

    /**
     * 添加CSS
     *
     * @param string $css
     * @return mixed
     */
    function addCss(string $css);

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
    function widget(string $name, string $value, array $data, array $params, array $attributes, array &$a = [], array &$b = []);

    /**
     * 初始化控件, 所需要的JS调用放在次数执行
     */
    function init();
}