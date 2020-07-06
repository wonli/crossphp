<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


class ApiDocData extends SQLModel
{
    public $id = null;
    public $doc_id = null;
    public $enable_mock = null; //0,关闭mock 1,开启mock
    public $global_params = null; //全局参数是否生效
    public $group_key = null; //分类（类名）
    public $group_name = null; //分类名称
    public $api_path = null;
    public $api_name = null; //接口名称
    public $api_params = null;
    public $api_method = null;
    public $api_response_struct = null;
    public $mock_response_data = null;
    public $update_user = null;
    public $update_at = null;

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
        'sequence' => '',
        'config' => __DIR__ . '/../../../../config/db.config.php'
    ];

    /**
     * 表字段属性
     *
     * @var array
     */
    protected $fieldsInfo = [
        'id' => ['primary' => true, 'is_index' => 'PRI', 'auto_increment' => true, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'doc_id' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'enable_mock' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 0, 'not_null' => true, 'comment' => '0,关闭mock 1,开启mock'],
        'global_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 1, 'not_null' => true, 'comment' => '全局参数是否生效'],
        'group_key' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '分类（类名）'],
        'group_name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '分类名称'],
        'api_path' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'api_name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => '接口名称'],
        'api_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => ''],
        'api_method' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'api_response_struct' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => ''],
        'mock_response_data' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => false, 'comment' => ''],
        'update_user' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'update_at' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
    ];

    /**
     * ⚠️以上代码是自动生的，任何修改都将被覆盖
     * ⚠️请在此成员变量之后编写业务代码
     * ⚠️请不要修改或使用此成员变量
     *
     * @var mixed
     */
    private $autoGenCodeFlag;
}