<?php

namespace app\cli\views;

use Cross\Exception\CoreException;
use Cross\Core\Helper;
use Cross\MVC\View;

/**
 * Class PropertyView
 * @package app\cli\views
 */
class PropertyView extends View
{
    /**
     * 单个类生成
     *
     * @param array $data
     * @return bool|int
     */
    function genClass($data = array())
    {
        $content = $this->obRenderTpl('property/default', $data);
        $classSavePath = PROJECT_REAL_PATH . trim($data['namespace'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        Helper::createFolders($classSavePath);
        return file_put_contents($classSavePath . "{$data['className']}.php", $content);
    }

    /**
     * 生成默认配置文件
     *
     * @param string $propertyFile
     * @return bool|int
     */
    function makePropertyFile($propertyFile)
    {
        $content = $this->tpl('property/property.config', true, false);
        return file_put_contents($propertyFile, $content);
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

            $result .= "'{$ak}' => {$v},";
        }

        return $result;
    }
}
