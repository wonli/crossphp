<?php
/**
 * @Auth: wonli <wonli@live.com>
 * AdminBase.php
 *
 * 管理登录和退出
 */
namespace app\admin\controllers;

use Cross\MVC\Controller;
use modules\admin\AdminModule;
use modules\admin\SecurityModule;

class Main extends Controller
{
    /**
     * @var SecurityModule
     */
    protected $SEC;

    /**
     * @var AdminModule
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

        //AdminModule
        $this->ADMIN = new AdminModule();
    }

    /**
     * 登录入口
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
                $data ['status'] = 10001;
            }
        }

        //生成安全码
        $data['v'] = $this->SEC->shuffleLocation();
        $this->display($data);
    }

    /**
     * 退出登录
     */
    function logout()
    {
        $_SESSION = array();
        session_destroy();
        $this->to();
    }
}
