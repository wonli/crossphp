<?php
/**
 * @author wonli <wonli@live.com>
 * Security.php
 */

namespace app\admin\controllers;

use app\admin\supervise\SecurityModule;

use Cross\Exception\LogicStatusException;
use Cross\Exception\CoreException;
use ReflectionException;

/**
 * 安全管理,密码和密保卡
 * @author wonli <wonli@live.com>
 *
 * Class Security
 * @package app\admin\controllers
 */
class Security extends Admin
{
    /**
     * @var SecurityModule
     */
    private $SEC;

    /**
     * Security constructor.
     *
     * @throws CoreException
     * @throws ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $this->SEC = new SecurityModule;
    }

    /**
     * 默认跳转到修改密码页面
     *
     * @throws CoreException
     */
    function index()
    {
        $this->to('security:changePassword');
    }

    /**
     * 密保卡
     *
     * @cp_params act=preview
     * @throws CoreException
     */
    function securityCard()
    {
        try {
            $act = $this->input('act')->val();
            switch ($act) {
                case 'bind':
                    $actRet = $this->SEC->bindCard($this->u);
                    break;

                case 'refresh':
                    $actRet = $this->SEC->updateCard($this->u);
                    break;

                case 'unbind':
                    $actRet = $this->SEC->unBind($this->u);
                    break;

                case 'download':
                    $actRet = $this->SEC->makeSecurityCardImage($this->u);
                    break;
            }

            if (!empty($actRet)) {
                $status = $actRet->getStatus();
                if ($status != 1) {
                    $this->end($status);
                    return;
                } else {
                    $this->to('security:securityCard');
                    return;
                }
            }

            $data = $this->SEC->getSecurityData($this->u);
            if (!empty($data) && $data[0] != -1) {
                $this->data['card'] = $data[1];
            }
        } catch (LogicStatusException $e) {
            $this->data = $e->getResponseData();
        }

        $this->display($this->data);
    }

    /**
     * 更改密码
     *
     * @throws CoreException
     * @throws LogicStatusException
     */
    function changePassword()
    {
        $postData = $this->request->getPostData();
        if ($this->isPost()) {
            if (0 !== strcmp($postData['np1'], $postData['np2'])) {
                $this->end(100220);
                return;
            } else {
                $isRight = $this->ADMIN->checkPassword($this->u, $postData['op']);
                if ($isRight) {
                    $this->ADMIN->updatePassword($this->u, $postData['np1']);
                } else {
                    $this->end(100221);
                    return;
                }
            }
        }

        $this->display();
    }

    /**
     * 个人信息
     *
     * @cp_params act, theme
     * @throws CoreException
     */
    function profile()
    {
        $adminInfo = $this->ADMIN->getAdminInfo(array('id' => $this->uid));
        if ($this->isPost()) {
            $this->ADMIN->update($this->uid, $this->request->getPostData());
            $this->to('security:profile');
            return;
        }

        //判断是否有主题配置
        $themeConfig = $this->parseGetFile('app::config/theme.config.php');
        $tplDir = $this->config->get('sys', 'default_tpl_dir');

        $hasTheme = false;
        $tplThemeList = [];
        if (isset($themeConfig[$tplDir])) {
            $hasTheme = true;
            $tplThemeList = &$themeConfig[$tplDir];
        }

        $act = $this->input('act')->val();
        if (!empty($act)) {
            switch ($act) {
                case 'setTheme':
                    $theme = $this->input('theme')->val();
                    if ($hasTheme && $theme) {
                        $useTheme = &$tplThemeList['themes'][$theme];
                        if ($useTheme && !empty($useTheme['class'])) {
                            $this->ADMIN->update($this->uid, ['theme' => $useTheme['class']]);
                            $this->setAuth('theme', $useTheme['class']);
                        }
                    }

                    $this->to('security:profile');
                    return;

                default:
                    $this->to('security:profile');
                    return;
            }
        }

        $this->data['themeList'] = $tplThemeList;
        $this->data['hasTheme'] = $hasTheme;
        $this->data['admin'] = $adminInfo;
        $this->display($this->data);
    }

    /**
     * 创建用户存储密保卡的表
     *
     * @throws CoreException
     */
    function create()
    {
        $data = $this->SEC->createTable();
        $this->display($data);
    }
}
