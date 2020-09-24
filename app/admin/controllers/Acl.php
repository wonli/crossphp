<?php
/**
 * @author wonli <wonli@live.com>
 * Acl.php
 */

namespace app\admin\controllers;

use Cross\Exception\LogicStatusException;
use Cross\Exception\FrontException;
use Cross\Exception\CoreException;

use app\admin\supervise\SecurityModule;
use app\admin\supervise\AclModule;

use ReflectionException;

/**
 * 权限管理(菜单,角色及用户)
 * @author wonli <wonli@live.com>
 *
 * Class Acl
 * @package app\admin\controllers
 */
class Acl extends Admin
{
    /**
     * 权限控制module
     *
     * @var AclModule
     */
    protected $ACL;

    /**
     * Acl constructor.
     *
     * @throws CoreException
     * @throws ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $this->ACL = new AclModule;
    }

    /**
     * @throws CoreException
     */
    function index()
    {
        $this->to("acl:navManager");
    }

    /**
     * 子菜单管理
     *
     * @cp_params id
     * @throws CoreException|LogicStatusException
     */
    function editMenu()
    {
        $id = $this->input('id')->uInt();
        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            if (!empty($postData['menu'])) {
                $this->ACL->saveMenu($postData['menu']);
            }

            if (!empty($postData['customMenu'])) {
                $this->ACL->saveMenu($postData['customMenu']);
            }

            $this->returnReferer();
        } else {
            $menuList = $this->ACL->getMenuAllDate($id);
            if (false === $menuList) {
                $this->to('acl');
                return;
            }

            $this->data['menu_list'] = $menuList;
        }

        $this->display($this->data);
    }

    /**
     * 导航菜单管理
     *
     * @throws CoreException
     * @throws ReflectionException
     */
    function navManager()
    {
        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            if (!empty($postData['addNav'])) {
                $this->ACL->saveNav($postData['addNav']);
            }

            if (!empty($postData['nav'])) {
                $this->ACL->saveNav($postData['nav']);
            }

            $this->to('acl:navManager');
            return;
        }

        $unSaveMenu = [];
        $this->ACL->initMenuList();

        $this->data['menu'] = $this->ACL->getNavList($unSaveMenu);
        $this->data['un_save_menu'] = $unSaveMenu;

        $this->display($this->data);
    }

    /**
     * 删除
     *
     * @cp_params id, e
     * @throws CoreException|LogicStatusException
     */
    function del()
    {
        if (!empty($this->input('id')->uInt())) {
            $this->ACL->delNav($this->input('id')->uInt());
        }

        if (!empty($this->input('e')->uInt())) {
            $this->to('acl:editMenu', array('id' => $this->input('e')->uInt()));
            return;
        }

        $this->to('acl:navManager');
    }

    /**
     * 添加管理角色
     *
     * @throws CoreException
     * @throws ReflectionException
     * @throws LogicStatusException
     */
    function addRole()
    {
        $menuList = $this->ACL->initMenuList();
        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            if (!empty($postData['name']) && !empty($postData['menu_id'])) {
                $menuSet = $postData ['menu_id'];
                $ret = $this->ACL->saveRoleMenu($postData['name'], $menuSet);

                if ($ret->getStatus() == 1) {
                    $this->to('acl:roleList');
                    return;
                } else {
                    $this->end($ret['status']);
                    return;
                }
            } else {
                $this->end(100670);
                return;
            }
        }

        $this->data['menu_list'] = $menuList;
        $this->display($this->data);
    }

    /**
     * 角色列表
     *
     * @throws CoreException
     */
    function roleList()
    {
        $this->data ['role_list'] = $this->ACL->getRoleList();
        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            $ret = $this->ACL->editRoleMenu($postData['rid'] ?? '', $postData['name'] ?? '', $postData['menu_id'] ?? '');
            if ($ret->getStatus() == 1) {
                $this->to("acl:roleList");
                return;
            }
        }

        $this->display($this->data);
    }

    /**
     * 编辑角色
     *
     * @cp_params rid
     * @throws CoreException
     * @throws ReflectionException|LogicStatusException
     */
    function editRole()
    {
        if (empty($this->input('rid')->uInt())) {
            $this->to('acl');
            return;
        }

        $rid = $this->input('rid')->uInt();
        $roleInfo = $this->ACL->getRoleInfo(['id' => $rid]);
        if (empty($roleInfo)) {
            $this->to('acl');
            return;
        }

        if ($this->isPost()) {
            $postData = $this->request->getPostData();
            $this->ACL->editRoleMenu($rid, $postData['name'] ?? '', $postData['menu_id'] ?? '');
            $this->to('acl:editRole', ['rid' => $rid]);
            return;
        }

        $menuList = $this->ACL->initMenuList();
        $this->data['role_info'] = $roleInfo;
        $this->data['menu_list'] = $menuList;

        $this->display($this->data);
    }

    /**
     * 删除角色
     *
     * @cp_params rid
     * @throws CoreException|LogicStatusException
     */
    function delRole()
    {
        $isAjax = $this->isAjax();
        $rid = $isAjax ? (int)$this->request->getGetData()['rid'] : $this->input('rid')->uInt();

        $ret = $this->ACL->delRole($rid);
        if ($isAjax) {
            echo (int)$ret;
        } else {
            $this->to('acl:roleList');
        }
    }

    /**
     * 管理员列表
     *
     * @cp_params p=1
     * @throws CoreException
     * @throws LogicStatusException
     */
    function user()
    {
        $page = [
            'p' => $this->input('p')->uInt(),
            'limit' => 10,
        ];

        $this->data ['u'] = $this->ADMIN->getAdminUserList($page);
        $this->data ['page'] = $page;
        $this->data ['roles'] = $this->ACL->getRoleList();

        if ($this->isPost()) {
            $error = 0;
            $postData = $this->request->getPostData();
            $a = $postData['a'] ?? [];
            foreach ($a as $k => $v) {
                $ret = $this->getResponseData(1);
                if (isset($v['t']) && ($v['t'] == 'on' || $v['t'] == 1)) {
                    $v['t'] = 1;
                } else {
                    $v['t'] = 0;
                }

                if (isset($v['usc']) && ($v['usc'] == 'on' || $v['usc'] == 1)) {
                    $v['usc'] = 1;
                } else {
                    $v['usc'] = 0;
                }

                if (0 == strcmp($k, '+')) {
                    if (!empty($v ['name']) && !empty($v ['password'])) {
                        $ret = $this->ADMIN->addAdmin($v);
                    }
                } else {
                    if (!empty($v['name'])) {
                        $ret = $this->ADMIN->update($k, $v);
                    } else {
                        $ret = $this->ADMIN->del(['id' => $k]);
                    }
                }

                $status = $ret->getStatus();
                if ($status != 1) {
                    $error++;
                    $this->end($status);
                    break;
                }
            }

            if ($error == 0) {
                $this->to('acl:user');
                return;
            }
        }

        $this->display($this->data);
    }

    /**
     * 操作密保卡
     *
     * @throws CoreException
     */
    function userSecurityCard()
    {
        $op = $this->input('op')->val();
        $user = $this->input('user')->val();

        $SEC = new SecurityModule();
        if ($op == 'bind') {
            $bind = $SEC->checkBind($user);
            if (!$bind) {
                $SEC->bindCard($user);
            }
        } else {
            $SEC->unBind($user, false);
        }

        $this->to('acl:user');
    }

    /**
     * 删除管理员
     *
     * @cp_params uid
     * @throws CoreException|LogicStatusException
     */
    function delUser()
    {
        $uid = $this->input('uid')->uInt();
        $this->ADMIN->del(['id' => $uid]);
        $this->to('acl:user');
    }
}