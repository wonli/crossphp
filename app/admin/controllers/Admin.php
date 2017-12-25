<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\controllers;

use app\admin\supervise\AdminUserModule;
use app\admin\supervise\AclModule;
use Cross\MVC\Controller;

/**
 * 管理模块控制器基类(导航菜单及权限验证)
 * @author wonli <wonli@live.com>
 *
 * Class Admin
 * @package app\admin\controllers
 */
abstract class Admin extends Controller
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
     * @var AdminUserModule
     */
    protected $ADMIN;

    /**
     * @var array
     */
    protected $data = array('status' => 1);

    /**
     * Admin constructor.
     *
     * @throws \Cross\Exception\CoreException
     */
    function __construct()
    {
        parent::__construct();
        $this->u = &$_SESSION['u'];

        $this->ACL = new AclModule();
        $this->ADMIN = new AdminUserModule();

        //查询登录用户信息
        $user_info = $this->ADMIN->getAdminInfo(array('name' => $this->u));
        if (empty($user_info)) {
            $this->to();
        }

        //导航菜单数据
        $nav_menu_data = $this->ACL->getMenu();
        $controller = lcfirst($this->controller);

        //加载菜单icon配置文件
        $icon = $this->parseGetFile('app::config/menu_icon.config.php');
        $tpl_dir_name = $this->config->get('sys', 'default_tpl_dir');
        $icon_config = array();
        if (isset($icon[$tpl_dir_name])) {
            $icon_config = $icon[$tpl_dir_name];
        }

        //权限判断, 超级管理员rid=0
        $child_menu = array();
        $role_id = $user_info['rid'];
        if ($role_id == 0) {
            $this->view->setNavMenu($nav_menu_data);
            $all_menu = $this->ACL->getNavChildMenu($nav_menu_data);

            if (isset($nav_menu_data [$controller])) {
                $child_menu = $all_menu[$controller]['child_menu'];
            }

            $this->view->setMenu($child_menu);
            $this->view->setAllMenu($all_menu, $icon_config);
        } else {
            //所属角色信息
            $role_info = $this->ACL->getRoleInfo(array('id' => $role_id));

            //角色权限
            $accept_behavior = explode(',', $role_info ['behavior']);
            $accept_behavior = array_combine($accept_behavior, array_pad(array(), count($accept_behavior), true));

            //只保留允许访问的菜单
            $allow_menu = array();
            $all_accept_action = array();
            foreach ($nav_menu_data as $k => &$nav) {
                $nav_id = $nav['id'];
                if (!isset($accept_behavior[$nav_id])) {
                    unset($nav_menu_data[$k]);
                } else {
                    $child_menu = $this->ACL->getMenuByCondition(array('pid' => $nav_id));
                    if (!empty($child_menu)) {
                        foreach ($child_menu as $ck => $m) {
                            if (!isset($accept_behavior[$m['id']])) {
                                unset($child_menu[$ck]);
                            } else {
                                if ($m['display'] == 1) {
                                    $allow_menu[$nav['link']] = $m['link'];
                                }

                                $all_accept_action[$nav['link']][$m['link']] = true;
                            }
                        }
                    }

                    $nav['child_menu'] = $child_menu;
                }
            }

            //跳转到第一个有权限的action
            if (!isset($nav_menu_data[$controller])) {
                if (!empty($allow_menu)) {
                    list($controller, $action) = each($allow_menu);
                    $this->to("{$controller}:{$action}");
                }
            }

            $accept_action = &$all_accept_action[$controller];
            if (!isset($accept_action[$this->action])) {
                if ($this->is_ajax_request()) {
                    $this->dieJson($this->getStatus(100101));
                } else {
                    $this->view->notice(100101);
                    exit(0);
                }
            }

            //设置导航数据
            $this->view->setNavMenu($nav_menu_data);
            $this->view->setMenu($child_menu);
            $this->view->setAllMenu($nav_menu_data, $icon_config);
        }
    }

    /**
     * 返回错误码和错误消息数组
     *
     * @param int $code
     * @return array|string
     * @throws \Cross\Exception\CoreException
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
     * @throws \Cross\Exception\CoreException
     */
    protected function getStatusMessage($code)
    {
        $code_config = $this->parseGetFile('config::notice.config.php');
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
