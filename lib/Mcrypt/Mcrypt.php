<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Mcrypt;

use lib\Mcrypt\Encoder\HexEncoder;
use lib\Mcrypt\Encoder\Encoder;

/**
 * @author wonli <wonli@live.com>
 * Class Mcrypt
 */
class Mcrypt
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $cryptKey = '@!c#r$o%*s^&s#p!h%p&!@#';

    /**
     * @var string
     */
    private $method = 'AES-256-CBC';

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @var int
     */
    private $ivLength;

    /**
     * @var bool
     */
    private $useEncoder = true;

    /**
     * Mcrypt constructor.
     *
     * @param string $method
     */
    function __construct($method = 'AES-256-CBC')
    {
        if ($method != $this->method) {
            $cipherMethods = openssl_get_cipher_methods(true);
            if (in_array($method, $cipherMethods)) {
                $this->method = $method;
            }
        }

        $this->ivLength = openssl_cipher_iv_length($this->method);
    }

    /**
     * 加密
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data)
    {
        $key = $this->getKey();
        $iv = openssl_random_pseudo_bytes($this->ivLength);

        $s = openssl_encrypt($data, $this->method, $key, 0, $iv) . $iv;
        if ($this->useEncoder) {
            return $this->getEncoder()->EnCode($s);
        }

        return $s;
    }

    /**
     * 解密
     *
     * @param $data
     * @return string
     */
    public function decrypt($data)
    {
        if ($this->useEncoder) {
            $data = $this->getEncoder()->DeCode($data);
        }

        $iv = substr($data, -$this->ivLength);
        $data = substr($data, 0, -$this->ivLength);
        $key = $this->getKey();

        return openssl_decrypt($data, $this->method, $key, 0, $iv);
    }

    /**
     * 设置用于加解密的key
     *
     * @param $key
     * @return $this
     */
    function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * isDecode
     *
     * @param bool $useEncoder
     */
    function setUseEncoder(bool $useEncoder)
    {
        $this->useEncoder = $useEncoder;
    }

    /**
     * setEncoder
     *
     * @param Encoder $encoder
     * @return $this
     */
    function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * 获取key
     *
     * @return string
     */
    protected function getKey()
    {
        if (!$this->key) {
            return md5($this->cryptKey);
        }

        return $this->key;
    }

    /**
     * getEncoder
     *
     * @return Encoder
     */
    protected function getEncoder()
    {
        if (!$this->encoder) {
            $this->setEncoder(new HexEncoder());
        }

        return $this->encoder;
    }
}
