<?php

namespace app\admin\supervise\Model\Table;

use Cross\I\IModelInfo;


class ApiDocDataTable implements IModelInfo
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
        'table' => 'cpa_doc_data',
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
        'doc_id' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'int unsigned'],
        'enable_mock' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 0, 'not_null' => true, 'comment' => '0,关闭mock 1,开启mock', 'type' => 'tinyint unsigned'],
        'global_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 1, 'not_null' => true, 'comment' => '全局参数是否生效', 'type' => 'tinyint unsigned'],
        'group_key' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '分类（类名）', 'type' => 'varchar(64)'],
        'group_name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '分类名称', 'type' => 'varchar(255)'],
        'api_path' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(128)'],
        'api_name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '接口名称', 'type' => 'varchar(64)'],
        'api_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => '', 'type' => 'text'],
        'api_method' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(8)'],
        'api_response_struct' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => '', 'type' => 'longtext'],
        'mock_response_data' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => '', 'type' => 'longtext'],
        'update_user' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'varchar(50)'],
        'update_at' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '', 'type' => 'int unsigned'],
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