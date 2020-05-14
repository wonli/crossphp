<?php

namespace app\cli\views;

use Cross\Exception\CoreException;
use Cross\Core\Helper;
use Cross\MVC\View;

/**
 * Class PropertyView
 * @package app\cli\views
 */
class ModelView extends View
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
        $classAbsoluteFile = $classSavePath . $data['name'] . '.php';
        if (file_exists($classAbsoluteFile)) {
            //处理用户自定义代码
            $classContent = file_get_contents($classAbsoluteFile);
            preg_match("/.*autoGenCodeFlag;(\n|\r\n)(.*)}/s", $classContent, $matches);
            $userCodeSegment = &$matches[2];
            if (!empty($userCodeSegment)) {
                $content = preg_replace("/(.*autoGenCodeFlag;(\n|\r\n).*?)/s", "$1{$userCodeSegment}", $content);
            }
        }

        Helper::createFolders($classSavePath);
        return file_put_contents($classAbsoluteFile, $content);
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
        foreach ($data as $f => $info) {
            if ($i != 0) {
                $space = '    ';
            } else {
                $space = '';
            }

            $fieldsTpl = sprintf('%spublic $%s = null;', $space, $f);
            if (!empty($info['comment'])) {
                $fieldsTpl = sprintf('%spublic $%s = null; //%s', $space, $f, $info['comment']);
            }

            echo $fieldsTpl . PHP_EOL;
            $i++;
        }
    }

    /**
     * 生成类名
     *
     * @param string $name
     * @param string $type
     */
    protected function makeObjectName($name, $type)
    {
        $objs = [];
        if ($type == 'class') {
            $objs[] = 'use Cross\Model\SQLModel;';
            $objs[] = PHP_EOL;
            $className = sprintf('%s %s extends SQLModel' . PHP_EOL, $type, $name);
        } else {
            $className = sprintf("%s %s" . PHP_EOL, $type, $name);
        }

        $objs[] = $className;
        echo implode(PHP_EOL, $objs);
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
                echo '        \'' . $mate_key . '\' => [' . $this->fieldsConfig($mate_info) . '],' . PHP_EOL;
            } else {
                echo '\'' . $mate_key . '\' => [' . $this->fieldsConfig($mate_info) . '],' . PHP_EOL;
            }
            $i++;
        }
    }

    /**
     * 生成数组属性
     *
     * @param array $data
     * @throws CoreException
     */
    protected function makeArrayProperty(array $data)
    {
        $i = 0;

        foreach ($data as $name => $value) {
            if ($i != 0) {
                echo '        \'' . $name . '\' => ' . $this->getDefaultValue($value) . ',' . PHP_EOL;
            } else {
                echo '\'' . $name . '\' => ' . $this->getDefaultValue($value) . ',' . PHP_EOL;
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
    protected function getDefaultValue($value)
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
                default:
                    $v = $this->getDefaultValue($av);
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
