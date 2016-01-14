<form id="form_nav" class="form-horizontal" action="" method="post">
    <div class="box">

        <div class="box-body table-responsive">

            <table class="table table-bordered table-hover border-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>菜单名称</th>
                    <th>类名称</th>
                    <th>是否显示</th>
                    <th>排序</th>
                    <th>操作</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data["menu"] as $m) : ?>
                    <tr>
                        <td>
                            <?php echo $m['id'] ?>
                            <input type="hidden" id="ele_id" name="id" value=""/>
                            <input type="hidden" name="nav[<?php echo $m['id'] ?>][id]" value="<?php echo $m['id'] ?>"/>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="nav[<?php echo $m['id'] ?>][name]" value="<?php echo $m['name'] ?>"/>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="nav[<?php echo $m['id'] ?>][link]" value="<?php echo $m['link'] ?>"/>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="nav[<?php echo $m['id'] ?>][status]" value="<?php echo $m['status'] ?>"/>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="nav[<?php echo $m['id'] ?>][order]" value="<?php echo $m['order'] ?>"/>
                        </td>
                        <td style="vertical-align:middle">
                            <a href="javascript:void(0)"
                               onclick="pop.confirm('确实要删除吗?', function(){ location.href='<?php echo $this->link("acl:del", array('id' => $m['id'])) ?>'; })">删除</a>
                            <a href="<?php echo $this->link("acl:editMenu", array('m' => $m['link'])) ?>">编辑子菜单</a>
                        </td>
                    </tr>
                <?php endforeach ?>

                <?php foreach ($data["un_save_menu"] as $k => $m) : ?>
                    <tr>
                        <td>+</td>
                        <td>
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][name]" value="<?php echo $m['name'] ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][link]" value="<?php echo $m['link'] ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][status]" id="">
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][order]" id="">
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php endforeach ?>

                <tr>
                    <td>+</td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][name]">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][link]" id="">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][status]" id="">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][order]" id="">
                    </td>
                    <td>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="box-footer">
            <input type="submit" class="btn btn-default" name="save" value="保存"/>
        </div>

    </div>
</form>
