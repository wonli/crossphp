<?php
/**
 * @author wonli <wonli@live.com>
 * imagePreview.php
 */


namespace lib\UI\Widget;

use lib\UI\Widget;


/**
 * 图片预览
 * @author wonli <wonli@live.com>
 *
 * Class ImagePreview
 * @package lib\HTML\Widget
 */
class ImagePreview extends Widget
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
    function widget(string $name, string $value, array $data, array $params, array $attributes, array &$a = [], array &$b = [])
    {
        $b = array(
            'class' => 'preview',
            'data-image' => $value,
            'data-toggle' => 'popover',
            'data-placement' => 'top',
            'data-container' => 'body'
        );

        return sprintf('<div class="form-control-static">[ <a href="%s" target="_blank">图片</a> ]</div>', $value);
    }

    /**
     * 初始化控件
     */
    function init()
    {
        ?>
        <script>
            $(function () {
                $('.preview').popover({
                    'trigger': 'hover',
                    'html': true,
                    'content': function () {
                        var image = $(this).data('image');
                        if (image) {
                            return "<img src='" + image + "' style='width:110px;'>";
                        } else {
                            return '暂无图片';
                        }
                    }
                });
            });
        </script>
        <?php
    }
}