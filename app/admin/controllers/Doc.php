<?php
/**
 * @author wonli <wonli@live.com>
 * Doc.php
 */


namespace app\admin\controllers;

use ReflectionException;
use Cross\Exception\CoreException;
use Cross\Core\Helper;

use app\admin\supervise\ApiDocModule;
use app\admin\supervise\CodeSegment\CURL;
use app\admin\supervise\CodeSegment\Generator;
use app\admin\views\DocView;

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
     * @throws CoreException
     * @throws ReflectionException
     */
    function __construct()
    {
        parent::__construct();
        $this->ADM = new ApiDocModule();
        $this->yamlFileCachePath = $this->ADM->getCachePath();
    }

    /**
     * @throws CoreException
     */
    function index()
    {
        $this->to('doc:setting');
    }

    /**
     * @param $doc_id
     * @param $args
     * @throws CoreException
     */
    function __call($doc_id, $args)
    {
        $data = $this->ADM->get((int)$doc_id);
        if (empty($data)) {
            $this->to('doc:setting');
            return;
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
            $this->display($this->data, 'index');
            return;
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
            if (empty($apiServer)) {
                $this->to('doc');
            }

            $this->data['current_sid'] = $currentServerID;
            $this->data['api_host'] = $apiServer['api_addr'];

            //更新文档数据
            $updateStatus = $this->getInitApiData($doc_id, $apiServer['api_addr'], $data['doc_token']);
            if ($updateStatus['status'] != 1) {
                $data = array_merge($this->data, $updateStatus);
                $this->display($data, 'index');
                return;
            }

            $apiServer['cache_name'] = $updateStatus['message']['cache_name'];
            $apiServer['cache_file'] = $updateStatus['message']['cache_file'];
            $apiServer['cache_at'] = time();
            $servers[$currentServerID] = $apiServer;
            $this->ADM->update($data['id'], [
                'servers' => json_encode($servers)
            ]);

            $docData = Spyc::YAMLLoad($apiServer['cache_file']);
            if (!empty($docData)) {
                $this->data['data'] = $docData;
            }

            $this->display($this->data, 'index');
            return;
        }
    }

    /**
     * 初始化api接口数据
     *
     * @throws CoreException
     */
    function initApiData()
    {
        $status = 1;
        $apiAddr = &$_REQUEST['api_addr'];
        if (empty($apiAddr)) {
            $status = 100711;
        }

        $docToken = &$_REQUEST['doc_token'];
        if (empty($docToken)) {
            $status = 100701;
        }

        if ($status !== 1) {
            $this->data = $this->getStatus($status);
            $this->display($this->data, 'JSON');
            return;
        }

        $updateStatus = $this->getInitApiData(0, $apiAddr, $docToken);
        if ($updateStatus['status'] != 1) {
            $this->display($updateStatus, 'JSON');
            return;
        }

        $this->data['data'] = $updateStatus['message'];
        $this->display($this->data, 'JSON');
    }

    /**
     * 代码片段
     *
     * @cp_display codeSegment
     * @throws CoreException
     */
    function codeSegment()
    {
        $docId = &$_POST['doc_id'];
        $method = &$_POST['method'];
        $params = &$_POST['params'];
        $apiUrl = &$_POST['api'];
        $apiPath = &$_POST['path'];

        $data = $this->getApiCurlData($docId, $apiUrl, $method, $params);
        if (!empty($data) && is_array($data)) {
            $g = (new Generator())->run($data);
            if (!empty($g['struct'])) {
                (new ApiDocModule())->saveCache($docId, $apiPath, $g['struct']);
            }

            $this->data['data'] = $g;
        } else {
            $this->data['data'] = [];
        }
        $this->data['curl_params'] = [
            'url' => $apiUrl,
            'method' => $method,
            'params' => $params
        ];

        $this->display($this->data);
    }

    /**
     * @cp_params host, path, doc_id, method=post
     * @throws CoreException
     */
    function curlRequest()
    {
        $host = $this->params['host'];
        if (empty($host)) {
            $this->to('doc');
            return;
        } else {
            $host = urldecode($host);
        }

        $path = $this->params['path'];
        if (empty($path)) {
            $this->to('doc');
            return;
        } else {
            $path = urldecode($path);
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

        $apiUrl = rtrim($host, '/') . '/' . ltrim($path, '/');
        $curlData = $this->getApiCurlData($docId, $apiUrl, $this->params['method'], $params);
        $g = (new Generator())->run($curlData);
        if (!empty($g['struct'])) {
            (new ApiDocModule())->saveCache($docId, $path, $g['struct']);
        }

        $this->data['data'] = $g;
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
     * @throws CoreException
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
     * @throws CoreException
     */
    function generator()
    {
        $data = array();
        $show_input = true;
        if ($this->isPost()) {
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
     * @throws CoreException
     */
    function changeApiServer()
    {
        $doc_id = (int)$this->params['doc_id'];
        if (!$doc_id) {
            $this->to('doc:setting');
            return;
        }

        $sid = (int)$this->params['sid'];
        $docInfo = $this->ADM->get($doc_id);
        $servers = &$docInfo['servers'];
        if (!isset($servers[$sid])) {
            $this->to('doc:setting');
            return;
        }

        $valueData = array('sid' => $sid);
        $data = $this->ADM->getUserData($this->u, $doc_id, ApiDocModule::KEY_HOST);
        if ($data == false) {
            $this->ADM->addUserData($this->u, $doc_id, ApiDocModule::KEY_HOST, $valueData);
        } else {
            $this->ADM->updateUserData($data['id'], [
                'value' => json_encode($valueData)
            ]);
        }

        $this->data['current_sid'] = $sid;
        $this->dieJson($this->data);
    }

    /**
     * 保存公共参数
     *
     * @cp_params doc_id
     * @throws CoreException
     */
    function saveCommonParams()
    {
        $doc_id = (int)$this->params['doc_id'];
        if (!$doc_id) {
            $this->to('doc:setting');
            return;
        }

        if ($this->isPost()) {
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

        $this->redirect($url);
    }

    /**
     * @throws CoreException
     */
    function setting()
    {
        $this->data['list'] = $this->ADM->getAll();
        $this->display($this->data);
    }

    /**
     * @cp_params action=add, id
     * @throws CoreException
     */
    function action()
    {
        if ($this->isPost()) {
            $siteName = &$_POST['name'];
            $docToken = &$_POST['doc_token'];
            if (!$siteName) {
                $this->dieJson($this->getStatus(100703));
                return;
            }

            if (!$docToken) {
                $this->dieJson($this->getStatus(100701));
                return;
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

            $saveData = [
                'name' => $siteName,
                'servers' => json_encode($servers),
                'global_params' => json_encode($global_params),
                'header_params' => json_encode($header_params),
                'doc_token' => $docToken,
                'last_update_admin' => $this->u,
            ];

            $id = $this->params['id'];
            if (!empty($id)) {
                $this->ADM->update($id, $saveData);
            } else {
                $this->ADM->add($saveData);
            }
            $this->to('doc:setting');
            return;
        } else {
            switch ($this->params['action']) {
                case 'edit':
                    $this->data['data'] = $this->ADM->get($this->params['id']);
                    break;

                case 'del':
                    $this->ADM->del($this->params['id']);
                    $this->to('doc:setting');
                    return;
                    break;

                default:
                    $this->data['data'] = array();
            }
        }

        $this->display($this->data);
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
     * @param int $docId
     * @param string $apiAddr
     * @param string $docToken
     * @return array|string
     * @throws CoreException
     */
    protected function getInitApiData($docId, $apiAddr, $docToken)
    {
        $requestParams = http_build_query([
            'doc_token' => md5(md5($docToken . TIME) . TIME),
            't' => TIME,
        ]);

        $url = $apiAddr . '?' . $requestParams;
        $response = Helper::curlRequest($url);
        if (($responseData = json_decode($response, true)) === false) {
            return $this->getStatus(100705, $url);
        }

        $responseData['api_url'] = $url;
        if (empty($responseData['status']) || $responseData['status'] != 1) {
            return $this->getStatus(100705, $responseData);
        }

        if (empty($responseData['data'])) {
            return $this->getStatus(100706, $responseData);
        }

        $data = &$responseData['data'];
        $cache_file_name = md5($apiAddr);

        //获取缓存数据
        $api_cache = [];
        if ($docId != 0) {
            $api_cache = (new ApiDocModule())->getCacheData($docId);
        }

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

                        if (isset($api_cache[$method['requestPath']])) {
                            $method['apiCache'] = $api_cache[$method['requestPath']];
                        } else {
                            $method['apiCache'] = '';
                        }

                        $apiParams = [];
                        if (!empty($m['request'])) {
                            if (isset($m['request'])) {
                                if (!empty($m['request'])) {
                                    $request = explode(',', $m['request']);
                                    foreach ($request as $r) {
                                        $rParams = explode("\n", $r);
                                        foreach ($rParams as $f) {
                                            @list($dd['field'], $dd['label'], $dd['required']) = explode('|', $f);
                                            $dd = array_map('trim', $dd);
                                            $apiParams[$dd['field']] = [
                                                'label' => $dd['label'],
                                                'required' => (bool)$dd['required'],
                                            ];
                                        }
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

        if (!$ret) {
            return $this->getStatus(100720, $url);
        }

        $data = [
            'url' => $url,
            'cache_name' => $cache_file_name,
            'cache_at' => TIME,
            'cache_file' => $cache_file,
            'user' => $this->u
        ];

        return $this->result(1, $data);
    }
}