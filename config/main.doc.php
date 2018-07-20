<?php
/**
 * 文档生成配置文件
 *
 * 调用方法
 * php cp genDoc
 * 生成指定名称的文档
 * php cp genDoc:api
 *
 * 类扩展注释
 * @cp_api_spec 分组名称
 * @cp_api_ignore 不生成文档
 *
 * 方法扩展注释
 * @cp_api [post|get], API地址, 说明信息
 * @cp_request 参数名称[:表单形式]|参数说明|是否必要参数[0|1]
 * 表单形式默认为input, 还支持以下几种
 * 1. textarea
 * 2. select 每一项由空格分隔
 *    如生成选择男女性别的格式为: gender:select:1-男 2-女
 * 3. file 文件上传表单
 * 4. multi_file 多文件上传表单
 *
 * 类和方法都支持的注释(用于控制公共参数是否生效)
 * @cp_global_params [enable|true|yes|1] 默认开
 */
return array(
    'api' => array(
        //标题及版本
        'info' => array(
            'title' => 'API文档',
            'version' => '1.0'
        ),
        //顶部超链接
        //名称 => 链接(可以是数组, 参考View::a()参数)
        'top_nav' => array(

        ),
        //公共参数
        //参数名称 => 参数标题
        'global_params' => array(

        ),
        //header传参
        //参数名称 => 参数标题
        'header_params' => array(

        ),
        //是否通过CURL请求接口
        //当header_params配置不为空时，强制使用curl
        'use_curl' => false,
        //basic认证
        //用户名 => 密码
        'basic_auth' => array(

        ),
        //控制器目录
        'source' => 'app/api/controllers',
        //文档入口输出目录
        'output' => 'htdocs/doc',
        //接口服务器地址
        'api_host' => '//127.0.0.1/skeleton/htdocs/api',
        //文档静态资源服务器
        'asset_server' => ''
    )
);
