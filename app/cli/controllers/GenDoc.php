<?php
/**
 * @Auth: wonli <wonli@live.com>
 * GenDoc.php
 */

namespace app\cli\controllers;

use Cross\Core\Annotate;
use ReflectionMethod;
use ReflectionClass;

/**
 * 生成API文档
 *
 * @Auth: wonli <wonli@live.com>
 * Class GenDoc
 * @package app\cli\controllers
 */
class GenDoc extends Cli
{
    function index()
    {
        $source = $this->params['source'];
        $output_dir = $this->params['output'];
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

        $annotate = $this->scanSource($source);
        $this->genApiPage($annotate, $output_dir, $api_host);
    }

    /**
     * 生成文档页面
     *
     * @param array $annotate
     * @param string $output_dir
     * @param string $api_host
     */
    private function genApiPage(array $annotate, $output_dir, $api_host)
    {
        $data['annotate'] = $annotate;
        $data['output_dir'] = $output_dir;
        $data['api_host'] = $api_host;

        $this->display($data);
    }

    /**
     * 扫描目录, 获取注释
     *
     * @param string $source
     * @return array
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

        $doc_info = array();
        $annotate_config = array();
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
            $classAnnotate = Annotate::getInstance($this->delegate)->parse($rcAnnotate);

            if (isset($classAnnotate['doc_info'])) {
                $doc_info = $classAnnotate['doc_info'];
            }

            $parentClassAnnotate = array();
            $pcParents = $rc->getParentClass();
            foreach ($pcParents as $pcParentName) {
                if (in_array($pcParentName, $ingotController)) {
                    continue;
                }

                $prc = new ReflectionClass($pcParentName);
                $prcAnnotate = $prc->getDocComment();
                $parentClassAnnotate = Annotate::getInstance($this->delegate)->parse($prcAnnotate);

                if (isset($parentClassAnnotate['doc_info'])) {
                    $doc_info = $parentClassAnnotate['doc_info'];
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
                            $actionAnnotate[$action->name] = $annotate;
                        }
                    }
                }
            }

            if (isset($classAnnotate['api_ignore'])) {
                continue;
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

        return array('doc_info' => $doc_info, 'data' => $annotate_config);
    }

}
