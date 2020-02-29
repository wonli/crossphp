<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\controllers;

use Cross\MVC\Controller;

use app\admin\supervise\AdminUserModule;
use app\admin\supervise\AclModule;
use app\admin\views\AdminView;

/**
 * 管理模块控制器基类(导航菜单及权限验证)
 * @author wonli <wonli@live.com>
 *
 * Class Admin
 * @package app\admin\controllers
 * @property AdminView $view
 */
abstract class Admin extends Controller
{
    /**
     * 管理员登录名
     *
     * @var string
     */
    protected $u;

    /**
     * 管理员用户ID
     *
     * @var int
     */
    protected $uid;

    /**
     * 管理员角色分组ID
     *
     * @var int
     */
    protected $rid;

    /**
     * @var AclModule
     */
    protected $ACL;

    /**
     * @var bool
     */
    protected $saveActLog = true;

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
     * @throws \ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $loginInfo = &$_SESSION['u'];
        $this->u = $loginInfo['name'];
        $this->uid = $loginInfo['id'];
        $this->rid = $loginInfo['rid'];

        $this->ACL = new AclModule();
        $this->ADMIN = new AdminUserModule();

        //保存操作日志
        if ($this->saveActLog) {
            if ($this->is_post()) {
                $type = 'post';
                $actParams = $_POST;
            } else {
                $type = 'get';
                $actParams = $this->params;
            }

            if ($this->is_ajax_request()) {
                $type = $type . '|' . 'ajax';
            }

            $this->ADMIN->updateActLog($this->u, $actParams, $type);
        }

        //查询登录用户信息
        $user_info = $this->ADMIN->getAdminInfo(array('id' => $this->uid));
        if (empty($user_info)) {
            $this->to();
            return;
        }

        //用户主题
        if (!empty($user_info['theme'])) {
            $_SESSION['theme'] = &$user_info['theme'];
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
        $role_id = &$user_info['rid'];
        if ($role_id == 0) {
            $menus = $this->ACL->getNavChildMenu($nav_menu_data);
            $this->view->setMenuData($menus, $icon_config);
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
                $nav_id = &$nav['id'];
                if (!isset($accept_behavior[$nav_id])) {
                    unset($nav_menu_data[$k]);
                } else {
                    $allow_menu[$nav['link']] = $nav['link'];
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
                    $this->to(key($allow_menu) . ':' . current($allow_menu));
                    return;
                }
            }

            //如果授权__call，所有方法均可访问
            $accept_action = &$all_accept_action[$controller];
            if (!isset($accept_action[$this->action]) && !isset($accept_action['__call'])) {
                if ($this->is_ajax_request()) {
                    $this->dieJson($this->getStatus(100101));
                    return;
                } else {
                    $this->view->notice(100101);
                }
            }

            //设置导航数据
            $this->view->setMenuData($nav_menu_data, $icon_config);
        }
    }

    /**
     * 返回错误码和错误消息数组
     *
     * @param int $code
     * @param string $msg
     * @return array|string
     * @throws \Cross\Exception\CoreException
     */
    protected function getStatus($code, $msg = '')
    {
        if (empty($msg)) {
            $msg = $this->getStatusMessage($code);
        }
        return $this->result($code, $msg);
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
    }
}
