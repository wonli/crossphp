<?php
/**
 * Cross - lightness PHP framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\LogStation;

use Cross\I\ILog;

/**
 * @author wonli <wonli@live.com>
 *
 * Class Log
 * @package lib\LogStation
 */
abstract class LogBase implements ILog
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
     * E SERVER
     * </pre>
     *
     * @var bool
     */
    protected $defaultLogData = 'GPCSE';

    /**
     * 日志中转服务器地址
     *
     * @var string
     */
    protected $stationServer = '212.129.138.182';

    /**
     * 合并日志分隔符
     *
     * @var string
     */
    protected static $lineSeparator = PHP_EOL;

    /**
     * stack
     *
     * @var array
     */
    private $stack = [];

    function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    /**
     * 输出日志
     *
     * @param string $e 文件名或tag
     * @param mixed $log
     * @return mixed
     */
    abstract function write(string $e, $log = '');

    /**
     * 格式化日志输出
     *
     * @param string $format
     * @param mixed ...$args
     */
    function writef(string $format, ...$args)
    {
        $msg = sprintf($format, ...$args);
        $this->write($msg);
    }

    /**
     * stack
     *
     * @param string $tag
     * @param mixed $data
     * @return $this
     */
    function addToLog(string $tag, $data = [])
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        $this->stack[] = self::prettyArray($tag, $data);
        return $this;
    }

    /**
     * @param string $tag
     * @param string $format
     * @param mixed ...$args
     * @return $this
     * @see addToLog
     */
    function addToLogf(string $tag, string $format, ...$args)
    {
        $data = sprintf($format, ...$args);
        return $this->addToLog($tag, [$data]);
    }

    /**
     * 是否附带默认LOG数据
     *
     * @param string $data
     * @return $this
     */
    function setDefaultLogData(string $data = 'GPCSE')
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
        $content = [];
        if ($this->defaultLogData) {
            $tokens = str_split($this->defaultLogData);
            $allowToken = array('G' => true, 'P' => true, 'C' => true, 'S' => true, 'E' => true);
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
                            $session = [];
                            if (isset($_SESSION)) {
                                $session = &$_SESSION;
                            }
                            $content[] = self::prettyArray('sessions', $session);
                            break;
                        case 'E':
                            $content[] = self::prettyArray('service', $_SERVER);
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
            return implode(static::$lineSeparator, $content);
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
    protected function formatRemoteLog(string $tag, string $type = 'udp')
    {
        $content = $this->getLogContent(false);
        if (!empty($content)) {
            array_walk($content, function (&$v) {
                $v = $v . PHP_EOL;
            });
            $content = implode(static::$lineSeparator, $content);
        } else {
            $content = '';
        }

        return [
            'type' => $type,
            'name' => $tag,
            'content' => $content,
            'time' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 用于远程日志验证的签名
     *
     * @param string $appId
     * @param string $appKey
     * @return string
     */
    protected function makeSign(string $appId, string $appKey)
    {
        return md5($appId . $appKey);
    }

    /**
     * 格式化数组
     *
     * @param string $tag
     * @param array $data
     * @param int $i
     * @return string
     */
    static function prettyArray(string $tag, array $data, $i = 2)
    {
        $space = '';
        if (0 === strcasecmp(static::$lineSeparator, PHP_EOL)) {
            $space = str_pad('', $i, ' ', STR_PAD_LEFT);
        }

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
        return implode(static::$lineSeparator, $data);
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