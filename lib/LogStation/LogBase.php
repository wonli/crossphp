<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\LogStation;

/**
 * @author wonli <wonli@live.com>
 *
 * Class Log
 * @package lib\LogStation
 */
abstract class LogBase
{
    /**
     * @var resource
     */
    protected $fp;

    /**
     * 要收集的默认日志内容
     * <pre>
     * G GET
     * P POST
     * C COOKIE
     * S SESSION
     * </pre>
     *
     * @var bool
     */
    protected $defaultLogData = 'GPCS';

    /**
     * stack
     *
     * @var array
     */
    private $stack = array();

    function __construct()
    {
        error_reporting(0);
        date_default_timezone_set('Asia/Shanghai');
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'fatalHandler'));
    }

    /**
     * @param string $log
     * @param string $name
     * @return mixed
     */
    abstract function write($log, $name);

    /**
     * 增加到stack
     *
     * @param string $content
     * @param array $data
     * @return $this
     */
    function addToLog($content, array $data = array())
    {
        if (!empty($data)) {
            $content = self::prettyArray($content, $data);
        }

        $this->stack[] = $content;
        return $this;
    }

    /**
     * 是否附带默认LOG数据
     *
     * @param string $data
     * @return $this
     */
    function setDefaultLogData($data = 'GPCS')
    {
        $this->defaultLogData = $data;
        return $this;
    }

    /**
     * 获取日志内容
     *
     * @param bool $string
     * @return array|string
     */
    function getLogContent($string = true)
    {
        $content = array();
        if ($this->defaultLogData) {
            $tokens = str_split($this->defaultLogData);
            $allowToken = array('G' => true, 'P' => true, 'C' => true, 'S' => true);
            foreach ($tokens as $t) {
                if (isset($allowToken[$t])) {
                    switch ($t) {
                        case 'G':
                            $content[] = self::prettyArray('gets', $_GET);
                            break;
                        case 'P':
                            $content[] = self::prettyArray('posts', $_POST);
                            break;
                        case 'C':
                            $content[] = self::prettyArray('cookies', $_COOKIE);
                            break;
                        case 'S':
                            $session = array();
                            if (isset($_SESSION)) {
                                $session = &$_SESSION;
                            }
                            $content[] = self::prettyArray('sessions', $session);
                            break;
                    }
                }
            }
        }

        if ($this->stack) {
            while ($log = array_shift($this->stack)) {
                array_unshift($content, $log);
            }
        }

        if ($string) {
            return implode(PHP_EOL, $content);
        }

        return $content;
    }

    /**
     * 格式化远程日志
     *
     * @param array|string|object $log
     * @param string $name
     * @return string
     */
    protected function formatRemoteLog($log, $name = '')
    {
        if (is_scalar($log)) {
            $log = (string)$log;
        } elseif (is_array($log)) {
            $log = self::prettyArray($name, $log);
        } elseif (is_object($log)) {
            $log = var_export($log, true);
        } else {
            $log = '!!!不支持的LOG格式!!!';
        }

        if ($name) {
            $log = sprintf("[%s] %s %s", $name, date('Y-m-d H:i:s'), $log);
        } else {
            $log = sprintf("%s %s", date('Y-m-d H:i:s'), $log);
        }

        $this->addToLog($log);
        $content = $this->getLogContent(false);

        //处理换行
        array_walk($content, function (&$v) {
            $v = $v . PHP_EOL;
        });

        return implode(PHP_EOL, $content);
    }

    /**
     * 格式化数组
     *
     * @param string $name
     * @param array $data
     * @param int $i
     * @return string
     */
    static function prettyArray($name, array $data, $i = 2)
    {
        $space = str_pad('', $i, ' ', STR_PAD_LEFT);
        if (!empty($data)) {
            array_walk($data, function (&$v, $k) use ($i, $space) {
                if (is_array($v)) {
                    $v = $space . self::prettyArray($k, $v, $i + 2);
                } else {
                    if (!is_int($k)) {
                        $v = $k . ': ' . $v;
                    }

                    $v = $space . $v;
                }
            });
        } else {
            $data[] = $space . '-';
        }

        array_unshift($data, $name);
        return implode(PHP_EOL, $data);
    }

    /**
     * 错误处理
     *
     * @param $no
     * @param $str
     * @param $file
     * @param $line
     */
    function errorHandler($no, $str, $file, $line)
    {
        $file = 'app::' . str_replace(PROJECT_REAL_PATH, 'APP', $file);
        $message = sprintf("[发送日志时产生错误] %s (%s, line %s, error %s)", $str, $file, $line, $no);
        if (is_resource($this->fp)) {
            fwrite($this->fp, $message);
        }
    }

    /**
     * 错误处理
     */
    function fatalHandler()
    {
        $error = error_get_last();
        if (!empty($error)) {
            $this->errorHandler('', $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * 关闭连接
     */
    function close()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    /**
     * 析构函数
     */
    function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }
}