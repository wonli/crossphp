<?php
/**
 * Cross - lightness PHP framework
 *
 * @link        http://www.crossphp.com
 * @license     MIT License
 */

namespace lib\LogStation;

/**
 * 命令行模式下的简单日志打印
 *
 * Class CliLog
 * @package lib\LogStation
 */
class CliLog extends LogBase
{
    protected $t = 'cliLog';
    protected $defaultLogData = '';
    protected static $lineSeparator = '';

    /**
     * CliLog constructor.
     * @param string $t 来源提示
     */
    function __construct($t = '')
    {
        parent::__construct();
        if (!empty($t)) {
            $this->t = $t;
        }
    }

    /**
     * 控制台输出日志
     *
     * @param string $e 文件名或tag
     * @param mixed $log
     * @return mixed
     */
    function write(string $e, $log = '')
    {
        $this->addToLog($e, $log);
        $log = $this->getLogContent();

        return fputs(STDOUT, sprintf('(%s) %s' . PHP_EOL, $this->t, $log));
    }
}