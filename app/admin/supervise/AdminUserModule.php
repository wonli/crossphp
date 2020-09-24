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
     * @param string $codeLocation
     * @param string $codeValue
     * @return ResponseData
     * @throws CoreException
     * @throws Exception
     */
    function checkAdmin(string $username, string $password, $codeLocation = '', $codeValue = ''): ResponseData
    {
        $SEC = new SecurityModule;
        try {
            $userInfo = $this->link->get($this->tAdmin, '*', ['name' => $username]);
        } catch (Exception $e) {
            return $this->responseData(100100);
        }

        if (empty($userInfo)) {
            return $this->responseData(100210);
        }

        if ($userInfo ['t'] != 1) {
            return $this->responseData(100211);
        }

        if ($userInfo && !empty($userInfo['password'])) {
            $isBind = $SEC->checkbind($username);
            if ($isBind) {
                if (empty($codeLocation) || empty($codeValue)) {
                    return $this->responseData(100300);
                }

                $verify = $SEC->verifyCode($username, $codeLocation, $codeValue);
                if (!$verify) {
                    return $this->responseData(100301);
                }
            }

            $userPassword = self::genPassword($password, $userInfo['salt']);
            if ($userPassword === $userInfo['password']) {

                //更新登录信息
                $token = Helper::random(32);
                $this->update($userInfo['id'], [
                    'token' => $token,
                    'last_login_date' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $this->request->getClientIPAddress()
                ]);

                return $this->responseData(1, [
                    'id' => $userInfo['id'],
                    'rid' => $userInfo['rid'],
                    'name' => $userInfo['name'],
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
    function getAdminUserList(array &$page = ['p' => 1, 'limit' => 10])
    {
        return $this->link->find("{$this->tAdmin} a LEFT JOIN {$this->tSecurityCard} s ON a.name=s.bind_user",
            'a.*, s.id bind_id', ['a.rid' => ['>', 0]], $page);
    }

    /**
     * 新增加管理员
     *
     * @param array $data
     * @return ResponseData
     * @throws CoreException
     * @throws Exception
     */
    function addAdmin(array $data): ResponseData
    {
        if (empty($data['name']) || empty($data['password'])) {
            return $this->responseData(100410);
        }

        $ai = $this->link->get($this->tAdmin, '*', ['name' => $data['name']]);
        if ($ai) {
            return $this->responseData(100411);
        }

        $data['last_login_ip'] = '';
        $data['password'] = self::genPassword($data['password'], $data['salt']);
        $id = $this->link->add($this->tAdmin, $data);
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
        return $this->link->get($this->tAdmin, '*', $condition);
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
        $ret = $this->link->del($this->tAdmin, $condition);
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
     * @throws Exception
     */
    function update(int $id, array $data): ResponseData
    {
        unset($data['id']);
        $adminInfo = $this->link->get($this->tAdmin, '*', ['id' => $id]);
        if (!$adminInfo) {
            return $this->responseData(100400);
        }

        //更新用户名
        if (isset($data['name'])) {
            $nameUser = $this->link->get($this->tAdmin, '*', ['name' => $data['name']]);
            if (!empty($nameUser) && $nameUser['id'] != $id) {
                return $this->responseData(100420);
            }
        }

        //重新生成密码
        if (isset($data['password']) && ($data['password'] !== $adminInfo['password'])) {
            $data ['password'] = self::genPassword($data['password'], $data['salt']);
        }

        $ret = $this->link->update($this->tAdmin, $data, ['id' => $id]);
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
    function updateActLog(string $name, $params, string $type = 'post')
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

        $actInfo = $this->link->get($this->tActLog, 'count(id) has, min(id) del_act_id', [
            'name' => $name,
            'type' => $type
        ]);

        if ($actInfo['has'] >= self::MAX_ACT_LOG) {
            $this->link->del($this->tActLog, ['id' => $actInfo['del_act_id']]);
        }

        $this->link->add($this->tActLog, $data);
    }

    /**
     * 验证当前密码
     *
     * @param string $name
     * @param string $pwd
     * @return bool
     * @throws CoreException
     * @throws Exception
     */
    function checkPassword(string $name, string $pwd)
    {
        $adminInfo = $this->link->get($this->tAdmin, '*', ['name' => $name]);
        return $adminInfo ['password'] === self::genPassword($pwd, $adminInfo['salt']);
    }

    /**
     * 更新密码
     *
     * @param string $name
     * @param string $pwd
     * @return ResponseData
     * @throws CoreException
     * @throws Exception
     */
    function updatePassword(string $name, string $pwd): ResponseData
    {
        $np = self::genPassword($pwd, $salt);
        $data = [
            'password' => $np,
            'salt' => $salt
        ];

        $status = $this->link->update($this->tAdmin, $data, ['name' => $name]);
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
     * @throws Exception
     */
    static function genPassword(string $password, &$salt = '')
    {
        if (empty($salt)) {
            $salt = Helper::random(16);
        }

        return hash('sha256', $salt . $password);
    }
}