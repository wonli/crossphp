<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


use app\admin\supervise\Model\Table\ApiDocDataTable;


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

    function __construct()
    {
        parent::__construct(new ApiDocDataTable());
    }
}