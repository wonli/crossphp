<?php
/**
 * @Author:       wonli <wonli@live.com>
 */
namespace app\api\controllers;

use Cross\Core\Delegate;

/**
 * @Auth: wonli <wonli@live.com>
 * Class Main
 * @package app\api\controllers
 *
 * @cp_api_spec 默认
 */
class Main extends Api
{
    /**
     * 默认控制器
     *
     * @cp_api get, /main/index, 获取框架当前版本号
     * @cp_request t|当前时间|1
     */
    function index()
    {
        $data['version'] = Delegate::getVersion();
        $data['t'] = $this->getInputData('t');

        $this->data['data'] = $data;
        $this->display($this->data);
    }
}
