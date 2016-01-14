<?php
/**
 * Cross - a micro PHP 5 framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */
namespace lib\Mcrypt;

/**
 * @Auth: wonli <wonli@live.com>
 * Class HexCrypt
 */
class HexCrypt extends DEcode
{
    public function __construct()
    {

    }

    /**
     * 加密
     *
     * @param $data
     * @return array
     */
    public function enCode($data)
    {
        return bin2hex($data);
    }

    /**
     * 解密
     *
     * @param $data
     * @return string
     */
    public function deCode($data)
    {
        return @pack('H*', $data);
    }
}
