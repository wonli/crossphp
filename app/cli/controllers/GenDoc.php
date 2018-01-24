<?php
/**
 * @author wonli <wonli@live.com>
 * GenDoc.php
 */

namespace app\cli\controllers;

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
     * @throws CoreException
     * @throws \ReflectionException
     */
    function index()
    {
        if (!empty($this->params['source'])) {
            $source = $this->params['source'];
        } else {
            throw new CoreException('source dir not define');
        }

        if (!empty($this->params['output'])) {
            $output_dir = rtrim($this->params['output'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (!is_dir($output_dir)) {
                mkdir($output_dir, 0755, true);
            }
        } else {
            throw new CoreException('output dir not define');
        }

        if (!empty($this->params['apiHost'])) {
            $api_host = &$this->params['apiHost'];
        } else {
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

            $api_host = trim($server_ip);
        }

        if (!empty($this->params['assetServer'])) {
            $asset_server = &$this->params['assetServer'];
        } else {
            $asset_server = '';
        }

        $annotate = $this->scanSource($source);

        //处理公共配置
        if (!empty($annotate['global_params'])) {
            $this->globalParams($annotate['global_params'], $output_dir);
        }

        $data['asset_server'] = $asset_server;
        $data['output_dir'] = $output_dir;
        $data['api_host'] = $api_host;
        $data['annotate'] = $annotate;

        $this->display($data);
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

        $doc_info = array();
        $basic_auth = array();
        $global_params = array();
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

            if (isset($classAnnotate['doc_info'])) {
                $doc_info = $classAnnotate['doc_info'];
            }

            if (isset($classAnnotate['global_params'])) {
                $enable = array('enable' => true, 'true' => true, 'yes' => true, '1' => true);
                $global_params_status = isset($enable[$classAnnotate['global_params']]) ? true : false;
            }

            $parentClassAnnotate = array();
            $pcParents = $rc->getParentClass();
            foreach ($pcParents as $pcParentName) {
                if (in_array($pcParentName, $ingotController)) {
                    continue;
                }

                $prc = new ReflectionClass($pcParentName);
                $prcAnnotate = $prc->getDocComment();
                $parentClassAnnotate = $ANNOTATE->parse($prcAnnotate);

                if (isset($parentClassAnnotate['doc_info'])) {
                    $doc_info = $ANNOTATE->toCode($parentClassAnnotate['doc_info']);
                }

                if (isset($parentClassAnnotate['doc_basic_auth'])) {
                    $basic_auth = $ANNOTATE->toCode($parentClassAnnotate['doc_basic_auth']);
                }

                if (isset($parentClassAnnotate['doc_global_params'])) {
                    $global_params = $ANNOTATE->toCode($parentClassAnnotate['doc_global_params']);
                }
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
                'parent_annotate' => $parentClassAnnotate,
                'action_annotate' => $actionAnnotate,
            );
        }

        return array('doc_info' => $doc_info, 'basic_auth' => $basic_auth, 'global_params' => $global_params, 'data' => $annotate_config);
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

        if (!empty($data)) {
            $global_config_file = $output_dir . '.global.json';
            file_put_contents($global_config_file, json_encode($data));
        }
    }
}
