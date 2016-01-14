<?php
/**
 * @Author: wonli <wonli@live.com>
 *
 * 除了Main控制器之外的基类
 */
namespace app\admin\controllers;

use Cross\Core\Loader;
use Cross\MVC\Controller;
use modules\admin\AclModule;
use modules\admin\AdminModule;

class Admin extends Controller
{
    /**
     * @var string
     */
    protected $u;

    /**
     * @var AclModule
     */
    protected $ACL;

    /**
     * @var AdminModule
     */
    protected $ADMIN;

    /**
     * @var array
     */
    protected $data = array('status' => 1);

    function __construct()
    {
        parent::__construct();
        $this->u = $_SESSION['u'];

        $this->ACL = new AclModule();
        $this->ADMIN = new AdminModule();

        /**
         * 查询登录用户信息
         */
        $user_info = $this->ADMIN->getAdminInfo(array('name' => $this->u));
        $role_id = $user_info['rid'];

        /**
         * 导航菜单
         */
        $nav_menu_data = $this->ACL->getMenu();
        $controller = lcfirst($this->controller);

        /**
         * 菜单icon
         */
        $icon = Loader::read(Loader::getFilePath('::config/menu_icon.config.php'));
        $tpl_dir_name = $this->config->get('sys', 'default_tpl_dir');
        $icon_config = array();
        if (isset($icon[$tpl_dir_name])) {
            $icon_config = $icon[$tpl_dir_name];
        }

        /**
         * 判断是否是超级管理员
         */
        if ($role_id == 0) {
            /**
             * 设置view导航数据
             */
            $this->view->setNavMenu($nav_menu_data);
            $all_menu = $this->ACL->getNavChildMenu($nav_menu_data);

            $child_menu = array();
            if (isset($nav_menu_data [$controller])) {
                $child_menu = $all_menu[$controller]['child_menu'];
            }

            $this->view->setMenu($child_menu);
            $this->view->setAllMenu($all_menu, $icon_config);
        } else {
            /**
             * 查询所属管理角色
             */
            $role_info = $this->ACL->getRoleInfo(array('id' => $role_id));

            /**
             * 角色允许的方法
             */
            $accept_behavior = explode(',', $role_info ['behavior']);

            /**
             * 只保留允许访问的菜单
             */
            foreach ($nav_menu_data as $k => $nav) {
                if (!in_array($nav['id'], $accept_behavior)) {
                    unset($nav_menu_data[$k]);
                }
            }

            /**
             * 设置view导航数据
             */
            $this->view->setNavMenu($nav_menu_data);
            $all_menu = $this->ACL->getNavChildMenu($nav_menu_data);
            $this->view->setAllMenu($all_menu, $icon_config);

            $child_menu = array();
            if (isset($nav_menu_data [$controller])) {
                $child_menu = $all_menu[$controller]['child_menu'];
            } else {
                //如果没有访问权限 使用有权限的第一个菜单
                $accept_menus = array_keys($nav_menu_data);
                if (! empty($accept_menus)) {
                    $this->to($accept_menus[0]);
                }

                $this->to();
            }

            $accept_action = array();
            foreach ($child_menu as $c_key => $c_value) {
                //过滤无权限的菜单
                if (!in_array($c_value ['id'], $accept_behavior)) {
                    unset($child_menu [$c_key]);
                } else {
                    $accept_action [] = $c_value ['link'];
                }
            }

            $this->view->setMenu($child_menu);

            //都没有权限执行action时,跳转到第一个有权限的action
            if (!in_array($this->action, $accept_action)) {
                if ($this->is_ajax_request()) {
                    $this->dieJson($this->getStatus(100030));
                } else {
                    $this->view->notice(100030);
                    exit(0);
                }
            }
        }
    }

    /**
     * 返回错误码和错误消息数组
     *
     * @param int $code
     * @return array|string
     */
    protected function getStatus($code)
    {
        return $this->result($code, $this->getStatusMessage($code));
    }

    /**
     * 根据错误码返回错误消息内容
     *
     * @param int $code
     * @return string
     */
    protected function getStatusMessage($code)
    {
        $code_config = Loader::read("::config/notice.config.php");
        if (isset($code_config[$code])) {
            $message = $code_config[$code];
        } else {
            $message = '未知错误 ' . $code;
        }

        return $message;
    }

    /**
     * 输出JSON格式消息并终止执行
     *
     * @param array $data
     */
    protected function dieJson($data)
    {
        $this->response->setContentType('json')->displayOver(json_encode($data));
        exit(0);
    }
}
