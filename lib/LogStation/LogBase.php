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
        date_default_timezone_set('Asia/Shanghai');
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'fatalHandler'));
    }

    /**
     * write
     *
     * @param string $e 文件名或tag
     * @param mixed $log
     * @return mixed
     */
    abstract function write($e, $log);

    /**
     * stack
     *
     * @param string $tag
     * @param mixed $data
     * @return $this
     */
    function addToLog($tag, $data = array())
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        $this->stack[] = self::prettyArray($tag, $data);
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
     * @param string $tag
     * @param string $type
     * @return array
     */
    protected function formatRemoteLog($tag, $type = 'udp')
    {
        $content = $this->getLogContent(false);
        if (!empty($content)) {
            array_walk($content, function (&$v) {
                $v = $v . PHP_EOL;
            });
            $content = implode(PHP_EOL, $content);
        } else {
            $content = '';
        }

        return array(
            'type' => $type,
            'name' => $tag,
            'content' => $content,
            'time' => date('Y-m-d H:i:s'),
        );
    }

    /**
     * 用于远程日志验证的签名
     *
     * @param string $app_id
     * @param string $app_key
     * @return string
     */
    protected function makeSign($app_id, $app_key)
    {
        return md5($app_id . $app_key);
    }

    /**
     * 格式化数组
     *
     * @param string $tag
     * @param array $data
     * @param int $i
     * @return string
     */
    static function prettyArray($tag, array $data, $i = 2)
    {
        $space = str_pad('', $i, ' ', STR_PAD_LEFT);
        if (!empty($data)) {
            array_walk($data, function (&$v, $k) use ($i, $space) {
                if (is_array($v)) {
                    $v = $space . self::prettyArray($k, $v, $i + 2);
                } elseif (is_object($v)) {
                    $v = $space . self::prettyArray($k, get_object_vars($v), $i + 2);
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

        array_unshift($data, $tag);
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
            $this->addToLog('- logStation -', $message);
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