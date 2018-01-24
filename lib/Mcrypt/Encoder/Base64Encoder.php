<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\Mcrypt\Encoder;

/**
 * @author wonli <wonli@live.com>
 * Class Base64Encoder
 */
class Base64Encoder extends Encoder
{
    /**
     * 加密
     *
     * @param string $data
     * @return string
     */
    public function enCode($data)
    {
        return str_replace(array('=', '/', '+'), array('', '-', '_'), base64_encode($data));
    }

    /**
     * 解密
     *
     * @param string $data
     * @return string
     */
    public function deCode($data)
    {
        return base64_decode(str_replace(array('-', '_'), array('/', '+'), $data));
    }
}
