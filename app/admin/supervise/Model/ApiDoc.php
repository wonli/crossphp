<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


use app\admin\supervise\Model\Table\ApiDocTable;


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

    function __construct()
    {
        parent::__construct(new ApiDocTable());
    }
}