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
     * @return array
     */
    function run(array $data)
    {
        if (is_array($data)) {
            $struct = array();
            $this->getStruct($data, $struct);
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
    private function getStruct(array $data, &$struct = array())
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
                        $is_list = false;
                        $data2 = $value;
                    } else {
                        $is_list = true;
                        $this->getArrayMaxMember($value, $data2);
                        if ($data2 === null) {
                            $data2 = $value;
                        }
                    }

                    $child = array();
                    $this->getStruct($data2, $child);

                    if ($is_list) {
                        $struct[$key] = array(
                            "[list]" => $child,
                        );
                    } else {
                        $struct[$key] = $child;
                    }

                } else if (is_array($value) && empty($value)) {
                    $struct[$key] = array();
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
    private function getArrayMaxMember(array $list, &$data = array())
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
        if (array() === $data) return false;
        return array_keys($data) !== range(0, count($data) - 1);
    }
}