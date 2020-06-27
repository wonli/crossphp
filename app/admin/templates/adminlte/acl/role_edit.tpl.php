<form action="" method="post" class="form-horizontal">
    <div class="box box-solid">
        <div class="box-body table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <td>
                        <div class="input-group col-sm-3">
                            <div class="input-group-addon">
                                <i class="fa fa-user"></i>
                                角色名称
                            </div>
                            <input type="text" class="form-control" name="name" id=""
                                   value="<?= $data['role_info']['name'] ?>"/>
                        </div>
                        <input type="hidden" name="rid" value="<?= $data['role_info']['id'] ?>"/>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php
                        $this->renderTpl('acl/behavior', array(
                            'menu_list' => $data['menu_list'],
                            'menu_select' => $data ['menu_select'],
                        ))
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <input class="btn btn-primary" type="submit" value="保存"/>
        </div>
    </div>
</form>
