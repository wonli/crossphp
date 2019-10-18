<?php
/**
 * @author wonli <wonli@live.com>
 * skeleton
 */

namespace app\cli\controllers;

use Cross\MVC\Controller;
use Cross\I\ILog;

use lib\LogStation\CliLog;


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

    /**
     * @var ILog
     */
    protected $logger;

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

        $commandName = strtolower("{$this->controller}:{$this->action}");
        if (isset($_SERVER['argv']) && !empty($_SERVER['argv'][1])) {
            $commandName = &$_SERVER['argv'][1];
        }

        $this->logger = new CliLog($commandName);
        $this->params = $params;
    }

    /**
     * 自定义logger
     *
     * @param ILog $logger
     */
    function setLogger(ILog $logger)
    {
        $this->logger = $logger;
    }
}
