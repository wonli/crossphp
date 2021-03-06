<?php
/**
 * @author wonli <wonli@live.com>
 * Panel.php
 */

namespace app\admin\controllers;

use Cross\Exception\CoreException;

/**
 * 登录成功后默认跳转到空白的面板
 *
 * @author wonli <wonli@live.com>
 *
 * Class Panel
 * @package app\admin\controllers
 */
class Panel extends Admin
{
    /**
     * @throws CoreException
     */
    function index()
    {
        $this->display($this->data);
    }
}
