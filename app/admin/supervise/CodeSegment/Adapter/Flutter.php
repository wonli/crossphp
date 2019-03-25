<?php
/**
 * @author wonli <wonli@live.com>
 * Flutter.php
 */

namespace app\admin\supervise\CodeSegment\Adapter;

use app\admin\supervise\CodeSegment\Adapter;

class Flutter extends Adapter
{
    /**
     * 单类容器
     *
     * @var array
     */
    protected $singleClass;

    /**
     * 防重名
     *
     * @var array
     */
    protected $usedClassName;

    /**
     * @return string
     */
    function gen()
    {
        $code = '';
        $this->doGen($this->struct, $code);

        $f = '';
        if(!empty($this->singleClass)) {
            foreach ($this->singleClass as $s) {
                $f .= "\n" . $s;
            }
        }

        $d = $this->genClass('Result', $code);
        return $d . $f;
    }

    /**
     * @param $data
     * @param string $code
     * @param string $name
     */
    protected function doGen($data, &$code = '', $name = 'Result')
    {
        $i = $j = 65;
        $json = array();
        foreach ($data as $n => $tree) {
            if ($tree['type'] == 'properties') {
                $code .= '    ' . $tree['segment']['properties'] . "\n";
                $json[] = $tree['segment']['json'];
            } else {
                $pName = $this->toCamelCase($n);
                $className = $this->toCamelCase($n, 'pascal') . 'Model';

                if (isset($this->usedClassName[$pName])) {
                    $pName = $pName . chr($i);
                    $i++;
                } else {
                    $this->usedClassName[$pName] = 1;
                }

                if (isset($this->usedClassName[$className])) {
                    $className = $className . chr($j);
                    $j++;
                } else {
                    $this->usedClassName[$className] = 1;
                }

                if ($tree['type'] == 'list') {
                    $p = $this->makeProperties("List<{$className}>", $pName);
                } else {
                    $p = $this->makeProperties($className, $pName);
                }

                $code .= '    ' . $p . "\n";
                $json[] = $this->propertieToJson($pName, $n);

                $item = '';
                $this->doGen($tree['segment'], $item, $className);

                $this->singleClass[] = $this->genClass($className, $item);
            }
        }

        $code .= $this->fromJsonBlock($name, $json);
    }

    /**
     * fromJson
     *
     * @param string $class
     * @param array $data
     * @return string
     */
    function fromJsonBlock($class, $data)
    {
        if (!empty($data)) {
            $a = '';
            $i = 0;
            array_map(function ($d) use (&$a, &$i) {
                if ($i > 0) {
                    $a .= '          ';
                }

                $a .= "{$d}\n";
                $i++;
            }, $data);

            $a = trim(trim($a, "\n"), ',');
            return "\n    {$class}.fromJson(Map<String, dynamic> json) 
        : {$a};";
        }

        return '';
    }

    /**
     * @param string $token
     * @param string $propertiesName
     * @return mixed
     */
    function makeProperties($token, $propertiesName)
    {
        return "final {$token} {$propertiesName};";
    }

    /**
     * @param string $propertiesName
     * @param string $name
     * @param string $token
     * @return mixed
     */
    function propertieToJson($propertiesName, $name, $token = '')
    {
        return "{$propertiesName} = json['{$name}'],";
    }

    /**
     * @param string $className
     * @param string $classBody
     * @return mixed
     */
    function genClass($className, $classBody)
    {
        return "class {$className} {\n" . $classBody . "\n}\n";
    }

    /**
     * 字段类型
     *
     * @return array
     */
    function getTokens()
    {
        return array(
            'float' => 'double',
            'int' => 'int',
            'bool' => 'bool',
            'string' => 'String',
        );
    }
}