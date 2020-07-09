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
     * @var array
     */
    protected $commandAlias = [
        'f' => 'file|model配置文件名称(默认main)'
    ];

    /**
     * 命名空间前缀
     *
     * @var string
     */
    protected $namespacePrefix;

    /**
     * 生成结构体
     *
     * @param string $name
     * @throws CoreException
     */
    function index($name = '')
    {
        $fileName = $this->command('file', true) ?: 'main';
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
            $this->consoleMsg("Please specify {$propertyType}::{$modelName} Specifying namespaces");
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
                        throw new CoreException('Please specify the sub-table fields');
                    }

                    $prefix = &$splitConfig['prefix'];
                    if (null === $prefix) {
                        throw new CoreException('Please specify the sub-table prefix');
                    }

                    $number = &$splitConfig['number'];
                    if (null === $number) {
                        $number = 32;
                    } elseif (!is_numeric($number) || $number > 2048) {
                        throw new CoreException('The number of sub-tables only supports numbers and cannot be greater than 2048！');
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
                throw new CoreException('Please specify the table name');
            }

            $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $modelConfig['namespace']);
            if (!empty($modelConfig['path'])) {
                $genPath = rtrim($modelConfig['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            } else {
                $genPath = PROJECT_REAL_PATH . trim($namespacePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }

            //配置文件路径
            $configPath = $this->delegate->getConfig()->get('path', 'config');
            $configRelativePath = $this->getRelativePath($genPath, $configPath);

            //配置文件名称
            $dbConfigFile = $this->getConfig()->get('sys', 'db_config');
            if (!$dbConfigFile) {
                $dbConfigFile = 'db.config.php';
            }

            $configFileRelativePath = $configRelativePath . $dbConfigFile;
            $mateData = $M->link->getMetaData($M->getPrefix($tableName));
            if (isset($field) && !isset($mateData[$field])) {
                throw new CoreException('The sub-table field does not exist: ' . $field);
            }

            if (empty($mateData)) {
                throw new CoreException('Failed to get data table information');
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
                throw new CoreException('Please set the primary key!');
            }

            //处理Oracle自动序列
            if ($isOracle && empty($sequence) && !empty($modelConfig['autoSequence'])) {
                $seqName = Helper::md10(implode('`', array_keys($mateData)));
                $sequence = strtoupper("auto_{$seqName}_seq");

                //判断是否存在
                $sequenceSQL = "select sequence_name from all_sequences where sequence_name= '{$sequence}'";
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
                    $isCreated = $M->link->rawSql("create sequence {$sequence}
                        increment by 1 --每次加几
                        start with {$startWith} --从几开始
                        nomaxvalue  --不设置最大值
                        nocycle cache 3")->stmt()->execute();
                    if (!$isCreated) {
                        $sequence = '';
                    }
                }
            }

            $data['pk'] = $primaryKey;
            $data['type'] = $propertyType;
            $data['name'] = $modelName;
            $data['model'] = $modelConfig;
            $data['mate_data'] = $mateData;
            $data['namespace'] = $namespace;
            $data['gen_path'] = $genPath;
            $data['db_config_path'] = $configFileRelativePath;
            $data['model_info'] = [
                'n' => $linkName,
                'type' => $linkType,
                'table' => $tableName,
                'sequence' => $sequence
            ];

            $ret = $this->view->genClass($data);
            if (false === $ret) {
                throw new CoreException("Please check directory permissions");
            } else {
                $this->consoleMsg("{$propertyType}::{$namespace}\\{$modelName} [success]");
            }

        } catch (Exception $e) {
            $this->consoleMsg("{$propertyType}::{$namespace}\\{$modelName} [fail : !! " . $e->getMessage() . ']');
        }
    }

    /**
     * 计算相对路径
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    function getRelativePath(string $from, string $to): string
    {
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;
        foreach ($from as $depth => $dir) {
            if ($dir === $to[$depth]) {
                array_shift($relPath);
            } else {
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
