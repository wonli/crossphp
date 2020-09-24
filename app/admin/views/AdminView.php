<?php

/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\views;

use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;
use Cross\MVC\View;

/**
 * @author wonli <wonli@live.com>
 *
 * Class AdminView
 * @package app\admin\views
 */
class AdminView extends View
{
    /**
     * @var array
     */
    private $menus;

    /**
     * @var array
     */
    private $actionName;

    /**
     * @var array
     */
    protected $loginInfo = [];

    /**
     * @param array $u
     */
    function setLoginInfo(array $u)
    {
        $this->loginInfo = $u;
    }

    /**
     * @throws CoreException
     */
    function noticeBlock()
    {
        $statusName = ResponseData::builder()->getStatusName();
        if ($this->data[$statusName] != 1) {
            ?>
            <div class="callout callout-info">
                <h4>提示!</h4>
                <?php $this->notice($this->data[$statusName], '%s'); ?>
            </div>
            <?php
        }
    }

    /**
     * 输出消息
     *
     * @param $code
     * @param null $tpl
     * @throws CoreException
     */
    function notice($code, $tpl = null)
    {
        $codeText = $this->parseGetFile('config::status.config.php');
        if (isset($codeText[$code])) {
            $this->text($codeText[$code], $tpl);
        } else {
            $this->text('未指明的错误识别码' . $code, $tpl);
        }
    }

    /**
     * 文本提示
     *
     * @param string $text
     * @param null $tpl
     */
    function text(string $text, $tpl = null)
    {
        if ($tpl === null) {
            $tpl = '<div style="background: #F0F8FF;padding:10px;">%s</div>';
        }

        printf($tpl, $text);
    }

    /**
     * return string
     */
    function getTitleBread()
    {
        return '欢迎使用本系统';
    }

    /**
     * 获取菜单数据
     *
     * @return mixed
     */
    function getMenuData()
    {
        return $this->menus;
    }

    /**
     * 设置所有菜单数据
     *
     * @param array $menu
     * @param array $menuIcon
     */
    function setMenuData(array $menu, array $menuIcon = [])
    {
        $actionName = &$this->actionName;
        foreach ($menu as $name => &$m) {
            $menuIconConfig = &$menuIcon[$name];
            if (is_array($menuIconConfig)) {
                $icon = $menuIconConfig[0];
                $childMenuIconConfig = $menuIconConfig[1];
            } else {
                $icon = $menuIconConfig;
                $childMenuIconConfig = [];
            }

            $m['icon'] = $icon;
            $m['child_menu_num'] = 0;
            if (!empty($m['child_menu'])) {
                foreach ($m['child_menu'] as $id => &$mc) {
                    $ca = strtolower($m['link'] . ':' . $mc['link']);
                    $actionName[$ca] = $mc['link'];
                    if ($mc['name']) {
                        $actionName[$ca] = $mc['name'];
                    }

                    if (is_array($childMenuIconConfig)) {
                        $mcIcon = &$childMenuIconConfig[$mc['link']];
                    } else {
                        $mcIcon = &$childMenuIconConfig;
                    }

                    $mc['icon'] = $mcIcon;
                    if ($mc['display'] == 1) {
                        $m['child_menu_num']++;
                    } else {
                        unset($m['child_menu'][$id]);
                    }
                }
            } else {
                $m['child_menu'] = [];
            }
        }

        $this->menus = $menu;
    }

    /**
     * 生成导航菜单
     *
     * @param string $controllerMenuName
     * @param string $actionMenuName
     * @throws CoreException
     */
    function renderNavMenu(string &$controllerMenuName = '', string &$actionMenuName = '')
    {
        $controller = lcfirst($this->controller);
        $ca = strtolower($controller . ':' . $this->action);
        if (isset($this->actionName[$ca])) {
            $actionMenuName = $this->actionName[$ca];
        }

        if (!empty($this->menus)) {
            foreach ($this->menus as $m) {
                if ($m['display'] != 1) {
                    continue;
                }

                $iconName = 'fa fa-circle-o';
                if (!empty($m['icon'])) {
                    $iconName = $m['icon'];
                }

                $class = '';
                if (0 === strcasecmp($controller, $m['link'])) {
                    $controllerMenuName = $m['name'];
                    $class = 'active';
                }

                $childNodeNum = &$m['child_menu_num'];
                if ($childNodeNum > 0) {
                    $class = "treeview {$class}";
                }

                if ($m['type'] == 1) {
                    $link = $this->url($m['link']);
                    $target = '_self';
                } else {
                    $link = $m['link'];
                    $target = '_blank';
                }

                $childMenu = array(
                    'controller' => &$m['link'],
                    'current_controller' => $controller,
                    'child' => &$m['child_menu']
                );

                $this->renderTpl('nav/li', array(
                    'link' => $link,
                    'name' => $m['name'],
                    'class' => $class,
                    'target' => $target,
                    'icon_name' => $iconName,
                    'child_menu' => &$childMenu,
                    'child_node_num' => $childNodeNum
                ));
            }
        }
    }

    /**
     * 生成询问URL
     * <pre>
     * js检查对应的class标记, 用户确认后跳转到执行该操作的链接
     * </pre>
     *
     * @param string $controller
     * @param array $params
     * @param string $linkText
     * @param string $confirmTitle
     * @throws CoreException
     */
    function confirmUrl(string $controller, array $params, string $linkText, string $confirmTitle = '确定执行该操作吗?')
    {
        echo $this->a($linkText, 'javascript:void(0)', array(
            'title' => $confirmTitle,
            'class' => 'confirm-href-flag',
            'action' => $this->url($controller, $params)
        ));
    }

    /**
     * 获取主题风格
     *
     * @return string
     * @throws CoreException
     */
    function getTheme()
    {
        return $this->getAuth('theme') ?: 'skin-black';
    }

    /**
     * 分页方法
     *
     * @param array $data
     * @param string $class
     * @param string $tpl
     */
    function page(array $data, string $class = 'pagination', string $tpl = 'default')
    {
        $data['pagination_class'] = $class;
        if (!isset($data['link'])) {
            $params = [];
            $currentController = lcfirst($this->controller);
            $controller = "{$currentController}:{$this->action}";
        } elseif (is_array($data['link']) && $data['link'][1]) {
            list($controller, $params) = $data['link'];
        } elseif (is_array($data['link'])) {
            $params = [];
            $controller = $data['link'][0];
        } else {
            $params = [];
            $controller = $data['link'];
        }

        if (!isset($data['anchor'])) {
            $data['anchor'] = '';
        }

        $data['controller'] = $controller;
        $data['params'] = $params;

        if (!isset($data['half'])) {
            $data['half'] = 5;
        }

        $this->renderTpl("page/{$tpl}", $data);
    }
}
