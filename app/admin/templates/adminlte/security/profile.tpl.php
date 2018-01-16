<?php
$admin = array();
if (!empty($data['admin'])) {
    $admin = &$data['admin'];
}
?>
<form class="form-horizontal" action="" method="post">
    <div class="box">
        <div class="box-body">
            <div class="form-group">
                <label for="nickname" class="col-sm-2 col-md-2 col-lg-1 control-label">昵称</label>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <input type="text" class="form-control" name="nickname" id="nickname"
                           placeholder="昵称" value="<?php echo $this->e($admin, 'nickname') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="realName" class="col-sm-2 col-md-2 col-lg-1 control-label">真实姓名</label>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <input type="text" class="form-control" name="real_name" id="realName"
                           placeholder="真实姓名" value="<?php echo $this->e($admin, 'real_name') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="cellphone" class="col-sm-2 col-md-2 col-lg-1 control-label">联系电话</label>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <input type="text" class="form-control" name="cellphone" id="cellphone"
                           placeholder="联系电话" value="<?php echo $this->e($admin, 'cellphone') ?>">
                </div>
            </div>

            <?php if (!empty($data['hasTheme'])) : ?>
                <div class="form-group">
                    <label for="cellphone" class="col-sm-2 col-md-2 col-lg-1 control-label">主题风格</label>
                    <div class="col-sm-10 col-md-8 col-lg-6">
                        <?php $this->renderTpl('security/theme', $data['themeList']) ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
        <div class="box-footer">
            <div class="form-group">
                <div class="col-sm-offset-2 col-md-offset-2 col-lg-offset-1 col-sm-10">
                    <button type="submit" class="btn btn-primary">提交</button>
                </div>
            </div>
        </div>
    </div>
</form>
