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
     *
     * <pre>
     * 格式如下：
     * --command 命令全名，支持等号传值
     * -c 命令简写，可以组合
     * </pre>
     *
     * @var []
     */
    protected $cliCommands;

    /**
     * 命令参数别名
     *
     * <pre>
     * 下面三种写法等效，第一种无tips
     * ['c' => 'clean', ‘u’ => 'update']
     * ['c' => 'clean|清理', ‘u’ => 'update|更新']
     * ['c' => ['command' => 'clean', 'tips' => '清理'],'u' => ['command' => 'update', 'tips' => '更新']]
     * -cu等效 -c -u，第一个命令传--help时，显示提示信息
     * </pre>
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
        global $argv;
        parent::__construct();
        $this->processTitle = strtolower("{$this->controller}:{$this->action}");
        if (!empty($argv[1])) {
            $this->processTitle = $argv[1];
        }

        cli_set_process_title($this->processTitle);
        $this->logger = new CliLog($this->processTitle);

        //处理注释配置中的参数
        $this->oriParams = &$this->params;
        if (!empty($this->action_annotate['params'])) {
            $params = $this->action_annotate['params'];
        } else {
            $params = [];
        }

        //处理$argv传递过来的参数
        if ($this->initCliParams) {
            $defaultCommand = null;
            foreach ($this->params as $p) {
                if (false !== strpos($p, '-')) {
                    if ($p[1] != '-') {
                        $cmd = trim(trim($p, '-'));
                        if (false !== strpos($cmd, '=')) {
                            list($cmd, $cmdValue) = explode('=', $cmd);
                            $cmd = trim($cmd);
                            $cmdValue = trim($cmdValue);
                        } else {
                            $cmdValue = null;
                        }

                        for ($i = 0, $j = strlen($cmd); $i < $j; $i++) {
                            $cmdFlag = $cmd[$i];
                            if (!isset($this->commandAlias[$cmdFlag])) {
                                $this->commandTips($cmdFlag);
                                break 2;
                            }

                            $commandAlias = &$this->commandAlias[$cmdFlag];
                            if (null !== $commandAlias && is_array($commandAlias)) {
                                $realCmd = &$commandAlias['command'];
                            } elseif (null !== $commandAlias && false !== strpos($commandAlias, '|')) {
                                list($realCmd) = explode('|', $commandAlias);
                            } elseif (null !== $commandAlias) {
                                $realCmd = $commandAlias;
                            } else {
                                $realCmd = $cmd;
                            }

                            $this->cliCommands[trim($cmd)] = $cmdValue;
                            $this->cliCommands[trim($realCmd)] = $cmdValue;
                        }
                    } else {
                        $cmd = trim($p, '-');
                        if (false !== strpos($cmd, '=')) {
                            list($cmd, $cmdValue) = explode('=', $cmd);
                            $cmdValue = trim($cmdValue);
                        } else {
                            $cmdValue = null;
                        }

                        $this->cliCommands[trim($cmd)] = $cmdValue;
                    }

                    if (null === $defaultCommand) {
                        $defaultCommand = $cmd;
                    }

                } elseif (!empty($p) && false !== strpos($p, '=')) {
                    list($key, $value) = explode('=', $p);
                    if ($key && $value) {
                        $params[trim(trim($key, '-'))] = trim($value);
                    }
                }
            }

            if ($defaultCommand == 'help') {
                $this->commandTips();
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
     * @param bool $getArgs
     * @return mixed
     */
    function command(string $command, bool $getArgs = false)
    {
        if (empty($this->cliCommands)) {
            return false;
        }

        if ($getArgs) {
            return $this->cliCommands[$command] ?? false;
        }

        $has = array_key_exists($command, $this->cliCommands);
        if ($has && null !== $this->cliCommands[$command]) {
            return $this->cliCommands[$command];
        } elseif ($has) {
            return true;
        }

        return false;
    }

    /**
     * 输出命令提示
     *
     * @param string $cmdFlag
     */
    protected function commandTips($cmdFlag = null)
    {
        if (null !== $cmdFlag) {
            $this->consoleMsg('  Not support command: -' . $cmdFlag . PHP_EOL, false);
        }

        $commandConfig = [];
        $commandMaxLength = 0;
        foreach ($this->commandAlias as $s => $set) {
            $d['s'] = $s;
            if (is_array($set)) {
                $d['command'] = $set['command'] ?? '';
                $d['tips'] = $set['tips'] ?? '';
            } elseif (false !== strpos($set, '|')) {
                list($d['command'], $d['tips']) = explode('|', $set);
            } else {
                $d['command'] = $d['tips'] = $set;
            }

            $commandConfig[] = $d;
            $commandLength = strlen($d['command']);
            if ($commandLength > $commandMaxLength) {
                $commandMaxLength = $commandLength;
            }
        }

        $helperContext = [];
        array_map(function ($d) use ($commandMaxLength, &$helperContext) {
            $line = str_pad("--{$d['command']}", $commandMaxLength + 3, ' ', STR_PAD_LEFT);
            $line .= ", -{$d['s']}";
            $line .= str_pad(' ', 3, ' ');
            $line .= $d['tips'];

            $helperContext[] = $line;
        }, $commandConfig);

        $helperContext[] = PHP_EOL;
        $this->consoleMsg(implode(PHP_EOL, $helperContext), false);
        exit(0);
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
