<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


class ApiDocUser extends SQLModel
{
    public $id = null;
    public $u = null;
    public $doc_id = null;
    public $name = null;
    public $value = null;

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
        'table' => 'cpa_doc_user',
        'connect' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'pass' => 123456,
            'prefix' => '',
            'charset' => 'utf8',
            'name' => 'test'
        ]
    ];

    /**
     * 表字段属性
     *
     * @var array
     */
    protected $fieldsInfo = [
        'id' => ['primary' => true, 'is_index' => 'PRI', 'auto_increment' => true, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'u' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'doc_id' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => 0, 'not_null' => true, 'comment' => ''],
        'name' => ['primary' => false, 'is_index' => 'MUL', 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
        'value' => ['primary' => false, 'is_index' => false, 'auto_increment' => false, 'default_value' => '', 'not_null' => true, 'comment' => ''],
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