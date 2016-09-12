<?php
/**
 * @Auth: wonli <wonli@live.com>
 * skeleton
 */
namespace app\cli\controllers;

use Cross\MVC\Controller;

/**
 * @Auth: wonli <wonli@live.com>
 * Class Cli
 * @package app\cli\controllers
 */
abstract class Cli extends Controller
{
    protected $command = '';

    function __construct()
    {
        parent::__construct();

        //处理$argv传递过来的参数
        //params1=value1 params2=value2 ... paramsN=valueN
        $params = array();
        foreach ($this->params as $k => $p) {
            if (strpos($p, '=') === false) {
                if($k == 0) {
                    $this->command = trim($p);
                }
                continue;
            }

            list($p_key, $p_value) = explode('=', $p);
            if ($p_key && $p_value) {
                $params[trim(trim($p_key, '-'))] = trim($p_value);
            }
        }

        $this->params = $params;
    }
}
