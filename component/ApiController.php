<?php
/**
 * @author wonli <wonli@live.com>
 * ApiController.php
 */

namespace component;

use Cross\Exception\CoreException;
use Cross\Exception\LogicStatusException;
use Cross\Interactive\ResponseData;
use Cross\Core\Annotate;
use Cross\MVC\Controller;
use Cross\MVC\View;

use ReflectionException;
use ReflectionMethod;
use ReflectionClass;

/**
 * @author wonli <wonli@live.com>
 * Class ApiController
 * @package app\api\controllers
 */
abstract class ApiController extends Controller
{
    /**
     * 默认请求类型
     *
     * @var string
     */
    protected $requestType = 'post';

    /**
     * 接口所需参数
     *
     * @var array
     */
    protected $apiRequestParams = [];

    /**
     * 参数容器类型
     *
     * @var array
     */
    protected $inputDataContainer = [];

    /**
     * 当前请求数据容器
     *
     * @var array
     */
    protected $requestDataContainer = [];

    /**
     * 根据输入类型重置数据容器
     *
     * @var array
     */
    protected $resetContainerType = ['multi_file' => true, 'file' => true];

    /**
     * @var ResponseData
     */
    protected $ResponseData;

    abstract function index();

    /**
     * ApiController constructor.
     *
     * @throws LogicStatusException|CoreException|ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $this->view = new View();
        $this->ResponseData = ResponseData::builder();

        //API文档数据
        $getData = $this->delegate->getRequest()->getGetData();
        $docToken = $getData['doc_token'] ?? null;
        $t = $getData['t'] ?? null;
        if ($docToken && $t) {
            $isVerify = $this->verifyDocApiToken($docToken, $t);
            if (!$isVerify) {
                $this->display(100700);
                return;
            }

            $docData = $this->docApiData();
            $this->display($docData);
            return;
        }

        //验证请求类型
        $requestType = &$this->requestType;
        $annotate_api = &$this->action_annotate['api'];
        if (!empty($annotate_api)) {
            $request_method = $this->delegate->getRequest()->SERVER('REQUEST_METHOD');
            list($requestType) = explode(',', $annotate_api);
            if (strcasecmp($request_method, trim($requestType)) !== 0) {
                $this->display(200000);
                return;
            }
        }

        //验证请求所需参数
        $this->requestDataContainer = $this->getDataContainer($requestType);
        $annotate_request = &$this->action_annotate['request'];
        if (!empty($annotate_request)) {
            $request = explode(',', $annotate_request);
            if (!empty($request)) {
                foreach ($request as $actionParams) {
                    $requestParams = explode("\n", $actionParams);
                    foreach ($requestParams as $p) {
                        list($params, $message, $require) = explode('|', trim($p));
                        if (strpos($params, ':') !== false) {
                            list($params, $inputParamsType) = explode(':', $params);
                            $this->inputDataContainer[$params] = $inputParamsType;
                        } else {
                            $inputParamsType = $requestType;
                            $this->inputDataContainer[$params] = $requestType;
                        }

                        $this->apiRequestParams[] = $params;
                        if ($require) {
                            $dataContainer = $this->requestDataContainer;
                            if (isset($this->resetContainerType[$inputParamsType])) {
                                $dataContainer = $this->getDataContainer($inputParamsType);
                            }

                            if (!isset($dataContainer[$params])) {
                                $this->ResponseData->setStatus(0);
                                $this->ResponseData->setMessage("缺少参数{$params}($message)");
                                $this->display($this->ResponseData);
                                return;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取输入数据
     *
     * @param string $key
     * @param mixed $default
     * @return DataFilter
     */
    function input(string $key, $default = null): DataFilter
    {
        $val = '';
        $dataContainer = $this->getDataContainer($this->inputDataContainer[$key] ?? $this->requestType);
        if (is_array($dataContainer)) {
            $val = $dataContainer[$key] ?? null;
        }

        if (empty($val) && null !== $default) {
            $val = $default;
        }

        return new DataFilter($val);
    }

    /**
     * 获取通过header传送的数据
     *
     * @param string $key
     * @return string
     */
    function getHeaderData(string $key)
    {
        $data = $this->delegate->getRequest()->SERVER('HTTP_' . strtoupper($key));
        if (!$data && function_exists('getallheaders')) {
            $headers = getallheaders();
            $data = &$headers[$key];
        }

        return $data;
    }

    /**
     * 输出JSON
     *
     * @param mixed $data
     * @param string|null $method
     * @param int $http_response_status
     * @throws CoreException|LogicStatusException
     * @see Controller::display()
     */
    protected function display($data = null, string $method = null, int $http_response_status = 200): void
    {
        $this->delegate->getResponse()->setResponseStatus($http_response_status)->setContentType('JSON');
        if (!$data instanceof ResponseData) {
            $data = parent::getResponseData($data);
        }

        if ($data->getStatus() != 1) {
            $LogicStatusException = new LogicStatusException($data->getStatus(), $data->getMessage());
            $LogicStatusException->addResponseData($data);
            throw $LogicStatusException;
        }

        $this->delegate->getResponse()->end(json_encode($data->getData(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * 获取对应请求类型的数据容器
     *
     * @param string $requestType
     * @return mixed
     */
    private function getDataContainer(string $requestType)
    {
        switch ($requestType) {
            case 'file':
            case 'multi_file':
                $dataContainer = $this->delegate->getRequest()->getFileData();
                break;

            case 'post':
                $dataContainer = $this->delegate->getRequest()->getPostData();
                if (empty($dataContainer)) {
                    $input = file_get_contents("php://input");
                    $content_type = $this->delegate->getRequest()->SERVER('CONTENT_TYPE');
                    if (0 == strcasecmp($content_type, 'application/json')) {
                        $dataContainer = json_decode($input, true);
                    } else {
                        $input = trim($input);
                        $input = trim($input, '"');
                        parse_str($input, $dataContainer);
                    }

                    $this->delegate->getRequest()->setPostData($dataContainer, true);
                }
                break;

            default:
                $dataContainer = $this->delegate->getRequest()->getRequestData();
        }

        return $dataContainer;
    }

    /**
     * API 调试文档基础数据
     *
     * @return array
     * @throws ReflectionException
     */
    protected function docApiData(): array
    {
        $result = [];
        $ANNOTATE = Annotate::getInstance($this->delegate);
        $controllerList = glob(PROJECT_REAL_PATH .
            str_replace('\\', DIRECTORY_SEPARATOR, $this->delegate->getAppNamespace()) . '/controllers/*.php');

        array_map(function ($f) use (&$result, $ANNOTATE) {
            $fileName = pathinfo($f, PATHINFO_FILENAME);
            $classNamespace = $this->delegate->getApplication()->getControllerNamespace($fileName);
            $rc = new ReflectionClass($classNamespace);
            if ($rc->isAbstract()) {
                return;
            }

            //跳过ignore
            $classAnnotate = $ANNOTATE->parse($rc->getDocComment());
            if (isset($classAnnotate['api_ignore'])) {
                return;
            }

            //公共参数是否生效
            $enable = ['enable' => true, 'true' => true, 'yes' => true, '1' => true];
            if (isset($classAnnotate['global_params'])) {
                $classAnnotate['global_params'] = isset($enable[$classAnnotate['global_params']]);
            } else {
                $classAnnotate['global_params'] = true;
            }

            $methodAnnotate = [];
            $methodList = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
            if (!empty($methodList)) {
                foreach ($methodList as $method) {
                    if ($method->class != $classNamespace) {
                        continue;
                    }

                    $ac = new ReflectionMethod($method->class, $method->name);
                    $comment = $ac->getDocComment();
                    if (!$comment) {
                        continue;
                    }

                    $annotate = $ANNOTATE->parse($comment);
                    if (isset($annotate['global_params'])) {
                        $annotate['global_params'] = isset($enable[$annotate['global_params']]);
                    } else {
                        $annotate['global_params'] = &$classAnnotate['global_params'];
                    }

                    $methodAnnotate[$method->name] = $annotate;
                }
            }

            $result[$fileName] = $classAnnotate;
            $result[$fileName]['methods'] = $methodAnnotate;

        }, $controllerList);

        return $result;
    }

    /**
     * 验证doc token
     *
     * @param string token
     * @param string $t
     * @return bool
     */
    private function verifyDocApiToken(string $token, $t): bool
    {
        //10秒过期
        if (time() - $t > 10) {
            return false;
        }

        $key = $this->getConfig()->get('encrypt', 'doc');
        $localToken = md5(md5($key . $t) . $t);
        return $localToken == $token;
    }
}
