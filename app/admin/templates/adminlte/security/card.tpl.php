<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">密保卡管理</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-app" href="<?php echo $this->url('security:securityCard') ?>">
                    <i class="fa fa-asterisk"></i> 预览
                </a>
                <a class="btn btn-app" href="<?php echo $this->url('security:securityCard', array('act' => 'bind')) ?>">
                    <i class="fa fa-link"></i> 绑定
                </a>
                <a class="btn btn-app"
                   href="<?php echo $this->url('security:securityCard', array('act' => 'refresh')) ?>">
                    <i class="fa fa-refresh"></i> 重置
                </a>
                <a class="btn btn-app"
                   href="<?php echo $this->url('security:securityCard', array('act' => 'unbind')) ?>">
                    <i class="fa fa-unlink"></i> 解绑
                </a>
                <a class="btn btn-app"
                   href="<?php echo $this->url('security:securityCard', array('act' => 'download')) ?>">
                    <i class="fa fa-save"></i> 下载
                </a>
            </div>
        </div>
    </div>
</div>

<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">密保卡预览</h3>
    </div>
    <div class="box-body table-responsive">
        <?php
        if (!empty($data['card'])) {
            $this->renderTpl('security/security_card', $data['card']);
        } else {
            echo '您还没有绑定密保卡';
        }
        ?>
    </div>
</div>
<?php
/**
 * @Auth wonli <wonli@live.com>
 * card.tpl.php
 */
