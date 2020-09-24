<?php

/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\views;

/**
 * @author wonli <wonli@live.com>
 *
 * Class AclView
 * @package app\admin\views
 */
class AclView extends AdminView
{
    /**
     * 权限控制系统默认页面
     *
     * @param $data
     */
    function index(array $data = [])
    {
        if (!empty($data['menu_list'])) {
            $this->renderTpl('acl/index', $data['menu_list']);
        } else {
            $data['status'] = 100680;
        }
    }

    /**
     * 编辑子菜单
     *
     * @param $data
     */
    function editMenu(array $data = [])
    {
        $menu = &$data['menu_list'];
        $methodList = &$menu['method'];
        unset($menu['method']);

        $this->renderTpl('acl/menu_manager', array('menu' => $menu, 'methodList' => $methodList));
    }

    /**
     * 导航管理视图
     *
     * @param array $data
     */
    function navManager(array $data = [])
    {
        $data['displayConfig'] = array(1 => '');
        $this->renderTpl('acl/nav_manager', $data);
    }

    /**
     * 添加角色
     *
     * @param $data
     */
    function addRole(array $data = [])
    {
        if (!empty($data['menu_list'])) {
            $this->renderTpl("acl/add_role", [
                'menu_list' => $data['menu_list'],
                'menu_select' => [],
            ]);
        }
    }

    /**
     * 编辑角色权限
     *
     * @param $data
     */
    function editRole(array $data = [])
    {
        $data['menu_select'] = explode(',', $data['role_info']['behavior']);
        $this->renderTpl('acl/role_edit', $data);
    }

    /**
     * 角色列表
     *
     * @param $data
     */
    function roleList(array $data = [])
    {
        if (!empty($data['role_list'])) {
            $this->renderTpl('acl/role_list', $data);
        } else {
            $this->text('暂无角色');
        }
    }

    /**
     * ACL用户列表
     *
     * @param $data
     */
    function user(array $data = [])
    {
        if (empty($data['roles'])) {
            $this->text('请先添加角色');
        } else {
            $this->renderTpl('acl/user', $data);
        }
    }

    /**
     * 输出管理员角色选择菜单
     *
     * @param mixed $uid
     * @param string $selectedValue
     */
    protected function roleSelect( $uid, $selectedValue = '')
    {
        $roleData = &$this->data['roles'];
        if (!empty($roleData)) {
            $roleOption = [];
            array_walk($roleData, function ($r) use (&$roleOption) {
                $roleOption[$r['id']] = $r['name'];
            });

            echo $this->select($roleOption, $selectedValue, array(
                'class' => 'form-control',
                'name' => "a[{$uid}][rid]",
            ));
        }
    }

    /**
     * 输出帐号状态选择菜单
     *
     * @param mixed $uid
     * @param string $currentValue
     */
    protected function statusCheckbox($uid, $currentValue = '')
    {
        $boxData['data-on'] = '正常';
        $boxData['data-off'] = '禁用';
        $boxData['data-toggle'] = 'toggle';
        $boxData['data-onstyle'] = 'success';
        $boxData['data-offstyle'] = 'danger';
        $boxData['name'] = "a[{$uid}][t]";
        if ($currentValue == 1) {
            $boxData['checked'] = true;
        }

        echo $this->input('checkbox', $boxData);
    }

    /**
     * 生成解绑选择按钮
     *
     * @param mixed $uid
     * @param string $currentValue
     */
    protected function securityCheckbox($uid, $currentValue = '')
    {
        $boxData['data-on'] = '允许解绑';
        $boxData['data-off'] = '禁止解绑';
        $boxData['data-toggle'] = 'toggle';
        $boxData['data-onstyle'] = 'success';
        $boxData['data-offstyle'] = 'danger';
        $boxData['data-width'] = '90px';
        $boxData['name'] = "a[{$uid}][usc]";
        if ($currentValue == 1) {
            $boxData['checked'] = true;
        }

        echo $this->input('checkbox', $boxData);
    }
}
