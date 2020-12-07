<?php
/**
 * @author wonli <wonli@live.com>
 * DocView.php
 */


namespace app\admin\views;


use Cross\Interactive\ResponseData;
use Cross\Exception\CoreException;

use lib\UI\Component\Table;
use lib\LogStation\LogBase;

/**
 * @author wonli <wonli@live.com>
 *
 * Class DocView
 * @package app\admin\views
 */
class DocView extends AdminView
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * 文档阅读页
     */
    function index()
    {
        $this->set([
            'layer' => 'doc',
        ]);
    }

    /**
     * 接口调试页面
     *
     * @param array $data
     */
    function setting($data = [])
    {
        $table = new Table();
        $table->addHead('name', '名称', '100px');
        $table->addHead('last_update_admin', '最后操作人', '200px');
        $table->addHead('last_update_time', '最后更新时间', '300px');
        $table->setActionMenu('操作', '139px');
        $table->addAction(function ($d) {
            $del = $this->a('删除', 'javascript:void(0)', array(
                'class' => 'confirm-href-flag',
                'action' => $this->url('doc:action', array(
                    'action' => 'del',
                    'id' => $d['id'],
                )),
            ));

            $edit = $this->a('编辑', $this->url('doc:action', array(
                'action' => 'edit',
                'id' => $d['id']
            )));

            $view = $this->a('查看', $this->url("doc:{$d['id']}"), array(
                'target' => '_blank'
            ));

            return $view . '&nbsp;&nbsp;' . $edit . '&nbsp;&nbsp;' . $del;
        });

        $table->setData($data['list']);
        $this->table = $table;
        $this->renderTpl('doc/setting');
    }

    /**
     * 文档分类
     */
    function docCategory()
    {
        foreach ($this->data['category'] ?? [] as $category => $d) {
            $this->renderTpl('doc/leftCategory', $d);
        }
    }

    /**
     * 生成api列表
     *
     * @throws CoreException
     */
    function docApiList()
    {
        $data = &$this->data;
        $statusName = ResponseData::builder()->getStatusName();
        $statusCode = $data[$statusName] ?? 0;
        if ($statusCode == 1) {
            foreach ($this->data['data'] ?? [] as $d) {
                ?>
                <div class="row">
                    <div class="col-md-12 case-title" style="margin:10px 0">
                        <span class="badge"><?= $d['api_method'] ?? 'post' ?></span>
                        <a href="javascript:void(0)" onclick="getTestCase('<?= $d['group_key'] ?>', '<?= $d['id'] ?>')">
                            <?= $d['api_name'] ?? '' ?>
                        </a>
                        <span class="hidden-xs">(<?= $d['api_path'] ?>)</span>
                    </div>
                </div>
                <?php
            }
        } else {
            $this->noticeBlock();
        }
    }

    /**
     * 生成测试表单
     * @param array $data
     */
    function makeTestForm($data = [])
    {
        $this->set(['load_layer' => false]);
        $this->renderTpl('doc/case_form', $data);
    }

    /**
     * 代码片段
     *
     * @param array $data
     */
    function codeSegment($data = [])
    {
        $this->set(array(
            'load_layer' => false,
        ));

        $this->renderTpl('doc/code_segment', $data);
    }

    /**
     * @param array $data
     */
    function curlRequest($data = [])
    {
        $this->set(array(
            'layer' => 'doc_response',
        ));

        $this->renderTpl('doc/curl_response', $data);
    }

    /**
     * @param array $data
     */
    function generator($data = [])
    {
        $this->set(array(
            'layer' => 'generator',
        ));

        if ($data['show_input']) {
            $this->renderTpl('doc/generator_form');
        } else {
            $data['t'] = 'generator';
            $this->renderTpl('doc/code_segment', $data);
        }
    }

    /**
     * 保存调试数据
     *
     * @param array $data
     */
    function action($data = [])
    {
        $this->renderTpl('doc/save', $data['data']);
    }

    /**
     * 服务器节点
     *
     * @param array $data
     */
    function makeDevServerNode($data = [])
    {
        $this->renderTpl('doc/dev_server_node', $data);
    }

    /**
     * 服务器节点
     *
     * @param array $data
     */
    function makeParamsNode($data = [])
    {
        $this->renderTpl('doc/params_node', $data);
    }

    /**
     * 生成增加按钮
     *
     * @param string $t
     * @return string
     */
    function makeAddButton($t = 'global')
    {
        return self::htmlTag('a', [
            't' => $t,
            'href' => 'javascript:void(0)',
            'class' => 'addParams btn btn-default',
            '@content' => self::htmlTag('i', ['class' => 'fa fa-plus'])
        ]);
    }

    /**
     * 文档数据
     */
    function docData()
    {
        if (empty($this->data) || (isset($this->data['status']) && $this->data['status'] != 1)) {

            $message = $this->data['message'];
            if (is_array($message)) {
                $message = LogBase::prettyArray('', $message);
            }

            $title = $this->wrap('div', ['class' => 'h4'])->html('发生错误！' . ' - ' . $this->data['status']);
            $msg = $this->wrap('pre', ['class' => ''])->html($message);

            echo $this->wrap('div', ['class' => 'col-md-8', 'style' => 'margin:0 auto'])
                ->wrap('div', ['class' => 'alert alert-danger alert-dismissible fade in', 'style' => 'margin-top: 100px'])
                ->html($title . $msg);
            return;
        }

        $data = $this->data['data'];
        ?>
        <div id="leftContainerWrap">
            <div class="leftContainer navbar-collapse collapse">
                <div class="menuContainer scroll">
                    <?php
                    foreach ($data as $name => $child) {
                        $this->renderTpl('doc/nav', [
                            'name' => $name,
                            'child' => $child,
                        ]);
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="rightContainer">
            <div class="contentContainer">
                <?php
                foreach ($data as $name => $child) {
                    $this->renderTpl('doc/case', [
                        'name' => $name,
                        'child' => $child,
                    ]);
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * 文档信息
     */
    function docInfo()
    {
        $data = $this->data['doc'];
        ?>
        <a class="navbar-brand" href="" title="生成时间 <?= $data['last_update_time'] ?>">
            <?= $data['name'] ?>
        </a>
        <button id="menuSwitch" type="button" class="menu-switch visible-lg visible-md visible-sm">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <?php
    }

    /**
     * 获取部署服务器信息
     *
     * @param array $data
     */
    function getApiServerInfo(array $data = [])
    {
        $this->set([
            'layer' => 'doc_response',
        ]);
        ?>
        <div class="server-status">
            <div class="media">
                <div class="media-body">
                    <h4 class="media-heading"><?= $data['server_name'] ?></h4>
                    <span class="s"><?= $data['api_addr'] ?? '' ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 公共参数开关
     */
    protected function genCommonParams()
    {
        $global = &$this->data['doc']['global_params'];
        $header = &$this->data['doc']['header_params'];

        if (!empty($global) || !empty($header)) {
            ?>
            <ul class="nav navbar-nav navbar-right">
                <li><a id="commonModalSwitch" href="javascript:void(0)">公共参数</a></li>
            </ul>
            <?php
        }
    }

    /**
     * 生成API服务器列表
     */
    protected function genApiServerList()
    {
        $docId = $this->data['doc_id'];
        $servers = &$this->data['doc']['servers'];
        $currentSid = $this->data['current_sid'];
        $currentServerName = '';

        if (!empty($servers)) {
            $serverList = '';
            foreach ($servers as $sid => $d) {
                $serverName = &$d['server_name'];
                if ($currentSid == $sid) {
                    $currentServerName = $serverName;
                }

                $serverList .= $this->wrap('li')->a($serverName, 'javascript:void(0)', array(
                    'doc_id' => $docId,
                    'sid' => $sid,
                    'class' => 'change-server-flag'
                ));
            }
            ?>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">服务器(<?= $currentServerName ?>) <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu"><?= $serverList ?></ul>
                </li>
            </ul>
            <?php
        }
    }

    /**
     * 公共参数表单
     */
    function globalParams()
    {
        $userData = $this->data['user']['global_params'] ?? [];
        $globalParams = $this->data['doc']['global_params'] ?? [];
        if (!empty($globalParams)) {
            foreach ($globalParams as $field => $name) {
                $userValue = '';
                if (isset($userData[$field])) {
                    $userValue = $userData[$field];
                }
                ?>
                <tr>
                    <td>
                        <div class="form-control-static">
                            <?= $field ?>
                        </div>
                    </td>
                    <td>
                        <div class="form-group" style="width: 100%">
                            <input type="text" class="form-control" required="1" name="<?= $field ?>"
                                   style="min-width: 75%"
                                   value="<?= $userValue ?>"
                                   placeholder="<?= $name ?>">
                            <span class="hidden-xs">
                                <b class="form-control-static">*</b>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="form-control-static">
                            <span class="visible-xs"><b class="form-control-static">*</b></span>
                            <span class="hidden-xs"><?= $name ?></span>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
    }

    /**
     * 公共参数弹窗表单
     *
     * @param string $key
     */
    function formParams($key = 'global_params')
    {
        $userData = &$this->data['user_data'][$key];
        $data = &$this->data['doc'][$key];
        if (!empty($data)) {
            foreach ($data as $field => $name) {
                $userValue = '';
                if (isset($userData[$field])) {
                    $userValue = $userData[$field];
                }
                ?>
                <div class="form-group">
                    <div class="col-sm-3">
                        <input type="hidden" name="<?= $key ?>[<?= $field ?>]" value="<?= $field ?>">
                        <label for="" class="form-control-static"><?= $field ?></label>
                    </div>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="<?= $key ?>[<?= $field ?>]"
                               value="<?= $userValue ?>" placeholder="值">
                    </div>
                    <div class="col-sm-3 text-left form-control-static">
                        <?= $name ?>
                    </div>
                </div>
                <?php
            }
        }
    }

    /**
     * 获取接口请求地址
     *
     * @return string
     * @throws CoreException
     */
    function getApiActionUrl()
    {
        $apiPath = $this->data['api']['api_path'] ?? '';
        $serverAddr = $this->data['doc']['current_server']['api_addr'] ?? '';
        $headerParams = $this->data['doc']['header_params'] ?? [];
        if (empty($headerParams)) {
            return rtrim($serverAddr, '/') . '/' . ltrim($apiPath, '/');
        }

        return $this->url('doc:curlRequest', [
            'ugp' => $this->data['api']['global_params'] ?? [],
            'method' => $this->data['api']['api_method'] ?? 'get',
            'doc_id' => $this->data['doc']['id'] ?? 0,
            'host' => urlencode($serverAddr),
            'path' => urlencode($apiPath)
        ]);
    }

    /**
     * 获取接口请求方法
     *
     * @return string
     */
    function getApiActionMethod()
    {
        $headerParams = $this->data['user']['global_params'] ?? [];
        if (empty($headerParams)) {
            return $this->data['api']['api_method'];
        }

        return $this->data['api']['api_method'] ?? 'POST';
    }

    /**
     * 输出缓存数据
     *
     * @param $data
     */
    function cacheData($data)
    {
        ?>
        <div class="cache-panel">
            <div class="cache-panel-title">Response structural</div>
            <div>
                <?php
                if (!empty($data)) {
                    echo $this->wrap('div', ['class' => 'cache-data-wrap'])
                        ->wrap('pre', ['class' => 'cache-data'])
                        ->wrap('code', ['class' => 'json hljs'])
                        ->html(json_encode(json_decode($data, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                } else {
                    echo $this->wrap('div', ['class' => 'cache-tips'])->html('第一次请求接口之后生成');
                }
                ?>
            </div>
        </div>
        <?php
    }
}