<?php
/**
 * @author wonli <wonli@live.com>
 * AdminUserModule.php
 */

namespace app\admin\supervise;

use Cross\Core\Helper;
use Exception;

/**
 * 后台用户相关
 * @author wonli <wonli@live.com>
 *
 * Class AdminUserModule
 * @package modules\admin
 */
class AdminUserModule extends AdminModule
{
    /**
     * 登录验证
     *
     * @param string $username
     * @param string $password
     * @param string $code_location
     * @param string $code_value
     * @return array|string
     * @throws \Cross\Exception\CoreException
     */
    function checkAdmin($username, $password, $code_location = '', $code_value = '')
    {
        $SEC = new SecurityModule;
        try {
            $user_info = $this->link->get($this->t_admin, '*', array('name' => $username));
        } catch (Exception $e) {
            return $this->result(100100);
        }

        if (empty($user_info)) {
            return $this->result(100210);
        }

        if ($user_info ['t'] != 1) {
            return $this->result(100211);
        }

        if ($user_info && !empty($user_info['password'])) {
            $is_bind = $SEC->checkbind($username);
            if ($is_bind) {
                if (empty($code_location) || empty($code_value)) {
                    return $this->result(100300);
                }

                $verify = $SEC->verifyCode($username, $code_location, $code_value);
                if (!$verify) {
                    return $this->result(100301);
                }
            }

            $user_password = self::genPassword($password, $user_info['salt']);
            if ($user_password === $user_info["password"]) {
                return $this->result(1);
            }
            return $this->result(100212);
        }

        return $this->result(100213);
    }

    /**
     * 管理员列表
     *
     * @return mixed
     * @throws \Cross\Exception\CoreException
     */
    function getAdminUserList()
    {
        return $this->link->getAll("{$this->t_admin} a LEFT JOIN {$this->t_security_card} s ON a.name=s.bind_user",
            'a.*, s.id bind_id', array('a.rid' => array('>', 0)));
    }

    /**
     * 新增加管理员
     *
     * @param $data
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function addAdmin($data)
    {
        if (empty($data['name']) || empty($data['password'])) {
            return $this->result(100410);
        }

        $ai = $this->link->get($this->t_admin, '*', array('name' => $data['name']));
        if ($ai) {
            return $this->result(100411);
        }

        $data['password'] = self::genPassword($data['password'], $data['salt']);
        $id = $this->link->add($this->t_admin, $data);
        if ($id) {
            return $this->result(1, $id);
        }

        return $this->result(100412);
    }

    /**
     * 根据condition查询管理员信息
     *
     * @param $condition
     * @return mixed
     * @throws \Cross\Exception\CoreException
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
     * @throws \Cross\Exception\CoreException
     */
    function del($condition)
    {
        $ret = $this->link->del($this->t_admin, $condition);
        if ($ret) {
            return $this->result(1);
        }

        return $this->result(100430);
    }

    /**
     * 更新管理员用户信息
     *
     * @param int $id
     * @param array $data
     * @return array|string
     * @throws \Cross\Exception\CoreException
     */
    function update($id, $data)
    {
        $admin_info = $this->link->get($this->t_admin, '*', array('id' => $id));
        if (!$admin_info) {
            return $this->result(100400);
        }

        $nameUser = $this->link->get($this->t_admin, '*', array('name' => $data['name']));
        if (!empty($nameUser) && $nameUser['id'] != $id) {
            return $this->result(100420);
        }

        //更新密码
        if ($data['password'] !== $admin_info['password']) {
            $data ['password'] = self::genPassword($data['password'], $data['salt']);
        }

        $this->link->update($this->t_admin, $data, array('id' => $id));
        return $this->result(1);
    }

    /**
     * 验证当前密码
     *
     * @param string $pwd
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function checkPassword($pwd)
    {
        $admin_info = $this->link->get($this->t_admin, '*', array('name' => $_SESSION['u']));
        return $admin_info ['password'] === self::genPassword($pwd, $admin_info['salt']);
    }

    /**
     * 更新密码
     *
     * @param string $pwd
     * @return array|string
     * @throws \Cross\Exception\CoreException
     */
    function updatePassword($pwd)
    {
        $np = self::genPassword($pwd, $salt);
        $data = array(
            'password' => $np,
            'salt' => $salt
        );

        $status = $this->link->update($this->t_admin, $data, array('name' => $_SESSION['u']));
        if ($status) {
            return $this->result(1);
        }

        return $this->result(100214);
    }

    /**
     * 生成密码
     *
     * @param string $password
     * @param string $salt
     * @return string
     */
    static function genPassword($password, &$salt = '')
    {
        if (empty($salt)) {
            $salt = Helper::random(16);
        }

        return hash('sha256', $salt . $password);
    }
}