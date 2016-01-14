<?php
/**
 * @Author: wonli <wonli@live.com>
 */
namespace modules\admin;

use ReflectionClass;
use ReflectionMethod;
use Cross\MVC\Module;
use Cross\Core\Loader;

/**
 * 权限处理
 *
 * @Auth: wonli <wonli@live.com>
 * Class AclModule
 * @package modules\admin
 */
class AclModule extends Module
{
    /**
     * @var string 角色表名
     */
    protected $t_role = 'cp_acl_role';

    /**
     * @var string 表名
     */
    protected $t_acl_menu = 'cp_acl_menu';

    /**
     * @var string 行为表
     */
    protected $t_behavior = 'cp_acl_behavior';

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
        $menu_id = $this->link->add(
            $this->t_acl_menu,
            array(
                'pid' => $pid,
                'name' => $name,
                'link' => $link,
            )
        );

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
        return $this->link->del(
            $this->t_acl_menu,
            array(
                'id' => $nav_id
            )
        );
    }

    /**
     * 初始化菜单
     *
     * @return mixed
     */
    function initMenuList()
    {
        /**
         * 要过滤的方法
         */
        $_filter = array(
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

        /**
         * 所有导航菜单
         */
        $menu_list = $this->getMenuList(0);

        foreach ($menu_list as & $m) {
            $controller_name = ucfirst($m["link"]);

            /**
             * 控制器文件物理路径
             */
            $controller_file = Loader::getFilePath("app::controllers/{$controller_name}.php");

            /**
             * 获取子菜单数据及整理菜单格式
             */
            $c_menu_data = $this->getMenuList($m["id"]);
            $c_menu_list = array();

            foreach ($c_menu_data as $cm) {
                $c_menu_list[$cm['link']] ['id'] = $cm ['id'];
                $c_menu_list[$cm['link']] ['pid'] = $cm ['pid'];
                $c_menu_list[$cm['link']] ['name'] = $cm ['name'];
                $c_menu_list[$cm['link']] ['order'] = $cm['order'];
                $c_menu_list[$cm['link']] ['display'] = $cm ['display'];
            }

            /**
             * 判断物理文件是否存在
             */
            if (file_exists($controller_file)) {
                /**
                 * 使用反射API 取得类中的名称
                 */
                $fullName = "app\\" . parent::getConfig()->get('app', 'name') . '\\controllers\\' . $controller_name;
                $rc = new ReflectionClass($fullName);
                $method = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

                /**
                 * 清除类中不存在但存在数据库中的方法
                 */
                foreach ($c_menu_list as $cm_key => $cm_value) {
                    if (!$rc->hasMethod($cm_key)) {
                        unset($c_menu_list[$cm_key]);
                        $this->delNav($cm_value['id']);
                    }
                }

                foreach ($method as $mm) {
                    if ($mm->class == $fullName) {
                        /**
                         * 类名称是否在过滤列表
                         */
                        if (!in_array($mm->name, $_filter)) {
                            if (isset($c_menu_list [$mm->name])) {
                                $m ["method"][$mm->name] = $c_menu_list [$mm->name];
                            } else {
                                $add_data = array();
                                $this->addClassMethodMenu($mm->class, $mm->name, $add_data);
                                $m ["method"][$mm->name] = $add_data;
                            }
                        }
                    }
                }

            } else {
                $m["error"] = "-1";
                $m["method"] = array();
            }
        }

        return $menu_list;
    }

    /**
     * 保存导航菜单
     *
     * @param $params
     * @return bool
     */
    function saveNav($params)
    {
        foreach ($params as $p) {
            if (empty($p['name']) || empty($p['link'])) {
                continue;
            }

            $data = array(
                'name' => $p ['name'],
                'link' => $p ['link'],
                '`order`' => !empty($p['order']) ? (int)$p ['order'] : 0,
                'status' => !empty($p ['status']) ? (int)$p ['status'] : 0,
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
            $m['child_menu'] = $this->getChildMenuByCondition(array('pid' => $m['id']));
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
        $menu_data = $this->getMenuList();
        $menu_list2 = array();
        foreach ($menu_data as $m) {
            if ($m ['pid'] == 0) {
                $menu_list2 [$m['id']] = array();
            }
        }

        foreach ($menu_data as $ml) {
            $menu_list2 [$ml['pid']] [$ml ['link']] = $ml;
        }

        $menu_list = $menu_list2;
        unset($menu_list2);
        foreach ($menu as $pid => $change_data) {
            if (isset($menu_list [$pid])) {
                $be_change = $menu_list [$pid];

                foreach ($change_data as $change_key => $change_value) {
                    if (isset($be_change [$change_key])) {
                        //更新
                        $_change ['name'] = $change_value['name'];
                        $_change ['display'] = isset($change_value ['display']) && $change_value ['display'] == 'on' ? 1 : 0;
                        $_change ['`order`'] = empty($change_value ['order']) ? 0 : intval($change_value ['order']);

                        $this->link->update(
                            $this->t_acl_menu,
                            $_change,
                            array(
                                'id' => $be_change [$change_key] ['id']
                            )
                        );
                    } else {
                        //新增方法
                        $change_value['pid'] = $pid;
                        $change_value ['link'] = $change_key;
                        $change_value ['display'] = isset($change_value ['display']) && $change_value ['display'] == 'on' ? 1 : 0;

                        $this->addAclMenuFunction($change_value);
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
    function addClassMethodMenu($class, $method, & $menu_data = array())
    {
        $class_menu_pid = $this->link->get(
            $this->t_acl_menu,
            'id',
            array(
                'pid' => 0,
                'link' => lcfirst($class),
            )
        );

        if (!empty($class_menu_pid['id'])) {
            $add_data ['pid'] = $class_menu_pid['id'];
            $add_data ['link'] = $method;
            $id = $this->addAclMenuFunction($add_data);

            if (false !== $id) {
                $add_data ['id'] = $id;
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
    function addAclMenuFunction($data)
    {
        if (!$data ['pid'] || !$data ['link']) {
            return false;
        }

        $add_data ['pid'] = $data['pid'];
        $add_data ['link'] = $data ['link'];
        $add_data ['name'] = empty($data ['name']) ? '' : $data['name'];
        $add_data ['`order`'] = empty($data ['order']) ? 0 : intval($data ['order']);
        $add_data ['display'] = isset($data ['display']) ? $data ['display'] : 0;
        $add_data ['type'] = 1;
        $add_data ['status'] = 1;

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
            return $this->result(100018);
        }

        if (empty($data)) {
            return $this->result(100019);
        }

        $save_data ['name'] = $menu_name;
        $save_data ['behavior'] = implode($data, ',');
        $role_info = $this->link->get($this->t_role, "*", array('name' => $menu_name));
        if ($role_info) {
            return $this->result(100020);
        }

        $rid = $this->link->add($this->t_role, $save_data);
        if ($rid) {
            return $this->result(1, $rid);
        }

        return $this->result(100021);
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
            return $this->result(100018);
        }

        if (empty($data)) {
            return $this->result(100019);
        }

        $save_data ['name'] = $menu_name;
        $save_data ['behavior'] = implode($data, ',');
        $role_info = $this->link->get($this->t_role, "*", array('id' => $rid));
        if (!$role_info) {
            return $this->result(100023);
        }

        $rid = $role_info['id'];
        $status = $this->link->update($this->t_role, $save_data, array('id' => $rid));
        if ($status) {
            return $this->result(1, $rid);
        }

        return $this->result(100024);
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
        $params = array();
        if (null !== $pid) {
            $params = array('pid' => $pid);
        }

        $menu_list = $this->link->getAll($this->t_acl_menu, '*', $params, '`order` ASC');
        return $menu_list;
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
        $saved_menus = $this->link->getAll($this->t_acl_menu, '*', array('pid' => '0'), '`order` ASC');

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
        return $this->getChildMenuByCondition(array('pid' => $pid));
    }

    /**
     * 根据条件查询菜单
     *
     * @param array|string $condition
     * @param array|string $order
     * @return array
     */
    function getChildMenuByCondition($condition, $order = '`order` ASC')
    {
        $result = $this->link->getAll($this->t_acl_menu, '*', $condition, $order);

        if (empty($result)) {
            $result = array();
        }

        return $result;
    }

    /**
     * 扫描控制器文件
     *
     * @param bool $hashMap
     * @return array
     */
    private function scanControllers($hashMap = false)
    {
        $controller_file = Loader::getFilePath("app::controllers");
        $nav_data = array();
        foreach (glob(rtrim($controller_file, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') as $f) {
            $fi = pathinfo($f);
            $class_name = $fi['filename'];
            $ori_nav_data = array(
                'name' => lcfirst($class_name),
                'link' => lcfirst($class_name),
                'status' => 1,
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
