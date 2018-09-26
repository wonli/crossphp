<?php
/**
 * @author wonli <wonli@live.com>
 * Web.php
 */

namespace app\web\controllers;


use Cross\MVC\Controller;

abstract class Web extends Controller
{
    /**
     * @var array
     */
    protected $data = array('status' => 0);

    abstract function index();
}
