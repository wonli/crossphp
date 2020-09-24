<?php
/**
 * @author wonli <wonli@live.com>
 * Date: 2019/3/25
 */

namespace app\admin\supervise\CodeSegment;


class CURL
{
    /**
     * curl地址
     *
     * @var string
     */
    protected $url;

    /**
     * curl方法
     *
     * @var string
     */
    protected $method;

    /**
     * @var int
     */
    protected $timeout = 20;

    /**
     * @var string
     */
    protected $cacert;

    /**
     * @var bool
     */
    protected $useSSL = false;

    /**
     * curl参数
     *
     * @var array
     */
    protected $params = [];

    /**
     * 附加的header参数
     *
     * @var array
     */
    protected $headerParams = [];


    /**
     * @param mixed $params
     * @return CURL
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param mixed $headerParams
     * @return CURL
     */
    public function setHeaderParams($headerParams)
    {
        $this->headerParams = $headerParams;
        return $this;
    }

    /**
     * @param mixed $url
     * @return CURL
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param mixed $method
     * @return CURL
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param int $timeout
     * @return CURL
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @param $use
     * @return CURL
     */
    public function useSSL($use)
    {
        $this->useSSL = (bool)$use;
        return $this;
    }

    /**
     * @param string $cacert
     * @return CURL
     */
    public function setCacert(string $cacert)
    {
        $this->cacert = $cacert;
        return $this;
    }

    /**
     * 通过CURL获取接口数据
     *
     * @return int|mixed|string
     */
    function request()
    {
        $url = $this->url;
        $timeout = $this->timeout;
        $addHeader = &$this->headerParams;
        $method = strtoupper($this->method);

        if ($method == 'GET' && !empty($this->params)) {
            $params = is_array($this->params) ? http_build_query($this->params) : $this->params;
            $url = rtrim($url, '?');
            if (false === strpos($url . $params, '?')) {
                $url = $url . '?' . ltrim($params, '&');
            } else {
                $url = $url . $params;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($this->useSSL && $this->cacert) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $this->cacert);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else if ($this->useSSL && empty($this->cacert)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }

        $header = array("X-HTTP-Method-Override: {$method}");
        if (!empty($addHeader)) {
            $header = array_merge($header, $addHeader);
        }

        if ($method == 'POST' || $method == 'PUT') {
            $header[] = 'Expect:';
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //避免data数据过长
        $result = curl_exec($ch);
        $errorNo = curl_errno($ch);
        if (!$errorNo) {
            $result = trim($result);
        } else {
            $result = $errorNo;
        }

        curl_close($ch);
        return $result;
    }
}