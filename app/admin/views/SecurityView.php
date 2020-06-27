<?php

/**
 * @author wonli <wonli@live.com>
 */

namespace app\admin\views;

/**
 * @author wonli <wonli@live.com>
 *
 * Class SecurityView
 * @package app\admin\views
 */
class SecurityView extends AdminView
{
    /**
     * 管理密保卡
     *
     * @param array $data
     */
    function securityCard(array $data = [])
    {
        if (!empty($data['card'])) {
            $this->renderTpl('security/bind_notice');
        }

        $this->renderTpl('security/card', $data);
    }

    /**
     * 个人信息
     *
     * @param array $data
     */
    function profile(array $data = [])
    {
        $this->renderTpl('security/profile', $data);
    }

    /**
     * 修改密码
     *
     * @param $data
     */
    function changePassword(array $data = [])
    {
        $this->renderTpl('security/change_password', $data);
    }
}
