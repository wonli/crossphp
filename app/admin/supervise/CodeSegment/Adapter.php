<?php
/**
 * @author wonli <wonli@live.com>
 * Adapter.php
 */


namespace app\admin\supervise\CodeSegment;


abstract class Adapter
{
    /**
     * @var array
     */
    protected $struct = [];

    /**
     * @var array
     */
    protected $defaultTokens = [
        'float' => 'float',
        'int' => 'int',
        'bool' => 'bool',
        'string' => 'string',
    ];

    /**
     * Adapter constructor.
     * @param array $data
     */
    function __construct(array $data)
    {
        $this->gen1($data, $this->struct);
    }

    /**
     * @return mixed
     */
    abstract function gen();

    /**
     * 类成员属性代码片段
     *
     * @param string $token
     * @param string $propertiesName
     * @return mixed
     */
    abstract function makeProperties($token, $propertiesName);

    /**
     * 类成员属性对应的JSON代码
     *
     * @param string $propertiesName
     * @param string $name
     * @param string $token
     * @return mixed
     */
    abstract function propertieToJson($propertiesName, $name, $token = '');

    /**
     * 类模版
     *
     * @param string $className
     * @param string $classBody
     * @return mixed
     */
    abstract function genClass($className, $classBody);

    /**
     * 字段类型
     *
     * @return array
     */
    abstract function getTokens();

    /**
     * 生成中间树结构
     *
     * @param array $struct
     * @param array $result
     */
    protected function gen1(array $struct, &$result)
    {
        $tokens = $this->getTokens();
        if (empty($tokens)) {
            $tokens = $this->defaultTokens;
        }

        foreach ($struct as $name => $data) {
            if (is_array($data)) {
                $type = 'class';
                if (isset($data['[list]'])) {
                    $type = 'list';
                    $data = $data['[list]'];
                }

                $item = [];
                $this->gen1($data, $item);

                $result[$name] = array(
                    'type' => $type,
                    'segment' => $item,
                );
            } else {

                $token = $tokens['string'];
                if (isset($tokens[$data])) {
                    $token = $tokens[$data];
                }

                //属性使用小驼峰命名法
                $camelName = $this->toCamelCase($name);
                $properties = $this->makeProperties($token, $camelName);
                $json = $this->propertieToJson($camelName, $name, $token);

                $result[$name] = array(
                    'type' => 'properties',
                    'segment' => array(
                        'properties' => $properties,
                        'json' => $json,
                    ),
                );
            }
        }
    }

    /**
     * 命名转换(默认小驼峰, 否则大驼峰)
     *
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function toCamelCase($name, $type = 'camel')
    {
        $result = '';
        $strLen = strlen($name);
        for ($i = 0; $i < $strLen; $i++) {
            if ($i == 0) {
                if ($type == 'camel') {
                    $result .= strtolower($name[$i]);
                } else {
                    $result .= strtoupper($name[$i]);
                }
            } elseif ($name[$i] == '_' || $name[$i] == '-') {
                $name[$i + 1] = strtoupper($name[$i + 1]);
                continue;
            } else {
                $result .= $name[$i];
            }
        }

        return $result;
    }
}