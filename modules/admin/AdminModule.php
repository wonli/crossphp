<?php
/**
 * @Author: wonli <wonli@live.com>
 */
namespace modules\admin;

use Cross\MVC\Module;
use Exception;

/**
 * 后台用户相关
 *
 * @Auth: wonli <wonli@live.com>
 * Class AdminModule
 * @package modules\admin
 */
class AdminModule extends Module
{
    /**
     * 管理员表
     *
     * @var string
     */
    protected $t_admin = 'cp_admin';

    /**
     * 登录验证
     *
     * @param string $username
     * @param string $password
     * @param string $code_location
     * @param string $code_value
     * @return array|string
     */
    function checkAdmin($username, $password, $code_location = '', $code_value = '')
    {
        $SEC = new SecurityModule;
        try {
            $user_info = $this->link->get($this->t_admin, '*', array('name' => $username));
        } catch (Exception $e) {
            return $this->result(100006);
        }

        if ($user_info ['t'] != 1) {
            return $this->result(100001);
        }

        if ($user_info && !empty($user_info["password"])) {
            $user_password = sha1(md5($password));
            $is_bind = $SEC->checkbind($username);

            if ($is_bind) {
                if (empty($code_location) || empty($code_value)) {
                    return $this->result(100004);
                }

                $verify_right = $SEC->verifyCode($username, $code_location, $code_value);
                if (!$verify_right) {
                    return $this->result(100005);
                }
            }

            if ($user_password === $user_info["password"]) {
                return $this->result(1);
            }
            return $this->result(100003);
        }

        return $this->result(100002);
    }

    /**
     * 管理员列表
     */
    function getUserList()
    {
        return $this->link->getAll($this->t_admin, "*");
    }

    /**
     * 新增加管理员
     *
     * @param $data
     * @return bool
     */
    function addAdmin($data)
    {
        if (empty($data['name']) || empty($data['password'])) {
            return false;
        }

        foreach ($data as $k => & $d) {
            if (empty($d)) {
                unset($data[$k]);
            }

            if ($k === 'password') {
                $d = sha1(md5($data ['password']));
            }
        }

        return $this->link->add($this->t_admin, $data);
    }

    /**
     * 根据condition查询管理员信息
     *
     * @param $condition
     * @return mixed
     */
    function getAdminInfo($condition)
    {
        return $this->link->get($this->t_admin, '*', $condition);
    }

    /**
     * 删除用户
     *
     * @param $condition
     * @return mixed
     */
    function del($condition)
    {
        return $this->link->del($this->t_admin, $condition);
    }

    /**
     * 更新管理员列表
     *
     * @param $data
     * @param $condition
     * @return array|string
     */
    function update($data, $condition)
    {
        $admin_info = $this->link->get($this->t_admin, '*', $condition);

        if (!$admin_info) {
            return $this->result(100025);
        }

        //更新密码
        if ($admin_info ['password'] !== $data['password']) {
            $data ['password'] = sha1(md5($data ['password']));
        }

        $this->link->update($this->t_admin, $data, $condition);
        return $this->result(1);
    }

    /**
     * 验证当前密码
     *
     * @param $inp_pwd
     * @return bool
     */
    function checkPassword($inp_pwd)
    {
        $admin_info = $this->link->get($this->t_admin, '*', array('name' => $_SESSION['u']));
        return $admin_info ['password'] === sha1(md5($inp_pwd));
    }

    /**
     * 更新密码
     *
     * @param $inp_pwd
     * @return array|string
     */
    function updatePassword($inp_pwd)
    {
        $np = sha1(md5($inp_pwd));
        $status = $this->link->update($this->t_admin, array('password' => $np), array('name' => $_SESSION['u']));

        if ($status) {
            return $this->result(1);
        }

        return $this->result(100009);
    }
}

