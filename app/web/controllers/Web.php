<?php
/**
 * @author wonli <wonli@live.com>
 * Web.php
 */

namespace app\web\controllers;


use Cross\Exception\CoreException;
use Cross\Interactive\DataFilter;
use Cross\MVC\Controller;

abstract class Web extends Controller
{
    /**
     * 默认方法
     */
    abstract function index();

    /**
     * 获取输入数据
     *
     * @param string $key
     * @param mixed $default
     * @return DataFilter
     */
    function input(string $key, $default = null): DataFilter
    {
        $val = '';
        $dataContainer = array_merge($this->params, $this->request->getRequestData());
        if (is_array($dataContainer)) {
            $val = $dataContainer[$key] ?? null;
        }

        if (empty($val) && null !== $default) {
            $val = $default;
        }

        return new DataFilter($val);
    }

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
