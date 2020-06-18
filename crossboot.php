<?php
/**
 * @author wonli <wonli@live.com>
 */
//框架依赖的唯一常量
define('PROJECT_PATH', __DIR__);

//项目中的常量在这里定义
define('COOKIE_DOMAIN', '');
define('TIME', time());

//一些全局配置
date_default_timezone_set('Asia/Chongqing');
header("Content-Type:text/html; charset=utf-8");

//使用composer install来进行安装
require PROJECT_PATH . '/vendor/autoload.php';
