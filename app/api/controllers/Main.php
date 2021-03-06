<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\api\controllers;

use Cross\Exception\LogicStatusException;
use Cross\Exception\CoreException;
use Cross\Core\Delegate;

/**
 * @author wonli <wonli@live.com>
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
     * @throws CoreException|LogicStatusException
     */
    function index()
    {
        $data['version'] = Delegate::getVersion();
        $data['t'] = $this->input('t')->int();

        $this->display($data);
    }
}
