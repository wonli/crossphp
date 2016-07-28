<?php

/**
 * @Author: wonli <wonli@live.com>
 */
namespace app\admin\views;

class AclView extends AdminView
{
    /**
     * 权限控制系统默认页面
     *
     * @param $data
     */
    function index($data)
    {
        if (!empty($data['menu_list'])) {
            $this->renderTpl('acl/index', $data['menu_list']);
        } else {
            $data['status'] = 100026;
        }
    }

    /**
     * 编辑子菜单
     *
     * @param $data
     */
    function editMenu($data)
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
    function navManager($data = array())
    {
        $data['displayConfig'] = array(1 => '');
        $this->renderTpl('acl/nav_manager', $data);
    }

    /**
     * 添加角色
     *
     * @param $data
     */
    function addRole($data)
    {
        $this->renderTpl("acl/add_role", array(
            'menu_list' => $data['menu_list'],
            'menu_select' => array(),
        ));
    }

    /**
     * 编辑角色权限
     *
     * @param $data
     */
    function editRole($data)
    {
        $data['menu_select'] = explode(',', $data['role_info']['behavior']);
        $this->renderTpl('acl/role_edit', $data);
    }

    /**
     * 角色列表
     *
     * @param $data
     */
    function roleList($data)
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
    function user($data)
    {
        if (empty($data['roles'])) {
            $this->text('请先添加角色');
        } else {
            $this->renderTpl('acl/user', $data);
        }
    }
}
