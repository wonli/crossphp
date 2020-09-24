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
 * Class Encoder
 */
abstract class Encoder
{
    /**
     * 编码
     *
     * @param string $data
     * @return mixed
     */
    abstract function enCode(string $data);

    /**
     * 解码
     *
     * @param string $data
     * @return mixed
     */
    abstract function deCode(string $data);
}
