<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\views;

/**
 * @author wonli <wonli@live.com>
 *
 * Class MainView
 * @package app\admin\views
 */
class MainView extends AdminView
{
    function __construct()
    {
        parent::__construct();

        //设置布局
        $this->set([
            'layer' => 'login'
        ]);
    }

    /**
     * 登录页面处理
     *
     * @param array $data
     */
    function login(array $data = [])
    {
        $this->renderTpl("main/index", $data);
    }
}
