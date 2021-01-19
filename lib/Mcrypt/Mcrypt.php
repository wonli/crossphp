<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Mcrypt;

use lib\Mcrypt\Encoder\HexEncoder;

/**
 * @author wonli <wonli@live.com>
 * Class Mcrypt
 */
class Mcrypt
{
    /**
     * @var string
     */
    private $iv;

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
    private $method = 'aes-256-cbc';

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
     * iv附在加密字符串后面（默认在前面)
     *
     * @var bool
     */
    private $withIvAppend = false;

    /**
     * Mcrypt constructor.
     *
     * @param string $method
     */
    function __construct(string $method = 'aes-256-cbc')
    {
        $method = strtolower($method);
        $cipherMethods = openssl_get_cipher_methods(true);
        if (in_array($method, $cipherMethods)) {
            $this->method = $method;
        }
    }

    /**
     * 加密
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $iv = $this->getIv();
        $s = openssl_encrypt($data, $this->method, $this->getKey(), 0, $iv);
        $s = $this->withIvAppend ? $s . $iv : $iv . $s;
        if ($this->useEncoder) {
            return $this->getEncoder()->EnCode($s);
        }

        return $s;
    }

    /**
     * 解密
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        if ($this->useEncoder) {
            $data = $this->getEncoder()->DeCode($data);
        }

        $key = $this->getKey();
        $ivLength = $this->getIvLength();

        if ($this->withIvAppend) {
            $iv = substr($data, -$ivLength);
            $data = substr($data, 0, -$ivLength);
        } else {
            $iv = substr($data, 0, $ivLength);
            $data = substr($data, $ivLength);
        }

        return openssl_decrypt($data, $this->method, $key, 0, $iv);
    }

    /**
     * 设置用于加解密的key
     *
     * @param string $key
     * @return $this
     */
    function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置iv
     *
     * @param string $iv
     * @return $this
     */
    function setIv(string $iv): self
    {
        $this->iv = $iv;
        return $this;
    }

    /**
     * 设置iv长度
     *
     * @param int $length
     * @return $this
     */
    function setIvLength(int $length): self
    {
        $this->ivLength = $length;
        return $this;
    }

    /**
     * isDecode
     *
     * @param bool $useEncoder
     * @return $this
     */
    function useEncoder(bool $useEncoder): self
    {
        $this->useEncoder = $useEncoder;
        return $this;
    }

    /**
     * 设置iv附在加密结果之后
     *
     * @return $this
     */
    function setIvAppend(): self
    {
        $this->withIvAppend = true;
        return $this;
    }

    /**
     * setEncoder
     *
     * @param Encoder $encoder
     * @return $this
     */
    function setEncoder(Encoder $encoder): self
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * 获取iv
     *
     * @return false|string
     */
    function getIv()
    {
        if (!$this->iv) {
            $this->iv = openssl_random_pseudo_bytes($this->getIvLength());
        }

        return $this->iv;
    }

    /**
     * 获取iv长度
     *
     * @return int
     */
    protected function getIvLength(): int
    {
        if ($this->iv) {
            $this->ivLength = strlen($this->iv);
        }

        if (!$this->ivLength) {
            $this->ivLength = openssl_cipher_iv_length($this->method);
        }

        return $this->ivLength;
    }

    /**
     * 获取key
     *
     * @return string
     */
    protected function getKey(): string
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
    protected function getEncoder(): Encoder
    {
        if (!$this->encoder) {
            $this->setEncoder(new HexEncoder());
        }

        return $this->encoder;
    }
}
