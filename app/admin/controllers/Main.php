<?php
/**
 * @author wonli <wonli@live.com>
 * Main.php
 */

namespace app\admin\controllers;

use app\admin\supervise\AdminUserModule;
use app\admin\supervise\SecurityModule;
use Cross\Exception\CoreException;
use Cross\MVC\Controller;

/**
 * 登录和退出
 *
 * @author wonli <wonli@live.com>
 *
 * Class Main
 * @package app\admin\controllers
 */
class Main extends Controller
{
    /**
     * @var SecurityModule
     */
    protected $SEC;

    /**
     * @var AdminUserModule
     */
    protected $ADMIN;

    /**
     * 设置layer
     */
    function __construct()
    {
        parent::__construct();

        //安全管理的module
        $this->SEC = new SecurityModule();

        //AdminUserModule
        $this->ADMIN = new AdminUserModule();
    }

    /**
     * 登录入口
     *
     * @throws CoreException
     */
    function login()
    {
        $data['status'] = 1;
        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            if (isset($postData['user']) && isset($postData['pwd']) && isset($postData['v']) && isset($postData['vv'])) {
                $checkRet = $this->ADMIN->checkAdmin($postData['user'], $postData['pwd'], $postData['v'], $postData['vv']);
                if ($checkRet->getStatus() == 1) {
                    $this->setAuth('u', $checkRet->getDataContent());
                    $this->to('panel');
                    return;
                } else {
                    $data['status'] = $checkRet->getStatus();
                }
            } else {
                $data['status'] = 100230;
            }
        }

        //随机安全码坐标
        $data['v'] = $this->SEC->shuffleLocation();
        $this->display($data);
    }

    /**
     * 退出登录
     *
     * @throws CoreException
     */
    function logout()
    {
        $this->setAuth('u', '');
        $this->to();
    }
}
