<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Mcrypt;

use Cross\Exception\CoreException;

/**
 * @author wonli <wonli@live.com>
 * Class HashEncrypt
 */
class HashEncrypt
{
    /**
     * 过期时间
     *
     * @var int
     */
    private $ttl = 1800;

    /**
     * @var string
     */
    private $key = '!@c#r$!o>s<s&*';

    /**
     * @var string
     */
    private $algorithm = 'crc32';

    /**
     * 设置过期时间
     *
     * @param string $ttl
     * @return $this
     */
    function setTTL(string $ttl): self
    {
        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置加密算法
     *
     * @param string $algorithm
     * @return $this
     * @throws CoreException
     */
    function setAlgorithm(string $algorithm): self
    {
        if (!in_array($algorithm, hash_algos())) {
            throw new CoreException("不支持的加密算法");
        }

        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * 生成加密数据
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        return hash_hmac($this->algorithm, $data, $this->key);
    }

    /**
     * 生成一个字符串用于校验encrypt的值
     *
     * @param string $key
     * @param int $action
     * @return string
     */
    public function make(string $key, $action = -1): string
    {
        $i = ceil(time() / $this->ttl);
        return substr($this->encrypt($i . $action . $key), -12, 10);
    }

    /**
     * 用make生成的校验字符串校验encrypt是否有效
     *
     * @param string $key
     * @param string $crumb
     * @param int $action
     * @return bool
     */
    public function verify(string $key, string $crumb, $action = -1): bool
    {
        $i = ceil(time() / $this->ttl);
        if (substr($this->encrypt($i . $action . $key), -12, 10) === $crumb ||
            substr($this->encrypt(($i - 1) . $action . $key), -12, 10) === $crumb
        ) {
            return true;
        }

        return false;
    }
}
