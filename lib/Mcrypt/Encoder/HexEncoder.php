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
 * Class HexEncoder
 */
class HexEncoder extends Encoder
{
    /**
     * 加密
     *
     * @param string $data
     * @return string
     */
    public function enCode(string $data)
    {
        return bin2hex($data);
    }

    /**
     * 解密
     *
     * @param string $data
     * @return string
     */
    public function deCode(string $data)
    {
        return hex2bin($data);
    }
}
