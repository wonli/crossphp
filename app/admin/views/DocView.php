<?php
/**
 * @author wonli <wonli@live.com>
 * DocView.php
 */


namespace app\admin\views;


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
     * 文档阅读页
     *
     * @param array $data
     */
    function index($data = array())
    {
        $this->set(array(
            'layer' => 'doc',
        ));
    }

    /**
     * 接口调试页面
     *
     * @param array $data
     */
    function setting($data = array())
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
        $this->renderTpl('doc/setting', $table);
    }

    /**
     * 代码片段
     *
     * @param array $data
     */
    function codeSegment($data = array())
    {
        $this->set(array(
            'load_layer' => false,
        ));

        $this->renderTpl('doc/code_segment', $data);
    }

    /**
     * @param array $data
     */
    function curlRequest($data = array())
    {
        $this->set(array(
            'layer' => 'doc_response',
        ));

        $this->renderTpl('doc/curl_response', $data);
    }

    /**
     * @param array $data
     */
    function generator($data = array())
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
    function action($data = array())
    {
        $this->renderTpl('doc/save', $data['data']);
    }

    /**
     * 服务器节点
     *
     * @param array $data
     */
    function makeDevServerNode($data = array())
    {
        $this->renderTpl('doc/dev_server_node', $data);
    }

    /**
     * 服务器节点
     *
     * @param array $data
     */
    function makeParamsNode($data = array())
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
        $a = self::htmlTag('a', [
            't' => $t,
            'href' => 'javascript:void(0)',
            'class' => 'addParams btn btn-default',
            '@content' => self::htmlTag('i', ['class' => 'fa fa-plus'])
        ]);

        return $a;
    }

    /**
     * 文档数据
     */
    function docData()
    {
        if(empty($this->data) || (isset($this->data['status']) && $this->data['status'] != 1)) {

            $message = $this->data['message'];
            if(is_array($message)) {
                $message = LogBase::prettyArray('', $message);
            }

            $title = $this->wrap('div', ['class' => 'h4'])->html('发生错误！' . ' - '. $this->data['status']);
            $msg = $this->wrap('pre', ['class' => ''])->html($message);

             echo $this->wrap('div', ['class' => 'col-md-8', 'style' => 'margin:0 auto'])
                ->wrap('div', ['class' => 'alert alert-danger alert-dismissible fade in', 'style' => 'margin-top: 100px'])
                ->html($title . $msg);
            return;
        }

        $data = $this->data['data'];
        ?>
        <div class="leftContainer navbar-collapse collapse">
            <div class="menuContainer">
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
        <a class="navbar-brand" href="" title="生成时间 <?php echo $data['last_update_time'] ?>">
            <?php echo $data['name'] ?>
        </a>
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
        $doc_id = $this->data['doc_id'];
        $servers = &$this->data['doc']['servers'];
        $current_sid = $this->data['current_sid'];
        $current_server_name = '';

        if (!empty($servers)) {
            $serverList = '';
            foreach ($servers as $sid => $d) {
                $server_name = &$d['server_name'];
                if ($current_sid == $sid) {
                    $current_server_name = $server_name;
                }

                $serverList .= $this->wrap('li')->a($server_name, 'javascript:void(0)', array(
                    'doc_id' => $doc_id,
                    'sid' => $sid,
                    'class' => 'change-server-flag'
                ));
            }
            ?>
            <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                   aria-expanded="false">
                    服务器(<?= $current_server_name ?>) <span class="caret"></span>
                </a>
                <ul class="dropdown-menu"><?= $serverList ?></ul>
            </li>
            <?php
        }
    }

    /**
     * 公共参数表单
     */
    function globalParams()
    {
        $userData = &$this->data['user_data']['global_params'];
        $global_params = &$this->data['doc']['global_params'];
        if (!empty($global_params)) {
            foreach ($global_params as $field => $name) {
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
                        <div class="form-group col-lg-12">
                            <input type="text" class="form-control" required="1" name="<?= $field ?>"
                                   value="<?= $userValue ?>"
                                   placeholder="<?= $name ?>">
                            <span class="hidden-xs">
                                <b style="form-control-static">*</b>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="form-control-static">
                            <span class="visible-xs"><b style="form-control-static">*</b></span>
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
     * @param array $data
     * @return string
     * @throws \Cross\Exception\CoreException
     */
    function getApiActionUrl($data)
    {
        if (empty($data)) {
            return '';
        }

        return $this->url('doc:curlRequest', [
            'ugp' => $data['useGlobalParams'],
            'method' => $data['method'],
            'doc_id' => $this->data['doc']['id'],
            'host' => urlencode($this->data['api_host']),
            'path' => urlencode($data['requestPath'])
        ]);
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
            <div class="cache-panel-title">响应数据结构</div>
            <div>
                <?php
                if (!empty($data)) {
                    echo $this->wrap('div', ['class' => 'cache-data-wrap'])
                        ->wrap('pre', ['class' => 'cache-data'])
                        ->wrap('code', ['class' => 'json hljs'])
                        ->html(json_encode(json_decode($data, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                } else {
                    echo $this->wrap('div', ['class' => 'cache-tips'])->html( '请求一次之后缓存');
                }
                ?>
            </div>
        </div>
        <?php
    }
}