<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Tree;

use Cross\Core\Helper;
use Exception;

/**
 * 前缀树生成和关键词匹配替换
 * @author wonli <wonli@live.com>
 *
 * Class TrieTree
 * @package lib\Structure
 */
class TrieTree
{
    /**
     * @var array
     */
    protected $tree = [];

    /**
     * 外部设置前缀树数据结构
     *
     * @param array $data
     */
    function setTree(array $data)
    {
        $this->tree = $data;
    }

    /**
     * 获取前缀树数据结构
     *
     * @return array
     */
    function getTree()
    {
        return $this->tree;
    }

    /**
     * 从磁盘加载文件并转换成前缀树结构
     *
     * @param string $file
     * @throws Exception
     */
    function loadFromDict(string $file)
    {
        $fp = fopen($file, 'r');
        if (!$fp) {
            throw new Exception('open file error');
        }

        while (!feof($fp)) {
            $word = trim(fgets($fp, 1024));
            if (empty($word)) {
                continue;
            }

            $tree = &$this->tree;
            $data = Helper::stringToArray($word);
            for ($i = 0, $count = count($data); $i < $count; $i++) {
                $c = &$data[$i];
                if (!isset($tree[$c])) {
                    $tree[$c] = [];
                }

                $tree = &$tree[$c];
            }
            $tree['!'] = true;
        }

        fclose($fp);
    }

    /**
     * 匹配关键词
     * <pre>
     * 如果匹配到关键词, 返回true
     * 匹配不到返回false
     * </pre>
     *
     * @param string $str
     * @param string $sensitiveWord
     * @param string $separator
     * @return bool
     */
    function match(string $str, &$sensitiveWord = '', $separator = '|')
    {
        $match = false;
        $matchWords = [];
        $data = Helper::stringToArray($str);
        for ($i = 0, $count = count($data); $i < $count; $i++) {
            $word = $data[$i];
            if (isset($this->tree[$word])) {
                $tree = &$this->tree[$word];
                for ($j = $i + 1; $j < $count; $j++) {
                    $nextWord = $data[$j];
                    if (isset($tree[$nextWord])) {
                        $word .= $nextWord;
                        $tree = &$tree[$nextWord];
                    } else {
                        break;
                    }
                }

                if (isset($tree['!'])) {
                    $matchWords[] = $word;
                    $match = true;
                }
            }
        }

        $sensitiveWord = implode($separator, $matchWords);
        return $match;
    }

    /**
     * 关键词替换
     *
     * @param string $str
     * @param string $char
     * @return string
     */
    function replace(string $str, $char = '*')
    {
        $data = Helper::stringToArray($str);
        for ($i = 0, $count = count($data); $i < $count; $i++) {
            if (isset($this->tree[$data[$i]])) {
                $tree = &$this->tree[$data[$i]];

                $matchIndexes = [];
                for ($j = $i + 1; $j < $count; $j++) {
                    if (isset($tree[$data[$j]])) {
                        $matchIndexes[] = $j;
                        $tree = &$tree[$data[$j]];
                    } else {
                        break;
                    }
                }

                if (isset($tree['!'])) {
                    $data[$i] = $char;
                    foreach ($matchIndexes as $k) {
                        if ($k - $i == 1) {
                            $i = $k;
                        }
                        $data[$k] = $char;
                    }
                }
            }
        }

        return implode($data);
    }
}