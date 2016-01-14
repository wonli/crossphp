<form action="" method="post" class="form-horizontal">
    <div class="box">
        <div class="box-body table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <td style="vertical-align: middle">名称</td>
                    <td>
                        <div class="col-xs-6">
                            <input type="text" class="form-control" name="name" id=""
                                   value="<?php echo $data['role_info']['name'] ?>"/>
                        </div>

                        <input type="hidden" name="rid" value="<?php echo $data['role_info']['id'] ?>"/>
                    </td>
                </tr>

                <tr>
                    <td style="vertical-align: middle">权限</td>
                    <td>
                        <div class="col-xs-12">
                            <?php
                            $this->renderTpl('acl/acl_behavior', array(
                                'menu_list' => $data['menu_list'],
                                'menu_select' => $data ['menu_select'],
                            ))
                            ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <input class="btn btn-default" type="submit" value="保存"/>
        </div>
    </div>
</form>
