<?php
/**
 * @author wonli <wonli@live.com>
 * GenDocView.php
 */

namespace app\cli\views;

use Cross\MVC\View;

/**
 * 生成文档视图控制器
 *
 * @author wonli <wonli@live.com>
 * Class GenDocView
 * @package app\cli\views
 */
class GenDocView extends View
{
    /**
     * 生成文档
     *
     * @param array $data
     * @return bool|int
     */
    function index($data = array())
    {
        $this->set(array(
            'load_layer' => false
        ));

        $leftNav = $main = '';
        $layer_data = array();
        $annotate_data = &$data['annotate']['data'];
        $global_params = &$data['annotate']['global_params'];
        if (!empty($annotate_data)) {
            foreach ($annotate_data as $d) {
                $d['api_host'] = &$data['api_host'];
                $leftNav .= $this->obRenderTpl('segment/api/nav', $d);
                $main .= $this->obRenderTpl('segment/api/main', $d);
            }
        }

        $layer_data['nav'] = $leftNav;
        $layer_data['main'] = $main;

        $docInfo = &$data['doc_info'];
        $docInfo['top_nav'] = &$data['top_nav'];
        $docInfo['has_global_params'] = !empty($global_params);
        $layer_data['head'] = $this->obRenderTpl('segment/api/title', $docInfo);

        $layer_data['action'] = file_get_contents($this->getTplPath() . 'segment/api/action');
        $layer_data['asset_server'] = &$data['asset_server'];

        $layer_data['do_action'] = '';
        if (!empty($data['basic_auth'])) {
            $layer_data['do_action'] = $this->obRenderFile($this->getTplPath() . 'segment/api/doAction', $data['basic_auth']);
        }

        $content = $this->obRenderTpl('segment/api_layer', $layer_data);
        $out_put_index_file = $data['output_dir'] . 'index.php';
        return file_put_contents($out_put_index_file, $content, LOCK_EX);
    }

    /**
     * 生成默认配置
     *
     * @param string $docFile
     * @return bool|int
     */
    function makeDocConfigFile($docFile)
    {
        $content = $this->tpl('segment/config/doc.config', true, false);
        return file_put_contents($docFile, $content);
    }
}
