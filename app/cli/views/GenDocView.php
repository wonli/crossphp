<?php
/**
 * @Auth: wonli <wonli@live.com>
 * GenDocView.php
 */

namespace app\cli\views;

use Cross\MVC\View;

/**
 * 生成文档视图控制器
 *
 * @Auth: wonli <wonli@live.com>
 * Class GenDocView
 * @package app\cli\views
 */
class GenDocView extends View
{
    function index($data = array())
    {
        $this->set(array(
            'load_layer' => false
        ));

        $out_put_dir = rtrim($data['output_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $out_put_index_file =  $out_put_dir . 'index.php';

        $doc_info = include("annotate://" . $data['annotate']['doc_info']);
        $annotate_data = $data['annotate']['data'];

        $nav = $main = '';
        $head = $this->obRenderTpl('segment/api/title', $doc_info);
        if (!empty($annotate_data)) {
            foreach ($annotate_data as $d) {
                $nav .= $this->obRenderTpl('segment/api/nav', $d);
                $main .= $this->obRenderTpl('segment/api/main', $d);
            }
        }

        $layer_data = array();
        $layer_data['nav'] = $nav;
        $layer_data['main'] = $main;
        $layer_data['head'] = $head;
        $layer_data['action'] = $this->obRenderTpl('segment/api/action');

        $content = $this->obRenderTpl('segment/api_layer', $layer_data);
        file_put_contents($out_put_index_file, $content, LOCK_EX);
        echo 'gen successful!' . PHP_EOL;
    }
}
