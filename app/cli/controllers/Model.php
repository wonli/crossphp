<?php

namespace app\cli\controllers;

use Cross\Exception\CoreException;
use Cross\Core\Loader;
use Cross\MVC\Module;
use Exception;

/**
 * 从数据库生成结构类
 *
 * Class Property
 * @package app\cli\controllers
 */
class Model extends Cli
{
    /**
     * 命名空间前缀
     *
     * @var string
     */
    private $namespacePrefix;

    /**
     * 生成结构体
     *
     * @cp_params file=main
     * @param string $name
     * @throws CoreException
     */
    function index($name = '')
    {
        $fileName = &$this->params['file'];
        $configName = "config::{$fileName}.model.php";
        $propertyFile = $this->getFilePath($configName);
        if (!file_exists($propertyFile)) {
            $this->flushMessage("是否生成配置文件 {$configName} (y/n) - ", false);
            $response = trim(fgetc(STDIN));
            if (0 === strcasecmp($response, 'y')) {
                //生成配置文件
                $ret = $this->view->makeModelFile($propertyFile);
                if (!$ret) {
                    $this->endFlushMessage('创建配置文件失败');
                }
            } else {
                $this->endFlushMessage('请先创建配置文件');
            }
        }

        $propertyConfig = Loader::read($propertyFile);
        if (!empty($name)) {
            if (!isset($propertyConfig[$name])) {
                $this->endFlushMessage("未发现指定的配置{$name}");
            }

            $this->makeModels($propertyConfig[$name]);
        } elseif (!empty($propertyConfig)) {
            foreach ($propertyConfig as $name => $config) {
                $this->makeModels($config);
            }
        }
    }

    /**
     * @see index
     *
     * @param string $name 指定参数
     * @param array $params
     * @throws CoreException
     * @cp_params file=main
     */
    function __call($name, $params)
    {
        $this->index($name);
    }

    /**
     * @see index
     *
     * @param array $config
     * @throws CoreException
     */
    private function makeModels($config)
    {
        if (!empty($config)) {
            $db = &$config['db'];
            if (empty($db)) {
                $this->endFlushMessage('请指定数据库链接配置');
            }

            if (empty($config['type'])) {
                $this->endFlushMessage('请指定生成类型 class或trait');
            }

            $this->namespacePrefix = &$config['namespace'];
            if (!empty($config['models'])) {
                foreach ($config['models'] as $modelName => $databaseTableName) {
                    $this->genClass($databaseTableName, $modelName, $db, $config['type']);
                }
            }
        }
    }

    /**
     * 生成类
     *
     * @param string $databaseTableName
     * @param string $modelName
     * @param string $db
     * @param string $propertyType 生成类的类型
     * @param array $tableConfig
     * @throws CoreException
     */
    private function genClass($databaseTableName, $modelName, $db = '', $propertyType = 'class', $tableConfig = array())
    {
        if (empty($db)) {
            $key = ':';
        } else {
            $key = $db;
        }

        static $cache;
        if (!isset($cache[$key])) {
            $cache[$key] = new Module($db);
        }

        $allowPropertyType = array('class' => true, 'trait' => true);
        if (!isset($allowPropertyType[$propertyType])) {
            $propertyType = 'class';
        }

        $M = $cache[$key];
        $linkType = $M->getLinkType();
        $linkName = $M->getLinkName();

        $modelName = str_replace('/', '\\', $modelName);
        $modelName = trim($modelName, '\\');
        $pos = strrpos($modelName, '\\');

        if ($pos) {
            $modelName = substr($modelName, $pos + 1);
            $namespace = substr($modelName, 0, $pos);
            if ($this->namespacePrefix) {
                $namespace = $this->namespacePrefix . '\\' . $namespace;
            }
        } else {
            $namespace = $this->namespacePrefix;
        }

        if (empty($namespace)) {
            $this->endFlushMessage("请为 {$propertyType}::{$modelName} 指定命名空间");
        }

        try {

            $mateData = $M->link->getMetaData($databaseTableName);
            $primaryKey = &$tableConfig['primary_key'];
            if (empty($primaryKey)) {
                foreach ($mateData as $key => $value) {
                    if ($value['primary'] && empty($primaryKey)) {
                        $primaryKey = $key;
                        break;
                    }
                }
            }

            $data['link_name'] = $linkName;
            $data['link_type'] = $linkType;
            $data['mate_data'] = $mateData;
            $data['primary_key'] = $primaryKey;
            $data['database_table_name'] = $databaseTableName;
            $data['namespace'] = $namespace;
            $data['type'] = $propertyType;
            $data['name'] = $modelName;

            $ret = $this->view->genClass($data, 'makeOne');
            if (false === $ret) {
                throw new CoreException("请检查目录权限");
            } else {
                $this->flushMessage("{$propertyType}::{$namespace}\\{$modelName} [成功]");
            }

        } catch (Exception $e) {
            $this->flushMessage("{$propertyType}::{$namespace}\\{$modelName} [失败 : !! " . $e->getMessage() . ']');
        }
    }
}
