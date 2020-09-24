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
    function genClass(array $data = [])
    {
        Helper::createFolders($data['genPath']);
        $content = $this->obRenderTpl('model/class', $data);
        $classAbsoluteFile = &$data['genAbsoluteFile'];
        if ($data['genFileExists']) {
            $fileContent = file_get_contents($classAbsoluteFile);
            preg_match("/(.*){$data['type']} {$data['modelName']}(?:.*__construct\(\))(.*)/s", $fileContent, $matches);
            $header = &$matches[1];
            if (!empty($header)) {
                $content = preg_replace("/(.*)({$data['type']} {$data['modelName']}.*)/s", "{$header}$2", $content);
            }

            $userCodeSegment = &$matches[2];
            if (!empty($userCodeSegment)) {
                $content = preg_replace("/(.*__construct\(\))(.*)/s", "$1{$userCodeSegment}", $content);
            }
        }

        return $this->saveContent($classAbsoluteFile, $content);
    }

    /**
     * 生成model信息类
     *
     * @param array $data
     * @return bool|int
     */
    function genModelInfo(array $data = [])
    {
        $content = $this->obRenderTpl('model/modelInfo', $data);
        $classAbsoluteFile = $data['genPath'] . $data['name'] . '.php';
        return $this->saveContent($classAbsoluteFile, $content);
    }

    /**
     * 生成默认配置文件
     *
     * @param string $propertyFile
     * @return bool|int
     */
    function makeModelFile(string $propertyFile)
    {
        $content = $this->tpl('model/model.config', true, false);
        return $this->saveContent($propertyFile, $content);
    }

    /**
     * 保存内容
     *
     * @param string $absoluteFileAddr
     * @param string $content
     * @return false|int
     */
    function saveContent(string $absoluteFileAddr, string $content)
    {
        $dir = dirname($absoluteFileAddr);
        Helper::createFolders($dir);
        $content = str_replace("\r\n", "\n", $content);
        return file_put_contents($absoluteFileAddr, $content);
    }

    /**
     * 生成字段属性
     *
     * @param array $data
     */
    protected function makeModelFields(array $data): void
    {
        $i = 0;
        foreach ($data as $f => $info) {
            if ($i != 0) {
                $space = '    ';
            } else {
                $space = '';
            }

            if (isset($info['userValue']) && null !== $info['userValue']) {
                $userValue = $info['userValue'];
            } else {
                $userValue = 'null';
            }

            $fieldsTpl = sprintf('%spublic $%s = %s;', $space, $f, $userValue);
            if (!empty($info['comment'])) {
                $fieldsTpl = sprintf('%spublic $%s = %s; //%s', $space, $f, $userValue, $info['comment']);
            }

            echo $fieldsTpl . PHP_EOL;
            $i++;
        }
    }

    /**
     * 生成类名
     *
     * @param string $name
     * @param string $modelName
     * @param string $type
     * @return string
     */
    protected function makeClassName(string $name, string $modelName, string $type): string
    {
        $objs = [];
        if ($type == 'class') {
            $objs[] = 'use Cross\Model\SQLModel;';
            $objs[] = PHP_EOL;
            $objs[] = "use {$modelName};";
            $objs[] = PHP_EOL;
            $className = sprintf('%s %s extends SQLModel' . PHP_EOL, $type, $name);
        } else {
            $className = sprintf("%s %s" . PHP_EOL, $type, $name);
        }

        $objs[] = $className;
        return implode(PHP_EOL, $objs);
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function makeModelInfoClassName(string $name, string $type): string
    {
        $objs = [];
        if ($type == 'class') {
            $objs[] = 'use Cross\I\IModelInfo;';
            $objs[] = PHP_EOL;
            $className = sprintf('%s %s implements IModelInfo' . PHP_EOL, $type, $name);
        } else {
            $className = sprintf("%s %s" . PHP_EOL, $type, $name);
        }

        $objs[] = $className;
        return implode(PHP_EOL, $objs);
    }

    /**
     * 生成属性字段
     *
     * @param array $data
     * @throws CoreException
     */
    protected function makeModelInfo(array $data): void
    {
        $i = 0;
        foreach ($data as $mateKey => $mateInfo) {
            if ($i != 0) {
                echo '        \'' . $mateKey . '\' => [' . $this->fieldsConfig($mateInfo) . '],' . PHP_EOL;
            } else {
                echo '\'' . $mateKey . '\' => [' . $this->fieldsConfig($mateInfo) . '],' . PHP_EOL;
            }
            $i++;
        }
    }

    /**
     * 生成数组属性
     *
     * @param array $data
     * @param int $addSpace
     * @param bool $inline
     * @return string
     * @throws CoreException
     */
    protected function makeArrayProperty(array $data, int $addSpace = 0, bool $inline = false): string
    {
        $i = 0;
        $result = [];
        foreach ($data as $name => $value) {
            $result[] = $this->makeArrayMemberInline($name, $value, $inline ? 0 : $i++, $addSpace);
        }

        if ($inline) {
            return implode(', ', $result);
        } else {
            return implode(',' . PHP_EOL, $result);
        }
    }

    /**
     * 生成数组属性
     *
     * @param mixed $name
     * @param mixed $value
     * @param int $line
     * @param int $addSpace
     * @return string
     * @throws CoreException
     */
    protected function makeArrayMemberInline($name, $value, int $line = 0, int $addSpace = 0): string
    {
        $space = '';
        if ($addSpace > 0) {
            $space = str_pad($space, $addSpace, ' ');
        }

        if (is_numeric($name)) {
            $nameValue = $name;
        } else {
            $nameValue = "'" . $name . "'";
        }

        if ($line != 0) {
            return $space . $nameValue . ' => ' . $this->getDefaultValue($value);
        } else {
            return $nameValue . ' => ' . $this->getDefaultValue($value);
        }
    }

    /**
     * 生成连接配置
     *
     * @param array $data
     * @return string
     * @throws CoreException
     */
    protected function makeConnectInfo(array $data): string
    {
        $i = 0;
        $result = [];
        foreach ($data as $name => $value) {
            if (is_array($value)) {
                $result[] = sprintf("            '%s' => [%s]", $name, $this->makeArrayProperty($value, 0, true));
            } else {
                $result[] = $this->makeArrayMemberInline($name, $value, $i++, 12);
            }
        }

        return implode(',' . PHP_EOL, $result) . PHP_EOL;
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
                    return (int)$value;
                } else {
                    $value = trim($value, "'");
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
