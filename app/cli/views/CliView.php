<?php
/**
 * @author ideaa <ideaa@qq.com>
 * CliView.php
 */

namespace app\cli\views;


use Cross\MVC\View;

/**
 * Class CliView
 * @package app\cli\views
 */
class CliView extends View
{
    /**
     * 生成默认配置文件
     *
     * @param string $file
     * @param array $data
     * @return bool|int
     */
    function genConfigFile($file, array $data = [])
    {
        $configTplFile = $this->tpl('cli/dev.config', false, false);
        $content = $this->obRenderFile($configTplFile, $data);
        return file_put_contents($file, $content);
    }
}