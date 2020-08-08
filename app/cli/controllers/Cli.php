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
     * 命令参数
     *
     * <pre>
     * 格式如下：
     * --command 命令
     * -c 命令简写，单字符可以组合
     * </pre>
     *
     * @var []
     */
    protected $cliCommands = [];

    /**
     * 命令参数配置
     *
     * <pre>
     * 配置命令参数和提示
     * ['clean|c' => '清理', ‘update|u’ => '更新']
     * -cu等效 -c -u，第一个命令传--help时，显示帮助信息
     * </pre>
     * @var array
     */
    protected $commandConfig = [];

    /**
     * 默认开启严格模式（传入未配置的参数会报错）
     *
     * @var bool
     */
    protected $strictCommand = true;

    /**
     * 命名描述信息, 多行用数组
     *
     * @var string|array
     */
    protected $commandDesc;

    /**
     * 进程title
     *
     * @var string
     */
    protected $processTitle;

    /**
     * 原始参数
     *
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
     * 是否处理开发者信息
     *
     * @var bool
     */
    protected $initDevConfig = true;

    /**
     * 是否解析命令和参数
     *
     * @var bool
     */
    protected $initCliParams = true;

    /**
     * 命令帮助信息
     *
     * @var array
     */
    private $tipsData = [];

    /**
     * 开发者信息存储文件
     *
     * @var string
     */
    private $devConfig = 'config::.dev.php';

    /**
     * Cli constructor.
     */
    function __construct()
    {
        global $argv;
        parent::__construct();
        $this->processTitle = strtolower("{$this->controller}:{$this->action}");
        if (!empty($argv[1])) {
            $this->processTitle = $argv[1];
        }

        $this->setLogger(new CliLog($this->processTitle));
        if ('Darwin' !== PHP_OS) {
            cli_set_process_title($this->processTitle);
        }

        $this->initDevInfo();
        $this->parseCliCommands();
        $this->parseParams();
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
     * 获取命令行参数值
     *
     * @param string $command 命令名称
     * @param bool $getValue 是否取值
     * @param null $default 默认值
     * @return mixed
     */
    function command(string $command, bool $getValue = false, $default = null)
    {
        if ($getValue) {
            if (!isset($this->cliCommands[$command]) && null !== $default) {
                return $default;
            } elseif (!isset($this->cliCommands[$command])) {
                $this->commandTips($command, 'Need params');
            }

            return $this->cliCommands[$command];
        }

        //不取值时仅判断是否传了参数
        $has = array_key_exists($command, $this->cliCommands);
        if ($has && null !== $this->cliCommands[$command]) {
            return $this->cliCommands[$command];
        } elseif ($has) {
            return true;
        }

        return false;
    }

    /**
     * 解析命令行参数配置
     */
    function parseCliCommands(): void
    {
        $aliasData = [];
        if (!empty($this->commandConfig)) {
            foreach ($this->commandConfig as $command => $tips) {
                $d = [];
                $command = trim($command);
                if (false !== strpos($command, '|')) {
                    list($command, $shortCommand) = explode('|', $command);
                    $d['shortCommand'] = trim($shortCommand);
                    $d['cmdMsg'] = " -{$shortCommand}, --{$command}";
                } else {
                    $d['shortCommand'] = '';
                    $d['cmdMsg'] = " --{$command}";
                }

                $d['command'] = $command;
                $d['tips'] = $tips;

                $this->tipsData[] = $d;
                $aliasData[$command] = $d;
                if (!empty($d['shortCommand'])) {
                    $aliasData[$d['shortCommand']] = $d;
                }
            }
        }

        $this->commandConfig = $aliasData;
    }

    /**
     * 处理命令和参数
     */
    function parseParams(): void
    {
        //处理注释配置中的参数
        $this->oriParams = &$this->params;
        if (!empty($this->action_annotate['params'])) {
            $params = $this->action_annotate['params'];
        } else {
            $params = [];
        }

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

                        if (isset($this->commandConfig[$cmd])) {
                            $alias = &$this->commandConfig[$cmd];
                            $this->cliCommands[$cmd] = $cmdValue;
                            $this->cliCommands[$alias['command']] = $cmdValue;
                        } else {
                            for ($i = 0, $j = strlen($cmd); $i < $j; $i++) {
                                $cmdFlag = $cmd[$i];
                                if ($this->strictCommand && !isset($this->commandConfig[$cmdFlag])) {
                                    $this->commandTips($cmdFlag);
                                    break 2;
                                } elseif (!isset($this->commandConfig[$cmdFlag])) {
                                    break 2;
                                }

                                $commandAlias = &$this->commandConfig[$cmdFlag];
                                if (null !== $commandAlias) {
                                    $this->cliCommands[$cmdFlag] = $cmdValue;
                                    $this->cliCommands[$commandAlias['command']] = $cmdValue;
                                }
                            }
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
    }

    /**
     * 处理开发者信息
     */
    function initDevInfo(): void
    {
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
                $result = (new CliView())->genConfigFile($devFile, $dev);
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
     * 命令帮助信息
     *
     * @param string $cmd
     * @param string $tipsText
     */
    protected function commandTips(string $cmd = null, string $tipsText = 'Not support params')
    {
        $this->consoleMsg(PHP_EOL, false);

        $s = false;
        if (null !== $cmd) {
            $s = true;
            $cmdMsg = $this->commandConfig[$cmd]['cmdMsg'] ?? (!empty($cmd) ? "-{$cmd}" : $cmd);
            $this->consoleMsg("  {$tipsText}: " . $cmdMsg . PHP_EOL . PHP_EOL, false);
        }

        if (!empty($this->tipsData)) {
            $commandMaxLength = $shortCommandMaxLength = 0;
            foreach ($this->tipsData as $set) {
                $sLength = strlen($set['shortCommand']);
                if ($sLength > $shortCommandMaxLength) {
                    $shortCommandMaxLength = $sLength;
                }

                $commandLength = strlen($set['command']);
                if ($commandLength > $commandMaxLength) {
                    $commandMaxLength = $commandLength;
                }
            }

            $helperContext = [];
            $padSpace = str_pad('', $shortCommandMaxLength + $commandMaxLength + 11, ' ', STR_PAD_LEFT);
            array_map(function ($d) use ($shortCommandMaxLength, $commandMaxLength, $padSpace, &$helperContext) {
                $line = str_pad(!empty($d['shortCommand']) ? "  -{$d['shortCommand']}, " : '', $shortCommandMaxLength + 5, ' ', STR_PAD_LEFT);
                $line .= str_pad("--{$d['command']}    ", $commandMaxLength + 6, ' ', STR_PAD_LEFT);
                if (is_array($d['tips'])) {
                    array_walk($d['tips'], function ($t, $index) use ($padSpace, &$line, &$helperContext) {
                        if ($index == 0) {
                            $line .= $t;
                        } else {
                            $line = $padSpace . $t;
                        }
                        $helperContext[] = $line;
                    });
                } else {
                    $line .= $d['tips'];
                    $helperContext[] = $line;
                }
            }, $this->tipsData);
        } else {
            $padSpace = '  ';
        }

        if (!empty($this->commandDesc)) {
            if (!is_array($this->commandDesc)) {
                $this->commandDesc = [$this->commandDesc];
            }

            if (!empty($helperContext)) {
                $helperContext[] = '';
            }

            foreach ($this->commandDesc as $desc) {
                $helperContext[] = $padSpace . $desc;
            }
        }

        if (empty($helperContext)) {
            $helperContext[] = ($s ? '    - ' : '  No more information!');
        }

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
