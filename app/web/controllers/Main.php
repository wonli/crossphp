<?php
/**
 * @author wonli <wonli@live.com>
 */
namespace app\web\controllers;

use Cross\Core\Delegate;
use Cross\Exception\CoreException;

class Main extends Web
{
    /**
     * 默认控制器
     *
     * @throws CoreException
     */
    function index()
    {
        $this->data ['action'] = __FUNCTION__;
        $this->data ['version'] = Delegate::getVersion();

        return $this->display($this->data);
    }
}
