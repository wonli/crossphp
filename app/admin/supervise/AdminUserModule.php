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
     * 最多保存多少条操作日志
     */
    const MAX_ACT_LOG = 200;

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
            if ($user_password === $user_info['password']) {

                //更新登录信息
                $token = Helper::random(32);
                $this->update($user_info['id'], array(
                    'token' => $token,
                    'last_login_date' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $this->request->getClientIPAddress()
                ));

                return $this->result(1, array(
                    'id' => $user_info['id'],
                    'rid' => $user_info['rid'],
                    'name' => $user_info['name'],
                    'token' => $token
                ));
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

        $data['last_login_ip'] = '';
        $data['last_login_date'] = date('Y-m-d H:i:s', 0);
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
        unset($data['id']);
        $admin_info = $this->link->get($this->t_admin, '*', array('id' => $id));
        if (!$admin_info) {
            return $this->result(100400);
        }

        //更新用户名
        if (isset($data['name'])) {
            $nameUser = $this->link->get($this->t_admin, '*', array('name' => $data['name']));
            if (!empty($nameUser) && $nameUser['id'] != $id) {
                return $this->result(100420);
            }
        }

        //重新生成密码
        if (isset($data['password']) && ($data['password'] !== $admin_info['password'])) {
            $data ['password'] = self::genPassword($data['password'], $data['salt']);
        }

        $ret = $this->link->update($this->t_admin, $data, array('id' => $id));
        if ($ret !== false) {
            return $this->result(1);
        }

        return $this->result(100421);
    }

    /**
     * 更新登录日志
     *
     * @param string $name
     * @param string|array $params
     * @param string $type
     * @throws \Cross\Exception\CoreException
     */
    function updateActLog($name, $params, $type = 'post')
    {
        if (is_array($params)) {
            $params = array_filter($params);
            if (!empty($params)) {
                $params = json_encode($params);
            } else {
                $params = '';
            }
        }

        $data = array(
            'name' => $name,
            'controller' => $this->controller,
            'action' => $this->action,
            'type' => $type,
            'params' => $params,
            'date' => date('Y-m-d H:i:s'),
            'ip' => $this->request->getClientIPAddress()
        );

        $act_info = $this->link->get($this->t_act_log, 'count(id) has, min(id) del_act_id', array(
            'name' => $name,
            'type' => $type
        ));

        if ($act_info['has'] >= self::MAX_ACT_LOG) {
            $this->link->del($this->t_act_log, array('id' => $act_info['del_act_id']));
        }

        $this->link->add($this->t_act_log, $data);
    }

    /**
     * 验证当前密码
     *
     * @param string $name
     * @param string $pwd
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function checkPassword($name, $pwd)
    {
        $admin_info = $this->link->get($this->t_admin, '*', array('name' => $name));
        return $admin_info ['password'] === self::genPassword($pwd, $admin_info['salt']);
    }

    /**
     * 更新密码
     *
     * @param string $name
     * @param string $pwd
     * @return array|string
     * @throws \Cross\Exception\CoreException
     */
    function updatePassword($name, $pwd)
    {
        $np = self::genPassword($pwd, $salt);
        $data = array(
            'password' => $np,
            'salt' => $salt
        );

        $status = $this->link->update($this->t_admin, $data, array('name' => $name));
        if ($status !== false) {
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