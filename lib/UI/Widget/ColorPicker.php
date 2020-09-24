<?php
/**
 * @author wonli <wonli@live.com>
 * ColorPicker.php
 */

namespace lib\UI\Widget;

use lib\UI\Widget;

/**
 * 颜色选择器
 *
 * @author wonli <wonli@live.com>
 *
 * Class ColorPicker
 * @package lib\HTML\Widget
 */
class ColorPicker extends Widget
{
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
    function widget(string $name, string $value, array $data, array $params, array $attributes, &$a = [], &$b = [])
    {
        $input = <<< INPUT_TPL
<div class="form-group color-picker-flag" style="margin-bottom:0px;">
    <div class="input-group">
        <input type="text" name="%s" class="form-control" value="%s">

        <div class="input-group-addon">
            <i style="background-color: rgb(69, 35, 35);"></i>
        </div>
    </div>
</div>
INPUT_TPL;
        return sprintf($input, $name, $value);
    }

    /**
     * 控件初始化
     */
    function init()
    {
        ?>
        <script>
            $(function () {
                $('.color-picker-flag').colorpicker()
            });
        </script>
        <?php
    }
}