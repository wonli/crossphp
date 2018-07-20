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
class Property extends Cli
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
        $configName = "config::{$fileName}.property.php";
        $propertyFile = $this->getFilePath($configName);
        if (!file_exists($propertyFile)) {
            $this->flushMessage("是否生成配置文件 {$configName} (y/n) - ", false);
            $response = trim(fgetc(STDIN));
            if (0 === strcasecmp($response, 'y')) {
                //生成配置文件
                $ret = $this->view->makePropertyFile($propertyFile);
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

            $this->makeProperty($propertyConfig[$name]);
        } elseif (!empty($propertyConfig)) {
            foreach ($propertyConfig as $name => $config) {
                $this->makeProperty($config);
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
    private function makeProperty($config)
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
            if (!empty($config['property'])) {
                foreach ($config['property'] as $propertyClass => $genConfig) {
                    $this->genClass($genConfig, $propertyClass, $db, $config['type']);
                }
            }
        }
    }

    /**
     * 生成类
     *
     * @param string $tableName
     * @param string $propertyClassName
     * @param string $db
     * @param string $propertyType 生成类的类型
     * @param array $table_config
     * @throws CoreException
     */
    private function genClass($tableName, $propertyClassName, $db = '', $propertyType = 'class', $table_config = array())
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
        $link_type = $M->getLinkType();
        $link_name = $M->getLinkName();

        $propertyClassName = str_replace('/', '\\', $propertyClassName);
        $propertyClassName = trim($propertyClassName, '\\');
        $pos = strrpos($propertyClassName, '\\');

        if ($pos) {
            $className = substr($propertyClassName, $pos + 1);
            $namespace = substr($propertyClassName, 0, $pos);
            if ($this->namespacePrefix) {
                $namespace = $this->namespacePrefix . '\\' . $namespace;
            }
        } else {
            $className = $propertyClassName;
            $namespace = $this->namespacePrefix;
        }

        if (empty($namespace)) {
            $this->endFlushMessage("请为 {$propertyType}::{$className} 指定命名空间");
        }

        try {

            $mate_data = $M->link->getMetaData($tableName);
            $primary_key = &$table_config['primary_key'];
            if (empty($primary_key)) {
                foreach ($mate_data as $key => $value) {
                    if ($value['primary'] && empty($primary_key)) {
                        $primary_key = $key;
                        break;
                    }
                }
            }

            $data['table'] = $tableName;
            $data['mate_data'] = $mate_data;
            $data['link_name'] = $link_name;
            $data['link_type'] = $link_type;
            $data['table_config'] = $table_config;
            $data['propertyType'] = $propertyType;
            $data['namespace'] = $namespace;
            $data['className'] = $className;
            $data['primary_key'] = $primary_key;

            $ret = $this->view->genClass($data, 'makeOne');
            if (false === $ret) {
                throw new CoreException("请检查目录权限");
            } else {
                $this->flushMessage("{$propertyType}::{$namespace}\\{$className} [成功]");
            }

        } catch (Exception $e) {
            $this->flushMessage("{$propertyType}::{$namespace}\\{$className} [失败 : !! " . $e->getMessage() . ']');
        }
    }
}
