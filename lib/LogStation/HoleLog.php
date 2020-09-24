<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\LogStation;

use Exception;

/**
 * 丢弃所有收集的日志
 *
 * Class HoleLog
 * @package lib\LogStation
 */
class HoleLog extends LogBase
{

    /**
     * stack
     *
     * @param string $tag
     * @param mixed $data
     * @return $this
     */
    function addToLog(string $tag, $data = [])
    {
        return $this;
    }

    /**
     * 发送日志
     *
     * @param string $tag
     */
    function send(string $tag)
    {

    }

    /**
     * write
     *
     * @param string $e 文件名或tag
     * @param mixed $log
     * @return mixed
     */
    function write(string $e, $log = '')
    {
        return;
    }

    /**
     * 保存Log到文件
     *
     * @param string $filePrefix LOG文件名前缀
     * @return bool|string
     */
    function save($filePrefix = 'exception')
    {
        return;
    }

    /**
     * 异常日志
     *
     * @param Exception $exception
     * @param string $logName
     * @return string
     */
    function exception(Exception $exception, string $logName = 'exception')
    {
        return;
    }
}