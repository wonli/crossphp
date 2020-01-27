<?php

namespace app\cli\views;

use Cross\Exception\CoreException;
use Cross\Core\Helper;

/**
 * Class PropertyView
 * @package app\cli\views
 */
class ModelView extends CliView
{
    /**
     * 单个类生成
     *
     * @param array $data
     * @return bool|int
     */
    function genClass($data = array())
    {
        $content = $this->obRenderTpl('model/default', $data);
        $namespacePath = str_replace('\\', DIRECTORY_SEPARATOR, $data['namespace']);
        $classSavePath = PROJECT_REAL_PATH . trim($namespacePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        Helper::createFolders($classSavePath);
        return file_put_contents($classSavePath . "{$data['name']}.php", $content);
    }

    /**
     * 生成默认配置文件
     *
     * @param string $propertyFile
     * @return bool|int
     */
    function makeModelFile($propertyFile)
    {
        $content = $this->tpl('model/model.config', true, false);
        return file_put_contents($propertyFile, $content);
    }

    /**
     * 生成字段属性
     *
     * @param array $data
     */
    protected function makeModelFields($data)
    {
        $i = 0;
        foreach ($data as $mate_key => $mate_info) {
            if ($i != 0) {
                echo '    public $' . $mate_key . ' = null;' . PHP_EOL;
            } else {
                echo 'public $' . $mate_key . ' = null;' . PHP_EOL;
            }
            $i++;
        }
    }

    /**
     * 生成属性字段
     *
     * @param $data
     * @throws CoreException
     */
    protected function makeModelInfo($data)
    {
        $i = 0;
        foreach ($data as $mate_key => $mate_info) {
            if ($i != 0) {
                echo '        \'' . $mate_key . '\' => array(' . $this->fieldsConfig($mate_info) . '),' . PHP_EOL;
            } else {
                echo '\'' . $mate_key . '\' => array(' . $this->fieldsConfig($mate_info) . '),' . PHP_EOL;
            }
            $i++;
        }
    }

    /**
     * 获取值
     *
     * @param string|null|int $value
     * @return string
     * @throws CoreException
     */
    protected function getFieldsDefaultValue($value)
    {
        if (is_scalar($value)) {
            if (is_bool($value)) {
                if ($value) {
                    return 'true';
                } else {
                    return 'false';
                }
            } else {
                if ($value == '') {
                    return '\'\'';
                } elseif ($value == 'CURRENT_TIMESTAMP') {
                    return '\'CURRENT_TIMESTAMP\'';
                } elseif (is_numeric($value)) {
                    return $value;
                } else {
                    return '\'' . $value . '\'';
                }
            }
        } elseif ($value === null) {
            return 'null';
        } else {
            throw new CoreException('不支持的默认值');
        }
    }

    /**
     * 生成表字段属性
     *
     * @param array $a
     * @return string
     * @throws CoreException
     */
    protected function fieldsConfig(array $a)
    {
        $i = 0;
        $result = '';
        foreach ($a as $ak => $av) {
            $v = '';
            switch ($ak) {
                case 'primary':
                case 'not_null':
                case 'auto_increment':
                    if ($av) {
                        $v = 'true';
                    } else {
                        $v = 'false';
                    }
                    break;

                case 'default_value':
                    $v = $this->getFieldsDefaultValue($av);
            }

            if ($i == 0) {
                $result .= "'{$ak}' => {$v},";
            } else {
                $result .= " '{$ak}' => {$v},";
            }
            $i++;
        }

        return trim($result, ',');
    }
}
