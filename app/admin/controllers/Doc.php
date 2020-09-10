<?php
/**
 * @author wonli <wonli@live.com>
 * Doc.php
 */


namespace app\admin\controllers;

use app\admin\supervise\Model\ApiDoc;
use app\admin\supervise\Model\ApiDocData;
use app\admin\supervise\Model\ApiDocUser;

use Cross\Exception\DBConnectException;
use Cross\Exception\FrontException;
use Cross\Interactive\ResponseData;
use Cross\Exception\CoreException;
use Cross\Core\Helper;

use app\admin\supervise\ApiDocModule;
use app\admin\supervise\CodeSegment\CURL;
use app\admin\supervise\CodeSegment\Generator;
use app\admin\views\DocView;
use ReflectionException;


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
     * @throws CoreException|DBConnectException
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
        $this->data['doc_id'] = $doc_id;
        $this->data['user_data'] = $userData;
        $this->data['current_sid'] = $currentServerID;

        if (empty($servers)) {
            $this->display($this->data, 'index');
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
            if ($updateStatus->getStatus() != 1) {
                $data = array_merge($this->data, $updateStatus->getData());
                $this->display($data, 'index');
                return;
            }

            $docCategory = [];
            $docData = (new ApiDocData())->getAll(['doc_id' => $doc_id], 'id, group_key, group_name, api_path, api_name, api_method, enable_mock');
            if (!empty($docData)) {
                foreach ($docData as $d) {
                    $docCategory[$d['group_key']]['group_key'] = $d['group_key'];
                    $docCategory[$d['group_key']]['group_name'] = $d['group_name'];
                    $docCategory[$d['group_key']]['children'][] = $d;
                }
            }

            $this->data['data'] = $docData;
            $this->data['category'] = $docCategory;
            $this->display($this->data, 'index');
        }
    }

    /**
     * 初始化api接口数据
     *
     * @throws CoreException|DBConnectException
     */
    function initApiData()
    {
        $status = 1;
        $apiAddr = $this->delegate->getRequest()->getRequestData()['api_addr'];
        if (empty($apiAddr)) {
            $status = 100711;
        }

        $docToken = $this->delegate->getRequest()->getRequestData()['doc_token'];
        if (empty($docToken)) {
            $status = 100701;
        }

        if ($status !== 1) {
            $this->data = $this->getStatus($status);
            $this->display($this->data, 'JSON');
            return;
        }

        $docId = $this->params['id'] ?: 0;
        $updateStatus = $this->getInitApiData($docId, $apiAddr, $docToken);
        if ($updateStatus->getStatus() != 1) {
            $this->display($updateStatus->getData(), 'JSON');
            return;
        }

        $this->data['data'] = $updateStatus->getDataContent();
        $this->display($this->data, 'JSON');
    }

    /**
     * 代码片段
     *
     * @cp_display codeSegment
     * @throws CoreException
     * @throws DBConnectException
     * @throws FrontException
     */
    function codeSegment()
    {
        $postData = $this->delegate->getRequest()->getPostData();
        $apiId = &$postData['apiId'];
        $params = &$postData['params'];

        $data = $this->getApiCurlData($apiId, $params);
        if (!empty($data) && is_array($data)) {
            $g = (new Generator())->run($data);
            if (!empty($g)) {
                $Add = new ApiDocData();
                $Add->id = $apiId;
                $Add->api_response_struct = json_encode($g['struct']);
                $Add->update();
                $this->data['data'] = $g;
            }
        } else {
            $this->data['data'] = [];
        }

        $this->display($this->data);
    }

    /**
     * @throws CoreException
     * @throws DBConnectException
     * @throws FrontException
     */
    function curlRequest()
    {
        $apiId = $this->params['doc_-_api-_-id'] ?? 0;
        if (empty($apiId)) {
            throw new FrontException('获取文档信息失败');
        }

        unset($this->params['doc_-_api-_-id']);
        $params = $this->params;
        $curlData = $this->getApiCurlData($apiId, $params, $serverInfo);
        $g = (new Generator())->run($curlData);
        if (!empty($g['struct'])) {
            if (!empty($g)) {
                $Add = new ApiDocData();
                $Add->id = $apiId;
                $Add->api_response_struct = json_encode($g['struct']);
                $Add->update();
                $this->data['data'] = $g;
            }
        }

        $this->data['data'] = $g;
        $this->data['curl_params'] = [
            'url' => $serverInfo['apiUrl'],
            'method' => $serverInfo['method'],
            'params' => $params
        ];

        $this->display($this->data);
    }

    /**
     * 发起curl请求
     *
     * @param string $apiId
     * @param array $params
     * @param array $serverInfo
     * @return array|mixed
     * @throws CoreException
     * @throws DBConnectException
     * @throws FrontException
     */
    function getApiCurlData($apiId, &$params = [], &$serverInfo = [])
    {
        $headerParams = [];
        $Api = new ApiDocData();
        $Api->id = $apiId;
        $apiData = $Api->get();
        if (empty($apiData)) {
            throw new FrontException('获取api数据失败');
        }

        $docId = $apiData['doc_id'];
        $doc = $this->ADM->get($docId);
        $Api = new ApiDocModule();
        $userData = $Api->getAllUserData($this->u, $docId);
        if (!empty($doc['header_params'])) {
            if (!empty($userData['header_params'])) {
                foreach ($userData['header_params'] as $k => $v) {
                    $headerParams[] = sprintf("%s: %s", $k, $v);
                }
            }

            if (!empty($userData['global_params'])) {
                $params = array_merge($params, $userData['global_params']);
            }
        }

        $sid = $userData['host']['sid'] ?? 0;
        $method = $apiData['api_method'] ?? 'POST';
        $server = $doc['servers'][$sid] ?? [];
        if (empty($server)) {
            throw new FrontException('获取Server信息失败');
        }

        $apiUrl = rtrim($server['api_addr'], '/') . '/' . $apiData['api_path'];
        $curlData = (new CURL())->setUrl($apiUrl)
            ->setParams($params)
            ->setHeaderParams($headerParams)
            ->setMethod($method)
            ->request();

        $data = json_decode($curlData, true);
        if (!is_array($data)) {
            return $curlData;
        }

        $serverInfo = [
            'sid' => $sid,
            'method' => $method,
            'apiUrl' => $apiUrl,
            'docId' => $docId
        ];

        return $data;
    }

    /**
     * 代码生成
     *
     * @throws CoreException
     */
    function generator()
    {
        $data = [];
        $show_input = true;
        if ($this->isPost()) {
            $show_input = false;
            $postData = $this->delegate->getRequest()->getPostData();
            $json = &$postData['json'];
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

        $postData = $this->delegate->getRequest()->getPostData();
        if ($this->isPost()) {
            foreach ($postData as $k => $v) {
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

        $hash = &$postData['hash'];
        $url = $this->view->url("doc:{$doc_id}");
        if ($hash) {
            $url .= '#!' . $hash;
        }

        $this->redirect($url);
    }

    /**
     * 生成测试表单
     *
     * @cp_params id
     * @throws CoreException|DBConnectException
     * @throws FrontException
     */
    function makeTestForm()
    {
        $id = $this->params['id'];
        $apiDocData = new ApiDocData();
        $apiDocData->id = $id;
        $apiData = $apiDocData->property();
        if (empty($apiData->doc_id)) {
            throw new FrontException('获取API数据失败');
        } else {
            $apiData->api_params = json_decode($apiData->api_params, true);
            $this->data['api'] = $apiData->getArrayData();
        }

        $UserDoc = new ApiDocUser();
        $UserDoc->u = $this->u;
        $UserDoc->doc_id = $apiDocData->doc_id;
        $userData = $UserDoc->getAll();
        $ud = [];
        foreach ($userData as &$d) {
            $ud[$d['name']] = json_decode($d['value'], true);
            $d['value'] = json_decode($d['value'], true);
        }
        $this->data['user'] = $ud;

        $Doc = new ApiDoc();
        $Doc->id = $apiDocData->doc_id;
        $docInfo = $Doc->get();
        $docInfo['servers'] = json_decode($docInfo['servers'], true);
        $docInfo['global_params'] = json_decode($docInfo['global_params'], true);
        $docInfo['header_params'] = json_decode($docInfo['header_params'], true);

        $sid = $ud['host']['sid'] ?? 0;
        $docInfo['current_server'] = $docInfo['servers'][$sid];
        $this->data['doc'] = $docInfo;

        $this->display($this->data);
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
     * @throws CoreException|DBConnectException
     */
    function action()
    {
        if ($this->isPost()) {
            $postData = $this->delegate->getRequest()->getPostData();
            $siteName = &$postData['name'];
            $docToken = &$postData['doc_token'];
            if (!$siteName) {
                $this->dieJson($this->getStatus(100703));
                return;
            }

            if (!$docToken) {
                $this->dieJson($this->getStatus(100701));
                return;
            }

            $servers = [];
            $devs = &$postData['dev'];
            if (!empty($devs)) {
                foreach ($devs as $d) {
                    if (!empty($d['api_addr'])) {
                        $d['api_addr'] = rtrim($d['api_addr'], '/');
                        $servers[] = $d;
                    }
                }
            }

            $global_params = [];
            $global = $postData['global'];
            if (!empty($global)) {
                foreach ($global as $g) {
                    $key = trim($g['key']);
                    $name = trim($g['name']);
                    if (!empty($key)) {
                        $global_params[$key] = !empty($name) ? $name : $key;
                    }
                }
            }

            $header_params = [];
            $header = $postData['header'];
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
                'servers' => json_encode($servers, JSON_UNESCAPED_UNICODE),
                'global_params' => json_encode($global_params, JSON_UNESCAPED_UNICODE),
                'header_params' => json_encode($header_params, JSON_UNESCAPED_UNICODE),
                'doc_token' => $docToken,
                'last_update_admin' => $this->u,
            ];

            $id = $this->params['id'];
            if (!empty($id)) {
                $this->ADM->update($id, $saveData);
            } else {
                $id = $this->ADM->add($saveData);
            }

            (new ApiDocData())->update(['doc_id' => 0], ['doc_id' => $id]);
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
                    $this->data['data'] = [];
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
     * @return ResponseData
     * @throws CoreException
     * @throws DBConnectException
     */
    protected function getInitApiData($docId, $apiAddr, $docToken): ResponseData
    {
        $requestParams = http_build_query([
            'doc_token' => md5(md5($docToken . TIME) . TIME),
            't' => TIME,
        ]);

        $url = $apiAddr . '?' . $requestParams;
        $response = Helper::curlRequest($url);
        if (($responseData = json_decode($response, true)) === false) {
            return $this->responseData(100705, ['url' => $url]);
        }

        $rdb = ResponseData::builder();
        if (!empty($responseData)) {
            $rdb->updateInfoProperty($responseData);
            $rdb->setData($responseData);
        }

        $responseData = $rdb->getDataContent();
        $responseData['api_url'] = $url;
        $status = $rdb->getStatus();
        if ($status != 1) {
            return $this->responseData(100705, $responseData);
        }

        if (empty($responseData['data'])) {
            return $this->responseData(100706, $responseData);
        }

        $ADD = new ApiDocData();
        $historyData = $ADD->getAll(['doc_id' => $docId], 'api_path');
        if (!empty($historyData)) {
            $historyData = array_column($historyData, 'api_path');
        }

        foreach ($responseData['data'] as $groupKey => $d) {
            $adc = new ApiDocData();
            $adc->doc_id = $docId;
            $adc->group_key = $groupKey;
            $adc->group_name = $d['api_spec'] ?? $groupKey;
            $adc->global_params = (int)$d['global_params'];
            $adc->update_at = time();
            if (!empty($d['methods'])) {
                foreach ($d['methods'] as $apiName => $apiData) {
                    $adc->api_name = $apiName;
                    if (!empty($apiData['api'])) {
                        $api = explode(',', $apiData['api']);
                        @list($method, $path, $name) = array_map('trim', $api);
                        $adc->api_method = $method ?: '';
                        $adc->api_path = $path ? '/' . ltrim($path, '/') : '';
                        $adc->api_name = $name ?: $apiName;
                    } else {
                        $adc->api_path = null;
                        $adc->api_method = null;
                        continue;
                    }

                    $apiRequestData = $apiData['request'] ?? '';
                    if (!empty($apiRequestData)) {
                        if (!is_array($apiRequestData)) {
                            $apiRequestData = [$apiRequestData];
                        }

                        $apiRequest = [];
                        foreach ($apiRequestData as $req) {
                            $request = explode(',', trim($req, ','));
                            foreach ($request as $n) {
                                @list($a['field'], $a['label'], $a['required']) = array_map('trim', explode('|', $n));
                                $apiRequest[] = $a;
                            }
                        }

                        $adc->api_params = json_encode($apiRequest, JSON_UNESCAPED_UNICODE);
                    } else {
                        $adc->api_params = '';
                    }

                    if (!empty($apiData['global_params'])) {
                        $adc->global_params = $apiData['global_params'];
                    } else {
                        $adc->global_params = null;
                    }

                    $index = array_search($adc->api_path, $historyData) ?? null;
                    if (null !== $index || false !== $index) {
                        unset($historyData[$index]);
                    }

                    $adc->update_user = $this->u;
                    $adc->useIndex('doc_id', $adc->doc_id);
                    $adc->useIndex('api_path', $adc->api_path);
                    $adc->updateOrAdd();
                }
            }
        }

        if (!empty($historyData)) {
            $ADD->doc_id = $docId;
            $ADD->api_path = ['IN', $historyData];
            $ADD->del();
        }

        $data = [
            'url' => $url,
            'cache_at' => TIME,
            'user' => $this->u
        ];

        return $this->responseData(1, $data);
    }
}