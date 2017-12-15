<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */
namespace lib\Mcrypt;

/**
 * @author wonli <wonli@live.com>
 * Class DEcode
 */
abstract class DEcode
{
    /**
     * 编码函数
     *
     * @param $data
     * @return mixed
     */
    abstract function enCode($data);

    /**
     * 解码函数
     *
     * @param $data
     * @return mixed
     */
    abstract function deCode($data);

    /**
     * PKCS5 补码
     *
     * @param $text
     * @param $block_size
     * @return string
     */
    function pkcs5Pad($text, $block_size = 16)
    {
        $pad = $block_size - (strlen($text) % $block_size);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * PKCS5解码
     *
     * @param $text
     * @return bool|string
     */
    function pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text) || (strspn($text, chr($pad), strlen($text) - $pad) != $pad) ) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }
}
