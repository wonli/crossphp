<?php
/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\supervise;

use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;
use PDO;

use ReflectionException;
use ReflectionMethod;
use ReflectionClass;

/**
 * 权限处理
 *
 * @author wonli <wonli@live.com>
 * Class AclModule
 * @package modules\admin
 */
class AclModule extends AdminModule
{
    /**
     * 增加导航菜单
     *
     * @param string $name
     * @param string $link
     * @param int $pid
     * @return array|string
     * @throws CoreException
     */
    function addNav(string $name, string $link, $pid = 0)
    {
        $menuId = $this->link->add($this->tAclMenu, [
            'pid' => $pid,
            'name' => $name,
            'link' => $link
        ]);

        return false !== $menuId;
    }

    /**
     * 删除导航
     *
     * @param int $navId
     * @return mixed
     * @throws CoreException
     */
    function delNav(int $navId)
    {
        return $this->delNavByCondition(['id = ? or pid = ?', [$navId, $navId]]);
    }

    /**
     * 初始化菜单
     *
     * @return mixed
     * @throws CoreException
     * @throws ReflectionException
     */
    function initMenuList()
    {
        //排除的类名称(基类,登录类等)
        $ingotController = [
            'Cross\MVC\Controller',
            'Cross\Core\FrameBase',
            'app\admin\controllers\Admin',
            'app\admin\controllers\Main'
        ];

        //要过滤的类方法
        $ingotAction = [
            '__construct',
            '__destruct',
            '__toString',
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
        ];

        //一级导航菜单
        $menuList = $this->getMenuList(0);
        foreach ($menuList as & $m) {
            //获取子菜单数据及整理菜单格式
            $cMenuList = [];
            $cMenuData = $this->getMenuList($m['id']);
            foreach ($cMenuData as $cm) {
                $cMenuList[$cm['link']] = $cm;
            }

            if ($m['type'] == 1) {
                //控制器文件物理路径
                $m['method'] = [];
                $controllerName = ucfirst($m['link']);
                $controllerFile = $this->getFilePath("app::controllers/{$controllerName}.php");
                if (file_exists($controllerFile)) {
                    //反射取得类中的方法列表
                    $fullName = "app\\" . parent::getConfig()->get('app', 'name') . '\\controllers\\' . $controllerName;
                    $rc = new ReflectionClass($fullName);
                    $method = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

                    //清理无效的类方法并整理自定义菜单
                    foreach ($cMenuList as $cmKey => $cmValue) {
                        if ($cmValue['type'] == 1) {
                            if (!$rc->hasMethod($cmKey)) {
                                unset($cMenuList[$cmKey]);
                                $this->delNav($cmValue['id']);
                            }
                        } else {
                            $m['method'][$cmValue['link']] = $cmValue;
                        }
                    }

                    foreach ($method as $mm) {
                        if ($mm->class != $fullName) {
                            continue;
                        }

                        if (in_array($mm->class, $ingotController)) {
                            continue;
                        }

                        //过滤
                        if (!in_array($mm->name, $ingotAction)) {
                            if (isset($cMenuList[$mm->name])) {
                                $m['method'][$mm->name] = $cMenuList[$mm->name];
                            } else {
                                $addData = [];
                                $this->addClassMethodMenu(lcfirst($controllerName), $mm->name, $addData);
                                $m['method'][$mm->name] = $addData;
                            }
                        }
                    }
                } else {
                    //删除不存在的控制器菜单和子菜单
                    $this->delNav($m['id']);
                }
            } else {
                $m['method'] = $cMenuList;
            }
        }

        return $menuList;
    }

    /**
     * 获取菜单及子菜单数据
     *
     * @param int $id
     * @return array
     * @throws CoreException
     */
    function getMenuAllDate(int $id)
    {
        $data = $this->link->select('*')
            ->from("{$this->tAclMenu} where id={$id} union all select * from {$this->tAclMenu} where pid={$id}")
            ->stmt()->fetchAll(PDO::FETCH_ASSOC);

        $main = $child = [];
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
     * @param array $menus
     * @return bool
     * @throws CoreException
     */
    function saveNav(array $menus)
    {
        foreach ($menus as $p) {
            if (empty($p['name']) || empty($p['link'])) {
                continue;
            }

            $data = [
                'name' => $p['name'],
                'link' => $p['link'],
                'pid' => $p['pid'] ?? 0,
                'type' => $p['type'] ?: 1,
                '`order`' => $p['order'] ?: 0,
                'display' => $p['display'] ?? 0
            ];

            if (isset($p['id'])) {
                $this->link->update($this->tAclMenu, $data, ['id' => $p['id']]);
            } else {
                $this->link->add($this->tAclMenu, $data);
            }
        }
        return true;
    }

    /**
     * 返回菜单列表
     *
     * @return array
     * @throws CoreException
     * @throws ReflectionException
     */
    function getMenu()
    {
        $menuList = [];
        $count = $this->link->get($this->tAclMenu, 'count(1) cnt', ['pid' => 0]);

        if (!$count['cnt'] || $count['cnt'] == 0) {
            $this->initMenu4controllers();
        }

        $menu = $this->link->getAll($this->tAclMenu, '*', ['pid' => 0], '`order` ASC');
        array_map(function ($m) use (&$menuList) {
            $menuList[$m['link']] = $m;
        }, $menu);

        return $menuList;
    }

    /**
     * 获取导航子菜单
     *
     * @param $navMenu
     * @return mixed
     * @throws CoreException
     */
    function getNavChildMenu($navMenu)
    {
        $pidMaps = [];
        $allChildMenu = $this->getMenuByCondition(['pid' => ['<>', 0]]);
        if (!empty($allChildMenu)) {
            array_map(function ($m) use (&$pidMaps) {
                $pidMaps[$m['pid']][] = $m;
            }, $allChildMenu);
        }

        array_walk($navMenu, function (&$m) use ($pidMaps) {
            $m['child_menu'] = [];
            if (isset($pidMaps[$m['id']])) {
                $m['child_menu'] = $pidMaps[$m['id']];
            }
        });

        return $navMenu;
    }

    /**
     * 从控制器中初始化菜单数据
     *
     * @throws CoreException
     * @throws ReflectionException
     */
    function initMenu4controllers()
    {
        //处理类菜单
        $navData = $this->scanControllers();
        $this->saveNav($navData);

        //处理子菜单
        $this->initMenuList();
    }

    /**
     * 菜单修改(批量更新导航菜单)
     *
     * @param array $menu
     * @throws CoreException
     */
    function saveMenu(array $menu)
    {
        //已经保存在数据库中的菜单
        $menuList = [];
        $menuData = $this->getMenuList();
        foreach ($menuData as $m) {
            if ($m['pid'] == 0) {
                $menuList[$m['id']] = [];
            }
        }

        foreach ($menuData as $ml) {
            $menuList[$ml['pid']][$ml['link']] = $ml;
        }

        foreach ($menu as $pid => $currentMenuData) {
            if (isset($menuList[$pid])) {
                $beChange = $menuList[$pid];
                foreach ($currentMenuData as $changeKey => $changeValue) {
                    $name = trim($changeValue['name']);
                    $type = isset($changeValue['type']) ? (int)$changeValue['type'] : 1;
                    $order = empty($changeValue['order']) ? 0 : (int)$changeValue['order'];
                    $display = isset($changeValue['display']) && $changeValue['display'] == 'on' ? 1 : 0;
                    $link = isset($changeValue['link']) ? $changeValue['link'] : $changeKey;

                    if (isset($beChange[$changeKey])) {
                        $id = $beChange[$changeKey]['id'];
                        if (empty($changeValue['link'])) {
                            $this->delNav($id);
                        } else {
                            $update = [
                                'name' => $name,
                                'link' => $link,
                                '`order`' => $order,
                                'display' => $display,
                            ];
                            $this->link->update($this->tAclMenu, $update, ['id' => $id]);
                        }
                    } else {
                        $addData = [
                            'pid' => $pid,
                            'type' => $type,
                            'name' => $name,
                            'link' => $link,
                            'order' => $order,
                            'display' => $display
                        ];

                        $this->addAclMenuFunction($addData);
                    }
                }
            }
        }
    }

    /**
     * 给类添加子菜单
     *
     * @param string $class
     * @param string $method
     * @param array $menuData
     * @throws CoreException
     */
    function addClassMethodMenu(string $class, string $method, array &$menuData = [])
    {
        $classMenuPid = $this->link->get($this->tAclMenu, 'id', [
            'pid' => 0,
            'link' => lcfirst($class),
        ]);

        if (!empty($classMenuPid['id'])) {
            $addData['pid'] = $classMenuPid['id'];
            $addData['link'] = $method;
            $id = $this->addAclMenuFunction($addData);
            if (false !== $id) {
                $addData['id'] = $id;
                $menuData = $addData;
            }
        }
    }

    /**
     * 添加二级导航菜单
     *
     * @param array $data
     * @return bool
     * @throws CoreException
     */
    function addAclMenuFunction(array &$data)
    {
        if (!$data['pid'] || !$data['link']) {
            return false;
        }

        $addData['pid'] = $data['pid'];
        $addData['link'] = $data['link'];
        $addData['name'] = empty($data['name']) ? '' : $data['name'];
        $addData['`order`'] = empty($data['order']) ? 0 : (int)$data['`order`'];
        $addData['display'] = isset($data['display']) ? $data['display'] : 0;
        $addData['type'] = isset($data['type']) ? $data['type'] : 1;
        $data = $addData;
        return $this->link->add($this->tAclMenu, $addData);
    }

    /**
     * 角色列表
     *
     * @return mixed 角色列表
     * @throws CoreException
     */
    function getRoleList()
    {
        return $this->link->getAll($this->tRole, '*');
    }

    /**
     * 查询role详细信息
     *
     * @param array|string $condition
     * @return mixed
     * @throws CoreException
     */
    function getRoleInfo($condition)
    {
        return $this->link->get($this->tRole, '*', $condition);
    }

    /**
     * 删除角色
     *
     * @param int $rid
     * @return bool
     * @throws CoreException
     */
    function delRole(int $rid)
    {
        return $this->link->del($this->tRole, ['id' => $rid]);
    }

    /**
     * 保存菜单设置
     *
     * @param string $menuName
     * @param array $data
     * @return ResponseData
     * @throws CoreException
     */
    function saveRoleMenu(string $menuName, array $data): ResponseData
    {
        if (!$menuName) {
            return $this->responseData(100610);
        }

        if (empty($data)) {
            return $this->responseData(100620);
        }

        $saveData ['name'] = $menuName;
        $saveData ['behavior'] = implode($data, ',');
        $roleInfo = $this->link->get($this->tRole, '*', ['name' => $menuName]);
        if ($roleInfo) {
            return $this->responseData(100630);
        }

        $rid = $this->link->add($this->tRole, $saveData);
        if ($rid) {
            return $this->responseData(1, ['rid' => $rid]);
        }

        return $this->responseData(100640);
    }

    /**
     * 编辑角色菜单权限
     *
     * @param int $rid
     * @param string $menuName
     * @param array $data
     * @return ResponseData
     * @throws CoreException
     */
    function editRoleMenu(int $rid, string $menuName, array $data): ResponseData
    {
        if (!$menuName) {
            return $this->responseData(100610);
        }

        $saveData ['name'] = $menuName;
        if (empty($data)) {
            $saveData['behavior'] = '';
        } else {
            $saveData['behavior'] = trim(implode($data, ','));
        }

        $roleInfo = $this->link->get($this->tRole, '*', ['id' => $rid]);
        if (!$roleInfo) {
            return $this->responseData(100650);
        }

        $rid = $roleInfo['id'];
        $status = $this->link->update($this->tRole, $saveData, ['id' => $rid]);
        if ($status !== false) {
            return $this->responseData(1, ['rid' => $rid]);
        }

        return $this->responseData(100660);
    }

    /**
     * 导航菜单列表
     *
     * @param null $pid
     * @return mixed
     * @throws CoreException
     */
    function getMenuList($pid = null)
    {
        $condition = [];
        if (null !== $pid) {
            $condition['pid'] = $pid;
        }

        return $this->getMenuByCondition($condition, '`order` ASC, type ASC');
    }

    /**
     * 创建控制器后自动添加菜单
     *
     * @param array $data
     * @return bool|mixed
     * @throws CoreException
     */
    function saveNavData(array $data)
    {
        if (empty($data['name']) || empty($data['link'])) {
            return false;
        }

        $data = array(
            'name' => $data['name'],
            'link' => $data['link'],
            'pid' => !empty($data['pid']) ? (int)$data['pid'] : 0,
            'type' => !empty($data['type']) ? (int)$data['type'] : 1,
            '`order`' => !empty($data['order']) ? (int)$data['order'] : 0,
            'display' => !empty($data['display']) ? (int)$data['display'] : 0
        );

        $has = $this->link->get($this->tAclMenu, 'id', [
            'name' => $data['name']
        ]);

        if ($has) {
            return $this->link->update($this->tAclMenu, $data, [
                'id' => $has['id']
            ]);
        } else {
            return $this->link->add($this->tAclMenu, $data);
        }
    }

    /**
     * 一级菜单列表
     *
     * @param array $unSaveMenu
     * @return mixed
     * @throws CoreException
     * @throws ReflectionException
     */
    function getNavList(array &$unSaveMenu = [])
    {
        $result = [];
        $controllersLists = $this->scanControllers();
        $savedMenus = $this->getMenuByCondition(['pid' => 0], '`order` ASC, type ASC');

        $savedMenuLinkHash = [];
        array_map(function ($m) use (&$savedMenuLinkHash, &$result) {
            $result[$m['id']] = $m;
            $savedMenuLinkHash[$m['link']] = true;
        }, $savedMenus);

        foreach ($controllersLists as $c) {
            if (!isset($savedMenuLinkHash[$c['link']])) {
                $unSaveMenu[] = $c;
            }
        }

        return $result;
    }

    /**
     * 根据条件查询菜单
     *
     * @param array|string $condition
     * @param array|string $order
     * @return array
     * @throws CoreException
     */
    function getMenuByCondition($condition, $order = '`order` ASC')
    {
        $result = $this->link->getAll($this->tAclMenu, '*', $condition, $order);
        if (empty($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * 按条件删除导航菜单
     *
     * @param $condition
     * @return bool
     * @throws CoreException
     */
    private function delNavByCondition($condition)
    {
        return $this->link->del($this->tAclMenu, $condition);
    }

    /**
     * 扫描控制器文件
     *
     * @param bool $hashMap
     * @return array
     * @throws ReflectionException
     */
    private function scanControllers($hashMap = false)
    {
        $navData = [];
        $controllerFile = $this->getFilePath('app::controllers');
        foreach (glob(rtrim($controllerFile, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') as $f) {
            $fi = pathinfo($f);
            $className = $fi['filename'];
            $menuName = lcfirst($className);

            $fullName = 'app\\' . parent::getConfig()->get('app', 'name') . '\\controllers\\' . $className;
            $rc = new ReflectionClass($fullName);

            $display = 1;
            if ($rc->isAbstract() || 0 === strcasecmp($menuName, 'main')) {
                $display = 0;
            }

            $oriNavData = [
                'name' => $className,
                'link' => $menuName,
                'display' => $display
            ];

            if ($hashMap) {
                $navData[$oriNavData['link']] = $oriNavData;
            } else {
                $navData[] = $oriNavData;
            }
        }

        return $navData;
    }
}
