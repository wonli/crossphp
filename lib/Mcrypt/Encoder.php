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
 * interface Encoder
 */
interface Encoder
{
    /**
     * 编码
     *
     * @param string $data
     * @return mixed
     */
    function enCode(string $data);

    /**
     * 解码
     *
     * @param string $data
     * @return mixed
     */
    function deCode(string $data);
}
