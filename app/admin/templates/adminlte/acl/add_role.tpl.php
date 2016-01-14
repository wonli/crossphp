<div class="box">
    <div class="box-body table-responsive">
        <form action="" class="form" method="post">
            <table class="table">
                <tr>
                    <td align="left">
                        <div class="input-group col-sm-3">
                            <div class="input-group-addon ">
                                <i class="fa fa-user"></i>
                            </div>
                            <input type="text" class="form-control col-sm-2 active" name="name" placeholder="角色名">

                            <div class="input-group-btn">
                                <input class="btn btn-default" type="submit" value="添加"/>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td><?php $this->renderTpl('acl/acl_behavior', $data) ?></td>
                </tr>
            </table>
        </form>
    </div>
</div>
