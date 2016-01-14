<?php

/**
 * @Author: wonli <wonli@live.com>
 */
namespace app\admin\views;

class SecurityView extends AdminView
{
    /**
     * 输出密保卡
     *
     * @param array $data
     */
    function printSecurityCard($data=array())
    {
        if ($data['status'] == 1) {
            $this->renderTpl('security/security_card', $data['card']);
        }
    }

    /**
     * 输出status
     *
     * @param array $data
     */
    function printStatus($data = array())
    {

    }

    /**
     * 下载密保卡
     *
     * @param $notes
     */
    function makeSecurityImage($notes)
    {
        if (isset($notes['ok']) && $notes['ok'] < 0) {
            echo $notes["msg"];
        } else {
            echo '正在下载...';
        }
    }

    /**
     * 修改密码
     *
     * @param $data
     */
    function changePassword($data)
    {
        $this->renderTpl('security/change_password', $data);
    }
}
