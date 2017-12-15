<?php
/**
 * @author wonli <wonli@live.com>
 * Security.php
 */

namespace app\admin\controllers;

use app\admin\supervise\SecurityModule;

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
     * @throws \Cross\Exception\CoreException
     */
    function __construct()
    {
        parent::__construct();
        $this->SEC = new SecurityModule;
    }

    /**
     * 默认跳转到修改密码页面
     *
     * @throws \Cross\Exception\CoreException
     */
    function index()
    {
        $this->to('security:changePassword');
    }

    /**
     * 密保卡
     *
     * @cp_params act=preview
     * @throws \Cross\Exception\CoreException
     */
    function securityCard()
    {
        $act = &$this->params['act'];
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
            if ($actRet['status'] != 1) {
                $this->data['status'] = $actRet['status'];
            } else {
                $this->to('security:securityCard');
            }
        }

        $data = $this->SEC->getSecurityData($this->u);
        if (!empty($data) && $data[0] != -1) {
            $this->data['card'] = $data[1];
        }

        $this->display($this->data);
    }

    /**
     * 更改密码
     *
     * @throws \Cross\Exception\CoreException
     */
    function changePassword()
    {
        if ($this->is_post()) {
            if ($_POST['np1'] != $_POST['np2']) {
                $this->data['status'] = 100220;
            } else {
                $is_right = $this->ADMIN->checkPassword($_POST['op']);
                if ($is_right) {
                    $this->ADMIN->updatePassword($_POST['np1']);
                } else {
                    $this->data['status'] = 100221;
                }
            }
        }
        $this->display($this->data);
    }

    /**
     * 创建用户存储密保卡的表
     *
     * @throws \Cross\Exception\CoreException
     */
    function create()
    {
        $data = $this->SEC->createTable();
        $this->display($data);
    }
}
