<?php
/**
 * 生成控制器类配置
 *
 * 调用方法
 * php cp ctl -c=类名 -v=是否生成视图控制器
 * 指定生成类的配置名称
 * php cp ctl:admin -c=类名 -v=是否生成视图控制器
 *
 * 基本配置
 * app app名称
 * updateNavMenu 是否更新导航菜单
 *
 * 控制器配置
 * extends 指定父类名称，留空从Cross\MVC\Controller继承
 *
 * 视图控制器配置
 * makeViewController 是否创建试图控制器
 * viewExtends 视图控制器从哪里继承，留空从Cross\MVC\View继承
 * makeTpl 是否创建默认模板
 *
 * 命令行控制
 * 可以通过命令行临时覆盖配置，比如变更父类：extends=className
 */
return [
    'admin' => [
        'app' => 'admin',
        'updateNavMenu' => true,

        'extends' => 'Admin',

        'makeViewController' => true,
        'viewExtends' => 'AdminView',
        'makeTpl' => true
    ],
    'api' => [
        'app' => 'api',

        'extends' => 'Api',

        'makeViewController' => false,
        'viewExtends' => '',
        'makeTpl' => false
    ],
    'web' => [
        'app' => 'web',

        'extends' => 'Web',

        'makeViewController' => true,
        'viewExtends' => 'WebView',
        'makeTpl' => true
    ],
    'cli' => [
        'app' => 'cli',

        'extends' => 'Cli',

        'makeViewController' => false,
        'viewExtends' => '',
        'makeTpl' => false
    ],
];