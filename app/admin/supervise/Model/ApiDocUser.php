<?php

namespace app\admin\supervise\Model;

use Cross\Model\SQLModel;


use app\admin\supervise\Model\Table\ApiDocUserTable;


class ApiDocUser extends SQLModel
{
    public $id = null;
    public $u = null;
    public $doc_id = null;
    public $name = null;
    public $value = null;

    function __construct()
    {
        parent::__construct(new ApiDocUserTable());
    }
}