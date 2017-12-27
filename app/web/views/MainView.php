<?php
/**
 * @author wonli <wonli@live.com>
 */
namespace app\web\views;

/**
 * @author wonli <wonli@live.com>
 * Class MainView
 * @package app\web\views
 */
class MainView extends WebView
{
    /**
     * 默认视图控制器
     *
     * @param array $data
     */
    function index($data = array())
    {
        $this->renderTpl('main/index', $data);
    }
}
