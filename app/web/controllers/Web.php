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
     * @param mixed $data
     * @param string|null $method
     * @param int $httpResponseStatus
     * @throws CoreException
     * @see Controller::display()
     */
    protected function display($data = null, string $method = null, int $httpResponseStatus = 200): void
    {
        $responseData = parent::getResponseData($data);
        if ($responseData->getStatus() != 1) {
            throw new CoreException($responseData->getMessage());
        }

        parent::display($data, $method, $httpResponseStatus);
    }
}
