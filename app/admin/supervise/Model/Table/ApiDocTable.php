<?php

namespace app\admin\supervise\Model\Table;

use Cross\I\IModelInfo;


class ApiDocTable implements IModelInfo
{
    /**
     * 主键名
     *
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型信息
     *
     * @var array
     */
    protected $modelInfo = [
        'n' => 'db',
        'type' => 'mysql',
        'table' => 'cpa_doc',
        'sequence' => ''
    ];

    /**
     * 分表配置
     *
     * @var array
     */
    protected $splitConfig = [];

    /**
     * 表字段属性
     *
     * @var array
     */
    protected $fieldsInfo = [
        'id' => ['primary' => true, 'is_index' => 'PRI', 'auto_increment' => true, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'int unsigned'],
        'name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(255)'],
        'doc_token' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(255)'],
        'servers' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'text'],
        'global_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'text'],
        'header_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'text'],
        'last_update_admin' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(255)'],
        'last_update_time' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 'CURRENT_TIMESTAMP', 'not_null' => true, 'comment' => '', 'type' => 'timestamp'],
    ];

    /**
     * 获取表主键
     *
     * @return string
     */
    function getPK(): string
    {
        return $this->pk;
    }

    /**
     * 获取模型信息
     *
     * @return array
     */
    function getModelInfo(): array
    {
        return $this->modelInfo;
    }

    /**
     * 获取表字段信息
     *
     * @return array
     */
    function getFieldInfo(): array
    {
        return $this->fieldsInfo;
    }

    /**
     * 获取分表配置
     *
     * @return array
     */
    function getSplitConfig(): array
    {
        return $this->splitConfig;
    }

    /**
     * 获取数据库配置文件地址
     *
     * @return string
     */
    function getConfigFile(): string
    {
        return __DIR__ . '/../../../../../config/db.config.php';
    }
}