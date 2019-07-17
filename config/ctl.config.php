<?php
/**
 * 生成控制器类配置
 *
 * 调用方法
 * php cp ctl 类名
 * 指定生成类的配置名称
 * php cp ctl:admin 类名
 *
 * 基本配置
 * app app名称
 * author 作者联系方式，建议每个开发人员都按照默认格式添加一个配置
 *
 * 控制器配置
 * extends 指定父类名称，留空从Cross\MVC\Controller继承
 *
 * 视图控制器配置
 * makeViewController 是否创建试图控制器
 * viewExtends 视图控制器从哪里继承，留空从Cross\MVC\View继承
 * makeTpl 是否创建默认模板
 */
return array(
    'admin' => array(
        'app' => 'admin',
        'author' => 'you@email.com',

        'extends' => 'Admin',

        'makeViewController' => true,
        'viewExtends' => 'AdminView',
        'makeTpl' => true
    ),
    'api' => array(
        'app' => 'api',
        'author' => 'you@email.com',

        'extends' => 'Api',

        'makeViewController' => false,
        'viewExtends' => '',
        'makeTpl' => false
    ),
    'web' => array(
        'app' => 'web',
        'author' => 'you@email.com',

        'extends' => 'Web',

        'makeViewController' => true,
        'viewExtends' => 'WebView',
        'makeTpl' => true
    )
);