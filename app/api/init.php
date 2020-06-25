<?php
/**
 * app配置文件
 */

use Cross\Interactive\ResponseData;
use Cross\Http\Response;

return [

    /**
     * 系统设置
     */
    'sys' => [
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
         * 默认响应类型
         */
        'content_type' => Response::getInstance()->setContentType('json')->getContentType(),
        /**
         * 指定View输出的方法,默认是HTML.
         * 如果值为JSON或XML的时候,会直接调用View中的JSON或XML方法来输出数据
         * 也可以在ApiView中自定义处理方法(比如MSG_PACK等)
         */
        'display' => 'JSON'
    ],

    /**
     * 系统中用到的各种密钥
     * uri 加密URL
     * auth 加密cookie
     * doc 生成调试文档时
     */
    'encrypt' => [
        'uri' => '*&9wru&!@#%#&',
        'auth' => '!@#cr@!$!21p#$%^',
        'doc' => 'Qe@#$%&qfd1!23sa',
    ],

    /**
     * 数据交互字段配置
     */
    'responseData' => [
        'status' => ResponseData::builder()->setStatusName('status'),
        'message' => ResponseData::builder()->setMessageName('message')
    ],

    /**
     * 关于url的配置
     */
    'url' => [
        /**
         * 默认调用的控制器和方法
         */
        '*' => 'Main:index',
        /**
         * URL风格配置
         *  1 最短最美观(搭配注释@cp_params使用)
         *  2 参数名和值都包含在URL中
         *  3 原生的参数形式
         */
        'type' => 3,
        /**
         * 服务器是否已经开启rewrite支持
         */
        'rewrite' => false,
        /**
         * URL参数分割符
         */
        'dot' => '/',
        /**
         * URL后缀
         */
        'ext' => '',
    ],

    /**
     * 路由配置
     * 'index' => 'main:index'
     * 为 main->index 指定别名为index
     *
     * 'main:hi' => 'main:index'
     * 为main控制器中的index方法指定别名hi
     * 如果为控制器和方法指定了别名,会自动使用别名
     */
    'router' => [],

    /**
     * 第三方类库的命名空间
     * 命名空间 => PROJECT_PATH的相对路径
     */
    'namespace' => []
];


