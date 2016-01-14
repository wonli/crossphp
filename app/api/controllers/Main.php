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
 */
class Main extends Api
{
    /**
     * 默认控制器
     */
    function index()
    {
        $this->data ['version'] = Delegate::getVersion();

        $this->display($this->data);
    }
}
