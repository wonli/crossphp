<?php

namespace app\cli\controllers;

use app\cli\views\ModelView;

use Cross\Core\Helper;
use Cross\Exception\CoreException;
use Cross\Core\Loader;
use Cross\MVC\Module;
use Exception;
use PDO;

/**
 * 从数据库生成结构类
 *
 * Class Property
 * @package app\cli\controllers
 * @property ModelView $view
 */
class Model extends Cli
{
    /**
     * 命名空间前缀
     *
     * @var string
     */
    protected $namespacePrefix;

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
            $this->consoleMsg("是否生成配置文件 {$configName} (y/n) - ", false);
            $response = trim(fgetc(STDIN));
            if (0 === strcasecmp($response, 'y')) {
                //生成配置文件
                $ret = $this->view->makeModelFile($propertyFile);
                if (!$ret) {
                    $this->consoleMsg('创建配置文件失败');
                    return;
                }
            } else {
                $this->consoleMsg('请先创建配置文件');
                return;
            }
        }

        $propertyConfig = Loader::read($propertyFile);
        if (!empty($name)) {
            if (!isset($propertyConfig[$name])) {
                $this->consoleMsg("未发现指定的配置{$name}");
                return;
            }

            $this->makeModels($propertyConfig[$name]);
        } elseif (!empty($propertyConfig)) {
            foreach ($propertyConfig as $name => $config) {
                $this->makeModels($config);
            }
        }
    }

    /**
     * @param string $name 指定参数
     * @param array $params
     * @throws CoreException
     * @cp_params file=main
     * @see index
     *
     */
    function __call($name, $params)
    {
        $this->index($name);
    }

    /**
     * 生成model类
     *
     * @param array $config
     * @throws CoreException
     */
    private function makeModels($config)
    {
        if (!empty($config)) {
            $db = &$config['db'];
            if (empty($db)) {
                $this->consoleMsg('请指定数据库链接配置');
                return;
            }

            if (empty($config['type'])) {
                $this->consoleMsg('请指定生成类型 class或trait');
                return;
            }

            if (empty($config['namespace'])) {
                $this->consoleMsg('请指定类的命名空间');
                return;
            }

            $this->namespacePrefix = str_replace('/', '\\', $config['namespace']);
            if (!empty($config['models'])) {
                foreach ($config['models'] as $modelName => $tableNameConfig) {
                    $this->genClass($tableNameConfig, $modelName, $db, $config['type'], $config);
                }
            }
        }
    }

    /**
     * 生成类
     *
     * @param string $tableNameConfig
     * @param string $modelName
     * @param string $db
     * @param string $propertyType 生成类的类型
     * @param array $modelConfig
     * @throws CoreException
     */
    private function genClass($tableNameConfig, $modelName, $db = '', $propertyType = 'class', $modelConfig = [])
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

        $allowPropertyType = ['class' => true, 'trait' => true];
        if (!isset($allowPropertyType[$propertyType])) {
            $propertyType = 'class';
        }

        /* @var $M Module */
        $M = &$cache[$key];
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
            $this->consoleMsg("请为 {$propertyType}::{$modelName} 指定命名空间");
            return;
        }

        try {
            $sequence = '';
            $data['split_info'] = [];
            if (is_array($tableNameConfig)) {
                //单独处理序号
                if (!empty($tableNameConfig['sequence'])) {
                    $sequence = &$tableNameConfig['sequence'];
                }

                if (!empty($tableNameConfig['split'])) {
                    //处理分表
                    $splitConfig = &$tableNameConfig['split'];
                    $method = &$splitConfig['method'];
                    if (null === $method) {
                        $method = 'hash';
                    }

                    $field = &$splitConfig['field'];
                    if (null === $field) {
                        throw new CoreException('请指定分表字段: field');
                    }

                    $prefix = &$splitConfig['prefix'];
                    if (null === $prefix) {
                        throw new CoreException('请指定分表前缀: prefix');
                    }

                    $number = &$splitConfig['number'];
                    if (null === $number) {
                        $number = 32;
                    } elseif (!is_numeric($number) || $number > 2048) {
                        throw new CoreException('分表数量仅支持数字且不能大于2048！');
                    }

                    $data['split_info'] = [
                        'number' => $number,
                        'method' => $method,
                        'field' => $field,
                        'prefix' => $prefix,
                    ];

                    //分表时默认使用第一张表的结构
                    $tableName = $splitConfig['prefix'] . '0';
                }

                if (empty($tableName) && !empty($tableNameConfig['table'])) {
                    $tableName = &$tableNameConfig['table'];
                }

            } else {
                $tableName = $tableNameConfig;
            }

            if (empty($tableName)) {
                throw new CoreException('请指定表名');
            }

            $connectConfig = $M->getLinkConfig();
            $mateData = $M->link->getMetaData($M->getPrefix($tableName));
            if (isset($field) && !isset($mateData[$field])) {
                throw new CoreException('分表字段不存在: ' . $field);
            }

            if (empty($mateData)) {
                throw new CoreException('获取数据表信息失败');
            }

            $primaryKey = null;
            $isOracle = (0 === strcasecmp($linkType, 'oracle'));
            foreach ($mateData as $key => $set) {
                if ($set['primary']) {
                    if ($isOracle && !empty($set['default_value'])) {
                        $dsq = preg_match("~(.*)\.\"(.*)\"\.nextval.*~", $set['default_value'], $matches);
                        if ($dsq && !empty($matches[2])) {
                            $sequence = &$matches[2];
                        }
                    }
                    $primaryKey = $key;
                    break;
                }
            }

            if (empty($primaryKey)) {
                throw new CoreException('主键未设置');
            }

            if (!empty($sequence)) {
                $connectConfig['sequence'] = $sequence;
            }

            //处理Oracle自动序列
            if ($isOracle && empty($connectConfig['sequence']) && !empty($modelConfig['autoSequence'])) {
                $seqName = Helper::md10(implode('`', array_keys($mateData)));
                $sequenceName = strtoupper("auto_{$seqName}_seq");

                //判断是否存在
                $sequenceSQL = "select sequence_name from all_sequences where sequence_name= '{$sequenceName}'";
                $hasSequences = $M->link->rawSql($sequenceSQL)
                    ->stmt()->fetch(PDO::FETCH_ASSOC);

                if (empty($hasSequences)) {
                    //获取表主键当前最大自增加ID值
                    $rows = $M->link->rawSql("select max($primaryKey) inc from {$tableName}")
                        ->stmt()->fetch(PDO::FETCH_ASSOC);
                    $startWith = 1;
                    if (!empty($rows['INC'])) {
                        $startWith = $rows['INC'] + 1;
                    }

                    //创建sequence
                    $isCreated = $M->link->rawSql("create sequence {$sequenceName}
                        increment by 1 --每次加几
                        start with {$startWith} --从几开始
                        nomaxvalue  --不设置最大值
                        nocycle cache 3")->stmt()->execute();
                    if ($isCreated) {
                        $connectConfig['sequence'] = $sequenceName;
                    }
                } else {
                    $connectConfig['sequence'] = $sequenceName;
                }
            }

            $data['pk'] = $primaryKey;
            $data['type'] = $propertyType;
            $data['name'] = $modelName;
            $data['model'] = $modelConfig;
            $data['connect'] = $connectConfig;
            $data['mate_data'] = $mateData;
            $data['namespace'] = $namespace;
            $data['model_info'] = [
                'n' => $linkName,
                'type' => $linkType,
                'table' => $tableName
            ];

            $ret = $this->view->genClass($data);
            if (false === $ret) {
                throw new CoreException("请检查目录权限");
            } else {
                $this->consoleMsg("{$propertyType}::{$namespace}\\{$modelName} [成功]");
            }

        } catch (Exception $e) {
            $this->consoleMsg("{$propertyType}::{$namespace}\\{$modelName} [失败 : !! " . $e->getMessage() . ']');
        }
    }
}
