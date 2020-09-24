<?php
/**
 * Cross - lightness PHP framework
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
    protected $appId;
    protected $appKey;

    protected $port = 9090;
    protected $timeout = null;

    /**
     * LogStation constructor.
     *
     * @param string $stationServer
     * @param string $port
     * @param string|int $timeout
     */
    function __construct($stationServer = '', $port = '', $timeout = '')
    {
        parent::__construct();
        $this->setDefaultLogData('');
        if (!empty($stationServer)) {
            $this->stationServer = $stationServer;
        }

        if (!empty($port)) {
            $this->port = $port;
        }

        if (!empty($timeout)) {
            $this->timeout = $timeout;
        }

        $fp = @fsockopen($this->stationServer, $this->port, $errorNo, $errorString, $this->timeout);
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
     * @param string $tag
     * @param string|array $log
     * @return mixed|void
     */
    function write(string $tag, $log = '')
    {
        $this->addToLog($tag, $log);
        $this->send($tag);
    }

    /**
     * 发送日志
     *
     * @param mixed $tag
     */
    function send($tag)
    {
        if (is_resource($this->fp)) {
            $log = parent::formatRemoteLog($tag, 'http');
            $logContent = json_encode($log);
            $contentLength = strlen($logContent);
            $sign = $this->makeSign($this->appId, $this->appKey);
            $q = array(
                'POST /write HTTP/1.1',
                "Host: {$this->stationServer}",
                "User-Agent: LogStation Client",
                "Content-Length: {$contentLength}",
                "App-Id: {$this->appId}",
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
