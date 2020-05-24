<?php
/**
 * @author wonli <wonli@live.com>
 * Web.php
 */

namespace app\web\controllers;


use Cross\Exception\CoreException;
use Cross\MVC\Controller;

abstract class Web extends Controller
{
    /**
     * 默认方法
     */
    abstract function index();

    /**
     * 处理页面
     *
     * @param null $data
     * @param string $method
     * @param int $http_response_status
     * @throws CoreException
     * @see Controller::display()
     */
    protected function display($data = null, string $method = null, int $http_response_status = 200): void
    {
        $responseData = parent::getResponseData($data);
        if ($responseData['status'] != 1) {
            throw new CoreException($responseData['message']);
        }

        parent::display($responseData, $method, $http_response_status);
    }
}
