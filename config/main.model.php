<?php
/**
 * 数据库表生成类
 *
 * 调用方法
 * php cp model
 * 指定配置项
 * php cp model:main
 *
 * 基本配置
 * db 指定数据库名称, 与数据库配置文件中的一致
 * type 指定生成class或trait
 * path 生成类存储路径（绝对路径）
 * namespace 指定命名空间前缀
 * autoSequence 自动为每张表创建自增序列并关联到主键（Oracle）
 * models 待生成的配置数组[生成对象名称 => 数据表配置]
 * 数据表配置默认传表名，当需要分表或指定自增加序列时传数组
 * - split 分表配置
 *   - field  分表规则字段
 *   - prefix 分表前缀，类数据结构从表prefix0获取
 *   - method 分表方法，mod或hash
 *   - number 分成多少张数量
 * - sequence 单独为表指定自增序列名称（Oracle）
 * - table 表名
 */
return [
    'main' => [
        'db' => 'mysql:db',
        //class或trait
        'type' => 'class',
        //自定义生存储路径
        'path' => '',
        //生成类的命名空间前缀
        'namespace' => 'model',
        //类名 => 表名数组
        'models' => [

        ]
    ]
];

