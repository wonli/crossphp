<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\LogStation;

/**
 * 将日志发送到中转站, 由中转站通过socket转发至客户端
 *
 * @author wonli <wonli@live.com>
 * LogStation.php
 */
class HttpLog extends LogBase
{
    private $station_server;
    private $port;
    private $timeout;

    /**
     * LogStation constructor.
     *
     * @param string $station_server
     * @param string $port
     * @param int $timeout
     */
    function __construct($station_server = '10.29.194.237', $port = '9090', $timeout = 1)
    {
        parent::__construct();
        $this->setDefaultLogData('');
        $this->station_server = $station_server;
        $this->port = $port;
        $this->timeout = $timeout;

        $fp = @fsockopen($station_server, $port, $error_no, $error_string, $timeout);
        if (!$fp) {
            return;
        }

        if (!stream_set_blocking($fp, 0)) {
            return;
        }

        $this->fp = $fp;
    }

    /**
     * 写入日志
     *
     * @param string|array $log
     * @param string $name
     * @return mixed|void
     */
    function write($log, $name = '')
    {
        if (is_resource($this->fp)) {
            $log = parent::formatRemoteLog($log, $name);
            $content_length = strlen($log);
            $q = array(
                'POST /write HTTP/1.1',
                "Host: {$this->station_server}",
                "User-Agent: LogStation Client",
                "Content-Length: {$content_length}",
                "Connection: Close\r\n",
                $log
            );

            $string = implode("\r\n", $q);
            fwrite($this->fp, $string, 40960);
            fclose($this->fp);
        }
    }
}
