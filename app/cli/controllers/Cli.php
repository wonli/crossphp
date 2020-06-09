<?php
/**
 * @author wonli <wonli@live.com>
 * skeleton
 */

namespace app\cli\controllers;


use Cross\Core\Loader;
use Cross\Exception\CoreException;
use Cross\MVC\Controller;
use Cross\Core\Helper;
use Cross\I\ILog;

use app\cli\views\CliView;

use lib\LogStation\CliLog;


/**
 * @author wonli <wonli@live.com>
 * Class Cli
 * @package app\cli\controllers
 * @property CliView $view
 */
abstract class Cli extends Controller
{
    /**
     * 命令列表
     * <pre>
     * 格式如下：
     * -command=[value|true]
     * --command=[value|true]
     *
     * 可以传传值，默认为true
     * 可以在子类通过commandAlias设置别名
     * </pre>
     *
     * @var []
     */
    protected $cliCommands;

    /**
     * 命令参数别名
     *
     * @var array
     */
    protected $commandAlias = [];

    /**
     * @var string
     */
    protected $processTitle;

    /**
     * 是否解析命令行参数(params1=value1 params2=value2)
     *
     * @var bool
     */
    protected $initCliParams = true;

    /**
     * @var array
     */
    protected $oriParams;

    /**
     * @var ILog
     */
    protected $logger;

    /**
     * 开发者信息
     *
     * @var array
     */
    protected $dev = [];

    /**
     * @var string
     */
    protected $devConfig = 'config::.dev.php';

    /**
     * @var bool
     */
    protected $initDevConfig = true;

    function __construct()
    {
        parent::__construct();

        //处理注释配置中的参数
        $this->oriParams = &$this->params;
        if (!empty($this->action_annotate['params'])) {
            $params = $this->action_annotate['params'];
        } else {
            $params = [];
        }

        //处理$argv传递过来的参数
        if ($this->initCliParams) {
            foreach ($this->params as $p) {
                if (false !== strpos($p, '-')) {
                    $cmd = trim($p, '-');
                    if (false !== strpos($cmd, '=')) {
                        list($cmd, $cmdArgs) = explode('=', $cmd);
                    } else {
                        $cmdArgs = true;
                    }

                    $this->cliCommands[$cmd] = $cmdArgs;
                    $commandAlias = &$this->commandAlias[$cmd];
                    if (null !== $commandAlias) {
                        $this->cliCommands[$commandAlias] = $cmdArgs;
                    }
                } elseif (!empty($p) && false !== strpos($p, '=')) {
                    list($key, $value) = explode('=', $p);
                    if ($key && $value) {
                        $params[trim(trim($key, '-'))] = trim($value);
                    }
                }
            }

            $this->params = $params;
        }

        //处理开发者信息
        if ($this->initDevConfig) {
            $devFile = $this->getFilePath($this->devConfig);
            if (!file_exists($devFile)) {
                $this->consoleMsg('Developer name: ', false);
                $name = trim(fgets(STDIN, 32));
                if (empty($name)) {
                    $this->consoleMsg('Please specified developer name!');
                    exit(0);
                }

                $this->consoleMsg('Developer email: ', false);
                $email = trim(fgets(STDIN, 128));
                $isValidEmail = Helper::validEmail($email);
                if (!$isValidEmail) {
                    $this->consoleMsg('Please specified developer email!');
                    exit(0);
                }

                $dev['name'] = $name;
                $dev['email'] = $email;
                $result = $this->view->genConfigFile($devFile, $dev);
                if (!$result) {
                    $this->consoleMsg('Save developer info fail!');
                    exit(0);
                } else {
                    $this->dev = $dev;
                }
            } else {
                try {
                    $this->dev = Loader::read($devFile);
                } catch (CoreException $e) {
                }
            }

            if (empty($this->dev['name']) || empty($this->dev['email'])) {
                @unlink($devFile);
                $this->consoleMsg('Please specified developer name or email!');
                exit(0);
            }
        }

        global $argv;
        $processTitle = strtolower("{$this->controller}:{$this->action}");
        if (!empty($argv[1])) {
            $processTitle = $argv[1];
        }

        cli_set_process_title($processTitle);
        $this->processTitle = $processTitle;
        $this->logger = new CliLog($processTitle);
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

    /**
     * command
     *
     * @param string $command
     * @return mixed
     */
    function command(string $command)
    {
        return $this->cliCommands[$command] ?? false;
    }

    /**
     * 在控制台打印消息
     *
     * @param string $message
     * @param bool $newLine
     */
    function consoleMsg($message, $newLine = true)
    {
        if ($newLine) {
            $msg = '(' . $this->processTitle . ') ' . $message . PHP_EOL;
        } else {
            $msg = $message;
        }

        fputs(STDOUT, $msg);
    }

    /**
     * 转换字符串形式的bool值
     *
     * @param string $value
     * @return bool|mixed
     */
    function getBooleanValueFromString($value)
    {
        $value = strtolower((string)$value);
        $a = ['false' => false, '0' => false, 'disable' => false];

        if (isset($a[$value])) {
            return $a[$value];
        }

        return true;
    }
}
