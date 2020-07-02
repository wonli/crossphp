<?php
/**
 * @author wonli <wonli@live.com>
 * AdminUserModule.php
 */

namespace app\admin\supervise;

use Cross\Core\Helper;
use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;
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
     * @return ResponseData
     * @throws CoreException
     */
    function checkAdmin($username, $password, $code_location = '', $code_value = ''): ResponseData
    {
        $SEC = new SecurityModule;
        try {
            $user_info = $this->link->get($this->t_admin, '*', ['name' => $username]);
        } catch (Exception $e) {
            return $this->responseData(100100);
        }

        if (empty($user_info)) {
            return $this->responseData(100210);
        }

        if ($user_info ['t'] != 1) {
            return $this->responseData(100211);
        }

        if ($user_info && !empty($user_info['password'])) {
            $is_bind = $SEC->checkbind($username);
            if ($is_bind) {
                if (empty($code_location) || empty($code_value)) {
                    return $this->responseData(100300);
                }

                $verify = $SEC->verifyCode($username, $code_location, $code_value);
                if (!$verify) {
                    return $this->responseData(100301);
                }
            }

            $user_password = self::genPassword($password, $user_info['salt']);
            if ($user_password === $user_info['password']) {

                //更新登录信息
                $token = Helper::random(32);
                $this->update($user_info['id'], [
                    'token' => $token,
                    'last_login_date' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $this->request->getClientIPAddress()
                ]);

                return $this->responseData(1, [
                    'id' => $user_info['id'],
                    'rid' => $user_info['rid'],
                    'name' => $user_info['name'],
                    'token' => $token
                ]);
            }

            return $this->responseData(100212);
        }

        return $this->responseData(100213);
    }

    /**
     * 管理员列表
     *
     * @param array $page
     * @return mixed
     * @throws CoreException
     */
    function getAdminUserList(&$page = ['p' => 1, 'limit' => 10])
    {
        return $this->link->find("{$this->t_admin} a LEFT JOIN {$this->t_security_card} s ON a.name=s.bind_user",
            'a.*, s.id bind_id', ['a.rid' => ['>', 0]], $page);
    }

    /**
     * 新增加管理员
     *
     * @param array $data
     * @return ResponseData
     * @throws CoreException
     */
    function addAdmin(array $data): ResponseData
    {
        if (empty($data['name']) || empty($data['password'])) {
            return $this->responseData(100410);
        }

        $ai = $this->link->get($this->t_admin, '*', ['name' => $data['name']]);
        if ($ai) {
            return $this->responseData(100411);
        }

        $data['last_login_ip'] = '';
        $data['password'] = self::genPassword($data['password'], $data['salt']);
        $id = $this->link->add($this->t_admin, $data);
        if ($id) {
            return $this->responseData(1, ['id' => $id]);
        }

        return $this->responseData(100412);
    }

    /**
     * 根据condition查询管理员信息
     *
     * @param $condition
     * @return mixed
     * @throws CoreException
     */
    function getAdminInfo($condition)
    {
        return $this->link->get($this->t_admin, '*', $condition);
    }

    /**
     * 删除用户
     *
     * @param array $condition
     * @return ResponseData
     * @throws CoreException
     */
    function del(array $condition): ResponseData
    {
        $ret = $this->link->del($this->t_admin, $condition);
        if ($ret) {
            return $this->responseData(1);
        }

        return $this->responseData(100430);
    }

    /**
     * 更新管理员用户信息
     *
     * @param int $id
     * @param array $data
     * @return ResponseData
     * @throws CoreException
     */
    function update($id, $data): ResponseData
    {
        unset($data['id']);
        $admin_info = $this->link->get($this->t_admin, '*', ['id' => $id]);
        if (!$admin_info) {
            return $this->responseData(100400);
        }

        //更新用户名
        if (isset($data['name'])) {
            $nameUser = $this->link->get($this->t_admin, '*', ['name' => $data['name']]);
            if (!empty($nameUser) && $nameUser['id'] != $id) {
                return $this->responseData(100420);
            }
        }

        //重新生成密码
        if (isset($data['password']) && ($data['password'] !== $admin_info['password'])) {
            $data ['password'] = self::genPassword($data['password'], $data['salt']);
        }

        $ret = $this->link->update($this->t_admin, $data, ['id' => $id]);
        if ($ret !== false) {
            return $this->responseData(1);
        }

        return $this->responseData(100421);
    }

    /**
     * 更新登录日志
     *
     * @param string $name
     * @param string|array $params
     * @param string $type
     * @throws CoreException
     */
    function updateActLog($name, $params, $type = 'post')
    {
        if (is_array($params)) {
            $params = array_filter($params);
            if (!empty($params)) {
                $params = json_encode($params, JSON_UNESCAPED_UNICODE);
            } else {
                $params = '';
            }
        }

        $data = [
            'name' => $name,
            'controller' => $this->controller,
            'action' => $this->action,
            'type' => $type,
            'params' => $params,
            'date' => date('Y-m-d H:i:s'),
            'ip' => $this->request->getClientIPAddress()
        ];

        $act_info = $this->link->get($this->t_act_log, 'count(id) has, min(id) del_act_id', [
            'name' => $name,
            'type' => $type
        ]);

        if ($act_info['has'] >= self::MAX_ACT_LOG) {
            $this->link->del($this->t_act_log, ['id' => $act_info['del_act_id']]);
        }

        $this->link->add($this->t_act_log, $data);
    }

    /**
     * 验证当前密码
     *
     * @param string $name
     * @param string $pwd
     * @return bool
     * @throws CoreException
     */
    function checkPassword($name, $pwd)
    {
        $admin_info = $this->link->get($this->t_admin, '*', ['name' => $name]);
        return $admin_info ['password'] === self::genPassword($pwd, $admin_info['salt']);
    }

    /**
     * 更新密码
     *
     * @param string $name
     * @param string $pwd
     * @return ResponseData
     * @throws CoreException
     */
    function updatePassword(string $name, string $pwd): ResponseData
    {
        $np = self::genPassword($pwd, $salt);
        $data = [
            'password' => $np,
            'salt' => $salt
        ];

        $status = $this->link->update($this->t_admin, $data, ['name' => $name]);
        if ($status !== false) {
            return $this->responseData(1);
        }

        return $this->responseData(100214);
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