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
 * 日志
 *
 * @author wonli <wonli@live.com>
 * FileLog.php
 */
class FileLog extends LogBase
{
    /**
     * @var string
     */
    protected $logPath;

    /**
     * FileLog constructor.
     *
     * @param string $path
     * @throws Exception
     */
    function __construct($path = '')
    {
        parent::__construct();
        if (!$path && defined('PROJECT_REAL_PATH')) {
            $path = PROJECT_REAL_PATH . 'cache' . DIRECTORY_SEPARATOR . 'log';
        }

        if ($path) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            $path = '.' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
        }

        $path .= date('Y-m') . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            $create = mkdir($path, 0755, true);
            if (!$create) {
                throw new Exception('创建日志目录失败');
            }
        }

        $this->logPath = $path;
    }

    /**
     * 写入日志
     *
     * @param string $logFileName
     * @param mixed $log
     * @return bool|string
     */
    function write($logFileName, $log)
    {
        $this->addToLog($logFileName, $log);
        return $this->save($logFileName);
    }

    /**
     * 异常日志
     *
     * @param Exception $exception
     * @param string $logName
     * @return string
     */
    function exception(Exception $exception, $logName = 'exception')
    {
        $trace = explode("\n", $exception->getTraceAsString());

        //隐藏trace中的路径
        if (defined('PROJECT_REAL_PATH')) {
            array_walk($trace, function (&$v) {
                $v = str_replace(array(PROJECT_REAL_PATH, CP_PATH, str_replace('/', DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'])),
                    array('Project->', 'Cross->', 'Index->'), $v);
            });
        }

        $this->addToLog('traces', $trace);
        $this->addToLog($exception->getMessage());

        return $this->save($logName);
    }

    /**
     * 保存Log到文件
     *
     * @param string $filePrefix LOG文件名前缀
     * @return bool|string
     */
    function save($filePrefix = 'exception')
    {
        $log_id = self::genLogID();
        $space = str_pad('-', 28, '-');
        $start = sprintf("%s ( %s - %s ) %s", $space, date('Y-m-d H:i:s'), $log_id, $space);

        //加入头部
        $content = $this->getLogContent(false);
        array_unshift($content, $start);

        //加入尾部
        $content[] = PHP_EOL;

        //处理换行
        array_walk($content, function (&$v) {
            $v = $v . PHP_EOL;
        });

        $content = implode(PHP_EOL, $content);
        $logFile = $this->logPath . $filePrefix . '-' . date('d') . '.log';
        $ret = error_log($content, 3, $logFile);
        if ($ret) {
            return $log_id;
        }

        return $ret;
    }

    /**
     * 生成异日志ID, 可以展示给用户, 便于定位日志
     *
     * @return string
     */
    private static function genLogID()
    {
        return date('Ymd.') .
            str_pad(time() - strtotime('00:00'), 5, 0, STR_PAD_LEFT) .
            '.' . mt_rand(1000, 9999);
    }
}