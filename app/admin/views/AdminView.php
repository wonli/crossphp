<?php

/**
 * @Author: wonli <wonli@live.com>
 */

namespace app\admin\views;

use Cross\MVC\View;

/**
 * @Auth wonli <wonli@live.com>
 *
 * Class AdminView
 * @package app\admin\views
 */
class AdminView extends View
{
    /**
     * @var array
     */
    private $nav_menu;

    /**
     * @var array
     */
    private $menu_data;

    /**
     * @var array
     */
    private $all_menu;

    /**
     * @var array
     */
    private $action_name;

    /**
     * 输出消息
     *
     * @param $code
     * @param null $tpl
     * @throws \Cross\Exception\CoreException
     */
    function notice($code, $tpl = null)
    {
        $code_text = $this->parseGetFile('config::notice.config.php');
        if (isset($code_text[$code])) {
            $this->text($code_text[$code], $tpl);
        } else {
            $this->text('未指明的错误识别码' . $code, $tpl);
        }
    }

    /**
     * 文本提示
     *
     * @param string $text
     * @param string $tpl
     */
    function text($text, $tpl = null)
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
     * 返回菜单
     *
     * @return array
     */
    function getMenu()
    {
        return $this->menu_data;
    }

    /**
     * 导航菜单数据
     *
     * @return array
     */
    function getNavMenu()
    {
        return $this->nav_menu;
    }

    /**
     * 获取所有菜单数据
     *
     * @return mixed
     */
    function getAllMenu()
    {
        return $this->all_menu;
    }

    /**
     * 设置导航菜单
     *
     * @param $nav_data
     */
    function setNavMenu($nav_data)
    {
        $this->nav_menu = $nav_data;
    }

    /**
     * 设置菜单
     *
     * @param $data
     */
    function setMenu(array $data = array())
    {
        $this->menu_data = $data;
    }

    /**
     * 设置所有菜单数据
     *
     * @param array $menu
     * @param array $menu_icon
     */
    function setAllMenu($menu, $menu_icon = array())
    {
        $action_name = &$this->action_name;
        foreach ($menu as $name => &$m) {
            $menu_icon_config = &$menu_icon[$name];
            if (is_array($menu_icon_config)) {
                $icon = $menu_icon_config[0];
                $child_menu_icon_config = $menu_icon_config[1];
            } else {
                $icon = $menu_icon_config;
                $child_menu_icon_config = array();
            }

            $m['icon'] = $icon;
            $m['child_menu_num'] = 0;
            foreach ($m['child_menu'] as $id => &$mc) {
                $ca = strtolower($m['link'] . ':' . $mc['link']);
                $action_name[$ca] = $mc['link'];
                if ($mc['name']) {
                    $action_name[$ca] = $mc['name'];
                }

                if (is_array($child_menu_icon_config)) {
                    $mc_icon = &$child_menu_icon_config[$mc['link']];
                } else {
                    $mc_icon = &$child_menu_icon_config;
                }

                $mc['icon'] = $mc_icon;
                if ($mc['display'] == 1) {
                    $m['child_menu_num']++;
                } else {
                    unset($m['child_menu'][$id]);
                }
            }
        }

        $this->all_menu = $menu;
    }

    /**
     * 生成导航菜单
     *
     * @param string $controller_menu_name
     * @param string $action_menu_name
     */
    function renderNavMenu(&$controller_menu_name = '', &$action_menu_name = '')
    {
        $controller = lcfirst($this->controller);
        $ca = strtolower($controller . ':' . $this->action);
        $action_menu_name = $this->action_name[$ca];

        if (!empty($this->all_menu)) {
            foreach ($this->all_menu as $m) {
                if ($m['display'] != 1) {
                    continue;
                }

                $icon_name = 'fa fa-circle-o';
                if (!empty($m['icon'])) {
                    $icon_name = $m['icon'];
                }

                $class = '';
                if (0 === strcasecmp($controller, $m['link'])) {
                    $controller_menu_name = $m['name'];
                    $class = 'active';
                }

                $child_node_num = &$m['child_menu_num'];
                if ($child_node_num > 0) {
                    $class = "treeview {$class}";
                }

                if ($m['type'] == 1) {
                    $link = $this->url($m['link']);
                    $target = '_self';
                } else {
                    $link = $m['link'];
                    $target = '_blank';
                }

                $child_menu = array(
                    'controller' => &$m['link'],
                    'current_controller' => $controller,
                    'child' => &$m['child_menu']
                );

                $this->renderTpl('nav/li', array(
                    'link' => $link,
                    'name' => $m['name'],
                    'class' => $class,
                    'target' => $target,
                    'icon_name' => $icon_name,
                    'child_menu' => &$child_menu,
                    'child_node_num' => $child_node_num
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
     * @param string $params
     * @param string $link_text
     * @param string $confirm_title
     */
    function confirmUrl($controller, $params, $link_text, $confirm_title = '确定执行该操作吗?')
    {
        echo $this->a($link_text, 'javascript:void(0)', array(
            'title' => $confirm_title,
            'class' => 'confirm-href-flag',
            'action' => $this->url($controller, $params)
        ));
    }

    /**
     * 分页方法
     *
     * @param array $data
     * @param string $class
     * @param string $tpl
     */
    function page(array $data, $class = 'pagination', $tpl = 'default')
    {
        $data['pagination_class'] = $class;
        if (!isset($data['link'])) {
            $params = array();
            $current_controller = lcfirst($this->controller);
            $controller = "{$current_controller}:{$this->action}";
        } elseif (is_array($data['link']) && $data['link'][1]) {
            list($controller, $params) = $data['link'];
        } elseif (is_array($data['link'])) {
            $params = array();
            $controller = $data['link'][0];
        } else {
            $params = array();
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
