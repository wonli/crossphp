<?php
/**
 * @author wonli <wonli@live.com>
 * CodeSegmentSegment.php
 */

namespace app\admin\supervise\CodeSegment;

use app\admin\supervise\CodeSegment\Adapter\Flutter;

class Generator
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
     * curl参数
     *
     * @var array
     */
    protected $params = array();

    /**
     * 附加的header参数
     *
     * @var array
     */
    protected $headerParams = array();


    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @param mixed $headerParams
     */
    public function setHeaderParams($headerParams)
    {
        $this->headerParams = $headerParams;
    }


    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    function run()
    {
        $RESULT = new Result();
        $curlResponse = $this->curlRequest();
        if (($data = json_decode($curlResponse, true)) === false) {
            $RESULT->setReasone($curlResponse);
            return $RESULT;
        }

        $curlData = json_decode($curlResponse, true);
        $RESULT->addData(Result::DATA_CURL, $curlData);

        $struct = array();
        $this->getStruct($curlData, $struct);

        $f = (new Flutter($struct))->gen();
        return [
            'struct' => $struct,
            'curl' => $curlResponse,
            'flutter' => $f
        ];
    }

    /**
     * 生成数据结构
     *
     * @param array $data
     * @param array $struct
     */
    private function getStruct(array $data, &$struct = array())
    {
        if (!empty($data)) {

            foreach ($data as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    if ($this->isAssoc($value)) {
                        $is_list = false;
                        $data = $value;
                    } else {
                        $is_list = true;
                        $valueFields = array_map('count', $value);
                        $index = array_search(max($valueFields), $valueFields);
                        $data = $value[$index];
                    }

                    $child = array();
                    $this->getStruct($data, $child);

                    if ($is_list) {
                        $struct[$key] = array(
                            "[list]" => $child,
                        );
                    } else {
                        $struct[$key] = $child;
                    }

                } else if (is_array($value) && empty($value)) {
                    $struct[$key] = array();
                } else {
                    if ($value === '') {
                        $type = 'string';
                    } elseif (is_float($value)) {
                        $type = 'float';
                    } elseif (is_int($value)) {
                        $type = 'int';
                    } elseif (is_bool($value)) {
                        $type = 'bool';
                    } elseif (is_null($value)) {
                        $type = 'null';
                    } else {
                        $type = 'string';
                    }

                    $struct[$key] = $type;
                }
            }
        }
    }

    /**
     * 判断是否关联数组
     *
     * @param array $data
     * @return bool
     */
    private function isAssoc(array $data)
    {
        if (array() === $data) return false;
        return array_keys($data) !== range(0, count($data) - 1);
    }

    /**
     * 通过CURL获取接口数据
     *
     * @param bool $CA
     * @param string $cacert
     * @param int $timeout
     * @return int|mixed|string
     */
    private function curlRequest($CA = false, $cacert = '', $timeout = 10)
    {
        $url = $this->url;
        $addHeader = &$this->headerParams;
        $method = strtoupper($this->method);
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        if ($method == 'GET' && !empty($vars)) {
            $params = is_array($vars) ? http_build_query($vars) : $vars;
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

        if ($SSL && $CA && $cacert) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else if ($SSL && !$CA) {
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
        $error_no = curl_errno($ch);
        if (!$error_no) {
            $result = trim($result);
        } else {
            $result = $error_no;
        }

        curl_close($ch);
        return $result;
    }
}