<?php
/**
 * @author wonli <wonli@live.com>
 * ApiDocModuleule.php
 */


namespace app\admin\supervise;

use Cross\Core\Helper;

/**
 * @author wonli <wonli@live.com>
 *
 * Class ApiDocModule
 * @package app\admin\supervise
 */
class ApiDocModule extends AdminModule
{
    const KEY_HOST = 'host';
    const KEY_GLOBALPARAMS = 'global_params';
    const KEY_HEADERPARAMS = 'header_params';

    /**
     * 获取单条数据
     *
     * @param int $id
     * @return mixed
     * @throws \Cross\Exception\CoreException
     */
    function get($id)
    {
        $data = $this->link->get($this->t_api_doc, '*', array(
            'id' => $id,
        ));

        if (!empty($data)) {
            $servers = &$data['servers'];
            $servers = json_decode($servers, true);
            foreach ($servers as &$s) {
                $s['cache_file'] = $this->getCacheFilePathFromCacheName($s['cache_name']);
            }

            if(!empty($data['global_params'])) {
                $data['global_params'] = json_decode($data['global_params'], true);
            } else {
                $data['global_params'] = array();
            }

            if(!empty($data['header_params'])) {
                $data['header_params'] = json_decode($data['header_params'], true);
            } else {
                $data['header_params'] = array();
            }
        }

        return $data;
    }

    /**
     * 添加
     *
     * @param array $data
     * @return bool|mixed
     * @throws \Cross\Exception\CoreException
     */
    function add($data = array())
    {
        return $this->link->add($this->t_api_doc, $data);
    }

    /**
     * 更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function update($id, $data)
    {
        return $this->link->update($this->t_api_doc, $data, array(
            'id' => (int)$id,
        ));
    }

    /**
     * 获取所有数据
     *
     * @throws \Cross\Exception\CoreException
     */
    function getAll()
    {
        $data = $this->link->getAll($this->t_api_doc, '*');
        if (!empty($data)) {
            array_walk($data, function (&$d) {
                $servers = &$d['servers'];
                $servers = json_decode($servers, true);
                if (!empty($servers)) {
                    array_walk($servers, function (&$dd) {
                        $dd['cache_file'] = $this->getCacheFilePathFromCacheName($dd['cache_name']);

                        if(!empty($dd['global_params'])) {
                            $dd['global_params'] = json_decode($dd['global_params'], true);
                        } else {
                            $dd['global_params'] = array();
                        }

                        if(!empty($dd['header_params'])) {
                            $dd['header_params'] = json_decode($dd['header_params'], true);
                        } else {
                            $dd['header_params'] = array();
                        }
                    });
                }
            });
        }

        return $data;
    }

    /**
     * 删除
     *
     * @param int $id
     * @throws \Cross\Exception\CoreException
     */
    function del($id)
    {
        $data = $this->link->get($this->t_api_doc, '*', array(
            'id' => $id,
        ));

        if (!empty($data)) {
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
     * @param int $doc_id
     * @return mixed
     * @throws \Cross\Exception\CoreException
     */
    function getAllUserData($u, $doc_id)
    {
        $result = array();
        $data = $this->link->getAll($this->t_api_doc_data, '*', array(
            'u' => $u,
            'doc_id' => $doc_id,
        ));

        if (!empty($data)) {
            array_walk($data, function ($d) use(&$result) {
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
     * @param int $doc_id
     * @param string $name
     * @return bool|array
     * @throws \Cross\Exception\CoreException
     */
    function getUserData($u, $doc_id, $name)
    {
        $data = $this->link->get($this->t_api_doc_data, '*', array(
            'u' => $u,
            'doc_id' => $doc_id,
            'name' => $name,
        ));

        if (!empty($data)) {
            $data['value'] = json_decode($data['value'], true);
        }

        return $data;
    }

    /**
     * 添加用户数据
     *
     * @param string $u
     * @param int $doc_id
     * @param string $name
     * @param array $value
     * @return bool|mixed
     * @throws \Cross\Exception\CoreException
     */
    function addUserData($u, $doc_id, $name, array $value)
    {
        $data = array(
            'u' => $u,
            'doc_id' => $doc_id,
            'name' => $name,
            'value' => json_encode($value),
        );

        return $this->link->add($this->t_api_doc_data, $data);
    }

    /**
     * 更新用户数据
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws \Cross\Exception\CoreException
     */
    function updateUserData($id, array $data)
    {
        return $this->link->update($this->t_api_doc_data, $data, array(
            'id' => (int)$id
        ));
    }

    /**
     * 获取缓存文件绝对路径
     *
     * @param string $name
     * @return string
     */
    function getCacheFilePathFromCacheName($name)
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
}