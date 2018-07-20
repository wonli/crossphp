<?php
/**
 * 数据库表生成类
 *
 * 调用方法
 * php cp property
 * 生成指定名称的文档
 * php cp property:main
 *
 * 基本配置
 * db 指定数据库名称, 与数据库配置文件中的一致
 * type 指定生成class或trait
 * namespace 指定命名空间前缀
 * property 待生成的配置数组[生成对象 => 数据表]
 */
return array(
    'main' => array(
        'db' => 'mysql:db',
        //class或trait
        'type' => 'class',
        //生成类的命名空间前缀
        'namespace' => 'structural',
        //类名 => 表名数组
        'property' => array(

        )
    )
);
