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
        $config = &$data['config'];
        $annotate_data = &$data['annotate'];
        $global_params = &$config['global_params'];
        $header_params = &$config['header_params'];

        $use_curl = $config['use_curl'];
        if (!empty($header_params)) {
            $use_curl = true;
        }

        if (!empty($annotate_data)) {
            foreach ($annotate_data as $d) {
                $d['use_curl'] = &$use_curl;
                $d['api_host'] = &$config['api_host'];

                $leftNav .= $this->obRenderTpl('doc/api/nav', $d);
                $main .= $this->obRenderTpl('doc/api/main', $d);
            }
        }

        $layer_data['nav'] = $leftNav;
        $layer_data['main'] = $main;

        $docInfo = &$config['info'];
        $docInfo['top_nav'] = &$config['top_nav'];
        $docInfo['set_params'] = !empty($global_params) || !empty($header_params);
        $layer_data['head'] = $this->obRenderTpl('doc/api/title', $docInfo);

        $layer_data['action'] = file_get_contents($this->getTplPath() . 'doc/api/action');
        $layer_data['asset_server'] = &$config['asset_server'];

        $layer_data['basic_auth'] = '';
        if (!empty($config['basic_auth'])) {
            $layer_data['basic_auth'] = $this->obRenderFile($this->getTplPath() . 'doc/api/basic_auth', $config['basic_auth']);
        }

        $content = $this->obRenderTpl('doc/api_layer', $layer_data);
        $out_put_index_file = $config['output'] . 'index.php';
        return file_put_contents($out_put_index_file, $content, LOCK_EX);
    }

    /**
     * 生成处理请求的
     *
     * @param array $data
     * @return bool
     */
    function makeRequestFile(array $data)
    {
        $layer_data['action'] = $this->obRenderFile($this->getTplPath() . 'doc/api/request_action', $data);
        $layer_data['basic_auth'] = '';
        if (!empty($data['basic_auth'])) {
            $layer_data['basic_auth'] = $this->obRenderFile($this->getTplPath() . 'doc/api/basic_auth', $data['basic_auth']);
        }

        $layer_data['asset_server'] = &$data['asset_server'];
        $content = $this->obRenderTpl('doc/request_layer', $layer_data);
        $out_put_index_file = $data['output'] . 'request' . DIRECTORY_SEPARATOR . 'index.php';
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
        $content = $this->tpl('doc/config/doc.config', true, false);
        return file_put_contents($docFile, $content);
    }

    /**
     * 输出PHP代码片段
     *
     * @param string $code
     * @param bool $newLine
     * @return string
     */
    protected function phpCode($code, $newLine = true)
    {
        $code = '<?php ' . $code . ' ?>';
        if ($newLine) {
            $code .= PHP_EOL;
        }

        return $code;
    }
}
