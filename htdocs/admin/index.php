<?php
/**
 * @Author: wonli <wonli@live.com>
 */
session_start();
require __DIR__ . '/../../crossboot.php';

//登录成功后$_SESSION['u']会被赋值
//如果$_SESSION['u']为空,访问任何页面都只会输出登录界面
if (empty($_SESSION['u'])) {
    Cross\Core\Delegate::loadApp('admin')->get('Main:login');
} else {
    Cross\Core\Delegate::loadApp('admin')->run();
}
