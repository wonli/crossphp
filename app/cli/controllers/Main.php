<?php
/**
 * @author wonli <wonli@live.com>
 * skeleton
 */

namespace app\cli\controllers;

/**
 * @author wonli <wonli@live.com>
 * Class Main
 * @package app\cli\controllers
 */
class Main extends Cli
{
    /**
     * 命令提示
     *
     * @var array
     */
    protected $commandDesc = [
        'php cp model  生成model',
        'php cp ctl    快速生成控制器及视图控制器',
    ];

    function index()
    {
        $this->commandTips('', '支持的命令列表');
    }
}
