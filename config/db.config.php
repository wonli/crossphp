<?php
$mysql = function (string $name) {
    return [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'root',
        'pass' => '123456',
        'prefix' => '',
        'charset' => 'utf8',
        'name' => $name
    ];
};

$redis = function (int $db) {
    return [
        'host' => '127.0.0.1',
        'port' => 6379,
        'pass' => '',
        'db' => $db,
        'timeout' => 2.5
    ];
};

/**
 * 数据库配置
 */
return [
    'mysql' => [
        'db' => $mysql('test')
    ],

    'redis' => [
        'cache' => $redis(1)
    ],
];
