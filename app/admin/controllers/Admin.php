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
        $userInfo = $this->ADMIN->getAdminInfo(['id' => $this->uid]);
        if (empty($userInfo)) {
            $this->to();
            return;
        }

        //用户主题
        if (!empty($userInfo['theme'])) {
            $this->setAuth('theme', $userInfo['theme']);
        }

        //导航菜单数据
        $navMenuData = $this->ACL->getMenu();
        $controller = lcfirst($this->controller);

        //加载菜单icon配置文件
        $icon = $this->parseGetFile('app::config/menu_icon.config.php');
        $tplDirName = $this->config->get('sys', 'default_tpl_dir');
        $iconConfig = [];
        if (isset($icon[$tplDirName])) {
            $iconConfig = $icon[$tplDirName];
        }

        //权限判断, 超级管理员rid=0
        $roleId = &$userInfo['rid'];
        if ($roleId == 0) {
            $menus = $this->ACL->getNavChildMenu($navMenuData);
            $this->view->setMenuData($menus, $iconConfig);
        } else {
            //所属角色信息
            $roleInfo = $this->ACL->getRoleInfo(array('id' => $roleId));

            //角色权限
            $acceptBehavior = explode(',', $roleInfo ['behavior']);
            $acceptBehavior = array_combine($acceptBehavior, array_pad([], count($acceptBehavior), true));

            //只保留允许访问的菜单
            $allowMenu = [];
            $allAcceptAction = [];
            foreach ($navMenuData as $k => &$nav) {
                $navId = &$nav['id'];
                if (!isset($acceptBehavior[$navId])) {
                    unset($navMenuData[$k]);
                } else {
                    $allowMenu[$nav['link']] = $nav['link'];
                    $childMenu = $this->ACL->getMenuByCondition(['pid' => $navId]);
                    if (!empty($childMenu)) {
                        foreach ($childMenu as $ck => $m) {
                            if (!isset($acceptBehavior[$m['id']])) {
                                unset($childMenu[$ck]);
                            } else {
                                if ($m['display'] == 1) {
                                    $allowMenu[$nav['link']] = $m['link'];
                                }

                                $allAcceptAction[$nav['link']][$m['link']] = true;
                            }
                        }
                    }

                    $nav['child_menu'] = $childMenu;
                }
            }

            //跳转到第一个有权限的action
            if (!isset($navMenuData[$controller])) {
                if (!empty($allowMenu)) {
                    $this->to(key($allowMenu) . ':' . current($allowMenu));
                    return;
                }
            }

            //如果授权__call，所有方法均可访问
            $acceptAction = &$allAcceptAction[$controller];
            if (!isset($acceptAction[$this->action]) && !isset($acceptAction['__call'])) {
                if ($this->isAjax()) {
                    $this->dieJson($this->getStatus(100101));
                    return;
                } else {
                    $this->view->notice(100101);
                }
            }

            //设置导航数据
            $this->view->setMenuData($navMenuData, $iconConfig);
        }
    }

    /**
     * @param null $data
     * @param string|null $method
     * @param int $httpResponseStatus
     * @throws CoreException
     */
    protected function display($data = null, string $method = null, int $httpResponseStatus = 200): void
    {
        if (!$data instanceof ResponseData) {
            $responseData = parent::getResponseData($data);
        } else {
            $responseData = $data;
        }

        $data = $responseData->getData();
        unset($data[$responseData->getDataName()]);
        $data = array_merge($data, $responseData->getDataContent());

        parent::display($data, $method, $httpResponseStatus);
    }

    /**
     * 返回错误码和错误消息数组
     *
     * @param int $code
     * @param string $msg
     * @return array|string
     * @throws CoreException
     */
    protected function getStatus(int $code, string $msg = ''): array
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
    protected function dieJson(array $data)
    {
        $this->response->setContentType('json')->end(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
