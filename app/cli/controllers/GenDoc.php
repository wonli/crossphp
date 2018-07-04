<?php
/**
 * @author wonli <wonli@live.com>
 * GenDoc.php
 */

namespace app\cli\controllers;

use Cross\Core\Loader;
use Cross\Exception\CoreException;
use Cross\Core\Annotate;
use ReflectionMethod;
use ReflectionClass;

/**
 * 生成API文档
 *
 * @author wonli <wonli@live.com>
 * Class GenDoc
 * @package app\cli\controllers
 */
class GenDoc extends Cli
{
    /**
     * 文档生成
     *
     * @cp_params file=main
     * @param string $name
     * @throws CoreException
     * @throws \ReflectionException
     */
    function index($name = '')
    {
        $fileName = &$this->params['file'];
        $configName = "config::{$fileName}.doc.php";
        $configFile = $this->getFilePath($configName);
        if (!file_exists($configFile)) {
            $this->flushMessage("是否生成配置文件 {$configName} (y/n) - ", false);
            $response = trim(fgetc(STDIN));
            if (0 === strcasecmp($response, 'y')) {
                //生成配置文件
                $ret = $this->view->makeDocConfigFile($configFile);
                if (!$ret) {
                    $this->endFlushMessage('创建配置文件失败');
                }
            } else {
                $this->endFlushMessage('请先创建配置文件');
            }
        }

        $docConfig = Loader::read($configFile);
        if (!empty($name)) {
            if (!isset($docConfig[$name])) {
                $this->endFlushMessage("未发现指定的配置{$name}");
            }

            $this->genDoc($name, $docConfig[$name]);
        } elseif (!empty($docConfig)) {
            foreach ($docConfig as $name => $config) {
                $this->genDoc($name, $config);
            }
        }
    }

    /**
     * @see index
     *
     * @param string $name 指定参数
     * @param array $params
     * @throws CoreException
     * @throws \ReflectionException
     * @cp_params file=main
     */
    function __call($name, $params)
    {
        $this->index($name);
    }

    /**
     * 生成文档
     *
     * @param string $name
     * @param array $config
     * @throws \ReflectionException
     */
    private function genDoc($name, $config)
    {
        if (!empty($config)) {
            $source = &$config['source'];
            if (!$source) {
                $this->endFlushMessage('请指定源目录');
            }

            $source = trim($source, '\\');
            $source = trim($source, '/');
            $source = PROJECT_REAL_PATH . $source;
            if (!is_dir($source)) {
                $this->endFlushMessage('源目录不存在');
            }

            $output_dir = &$config['output'];
            if (!$output_dir) {
                $this->endFlushMessage('请指定文档入口生成目录');
            }

            $output_dir = trim($output_dir, '\\');
            $output_dir = trim($output_dir, '/');
            $output_dir = PROJECT_REAL_PATH . $output_dir . DIRECTORY_SEPARATOR;
            if (!is_dir($output_dir)) {
                mkdir($output_dir, 0755, true);
            }

            $api_host = &$config['api_host'];
            if (!$api_host) {
                if (PHP_SAPI == 'cli') {
                    if (!empty($_SERVER['SSH_CONNECTION'])) {
                        list(, , $server_ip) = explode(' ', $_SERVER['SSH_CONNECTION']);
                    } else {
                        $host = gethostname();
                        $server_ip = gethostbyname($host);
                    }
                } else {
                    $server_ip = $_SERVER['SERVER_ADDR'];
                }

                $api_host = '//' . trim($server_ip);
            }

            $asset_server = &$config['asset_server'];
            if (!$asset_server) {
                $asset_server = '';
            }

            $annotate = $this->scanSource($source);

            //处理公共配置
            $global_params = &$config['global_params'];
            $this->globalParams($global_params, $output_dir);

            $data['doc_info'] = &$config['info'];
            $data['top_nav'] = &$config['top_nav'];
            $data['basic_auth'] = &$config['basic_auth'];
            $data['asset_server'] = $asset_server;
            $data['output_dir'] = $output_dir;
            $data['api_host'] = $api_host;
            $data['annotate'] = array(
                'global_params' => $global_params,
                'data' => $annotate
            );

            $ret = $this->view->index($data);
            if ($ret) {
                $this->flushMessage("生成{$name}文档 [成功]");
            } else {
                $this->flushMessage("生成{$name}文档 [失败]");
            }
        }
    }

    /**
     * 扫描目录, 获取注释
     *
     * @param string $source
     * @return array
     * @throws \ReflectionException
     */
    private function scanSource($source)
    {
        //过滤的类名称
        $ingotController = array(
            'Cross\MVC\Controller',
            'Cross\Core\FrameBase'
        );

        //过滤的方法
        $ingotAction = array(
            '__construct',
            '__destruct',
            '__toString',
            '__call',
            '__set',
            '__isset',
            '__unset',
            '__sleep',
            '__wakeup',
            '__invoke',
            '__clone',
            '__set_state',
            '__debug_info',
            '__get'
        );

        $ANNOTATE = Annotate::getInstance($this->delegate);

        $annotate_config = array();
        $global_params_status = true;
        $controllerList = glob("{$source}/*.*");
        foreach ($controllerList as & $file) {
            $file_info = explode('/', $file);
            $i['file_name'] = array_pop($file_info);
            $i['dir'] = array_pop($file_info);
            $i['app'] = array_pop($file_info);

            $f = explode('.', $i['file_name']);
            if (end($f) != 'php') {
                continue;
            }

            $app = $i['app'];
            $className = explode('.', $i['file_name']);
            $className = array_shift($className);
            $classNameSpace = "app\\{$app}\\controllers\\{$className}";
            $controllerName = lcfirst($className);

            $rc = new ReflectionClass($classNameSpace);
            if ($rc->isAbstract()) {
                continue;
            }

            $rcAnnotate = $rc->getDocComment();
            $classAnnotate = $ANNOTATE->parse($rcAnnotate);

            if (isset($classAnnotate['api_ignore'])) {
                continue;
            }

            if (isset($classAnnotate['global_params'])) {
                $enable = array('enable' => true, 'true' => true, 'yes' => true, '1' => true);
                $global_params_status = isset($enable[$classAnnotate['global_params']]) ? true : false;
            }

            $actionAnnotate = array();
            $methodList = $rc->getMethods();
            if (!empty($methodList)) {
                foreach ($methodList as $action) {
                    if (in_array($action->class, $ingotController)) {
                        continue;
                    }

                    if (in_array($action->name, $ingotAction)) {
                        continue;
                    }

                    $methodRc = new ReflectionMethod($classNameSpace, $action->name);
                    if ($methodRc->isPublic() && !$methodRc->isAbstract()) {
                        $annotate = Annotate::getInstance($this->delegate)->parse($methodRc->getDocComment());
                        if (!empty($annotate)) {
                            $annotate['action'] = $action->name;
                            $annotate['controller'] = $controllerName;
                            if (!isset($annotate['global_params'])) {
                                $annotate['global_params'] = $global_params_status;
                            }

                            $actionAnnotate[$action->name] = $annotate;
                        }
                    }
                }
            }

            $annotate_config[] = array(
                'info' => array(
                    'class' => $controllerName,
                    'namespace' => $classNameSpace,
                    'desc' => isset($classAnnotate['api_spec']) ? trim($classAnnotate['api_spec']) : '',
                ),
                'class_annotate' => $classAnnotate,
                'action_annotate' => $actionAnnotate,
            );
        }

        return $annotate_config;
    }

    /**
     * 处理全局参数
     *
     * @param array $global_params
     * @param string $output_dir
     */
    private function globalParams(array $global_params, $output_dir)
    {
        $data = array();
        if (!empty($global_params)) {
            foreach ($global_params as $k => $v) {
                if (!empty($k) && !empty($v)) {
                    $data[] = array('t' => $v, 'f' => $k, 'v' => '');
                }
            }
        }

        $global_config_file = $output_dir . '.global.json';
        if (file_exists($global_config_file)) {
            unlink($global_config_file);
        }

        if (!empty($data)) {
            file_put_contents($global_config_file, json_encode($data));
        }
    }
}
