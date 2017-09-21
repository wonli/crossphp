<?php
/**
 * @Auth wonli <wonli@live.com>
 * Panel.php
 */
namespace app\admin\controllers;

/**
 * 登录成功后默认跳转到空白的面板
 *
 * @Auth wonli <wonli@live.com>
 *
 * Class Panel
 * @package app\admin\controllers
 */
class Panel extends Admin
{
    function index()
    {
        $this->display($this->data);
    }
}
