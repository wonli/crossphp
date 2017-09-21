<?php

/**
 * @Author: wonli <wonli@live.com>
 */

namespace app\admin\views;

/**
 * @Auth wonli <wonli@live.com>
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
    function securityCard(array $data = array())
    {
        if (!empty($data['card'])) {
            $this->renderTpl('security/bind_notice');
        }

        $this->renderTpl('security/card', $data);
    }

    /**
     * 修改密码
     *
     * @param $data
     */
    function changePassword(array $data = array())
    {
        $this->renderTpl('security/change_password', $data);
    }
}
