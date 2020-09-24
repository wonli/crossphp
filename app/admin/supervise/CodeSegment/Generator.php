<?php
/**
 * @author wonli <wonli@live.com>
 * CodeSegmentSegment.php
 */

namespace app\admin\supervise\CodeSegment;

use app\admin\supervise\CodeSegment\Adapter\Flutter;
use app\admin\supervise\CodeSegment\Adapter\Go;

class Generator
{
    /**
     * @param array $data
     * @param bool $s 是否仅返回结构
     * @return array
     */
    function run(array $data, $s = false)
    {
        if (is_array($data)) {
            $struct = [];
            $this->getStruct($data, $struct);
            if ($s) {
                return $struct;
            }

            return [
                'struct' => $struct,
                'curl' => $data,
                'flutter' => (new Flutter($struct))->gen(),
                'go' => (new Go($struct))->gen(),
            ];
        } else {
            return [];
        }
    }

    /**
     * 生成数据结构
     *
     * @param array $data
     * @param array $struct
     */
    private function getStruct(array $data, &$struct = [])
    {
        if (!empty($data)) {
            if (!$this->isAssoc($data)) {
                $this->getArrayMaxMember($data, $object);
                if (null === $object) {
                    $object = $data;
                }
            } else {
                $object = $data;
            }

            foreach ($object as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    if ($this->isAssoc($value)) {
                        $isList = false;
                        $data2 = $value;
                    } else {
                        $isList = true;
                        $this->getArrayMaxMember($value, $data2);
                        if ($data2 === null) {
                            $data2 = $value;
                        }
                    }

                    $child = [];
                    $this->getStruct($data2, $child);

                    if ($isList) {
                        $struct[$key] = array(
                            "[list]" => $child,
                        );
                    } else {
                        $struct[$key] = $child;
                    }

                } else if (is_array($value) && empty($value)) {
                    $struct[$key] = [];
                } else {
                    if ($value === '') {
                        $type = 'string';
                    } elseif (is_float($value)) {
                        $type = 'float';
                    } elseif (is_int($value)) {
                        $type = 'int';
                    } elseif (is_bool($value)) {
                        $type = 'bool';
                    } elseif (is_null($value)) {
                        $type = 'null';
                    } else {
                        $type = 'string';
                    }

                    $struct[$key] = $type;
                }
            }
        }
    }

    /**
     * 获取二维数组中成员最多的那一条数据
     *
     * @param array $list
     * @param array $data
     */
    private function getArrayMaxMember(array $list, &$data = [])
    {
        if (!empty($list)) {
            foreach ($list as $a) {
                if (!empty($a)) {
                    if (is_array($a)) {
                        foreach ($a as $k => $v) {
                            if (!is_array($v)) {
                                $data[$k] = $v;
                            } else {
                                if ($this->isAssoc($v)) {
                                    $data[$k] = $v;
                                } elseif (!empty($v)) {
                                    $this->getArrayMaxMember($v, $child);
                                    if (!empty($data[$k])) {
                                        $data[$k] += $child;
                                    } else {
                                        $data[$k] = $child;
                                    }
                                } elseif (!isset($data[$k])) {
                                    $data[$k] = $v;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 判断是否关联数组
     *
     * @param array $data
     * @return bool
     */
    private function isAssoc(array $data)
    {
        if ([] === $data) return false;
        return array_keys($data) !== range(0, count($data) - 1);
    }
}