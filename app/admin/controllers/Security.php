<?php
/**
 * @Author: wonli <wonli@live.com>
 * 安全管理
 */
namespace app\admin\controllers;

use modules\admin\SecurityModule;

class Security extends Admin
{
    /**
     * 密保卡module
     *
     * @var \modules\admin\SecurityModule
     */
    private $SEC;

    function __construct()
    {
        parent::__construct();
        $this->SEC = new SecurityModule;
    }

    /**
     * 默认跳转到修改密码页面
     */
    function index()
    {
        $this->to("security:changePassword");
    }

    /**
     * 用HTML表格的形式打印出密保卡
     */
    function printSecurityCard()
    {
        $ret = $this->SEC->securityData($this->u);
        if ($ret['status'] != 1) {
            $this->data['status'] = $ret['status'];
        } else {
            $this->data['card'] = $ret['message'];
        }

        $this->display($this->data);
    }

    /**
     * 下载密保卡
     */
    function makeSecurityImage()
    {
        $data = $this->SEC->makeSecurityCardImage($this->u);
        $this->display($data, 'printStatus');
    }

    /**
     * 绑定密保卡
     */
    function bind()
    {
        $data = $this->SEC->bindCard($this->u);
        $this->data['status'] = $data['status'];
        $this->display($this->data, 'printStatus');
    }

    /**
     * 刷新已绑定的密保卡
     */
    function refresh()
    {
        $ret = $this->SEC->updateCard($this->u);
        if ($ret['status'] == 1) {
            $this->to("security:printSecurityCard");
        } else {
            $this->data['status'] = $ret['status'];
        }
        $this->display($this->data, 'printStatus');
    }

    /**
     * 解除密绑定的密保卡
     */
    function kill()
    {
        $ret = $this->SEC->killBind($this->u);
        $this->display($ret, 'printStatus');
    }

    /**
     * 创建用户存储密保卡的表
     */
    function create()
    {
        $data = $this->SEC->createTable();
        $this->display($data);
    }

    /**
     * 更改密码
     */
    function changePassword()
    {
        if ($this->is_post()) {
            if ($_POST ['np1'] != $_POST ['np2']) {
                $this->data ['status'] = 100008;
            } else {
                $is_right = $this->ADMIN->checkPassword($_POST['op']);
                if ($is_right) {
                    $this->ADMIN->updatePassword($_POST['np1']);
                } else {
                    $this->data ['status'] = 100007;
                }
            }
        }
        $this->display($this->data);
    }
}
