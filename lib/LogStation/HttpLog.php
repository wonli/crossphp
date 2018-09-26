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
 * HttpLog.php
 */
class HttpLog extends LogBase
{
    private $app_id = '';
    private $app_key = '';

    private $station_server = '118.24.73.121';
    private $port = 9090;
    private $timeout = null;

    /**
     * LogStation constructor.
     *
     * @param string $station_server
     * @param string $port
     * @param string|int $timeout
     */
    function __construct($station_server = '', $port = '', $timeout = '')
    {
        parent::__construct();
        $this->setDefaultLogData('');
        if (!empty($station_server)) {
            $this->station_server = $station_server;
        }

        if (!empty($port)) {
            $this->port = $port;
        }

        if (!empty($timeout)) {
            $this->timeout = $timeout;
        }

        $fp = @fsockopen($this->station_server, $this->port, $error_no, $error_string, $this->timeout);
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
        if (is_array($log)) {
            $this->addToLog($name, $log);
        } else {
            $this->addToLog($log);
        }

        $this->send($name);
    }

    /**
     * 发送日志
     *
     * @param string $name
     */
    function send($name)
    {
        if (is_resource($this->fp)) {
            $log = parent::formatRemoteLog($name, 'http');
            $logContent = json_encode($log);
            $contentLength = strlen($logContent);
            $sign = $this->makeSign($this->app_id, $this->app_key);
            $q = array(
                'POST /write HTTP/1.1',
                "Host: {$this->station_server}",
                "User-Agent: LogStation Client",
                "Content-Length: {$contentLength}",
                "App-Id: {$this->app_id}",
                "App-Sign: {$sign}",
                "Connection: Close\r\n",
                $logContent
            );

            $string = implode("\r\n", $q);
            fwrite($this->fp, $string, 40960);
            fclose($this->fp);
        }
    }
}
