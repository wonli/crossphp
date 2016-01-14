<?php
/**
 * app配置文件
 */
return array(

    /**
     * 系统设置
     */
    'sys' => array(

        /**
         * Http会话认证方式
         * 默认支持 COOKIE 和 SESSION, 也可以指定为自定义的类
         * 如: '\lib\MysqlSession' 或 new \lib\MysqlSession()
         */
        'auth' => 'COOKIE',
        /**
         * 默认的template路径
         */
        'default_tpl_dir' => 'default',
        /**
         * 指定View输出的方法,默认是HTML.
         * 如果值为JSON或XML的时候,会直接调用View中的JSON或XML方法来输出数据
         * 也可以在View中自定义处理方法(比如RSS等)
         */
        'display' => 'HTML'
    ),
    /**
     * uri和auth加解密key
     */
    'encrypt'   =>  array(
        'uri'   =>  '*&9wru&!@#%#&',
        'auth'    =>  '!@#cr@!$!21p#$%^'
    ),
    /**
     * 关于url的配置
     */
    'url' => array(

        /**
         * 默认调用的控制器和方法
         */
        '*' => 'Main:index',
        /**
         * QUERY_STRING 方式
         *  1 简短的url(不包含参数名,需要在方法注释中使用@cp_params key...来手动指定参数的名字)
         *    生成的url类似 /?/controller/action/bar
         *  3 友好的url形式 /?/controller/action/foo/bar...
         * PATH_INFO 方式
         *  2 原生的参数形式: /index.php/controller/action?foo=bar
         *  4 友好的参数形式: /index.php/controller/action/foo/bar...
         *  5 PATH_INFO 下的简短形式
         */
        'type' => 3,
        /**
         * 服务器是否已经开启rewrite支持
         */
        'rewrite' => false,
        /**
         * url请求中的连接字符(controller/action/params)之间的/
         */
        'dot' => '/',
        /**
         * url后缀
         */
        'ext' => '',
        /**
         * 索引文件名称
         */
        'index' => 'index.php'
    ),
    /**
     * 路由配置
     * 'index' => 'main:index'
     * 为 main->index 指定别名为index
     *
     * 'main:hi' => 'main:index'
     * 为main控制器中的index方法指定别名hi
     * 如果为控制器和方法指定了别名,会自动使用别名
     */
    'router' => array()
);


