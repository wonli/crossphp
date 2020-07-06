<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


class ApiDoc extends SQLModel
{
    public $id = null;
    public $name = null;
    public $doc_token = null;
    public $servers = null;
    public $global_params = null;
    public $header_params = null;
    public $last_update_admin = null;
    public $last_update_time = null;

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
        'name' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'doc_token' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'servers' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'global_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'header_params' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'last_update_admin' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'last_update_time' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 'CURRENT_TIMESTAMP', 'not_null' => true, 'comment' => ''],
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