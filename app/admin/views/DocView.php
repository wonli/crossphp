<?php
/**
 * @author wonli <wonli@live.com>
 * DocView.php
 */


namespace app\admin\views;


use lib\UI\Component\Table;

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

        $this->renderTpl('doc/index', $data['data']);
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
        $table->setActionMenu('操作', '100px');
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
            'load_layer'=>false,
        ));

        $this->renderTpl('doc/code_segment', $data);
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
     * 文档数据
     */
    function docData()
    {
        $data = $this->data['data'];
        if (!empty($data)) {
            ?>
            <div class="col-md-4">
                <div class="leftContainer navbar-collapse collapse">
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
            <div class="col-md-8">
                <div class="rightContainer">
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
        } else {
            ?>
            <div class="col-md-12">
                <div class="text-center">
                    <div class="well-lg none">
                        暂无数据
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * 文档头部
     */
    function docHeader()
    {
        $data = $this->data['doc'];
        ?>
        <div class="container">
            <div class="navbar-header">
                <button id="collapseBtn" type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target="leftContainer">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="" title="生成时间 <?php echo $data['last_update_time'] ?>">
                    <?php echo $data['name'] ?>
                </a>
            </div>

            <div class="navbar-collapse collapse">
                <?php $this->genCommonParams() ?>
                <?php $this->genApiServerList() ?>
            </div>
        </div>
        <?php
    }

    /**
     * 公共参数开关
     */
    private function genCommonParams()
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
    private function genApiServerList()
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
                if(isset($userData[$field])) {
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
                if(isset($userData[$field])) {
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
}