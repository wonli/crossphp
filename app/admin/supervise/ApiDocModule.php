<?php
/**
 * @author wonli <wonli@live.com>
 * ApiDocModuleule.php
 */


namespace app\admin\supervise;

use Cross\Exception\CoreException;
use Cross\Core\Helper;
use PDO;

/**
 * @author wonli <wonli@live.com>
 *
 * Class ApiDocModule
 * @package app\admin\supervise
 */
class ApiDocModule extends AdminModule
{
    const KEY_HOST = 'host';
    const KEY_GLOBAL_PARAMS = 'global_params';
    const KEY_HEADER_PARAMS = 'header_params';

    /**
     * 获取单条数据
     *
     * @param int $id
     * @param int|null $rid 当rid有值时判断角色权限
     * @return mixed
     * @throws CoreException
     */
    function get(int $id, ?int $rid = null)
    {
        $data = $this->link->get($this->tApiDoc, '*', [
            'id' => $id,
        ]);

        if (!empty($data)) {
            if (null !== $rid && !self::checkDocRoleLimit($rid, $data)) {
                return false;
            }

            if (!empty($data['servers'])) {
                $data['servers'] = json_decode($data['servers'], true);
            } else {
                $data['servers'] = [];
            }

            if (!empty($data['global_params'])) {
                $data['global_params'] = json_decode($data['global_params'], true);
            } else {
                $data['global_params'] = [];
            }

            if (!empty($data['header_params'])) {
                $data['header_params'] = json_decode($data['header_params'], true);
            } else {
                $data['header_params'] = [];
            }
        }

        return $data;
    }

    /**
     * 添加
     *
     * @param array $data
     * @return bool|mixed
     * @throws CoreException
     */
    function add($data = [])
    {
        return $this->link->add($this->tApiDoc, $data);
    }

    /**
     * 更新
     *
     * @param int $id
     * @param mixed $data
     * @return bool
     * @throws CoreException
     */
    function update(int $id, $data)
    {
        return $this->link->update($this->tApiDoc, $data, [
            'id' => (int)$id,
        ]);
    }

    /**
     * 获取所有数据
     *
     * @param int|null $rid
     * @return mixed
     * @throws CoreException
     */
    function getAll(?int $rid = null)
    {
        $result = [];
        $apiListData = $this->link->getAll($this->tApiDoc, '*');
        if (!empty($apiListData)) {
            foreach ($apiListData as $index => $d) {
                $has = self::checkDocRoleLimit($rid, $d);
                if ($has) {
                    $result[] = $d;
                }
            }
        }

        return $result;
    }

    /**
     * 删除
     *
     * @param int $id
     * @throws CoreException
     */
    function del(int $id)
    {
        $data = $this->link->get($this->tApiDoc, '*', [
            'id' => $id,
        ]);

        if (!empty($data)) {
            $this->link->del($this->tApiDoc, [
                'id' => $id
            ]);

            $this->link->del($this->tApiDocUser, [
                'doc_id' => $id
            ]);

            $data = json_decode($data['servers'], true);
            foreach ($data as $d) {
                @unlink($this->getCacheFilePathFromCacheName($d['cache_name']));
            }
        }
    }

    /**
     * 获取用户所有设置
     *
     * @param string $u
     * @param int $docId
     * @return mixed
     * @throws CoreException
     */
    function getAllUserData(string $u, int $docId)
    {
        $result = [];
        $data = $this->link->getAll($this->tApiDocUser, '*', [
            'u' => $u,
            'doc_id' => $docId,
        ]);

        if (!empty($data)) {
            array_walk($data, function ($d) use (&$result) {
                $d['value'] = json_decode($d['value'], true);
                $result[$d['name']] = $d['value'];
            });
        }

        return $result;
    }

    /**
     * 获取用户数据
     *
     * @param string $u
     * @param int $docId
     * @param string $name
     * @return bool|array
     * @throws CoreException
     */
    function getUserData(string $u, int $docId, string $name)
    {
        $data = $this->link->get($this->tApiDocUser, '*', [
            'u' => $u,
            'doc_id' => $docId,
            'name' => $name,
        ]);

        if (!empty($data)) {
            $data['value'] = json_decode($data['value'], true);
        }

        return $data;
    }

    /**
     * 添加用户数据
     *
     * @param string $u
     * @param int $docId
     * @param string $name
     * @param array $value
     * @return bool|mixed
     * @throws CoreException
     */
    function addUserData(string $u, int $docId, string $name, array $value)
    {
        $data = [
            'u' => $u,
            'doc_id' => $docId,
            'name' => $name,
            'value' => json_encode($value),
        ];

        return $this->link->add($this->tApiDocUser, $data);
    }

    /**
     * 缓存接口结构
     *
     * @param int $docId
     * @param string $apiPath
     * @param array $structData
     * @return bool|mixed
     * @throws CoreException
     */
    function saveCache(int $docId, string $apiPath, array $structData)
    {
        $apiPath = '/' . ltrim($apiPath, '/');
        $cacheInfo = $this->link->get($this->tApiDocData, 'cache_id', [
            'doc_id' => $docId,
            'api_path' => $apiPath,
        ]);

        $data['doc_id'] = $docId;
        $data['api_path'] = $apiPath;
        $data['api_response'] = json_encode($structData);
        $data['cache_at'] = TIME;
        if (!empty($cacheInfo)) {
            return $this->link->update($this->tApiDocData, $data, [
                'cache_id' => $cacheInfo['cache_id']
            ]);
        } else {
            return $this->link->add($this->tApiDocData, $data);
        }
    }

    /**
     * 返回文档接口所有缓存数据
     *
     * @param int $docId
     * @return array
     * @throws CoreException
     */
    function getCacheData(int $docId)
    {
        return $this->link->select('api_path, api_response')
            ->from($this->tApiDocData)->where(['doc_id' => $docId])
            ->stmt()->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_KEY_PAIR);
    }

    /**
     * 更新用户数据
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws CoreException
     */
    function updateUserData(int $id, array $data)
    {
        return $this->link->update($this->tApiDocUser, $data, [
            'id' => $id
        ]);
    }

    /**
     * 获取缓存文件绝对路径
     *
     * @param string $name
     * @return string
     */
    function getCacheFilePathFromCacheName(string $name)
    {
        $file = $this->getCachePath() . DIRECTORY_SEPARATOR . $name . '.yaml';
        if (file_exists($file)) {
            return $file;
        }

        return '';
    }

    /**
     * 缓存文件路径
     *
     * @return string
     */
    function getCachePath()
    {
        static $yamlFileCachePath = null;
        if (null === $yamlFileCachePath) {
            $yamlFileCachePath = $this->getFilePath('cache::doc');
            if (!is_dir($yamlFileCachePath)) {
                Helper::createFolders($yamlFileCachePath);
            }
        }
        return $yamlFileCachePath;
    }

    /**
     * 检查角色是否有文档相关权限
     *
     * @param int $rid
     * @param array $docData
     * @return bool
     */
    static function checkDocRoleLimit(int $rid, array $docData): bool
    {
        $roleLimit = $docData['role_limit'] ?? '';
        if ('' !== $roleLimit) {
            $roleConfig = explode(',', trim($roleLimit));
            if (!empty($roleConfig) && !in_array($rid, $roleConfig)) {
                return false;
            }
        }

        return true;
    }
}