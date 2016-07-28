<?php

/**
 * @Author: wonli <wonli@live.com>
 */
namespace app\admin\views;

use Cross\Core\Loader;
use Cross\MVC\View;

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
     * 输出消息
     *
     * @param $code
     * @param null $tpl
     * @throws \Cross\Exception\CoreException
     */
    function notice($code, $tpl = null)
    {
        $code_text = Loader::read("::config/notice.config.php");
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
    function setMenu($data)
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
            foreach ($m['child_menu'] as $id => &$mc) {
                if(is_array($child_menu_icon_config)) {
                    $mc_icon = &$child_menu_icon_config[$mc['link']];
                } else {
                    $mc_icon = &$child_menu_icon_config;
                }
                $mc['icon'] = $mc_icon;
                if ($mc['display'] != 1) {
                    unset($m['child_menu'][$id]);
                }
            }
        }

        $this->all_menu = $menu;
    }

    /**
     * 分页方法
     *
     * @param $page
     * @param string $tpl
     */
    function page($page, $tpl = 'page')
    {
        list($controller, $params) = $page['link'];

        $_dot = isset($page['dot']) ? $page["dot"] : $this->config->get('url', 'dot');
        include $this->tpl("page/{$tpl}");
    }
}
