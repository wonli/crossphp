<?php
/**
 * @author wonli <wonli@live.com>
 * ApiController.php
 */

namespace app\api\controllers;

use Cross\Exception\CoreException;
use Cross\Exception\FrontException;

use component\ApiController;

/**
 * @author wonli <wonli@live.com>
 * Class ApiController
 * @package app\api\controllers
 */
abstract class Api extends ApiController
{
    /**
     * 用户ID
     *
     * @var int
     */
    protected $uid = 0;

    /**
     * 渠道
     *
     * @var string
     */
    protected $channel;

    /**
     * 平台
     *
     * @var string
     */
    protected $platform;

    /**
     * 客户端版本
     *
     * @var string
     */
    protected $version;

    /**
     * 过滤数据
     *
     * @param string $key
     * @param string $value
     * @return string|void
     * @throws FrontException
     * @throws CoreException
     */
    protected function filterInputData(string $key, $value)
    {
        switch ($key) {
            case 'channel':
                if (empty($value)) {
                    $this->data['status'] = 200210;
                    $this->display($this->data);
                    return;
                }
                break;

            case 'platform':
                if (empty($value)) {
                    $this->data['status'] = 200220;
                    $this->display($this->data);
                    return;
                }
                break;

            case 'version':
                if (empty($value)) {
                    $this->data['status'] = 200230;
                    $this->display($this->data);
                    return;
                }
                break;

            default:
                $value = htmlentities(strip_tags(trim($value)), ENT_COMPAT, 'utf-8');
        }

        return $value;
    }
}
