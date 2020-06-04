<?php
/**
 * app配置文件
 */
return [
    /**
     * 默认控制器
     */
    '*' => 'main:index',

    /**
     * 第三方类库的命名空间
     * 命名空间 => PROJECT_PATH的相对路径
     */
    'namespace' => [],

    /**
     * 路由配置
     * 'index' => 'main:index'
     * 为 main->index 指定别名为index
     *
     * 'main:hi' => 'main:index'
     * 为main控制器中的index方法指定别名hi
     * 如果为控制器和方法指定了别名,会自动使用别名
     */
    'router' => [
        'hello' => 'main:index'
    ],
];


