<?php
/**
 * @author wonli <wonli@live.com>
 * skeleton
 */

namespace app\cli\controllers;

use Cross\MVC\Controller;

/**
 * @author wonli <wonli@live.com>
 * Class Cli
 * @package app\cli\controllers
 */
abstract class Cli extends Controller
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $oriParams;

    function __construct()
    {
        parent::__construct();

        //处理注释配置中的参数
        $this->oriParams = &$this->params;
        if (!empty($this->action_annotate['params'])) {
            $params = $this->action_annotate['params'];
        } else {
            $params = array();
        }

        //处理$argv传递过来的参数
        //params1=value1 params2=value2 ... paramsN=valueN
        $i = 0;
        foreach ($this->params as $p) {
            if ((false === strpos($p, '=')) && $i == 0) {
                $this->command = trim($p);
            } elseif (!empty($p) && false !== strpos($p, '=')) {
                list($key, $value) = explode('=', $p);
                if ($key && $value) {
                    $params[trim(trim($key, '-'))] = trim($value);
                }
            } else {
                $params[] = trim($p);
            }

            $i++;
        }

        $this->params = $params;
    }

    /**
     * 输出消息并刷新输入输出缓存
     *
     * @param string $message
     */
    function flushMessage($message)
    {
        echo $message . PHP_EOL;
        flush();
        ob_flush();
    }

    /**
     * @see flushMessage
     *
     * @param string $message
     */
    function endFlushMessage($message)
    {
        $this->flushMessage($message);
        exit(0);
    }
}
