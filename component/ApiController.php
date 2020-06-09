<?php
/**
 * @author wonli <wonli@live.com>
 * ApiController.php
 */

namespace component;

use Cross\Core\Annotate;
use Cross\Exception\CoreException;
use Cross\Interactive\ResponseData;
use Cross\Exception\FrontException;
use Cross\MVC\Controller;
use Cross\MVC\View;

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
     * 接口所需参数
     *
     * @var array
     */
    protected $api_request_params = [];

    /**
     * 默认请求类型
     *
     * @var string
     */
    private $request_type = 'post';

    /**
     * 数据容器
     *
     * @var array
     */
    private $data_container = [];

    /**
     * 根据输入类型重置数据容器
     *
     * @var array
     */
    private $input_data_container = ['multi_file' => true, 'file' => true];

    /**
     * 需要强制验证的输入数据
     *
     * @var array
     */
    private $force_params = [];

    /**
     * @return mixed
     */
    abstract function index();

    /**
     * ApiController constructor.
     *
     * @throws FrontException
     * @throws CoreException
     */
    function __construct()
    {
        parent::__construct();
        $this->view = new View();
        $this->data = (new ResponseData())->getData();

        //API文档数据
        if (!empty($_REQUEST['doc_token']) && !empty($_REQUEST['t'])) {
            $isVerify = $this->verifyDocApiToken($_REQUEST['doc_token'], $_REQUEST['t']);
            if (!$isVerify) {
                $this->display(100700);
                return;
            }

            $docData = $this->docApiData();
            $this->display($docData);
            return;
        }

        //验证请求类型
        $request_type = &$this->request_type;
        $annotate_api = &$this->action_annotate['api'];
        if (!empty($annotate_api)) {
            $request_method = $this->request->SERVER('REQUEST_METHOD');
            list($request_type) = explode(',', $annotate_api);
            if (strcasecmp($request_method, trim($request_type)) !== 0) {
                $this->data['status'] = 200000;
                $this->display($this->data);
                return;
            }
        }

        //验证请求所需参数
        $this->data_container = $this->getDataContainer($request_type);
        $annotate_request = &$this->action_annotate['request'];
        if (!empty($annotate_request)) {
            $request = explode(',', $annotate_request);
            if (!empty($request)) {
                foreach ($request as $params) {
                    $requestParams = explode("\n", $params);
                    foreach ($requestParams as $p) {
                        list($params, $message, $require) = explode('|', trim($p));
                        if (strpos($params, ':') !== false) {
                            list($params, $input_type) = explode(':', $params);
                        }

                        $this->api_request_params[] = $params;
                        if ($require) {
                            $data_container = $this->data_container;
                            if (isset($input_type) && isset($this->input_data_container[$input_type])) {
                                $data_container = $this->getDataContainer($input_type);
                            }

                            if (!isset($data_container[$params])) {
                                $this->data['status'] = 0;
                                $this->data['message'] = "缺少参数{$params}($message)";
                                $this->display($this->data);
                                return;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 从POST中获取指定数据
     *
     * @param string $key
     * @param bool $filter_data 是否使用过滤器
     * @param bool $is_force_params 是否强制验证
     * @return string
     * @throws FrontException
     * @throws CoreException
     */
    function getInputData(string $key, bool $filter_data = true, bool $is_force_params = false)
    {
        $value = '';
        $defaultValue = &$this->data_container[$key];
        if ($filter_data && $defaultValue) {
            $defaultValue = htmlentities(strip_tags(trim($defaultValue)), ENT_COMPAT, 'utf-8');
        }

        if (isset($this->force_params[$key]) || $is_force_params) {
            if (!isset($this->data_container[$key]) || '' == $defaultValue) {
                $this->data['status'] = 200100;
                $this->data['data']['need_params'] = $key;
                $this->display($this->data);
            } elseif ($filter_data) {
                $value = $this->filterInputData($key, $defaultValue);
            } else {
                $value = $defaultValue;
            }
        } elseif (isset($this->data_container[$key])) {
            if ($filter_data && '' != $defaultValue) {
                $value = $this->filterInputData($key, $defaultValue);
            } else {
                $value = $defaultValue;
            }
        }

        return $value;
    }

    /**
     * 过滤数据
     *
     * @param string $key
     * @param string $value
     * @return string|void
     * @throws FrontException
     * @throws CoreException
     */
    abstract protected function filterInputData(string $key, $value);

    /**
     * 获取通过header传送的数据
     *
     * @param string $key
     * @return string
     */
    function getHeaderData(string $key)
    {
        $data = $this->request->SERVER('HTTP_' . strtoupper($key));
        if (!$data && function_exists('getallheaders')) {
            $headers = getallheaders();
            $data = &$headers[$key];
        }

        return $data;
    }

    /**
     * 从FILES中获取数据
     *
     * @param string $key
     * @param string $filter_name
     * @param bool $is_multi 是否是多文件
     * @return array
     * @throws FrontException
     * @throws CoreException
     */
    function getFileData(string $key, string $filter_name = 'images', bool &$is_multi = false)
    {
        if (!empty($_FILES[$key]) && !empty($_FILES[$key]['name'])) {
            if ($filter_name == 'images') {
                $upload_name = &$_FILES[$key]['name'];
                $upload_tmp_name = &$_FILES[$key]['tmp_name'];
                if (!is_array($upload_name)) {
                    $is_multi = false;
                    $ext = $this->checkUploadImage($upload_name, $upload_tmp_name);
                } else {
                    $ext = array();
                    $is_multi = true;
                    for ($i = 0, $j = count($upload_name); $i < $j; $i++) {
                        $ext[$i] = $this->checkUploadImage($upload_name[$i], $upload_tmp_name[$i]);
                    }
                }

                $_FILES[$key]['ext'] = $ext;
                return $_FILES[$key];
            }
        }

        return [];
    }

    /**
     * 视图
     *
     * @param null $data
     * @param string $method
     * @param int $http_response_status
     * @throws FrontException
     * @throws CoreException
     * @see Controller::display()
     */
    protected function display($data = null, string $method = null, int $http_response_status = 200): void
    {
        $this->response->setContentType('JSON');
        $responseData = parent::getResponseData($data, true);
        if ($responseData['status'] != 1) {
            $frontException = new FrontException($responseData['message'], $responseData['status']);
            unset($responseData['message'], $responseData['status']);
            if (!empty($responseData)) {
                $frontException->addExtData($responseData);
            }

            throw $frontException;
        }

        $this->response->display(json_encode($responseData));
    }

    /**
     * 获取对应请求类型的数据容器
     *
     * @param string $request_type
     * @return mixed
     */
    private function getDataContainer(string $request_type)
    {
        switch ($request_type) {
            case 'file':
            case 'multi_file':
                $data_container = &$_FILES;
                break;

            case 'post':
                $data_container = &$_REQUEST;
                if (empty($data_container)) {
                    $input = file_get_contents("php://input");
                    $content_type = &$_SERVER['CONTENT_TYPE'];
                    if (0 == strcasecmp($content_type, 'application/json')) {
                        $data_container = json_decode($input, true);
                    } else {
                        $input = trim($input);
                        $input = trim($input, '"');
                        parse_str($input, $data_container);
                    }

                    $_POST = $data_container;
                }
                break;

            default:
                $data_container = &$_REQUEST;
        }

        return $data_container;
    }

    /**
     * API 调试文档基础数据
     *
     * @return array
     */
    protected function docApiData(): array
    {
        $result = [];
        $ANNOTATE = Annotate::getInstance($this->delegate);
        $controllerList = glob(__DIR__ . '/*.php');
        array_map(function ($f) use (&$result, $ANNOTATE) {
            $fileName = pathinfo($f, PATHINFO_FILENAME);
            $classNamespace = __NAMESPACE__ . '\\' . $fileName;
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
        $key = $this->config->get('encrypt', 'doc');
        $localToken = md5(md5($key . $t) . $t);
        return $localToken == $token;
    }

    /**
     * 验证上传图片文件
     *
     * @param string $upload_file_name 上传原始文件名
     * @param string $tmp_file 临时文件路径
     * @param int $size
     * @return mixed
     * @throws FrontException
     * @throws CoreException
     */
    private function checkUploadImage(string $upload_file_name, string $tmp_file, int $size = 3000000)
    {
        $allow_image_type = array('jpeg' => 1, 'jpg' => 1, 'png' => 1);
        $origin_name_info = explode('.', $upload_file_name);
        $origin_name_ext = end($origin_name_info);

        if (!isset($allow_image_type[$origin_name_ext])) {
            $this->data['status'] = 200052;
            $this->display($this->data);
        }

        $image_info = @getimagesize($tmp_file);
        if (!empty($image_info)) {
            $image_ext = strtolower(image_type_to_extension($image_info[2]));
            $image_type = substr($image_ext, 1);
            $image_size = filesize($tmp_file);

            //验证图片类型
            if (!isset($allow_image_type[$image_type])) {
                $this->data['status'] = 200052;
                $this->display($this->data);
            }

            //验证图片大小
            if ($image_size > $size) {
                $this->data['status'] = 200051;
                $this->display($this->data);
            }
        } else {
            $this->data['status'] = 200050;
            $this->display($this->data);
        }

        return $origin_name_ext;
    }
}