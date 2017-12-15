<?php
/**
 * @Author:       wonli <wonli@live.com>
 */
namespace app\web\controllers;

use Cross\Core\Delegate;

class Main extends Web
{
    /**
     * 默认控制器
     *
     * @throws \Cross\Exception\CoreException
     */
    function index()
    {
        $this->data ['action'] = __FUNCTION__;
        $this->data ['version'] = Delegate::getVersion();

        $this->display($this->data);
    }
}
