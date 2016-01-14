<?php
/**
 * mysql
 */
$mysql_link = array(
    'host' => '127.0.0.1',
    'port' => '3306',
    'user' => 'root',
    'pass' => '123456',
    'prefix' => '',
    'charset' => 'utf8',
);

/**
 * redis
 */
$redis_link = array(
    'host' => '127.0.0.1',
    'port' => 6379,
    'pass' => '',
    'timeout' => 2.5
);

#默认数据库配置
$db = $mysql_link;
$db['name'] = 'test';

return array(
    'mysql' => array(
        'db' => $db,
    )
);
