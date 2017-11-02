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

        $nav = $main = '';
        $layer_data = array();
        $annotate_data = &$data['annotate']['data'];
        if (!empty($annotate_data)) {
            foreach ($annotate_data as $d) {
                $nav .= $this->obRenderTpl('segment/api/nav', $d);
                $main .= $this->obRenderTpl('segment/api/main', $d);
            }
        }

        $layer_data['nav'] = $nav;
        $layer_data['main'] = $main;
        $layer_data['head'] = $this->obRenderTpl('segment/api/title', $data['annotate']['doc_info']);
        $layer_data['action'] = file_get_contents($this->getTplPath() . 'segment/api/action');
        $layer_data['asset_server'] = &$data['asset_server'];

        $layer_data['do_action'] = '';
        if (!empty($data['annotate']['basic_auth'])) {
            $layer_data['do_action'] = $this->obRenderFile($this->getTplPath() . 'segment/api/doAction', $data['annotate']['basic_auth']);
        }

        $content = $this->obRenderTpl('segment/api_layer', $layer_data);
        $out_put_index_file = $data['output_dir'] . 'index.php';
        file_put_contents($out_put_index_file, $content, LOCK_EX);
        echo 'gen successful!' . PHP_EOL;
    }
}
