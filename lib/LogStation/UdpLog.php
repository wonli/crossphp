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
class UdpLog extends LogBase
{
    private $station_server;
    private $port;

    /**
     * LogStation constructor.
     *
     * @param string $station_server
     * @param string $port
     */
    function __construct($station_server = '10.29.194.237', $port = '9091')
    {
        parent::__construct();
        $this->setDefaultLogData('');
        $this->station_server = $station_server;
        $this->port = $port;
        $fp = fsockopen("udp://{$station_server}", $port, $error_no, $error_string);
        if (!$fp) {
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
            $log = $this->formatRemoteLog($log, $name);
            fwrite($this->fp, $log);
            fclose($this->fp);
        }
    }
}
