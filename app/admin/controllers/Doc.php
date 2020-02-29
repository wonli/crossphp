<?php
/**
 * @author wonli <wonli@live.com>
 * Doc.php
 */


namespace app\admin\controllers;


use app\admin\supervise\ApiDocModule;
use app\admin\supervise\CodeSegment\CURL;
use app\admin\supervise\CodeSegment\Generator;
use app\admin\views\DocView;
use Cross\Core\Helper;
use lib\Spyc;

/**
 * Class Doc
 * @package app\admin\controllers
 * @property DocView $view
 */
class Doc extends Admin
{
    /**
     * ApiDocModule
     *
     * @var ApiDocModule
     */
    protected $ADM;

    /**
     * yaml缓存路径
     *
     * @var string
     */
    protected $yamlFileCachePath;

    /**
     * 禁用操作日志
     *
     * @var bool
     */
    protected $saveActLog = false;

    /**
     * Doc constructor.
     *
     * @throws \Cross\Exception\CoreException
     * @throws \ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $this->ADM = new ApiDocModule();
        $this->yamlFileCachePath = $this->ADM->getCachePath();
    }

    /**
     * @throws \Cross\Exception\CoreException
     */
    function index()
    {
        $this->to('doc:setting');
    }

    /**
     * @param $doc_id
     * @param $args
     * @throws \Cross\Exception\CoreException
     */
    function __call($doc_id, $args)
    {
        $data = $this->ADM->get((int)$doc_id);
        if (empty($data)) {
            return $this->to('doc:setting');
        }

        $servers = &$data['servers'];
        $userData = $this->ADM->getAllUserData($this->u, $doc_id);
        $userServerID = &$userData['host']['sid'];
        $currentServerID = 0;

        $this->data['doc'] = $data;
        $this->data['data'] = array();
        $this->data['doc_id'] = $doc_id;
        $this->data['user_data'] = $userData;
        $this->data['current_sid'] = $currentServerID;

        if (empty($servers)) {
            return $this->display($this->data, 'index');
        } else {
            if (isset($servers[$userServerID])) {
                $currentServerID = $userServerID;
            } else {
                foreach ($servers as $sid => $s) {
                    if (isset($s['is_default'])) {
                        $currentServerID = $sid;
                        break;
                    }
                }
            }

            $apiServer = &$servers[$currentServerID];
            $this->initApiData($apiServer['server_name'], $apiServer['api_addr'], $data['doc_token'], false);

            $this->data['api_host'] = $apiServer['api_addr'];
            $this->data['current_sid'] = $currentServerID;
            $docData = Spyc::YAMLLoad($apiServer['cache_file']);
            if (!empty($docData)) {
                $this->data['data'] = $docData;
            }

            return $this->display($this->data, 'index');
        }
    }

    /**
     * 代码片段
     *
     * @cp_display codeSegment
     * @throws \Cross\Exception\CoreException
     */
    function codeSegment()
    {
        $docId = &$_POST['doc_id'];
        $method = &$_POST['method'];
        $params = &$_POST['params'];
        $url = &$_POST['action'];

        $data = $this->getApiCurlData($docId, $url, $method, $params);
        if (!empty($data) && is_array($data)) {
            $this->data['data'] = (new Generator())->run($data);
        } else {
            $this->data['data'] = [];
        }
        $this->data['curl_params'] = [
            'url' => $url,
            'method' => $method,
            'params' => $params
        ];

        $this->display($this->data);
    }

    /**
     * @cp_params api, doc_id, method=post
     * @throws \Cross\Exception\CoreException
     */
    function curlRequest()
    {
        $api = $this->params['api'];
        if (empty($api)) {
            $this->to('doc');
            return;
        }

        $docId = $this->params['doc_id'];
        if (empty($docId)) {
            $this->to('doc');
            return;
        }

        $params = [];
        foreach ($_REQUEST as $k => $v) {
            if (!isset($this->params[$k])) {
                $params[$k] = $v;
            }
        }

        $apiUrl = urldecode($api);
        $curlData = $this->getApiCurlData($docId, urldecode($apiUrl), $this->params['method'], $params);
        $this->data['data'] = $curlData;
        $this->data['curl_params'] = [
            'url' => $apiUrl,
            'method' => $this->params['method'],
            'params' => $params
        ];

        $this->display($this->data);
    }

    /**
     * 发起curl请求
     *
     * @param string $docId
     * @param string $apiUrl
     * @param string $method
     * @param array $params
     * @return array|mixed
     * @throws \Cross\Exception\CoreException
     */
    function getApiCurlData($docId, $apiUrl, $method, &$params = array())
    {
        $headerParams = array();
        if (!empty($docId)) {
            $doc = $this->ADM->get($docId);
            if (!empty($doc['header_params'])) {
                $Api = new ApiDocModule();
                $userData = $Api->getAllUserData($this->u, $docId);
                if (!empty($userData['header_params'])) {
                    foreach ($userData['header_params'] as $k => $v) {
                        $headerParams[] = sprintf("%s: %s", $k, $v);
                    }
                }

                if (!empty($userData['global_params'])) {
                    $params = array_merge($params, $userData['global_params']);
                }
            }
        }

        $curlData = (new CURL())->setUrl($apiUrl)
            ->setParams($params)
            ->setHeaderParams($headerParams)
            ->setMethod($method)
            ->request();

        $data = json_decode($curlData, true);
        if (!is_array($data)) {
            return $curlData;
        }

        return $data;
    }

    /**
     * 代码生成
     *
     * @throws \Cross\Exception\CoreException
     */
    function generator()
    {
        $data = array();
        $show_input = true;
        if ($this->is_post()) {
            $show_input = false;
            $json = &$_POST['json'];
            if (!empty($json)) {
                $json = str_replace(["\r\n", "\r", "\n"], "", $json);
                if (false !== ($inputData = json_decode($json, true)) && is_array($inputData)) {
                    $data = (new Generator())->run($inputData);
                }
            }
        }

        $this->data['data'] = $data;
        $this->data['show_input'] = $show_input;
        $this->display($this->data);
    }

    /**
     * 更改API服务器地址
     *
     * @cp_params doc_id, sid=0
     * @throws \Cross\Exception\CoreException
     */
    function changeApiServer()
    {
        $doc_id = (int)$this->params['doc_id'];
        if (!$doc_id) {
            return $this->to('doc:setting');
        }

        $sid = (int)$this->params['sid'];
        $docInfo = $this->ADM->get($doc_id);
        $servers = &$docInfo['servers'];
        if (!isset($servers[$sid])) {
            return $this->to('doc:setting');
        }

        $valueData = array('sid' => $sid);
        $data = $this->ADM->getUserData($this->u, $doc_id, ApiDocModule::KEY_HOST);
        if ($data == false) {
            $this->ADM->addUserData($this->u, $doc_id, ApiDocModule::KEY_HOST, $valueData);
        } else {
            $this->ADM->updateUserData($data['id'], array(
                'value' => json_encode($valueData)
            ));
        }

        return $this->dieJson($this->data);
    }

    /**
     * 保存公共参数
     *
     * @cp_params doc_id
     * @throws \Cross\Exception\CoreException
     */
    function saveCommonParams()
    {
        $doc_id = (int)$this->params['doc_id'];
        if (!$doc_id) {
            return $this->to('doc:setting');
        }

        if ($this->is_post()) {
            foreach ($_POST as $k => $v) {
                switch ($k) {
                    case ApiDocModule::KEY_HEADERPARAMS:
                    case ApiDocModule::KEY_GLOBALPARAMS:
                        if (!empty($v)) {
                            $data = $this->ADM->getUserData($this->u, $doc_id, $k);
                            if ($data == false) {
                                $this->ADM->addUserData($this->u, $doc_id, $k, $v);
                            } else {
                                $this->ADM->updateUserData($data['id'], array(
                                    'value' => json_encode($v)
                                ));
                            }
                        }
                        break;
                }
            }
        }

        $hash = &$_POST['hash'];
        $url = $this->view->url("doc:{$doc_id}");
        if ($hash) {
            $url .= '#!' . $hash;
        }

        return $this->redirect($url);
    }

    /**
     * @throws \Cross\Exception\CoreException
     */
    function setting()
    {
        $this->data['list'] = $this->ADM->getAll();
        $this->display($this->data);
    }

    /**
     * @cp_params action=add, id
     * @throws \Cross\Exception\CoreException
     */
    function action()
    {
        if ($this->is_post()) {
            $siteName = &$_POST['name'];
            $docToken = &$_POST['doc_token'];
            if (!$siteName) {
                return $this->dieJson($this->getStatus(100703));
            }

            if (!$docToken) {
                return $this->dieJson($this->getStatus(100701));
            }

            $servers = array();
            $devs = &$_POST['dev'];
            if (!empty($devs)) {
                foreach ($devs as $d) {
                    if (!empty($d['cache_name']) && !empty($d['api_addr'])) {
                        $d['api_addr'] = rtrim($d['api_addr'], '/');
                        $servers[] = $d;
                    }
                }
            }

            $global_params = array();
            $global = $_POST['global'];
            if (!empty($global)) {
                foreach ($global as $g) {
                    $key = trim($g['key']);
                    $name = trim($g['name']);
                    if (!empty($key)) {
                        $global_params[$key] = !empty($name) ? $name : $key;
                    }
                }
            }

            $header_params = array();
            $header = $_POST['header'];
            if (!empty($header)) {
                foreach ($header as $g) {
                    $key = trim($g['key']);
                    $name = trim($g['name']);
                    if (!empty($key)) {
                        $header_params[$key] = !empty($name) ? $name : $key;
                    }
                }
            }

            $saveData = array(
                'name' => $siteName,
                'servers' => json_encode($servers),
                'global_params' => json_encode($global_params),
                'header_params' => json_encode($header_params),
                'doc_token' => $docToken,
                'last_update_admin' => $this->u,
            );

            $id = $this->params['id'];
            if (!empty($id)) {
                $this->ADM->update($id, $saveData);
            } else {
                $this->ADM->add($saveData);
            }
            return $this->to('doc:setting');
        } else {
            switch ($this->params['action']) {
                case 'edit':
                    $this->data['data'] = $this->ADM->get($this->params['id']);
                    break;

                case 'del':
                    $this->ADM->del($this->params['id']);
                    return $this->to('doc:setting');
                    break;

                default:
                    $this->data['data'] = array();
            }
        }

        return $this->display($this->data);
    }

    /**
     * 生成部署服务器DOM
     */
    function makeDevServerNode()
    {
        $this->view->makeDevServerNode($this->data);
    }

    /**
     * @cp_params t=global
     * 生成参数DOM
     */
    function makeParamsNode()
    {
        $this->data['t'] = $this->params['t'];
        $this->view->makeParamsNode($this->data);
    }

    /**
     * 获取接口文档数据
     *
     * @param string $serverName
     * @param string $apiAddr
     * @param string $docToken
     * @param bool $display
     * @throws \Cross\Exception\CoreException
     */
    function initApiData($serverName = '', $apiAddr = '', $docToken = '', $display = true)
    {
        if (empty($serverName)) {
            $serverName = &$_REQUEST['server_name'];
        }

        if (empty($apiAddr)) {
            $apiAddr = &$_REQUEST['api_addr'];
        }

        if (empty($docToken)) {
            $docToken = &$_REQUEST['doc_token'];
        }

        if (!$docToken) {
            $this->dieJson($this->getStatus(100701));
            return;
        }

        if (!$serverName) {
            $this->dieJson($this->getStatus(100710));
            return;
        }

        if (!$apiAddr) {
            $this->dieJson($this->getStatus(100711));
            return;
        }

        $requestParams = http_build_query([
            'doc_token' => md5(md5($docToken . TIME) . TIME),
            't' => TIME,
        ]);

        $url = $apiAddr . '?' . $requestParams;
        $response = Helper::curlRequest($url);
        if (($responseData = json_decode($response, true)) === false) {
            $this->dieJson($this->getStatus(100705, $url));
            return;
        }

        if (empty($responseData['status']) || $responseData['status'] != 1) {
            $responseData['api_url'] = $url;
            $this->dieJson($responseData);
            return;
        }

        if (empty($responseData['data'])) {
            $this->dieJson($this->getStatus(100706, $url));
            return;
        }

        $data = &$responseData['data'];
        $cache_file_name = md5($apiAddr);

        $result = [];
        foreach ($data as $k => $d) {
            $actions = [];
            if (!empty($d['methods'])) {
                foreach ($d['methods'] as $act => $m) {
                    if (!empty($m['api'])) {
                        $api = explode(',', $m['api']);
                        $api = array_map('trim', $api);

                        $method = [
                            'class' => $k,
                            'action' => $act,
                            'method' => $api[0],
                            'requestPath' => $api[1],
                            'useGlobalParams' => $m['global_params'],
                        ];

                        $apiParams = [];
                        if (!empty($m['request'])) {
                            if (isset($m['request'])) {
                                if (!empty($m['request'])) {
                                    $request = explode(',', $m['request']);
                                    foreach ($request as $f) {
                                        @list($dd['field'], $dd['label'], $dd['required']) = explode('|', $f);
                                        $dd = array_map('trim', $dd);
                                        $apiParams[$dd['field']] = [
                                            'label' => $dd['label'],
                                            'required' => (bool)$dd['required'],
                                        ];
                                    }
                                }
                            }
                            $method['params'] = $apiParams;
                        }

                        $actions[$api[2]] = $method;
                    }
                }
            }

            if (!empty($actions)) {
                $apiSpec = !empty($d['api_spec']) ? $d['api_spec'] : $k;
                $result[$apiSpec] = $actions;
            }
        }

        $a = Spyc::YAMLDump($result);
        $cache_file = $this->yamlFileCachePath . "/{$cache_file_name}.yaml";
        $ret = file_put_contents($cache_file, $a);

        if ($display) {
            if (!$ret) {
                $this->dieJson($this->getStatus(100720, $url));
            } else {
                $this->data['data'] = array(
                    'url' => $url,
                    'cache_name' => $cache_file_name,
                    'cache_at' => TIME,
                    'user' => $this->u,
                );

                $this->dieJson($this->data);
            }
        }
    }
}