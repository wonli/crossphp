<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\controllers;

use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;
use Cross\MVC\Controller;

use app\admin\supervise\AdminUserModule;
use app\admin\supervise\AclModule;
use app\admin\views\AdminView;
use ReflectionException;

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
     * Admin constructor.
     *
     * @throws CoreException
     * @throws ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $loginInfo = $this->getAuth('u', true);
        if (empty($loginInfo)) {
            $this->to();
            return;
        }

        $this->u = $loginInfo['name'];
        $this->uid = $loginInfo['id'];
        $this->rid = $loginInfo['rid'];

        $this->ACL = new AclModule();
        $this->ADMIN = new AdminUserModule();

        //更新登录用户信息
        $this->view->setLoginInfo($loginInfo);

        //保存操作日志
        if ($this->saveActLog) {
            if ($this->isPost()) {
                $type = 'post';
                $actParams = $this->request->getPostData();
            } else {
                $type = 'get';
                $actParams = $this->params;
            }

            if ($this->isAjax()) {
                $type = $type . '|' . 'ajax';
            }

            $this->ADMIN->updateActLog($this->u, $actParams, $type);
        }

        //查询登录用户信息
        $user_info = $this->ADMIN->getAdminInfo(['id' => $this->uid]);
        if (empty($user_info)) {
            $this->to();
            return;
        }

        //用户主题
        if (!empty($user_info['theme'])) {
            $this->setAuth('theme', $user_info['theme']);
        }

        //导航菜单数据
        $nav_menu_data = $this->ACL->getMenu();
        $controller = lcfirst($this->controller);

        //加载菜单icon配置文件
        $icon = $this->parseGetFile('app::config/menu_icon.config.php');
        $tpl_dir_name = $this->config->get('sys', 'default_tpl_dir');
        $icon_config = [];
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
            $accept_behavior = array_combine($accept_behavior, array_pad([], count($accept_behavior), true));

            //只保留允许访问的菜单
            $allow_menu = [];
            $all_accept_action = [];
            foreach ($nav_menu_data as $k => &$nav) {
                $nav_id = &$nav['id'];
                if (!isset($accept_behavior[$nav_id])) {
                    unset($nav_menu_data[$k]);
                } else {
                    $allow_menu[$nav['link']] = $nav['link'];
                    $child_menu = $this->ACL->getMenuByCondition(['pid' => $nav_id]);
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
                if ($this->isAjax()) {
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
     * @param null $data
     * @param string|null $method
     * @param int $http_response_status
     * @throws CoreException
     */
    protected function display($data = null, string $method = null, int $http_response_status = 200): void
    {
        if (!$data instanceof ResponseData) {
            $responseData = parent::getResponseData($data);
        } else {
            $responseData = $data;
        }

        $data = $responseData->getData();
        unset($data[$responseData->getDataName()]);
        $data = array_merge($data, $responseData->getDataContent());

        parent::display($data, $method, $http_response_status);
    }

    /**
     * 返回错误码和错误消息数组
     *
     * @param int $code
     * @param string $msg
     * @return array|string
     * @throws CoreException
     */
    protected function getStatus($code, string $msg = '')
    {
        if (empty($msg)) {
            $msg = $this->getStatusMessage($code);
        }

        $rd = ResponseData::builder();
        $rd->setStatus($code);
        $rd->setMessage($msg);

        return $rd->getData();
    }

    /**
     * 输出JSON格式消息并终止执行
     *
     * @param array $data
     */
    protected function dieJson($data)
    {
        $this->response->setContentType('json')->end(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
