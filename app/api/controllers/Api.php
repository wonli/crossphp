<?php
/**
 * @author wonli <wonli@live.com>
 * Web.php
 */

namespace app\api\controllers;


use app\api\views\ApiView;
use Cross\MVC\Controller;

/**
 * @author wonli <wonli@live.com>
 * Class Api
 * @package app\api\controllers
 *
 * @cp_doc_info array('title' => 'CrossPHP API', 'version' => '0.0.1')
 */
abstract class Api extends Controller
{
    /**
     * 用户ID
     *
     * @var int
     */
    protected $uid = 0;

    /**
     * 渠道
     *
     * @var string
     */
    protected $channel;

    /**
     * 平台
     *
     * @var string
     */
    protected $platform;

    /**
     * 客户端版本
     *
     * @var string
     */
    protected $version;

    /**
     * 通用数据结构
     *
     * @var array
     */
    protected $data = array('status' => 1, 'message' => 'ok', 'data' => array());

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
    private $data_container = array();

    /**
     * 根据输入类型重置数据容器
     *
     * @var array
     */
    private $input_data_container = array('multi_file' => true, 'file' => true);

    /**
     * 需要强制验证的输入数据
     *
     * @var array
     */
    private $force_params = array();

    /**
     * @return mixed
     */
    abstract function index();

    /**
     * Api constructor.
     *
     * @throws \Cross\Exception\CoreException
     */
    function __construct()
    {
        parent::__construct();
        $this->view = new ApiView();

        //验证请求类型
        $request_type = &$this->request_type;
        $annotate_api = &$this->action_annotate['api'];
        if (!empty($annotate_api)) {
            $request_method = $this->request->SERVER('REQUEST_METHOD');
            list($request_type) = explode(',', $annotate_api);
            if (strcasecmp($request_method, trim($request_type)) !== 0) {
                $this->data['status'] = 200000;
                $this->display($this->data);
            }
        }

        //验证请求所需参数
        $this->data_container = $this->getDataContainer($request_type);
        $annotate_request = &$this->action_annotate['request'];
        if (!empty($annotate_request)) {
            $request = explode(',', $annotate_request);
            if (!empty($request)) {
                foreach ($request as $p) {
                    list($params, $message, $require) = explode('|', trim($p));
                    if ($require) {
                        $param_name = $params;
                        $data_container = $this->data_container;
                        if (strpos($params, ':') !== false) {
                            list($param_name, $input_type) = explode(':', $params);
                            if (isset($this->input_data_container[$input_type])) {
                                $data_container = $this->getDataContainer($input_type);
                            }
                        }

                        if (!isset($data_container[$param_name])) {
                            $this->data['status'] = 0;
                            $this->data['message'] = "缺少参数{$param_name}($message)";
                            $this->display($this->data);
                        }
                    }
                }
            }
        }

        //设置公共参数
        $this->channel = $this->getInputData('channel', true, true);
        $this->platform = $this->getInputData('platform', true, true);
        $this->version = $this->getInputData('version', true, true);
    }

    /**
     * 从POST中获取指定数据
     *
     * @param string $key
     * @param bool $filter_data 是否使用过滤器
     * @param bool $is_force_params 是否强制验证
     * @return string
     * @throws \Cross\Exception\CoreException
     */
    function getInputData($key, $filter_data = true, $is_force_params = false)
    {
        $value = '';
        $defaultValue = &$this->data_container[$key];
        if ($defaultValue) {
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
            if ($filter_data && '' != $value) {
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
     * @return int|string
     * @throws \Cross\Exception\CoreException
     */
    protected function filterInputData($key, $value)
    {
        switch ($key) {

            case 'channel':
                if (empty($value)) {
                    $this->data['status'] = 200210;
                    $this->display($this->data);
                }
                break;

            case 'platform':
                if (empty($value)) {
                    $this->data['status'] = 200220;
                    $this->display($this->data);
                }
                break;

            case 'version':
                if (empty($value)) {
                    $this->data['status'] = 200230;
                    $this->display($this->data);
                }
                break;

            default:
                $value = htmlentities(strip_tags(trim($value)), ENT_COMPAT, 'utf-8');
        }

        return $value;
    }

    /**
     * 从FILES中获取数据
     *
     * @param string $key
     * @param string $filter_name
     * @param bool $is_multi 是否是多文件
     * @return array
     * @throws \Cross\Exception\CoreException
     */
    function getFileData($key, $filter_name = 'images', &$is_multi = false)
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

        return array();
    }

    /**
     * @see parent::display()
     *
     * @param null $data
     * @param null $method
     * @param int $http_response_status
     * @throws \Cross\Exception\CoreException
     */
    function display($data = null, $method = null, $http_response_status = 200)
    {
        if ($data['status'] != 1 && $data['message'] == 'ok') {
            $data['message'] = $this->getStatusMessage($data['status']);
        }

        $apiData['status'] = $data['status'];
        $apiData['message'] = $data['message'];
        if (!isset($data['data'])) {
            $apiData['data'] = array();
        } else {
            $apiData['data'] = $data['data'];
        }

        $this->response->setContentType('json')->display(json_encode($apiData));
        exit(0);
    }

    /**
     * 获取对应请求类型的数据容器
     *
     * @param string $request_type
     * @return mixed
     */
    private function getDataContainer($request_type)
    {
        switch ($request_type) {
            case 'file':
            case 'multi_file':
                $data_container = &$_FILES;
                break;

            case 'post':
                $data_container = &$_POST;
                break;

            case 'get':
                $data_container = &$_GET;
                break;

            default:
                $data_container = &$_POST;
        }

        return $data_container;
    }

    /**
     * 获取消息状态内容
     *
     * @param int $status
     * @return string
     * @throws \Cross\Exception\CoreException
     */
    private function getStatusMessage($status)
    {
        static $notice = null;
        if ($notice === null) {
            $notice = $this->parseGetFile('config/notice.config.php');
        }

        if (isset($notice[$status])) {
            $message = $notice[$status];
        } else {
            $message = 'ok';
        }

        return $message;
    }

    /**
     * 验证上传图片文件
     *
     * @param string $upload_file_name 上传原始文件名
     * @param string $tmp_file 临时文件路径
     * @param int $size
     * @return mixed
     * @throws \Cross\Exception\CoreException
     */
    private function checkUploadImage($upload_file_name, $tmp_file, $size = 3000000)
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
