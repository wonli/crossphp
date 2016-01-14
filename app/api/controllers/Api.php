<?php
/**
 * @Auth: wonli <wonli@live.com>
 * Web.php
 */

namespace app\api\controllers;


use app\api\views\ApiView;
use Cross\MVC\Controller;

/**
 * @Auth: wonli <wonli@live.com>
 * Class Api
 * @package app\api\controllers
 */
abstract class Api extends Controller
{
    /**
     * @var array
     */
    protected $data = array('status' => 0);

    /**
     * @return mixed
     */
    abstract function index();

    function __construct()
    {
        parent::__construct();
        $this->view = new ApiView();
    }
}
