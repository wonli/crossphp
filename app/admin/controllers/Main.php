<?php
/**
 * @author wonli <wonli@live.com>
 * Main.php
 */
namespace app\admin\controllers;

use app\admin\supervise\AdminUserModule;
use app\admin\supervise\SecurityModule;
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
     * @throws \Cross\Exception\CoreException
     */
    function login()
    {
        $data['status'] = 1;
        if ($this->is_post()) {
            if (isset($_POST['user']) && isset($_POST['pwd']) && isset($_POST['v']) && isset($_POST['vv'])) {
                $check_ret = $this->ADMIN->checkAdmin($_POST['user'], $_POST['pwd'], $_POST['v'], $_POST['vv']);
                if ($check_ret['status'] == 1) {
                    $_SESSION['u'] = $_POST['user'];
                    $this->to("panel");
                } else {
                    $data['status'] = $check_ret['status'];
                }
            } else {
                $data ['status'] = 100230;
            }
        }

        //随机安全码坐标
        $data['v'] = $this->SEC->shuffleLocation();
        $this->display($data);
    }

    /**
     * 退出登录
     * 
     * @throws \Cross\Exception\CoreException
     */
    function logout()
    {
        $_SESSION = array();
        session_destroy();
        $this->to();
    }
}
