<?php
/**
 * @Auth: wonli <wonli@live.com>
 * Panel.php
 */
namespace app\admin\controllers;

class Panel extends Admin
{
    /**
     * 登录成功后默认跳转到空白的面板
     */
    function index()
    {
        $this->display($this->data);
    }
}
