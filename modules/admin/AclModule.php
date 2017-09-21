<?php
/**
 * @Author: wonli <wonli@live.com>
 */

namespace modules\admin;

use ReflectionMethod;
use ReflectionClass;

/**
 * 权限处理
 *
 * @Auth: wonli <wonli@live.com>
 * Class AclModule
 * @package modules\admin
 */
class AclModule extends AdminModule
{
    /**
     * 增加导航菜单
     *
     * @param $name
     * @param $link
     * @param int $pid
     * @return array|string
     */
    function addNav($name, $link, $pid = 0)
    {
        $menu_id = $this->link->add($this->t_acl_menu, array(
            'pid' => $pid,
            'name' => $name,
            'link' => $link
        ));

        if ($menu_id) {
            return $this->result(1, "保存成功");
        } else {
            return $this->result(-1, "保存失败,请联系管理员");
        }
    }

    /**
     * 删除导航
     *
     * @param $nav_id
     * @return mixed
     */
    function delNav($nav_id)
    {
        return $this->delNavByCondition(array('id = ? or pid = ?', array($nav_id, $nav_id)));
    }

    /**
     * 初始化菜单
     *
     * @return mixed
     */
    function initMenuList()
    {
        //排除的类名称(基类,登录类等)
        $ingot_controller = array(
            'Cross\MVC\Controller',
            'Cross\Core\FrameBase',
            'app\admin\controllers\Admin',
            'app\admin\controllers\Main'
        );

        //要过滤的类方法
        $ingot_action = array(
            '__construct',
            '__destruct',
            '__toString',
            '__call',
            '__set',
            '__isset',
            '__unset',
            '__sleep',
            '__wakeup',
            '__invoke',
            '__clone',
            '__set_state',
            '__debug_info',
            '__get'
        );

        //一级导航菜单
        $menu_list = $this->getMenuList(0);
        foreach ($menu_list as & $m) {
            //获取子菜单数据及整理菜单格式
            $c_menu_list = array();
            $c_menu_data = $this->getMenuList($m['id']);
            foreach ($c_menu_data as $cm) {
                $c_menu_list[$cm['link']] = $cm;
            }

            if ($m['type'] == 1) {
                //控制器文件物理路径
                $m['method'] = array();
                $controller_name = ucfirst($m['link']);
                $controller_file = $this->getFilePath("app::controllers/{$controller_name}.php");
                if (file_exists($controller_file)) {
                    //反射取得类中的方法列表
                    $fullName = "app\\" . parent::getConfig()->get('app', 'name') . '\\controllers\\' . $controller_name;
                    $rc = new ReflectionClass($fullName);
                    $method = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

                    //清理无效的类方法并整理自定义菜单
                    foreach ($c_menu_list as $cm_key => $cm_value) {
                        if ($cm_value['type'] == 1) {
                            if (!$rc->hasMethod($cm_key)) {
                                unset($c_menu_list[$cm_key]);
                                $this->delNav($cm_value['id']);
                            }
                        } else {
                            $m['method'][$cm_value['link']] = $cm_value;
                        }
                    }

                    foreach ($method as $mm) {

                        if ($mm->class != $fullName) {
                            continue;
                        }

                        if (in_array($mm->class, $ingot_controller)) {
                            continue;
                        }

                        //过滤
                        if (!in_array($mm->name, $ingot_action)) {
                            if (isset($c_menu_list[$mm->name])) {
                                $m['method'][$mm->name] = $c_menu_list[$mm->name];
                            } else {
                                $add_data = array();
                                $this->addClassMethodMenu(lcfirst($controller_name), $mm->name, $add_data);
                                $m['method'][$mm->name] = $add_data;
                            }
                        }
                    }
                } else {
                    //删除不存在的控制器菜单和子菜单
                    $this->delNav($m['id']);
                }
            } else {
                $m['method'] = $c_menu_list;
            }
        }

        return $menu_list;
    }

    /**
     * 获取菜单及子菜单数据
     *
     * @param int $id
     * @return array
     * @throws \Cross\Exception\CoreException
     */
    function getMenuAllDate($id)
    {
        $data = $this->link->select('*')
            ->from("{$this->t_acl_menu} where id={$id} union all select * from {$this->t_acl_menu} where pid={$id}")
            ->stmt()->fetchAll(\PDO::FETCH_ASSOC);

        $main = $child = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['id'] == $id) {
                    $main = $d;
                }

                if ($d['pid'] == $id) {
                    $child[$d['link']] = $d;
                }
            }

            $main['method'] = $child;
        }

        return $main;
    }

    /**
     * 保存导航菜单
     *
     * @param $params
     * @return bool
     */
    function saveNav(array $params)
    {
        foreach ($params as $p) {
            if (empty($p['name']) || empty($p['link'])) {
                continue;
            }

            $data = array(
                'name' => $p['name'],
                'link' => $p['link'],
                'pid' => !empty($p['pid']) ? (int)$p['pid'] : 0,
                'type' => !empty($p['type']) ? (int)$p['type'] : 1,
                '`order`' => !empty($p['order']) ? (int)$p['order'] : 0,
                'display' => !empty($p['display']) ? (int)$p['display'] : 0
            );

            if (isset($p['id'])) {
                $this->link->update($this->t_acl_menu, $data, array('id' => $p['id']));
            } else {
                $this->link->add($this->t_acl_menu, $data);
            }
        }
        return true;
    }

    /**
     * 返回菜单列表
     *
     * @return array
     */
    function getMenu()
    {
        $menu_list = array();
        $count = $this->link->get($this->t_acl_menu, 'count(1) cnt', array('pid' => 0));

        if (!$count['cnt'] || $count['cnt'] == 0) {
            $this->initMenu4controllers();
        }

        $menu = $this->link->getAll($this->t_acl_menu, '*', array('pid' => 0), '`order` ASC');
        foreach ($menu as $m) {
            $menu_list[$m["link"]] = $m;
        }

        return $menu_list;
    }

    /**
     * 获取导航子菜单
     *
     * @param $nav_menu
     * @return mixed
     */
    function getNavChildMenu($nav_menu)
    {
        foreach ($nav_menu as & $m) {
            $m['child_menu'] = $this->getMenuByCondition(array('pid' => $m['id']));
        }

        return $nav_menu;
    }

    /**
     * 从控制器中初始化菜单数据
     */
    function initMenu4controllers()
    {
        $nav_data = $this->scanControllers();
        $this->saveNav($nav_data);
    }

    /**
     * 菜单修改(批量更新导航菜单)
     *
     * @param array $menu
     */
    function saveMenu(array $menu)
    {
        //已经保存在数据库中的菜单
        $menu_list = array();
        $menu_data = $this->getMenuList();
        foreach ($menu_data as $m) {
            if ($m['pid'] == 0) {
                $menu_list[$m['id']] = array();
            }
        }

        foreach ($menu_data as $ml) {
            $menu_list[$ml['pid']][$ml['link']] = $ml;
        }

        foreach ($menu as $pid => $current_menu_data) {
            if (isset($menu_list[$pid])) {
                $be_change = $menu_list[$pid];
                foreach ($current_menu_data as $change_key => $change_value) {
                    $name = trim($change_value['name']);
                    $type = isset($change_value['type']) ? (int)$change_value['type'] : 1;
                    $order = empty($change_value['order']) ? 0 : (int)$change_value['order'];
                    $display = isset($change_value['display']) && $change_value['display'] == 'on' ? 1 : 0;
                    $link = isset($change_value['link']) ? $change_value['link'] : $change_key;

                    if (isset($be_change[$change_key])) {
                        $id = $be_change[$change_key]['id'];
                        if (empty($change_value['link'])) {
                            $this->delNav($id);
                        } else {
                            $update = array(
                                'name' => $name,
                                'link' => $link,
                                '`order`' => $order,
                                'display' => $display,
                            );
                            $this->link->update($this->t_acl_menu, $update, array('id' => $id));
                        }
                    } else {
                        $add_data = array(
                            'pid' => $pid,
                            'type' => $type,
                            'name' => $name,
                            'link' => $link,
                            'order' => $order,
                            'display' => $display
                        );

                        $this->addAclMenuFunction($add_data);
                    }
                }
            }
        }
    }

    /**
     * 给类添加子菜单
     *
     * @param $class
     * @param $method
     * @param array $menu_data
     */
    function addClassMethodMenu($class, $method, &$menu_data = array())
    {
        $class_menu_pid = $this->link->get($this->t_acl_menu, 'id', array(
            'pid' => 0,
            'link' => lcfirst($class),
        ));

        if (!empty($class_menu_pid['id'])) {
            $add_data['pid'] = $class_menu_pid['id'];
            $add_data['link'] = $method;
            $id = $this->addAclMenuFunction($add_data);
            if (false !== $id) {
                $add_data['id'] = $id;
                $menu_data = $add_data;
            }
        }
    }

    /**
     * 添加二级导航菜单
     *
     * @param $data
     * @return bool
     */
    function addAclMenuFunction(&$data)
    {
        if (!$data['pid'] || !$data['link']) {
            return false;
        }

        $add_data['pid'] = $data['pid'];
        $add_data['link'] = $data['link'];
        $add_data['name'] = empty($data['name']) ? '' : $data['name'];
        $add_data['`order`'] = empty($data['order']) ? 0 : (int)$data['order'];
        $add_data['display'] = isset($data['display']) ? $data['display'] : 0;
        $add_data['type'] = isset($data['type']) ? $data['type'] : 1;
        $data = $add_data;
        return $this->link->add($this->t_acl_menu, $add_data);
    }

    /**
     * @return mixed 角色列表
     */
    function getRoleList()
    {
        return $this->link->getAll($this->t_role, '*');
    }

    /**
     * 查询role详细信息
     *
     * @param $condition
     * @return mixed
     */
    function getRoleInfo($condition)
    {
        return $this->link->get($this->t_role, '*', $condition);
    }

    /**
     * 删除角色
     *
     * @param int $rid
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function delRole($rid)
    {
        return $this->link->del($this->t_role, array('id' => $rid));
    }

    /**
     * 保存菜单设置
     *
     * @param $menu_name
     * @param $data
     * @return array|string
     */
    function saveRoleMenu($menu_name, $data)
    {
        if (!$menu_name) {
            return $this->result(100610);
        }

        if (empty($data)) {
            return $this->result(100620);
        }

        $save_data ['name'] = $menu_name;
        $save_data ['behavior'] = implode($data, ',');
        $role_info = $this->link->get($this->t_role, '*', array('name' => $menu_name));
        if ($role_info) {
            return $this->result(100630);
        }

        $rid = $this->link->add($this->t_role, $save_data);
        if ($rid) {
            return $this->result(1, $rid);
        }

        return $this->result(100640);
    }

    /**
     * 编辑角色菜单权限
     *
     * @param $rid
     * @param $menu_name
     * @param $data
     * @return array|string
     */
    function editRoleMenu($rid, $menu_name, $data)
    {
        if (!$menu_name) {
            return $this->result(100610);
        }

        $save_data ['name'] = $menu_name;
        if (empty($data)) {
            $save_data['behavior'] = '';
        } else {
            $save_data['behavior'] = trim(implode($data, ','));
        }

        $role_info = $this->link->get($this->t_role, '*', array('id' => $rid));
        if (!$role_info) {
            return $this->result(100650);
        }

        $rid = $role_info['id'];
        $status = $this->link->update($this->t_role, $save_data, array('id' => $rid));
        if ($status) {
            return $this->result(1, $rid);
        }

        return $this->result(100660);
    }

    /**
     * 根据菜单ID获取信息
     *
     * @param $id
     * @return mixed
     */
    function getMenuInfo($id)
    {
        return $this->link->get($this->t_acl_menu, '*', array('id' => (int)$id));
    }

    /**
     * 导航菜单列表
     *
     * @param null $pid
     * @return mixed
     */
    function getMenuList($pid = null)
    {
        $condition = array();
        if (null !== $pid) {
            $condition['pid'] = $pid;
        }

        return $this->getMenuByCondition($condition, '`order` ASC, type ASC');
    }

    /**
     * 一级菜单列表
     *
     * @param array $un_save_menu
     * @return mixed
     */
    function getNavList(& $un_save_menu = array())
    {
        $controllers_lists = $this->scanControllers();
        $saved_menus = $this->getMenuByCondition(array('pid' => 0), '`order` ASC, type ASC');

        $saved_menu_link_hash = array();
        array_map(function ($m) use (&$saved_menu_link_hash) {
            $saved_menu_link_hash[$m['link']] = true;
        }, $saved_menus);

        foreach ($controllers_lists as $c) {
            if (!isset($saved_menu_link_hash[$c['link']])) {
                $un_save_menu[] = $c;
            }
        }

        return $saved_menus;
    }

    /**
     * 根据父级id查询子菜单
     *
     * @param $pid
     * @return mixed
     */
    function getChildMenu($pid)
    {
        return $this->getMenuByCondition(array('pid' => $pid));
    }

    /**
     * 根据条件查询菜单
     *
     * @param array|string $condition
     * @param array|string $order
     * @return array
     */
    function getMenuByCondition($condition, $order = '`order` ASC')
    {
        $result = $this->link->getAll($this->t_acl_menu, '*', $condition, $order);
        if (empty($result)) {
            $result = array();
        }

        return $result;
    }

    /**
     * 按条件删除导航菜单
     *
     * @param $condition
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    private function delNavByCondition($condition)
    {
        return $this->link->del($this->t_acl_menu, $condition);
    }

    /**
     * 扫描控制器文件
     *
     * @param bool $hashMap
     * @return array
     */
    private function scanControllers($hashMap = false)
    {
        $nav_data = array();
        $controller_file = $this->getFilePath('app::controllers');
        foreach (glob(rtrim($controller_file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') as $f) {
            $fi = pathinfo($f);
            $class_name = $fi['filename'];
            $menuName = lcfirst($class_name);

            $fullName = "app\\" . parent::getConfig()->get('app', 'name') . '\\controllers\\' . $class_name;
            $rc = new ReflectionClass($fullName);

            $display = 1;
            if ($rc->isAbstract() || $menuName == 'main') {
                $display = 0;
            }

            $ori_nav_data = array(
                'name' => $menuName,
                'link' => $menuName,
                'display' => $display
            );

            if ($hashMap) {
                $nav_data[$ori_nav_data['link']] = $ori_nav_data;
            } else {
                $nav_data[] = $ori_nav_data;
            }
        }

        return $nav_data;
    }

}
