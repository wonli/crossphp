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
 * UdpLog.php
 */
class UdpLog extends LogBase
{
    protected $app_id;
    protected $app_key;

    protected $station_server = '118.24.73.121';
    protected $port = 9091;
    protected $timeout = null;

    /**
     * LogStation constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->setDefaultLogData('');
        $fp = fsockopen("udp://{$this->station_server}", $this->port, $error_no, $error_string, $this->timeout);
        if (!$fp) {
            return;
        }

        $this->fp = $fp;
    }

    /**
     * 写入日志
     *
     * @param string|array $log
     * @param string $tag
     * @return mixed|void
     */
    function write($tag, $log)
    {
        $this->addToLog($tag, $log);
        $this->send($tag);
    }

    /**
     * 发送日志
     *
     * @param string $tag
     */
    function send($tag)
    {
        if (is_resource($this->fp)) {
            $content = $this->formatRemoteLog($tag, 'udp');
            $body = pack('a*', json_encode($content));

            //日志分割成小块(456字节, 加上头部56字节, 每个包最大512字节)
            $logSections = str_split($body, 456);
            $sectionCount = count($logSections);

            $appInfo = $this->getAppHeaderPack();
            $packageId = mt_rand(1000000, 9999999);

            if ($sectionCount > 1) {
                foreach ($logSections as $i => $segment) {
                    $packageInfo = pack("Nnn", $packageId, $sectionCount, $i);
                    fwrite($this->fp, $packageInfo . $appInfo . $segment);
                }
            } else {
                fwrite($this->fp, pack("Nnn", $packageId, 1, 1) . $appInfo . $body);
            }

            fclose($this->fp);
        }
    }

    /**
     * app信息
     *
     * @return string
     */
    private function getAppHeaderPack()
    {
        $sign = $this->makeSign($this->app_id, $this->app_key);
        return pack('a16a32', $this->app_id, $sign);
    }
}
