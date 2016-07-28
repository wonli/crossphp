<form id="form_nav" class="form-horizontal" action="" method="post">
    <div class="box">

        <div class="box-body table-responsive">

            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th style="min-width:80px;width:80px;">ID</th>
                    <th style="min-width:250px;width:250px;">菜单名称</th>
                    <th style="min-width:260px;">类名称(超链接)</th>
                    <th style="min-width:76px;width:76px;">是否显示</th>
                    <th style="min-width:150px;width:150px;">菜单类型</th>
                    <th style="min-width:80px;width:80px;">排序</th>
                    <th style="min-width:180px;width:180px;">操作</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data["menu"] as $m) : ?>
                    <tr>
                        <td>
                            <p class="form-control-static">
                                <?php echo $m['id'] ?>
                            </p>
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
                        <td style="text-align:center">
                            <?php echo $this->checkbox($data['displayConfig'], $m['display'], array('name' => sprintf("nav[%s][display]", $m['id'])), array('style' => 'margin-top:5px;')) ?>
                        </td>
                        <td>
                            <input type="hidden" class="form-control" name="nav[<?php echo $m['id'] ?>][type]" value="<?php echo $m['type'] ?>">
                            <p class="form-control-static">
                                <?php
                                if ($m['type'] == 1) {
                                    echo '类菜单';
                                } else {
                                    echo '自定义菜单';
                                }
                                ?>
                            </p>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="nav[<?php echo $m['id'] ?>][order]" value="<?php echo $m['order'] ?>"/>
                        </td>
                        <td style="vertical-align:middle">
                            <a href="javascript:void(0)" class="confirm-href-flag" title="确定要删除吗?"
                               action="<?php echo $this->link("acl:del", array('id' => $m['id'])) ?>">删除</a>
                            <a href="<?php echo $this->link("acl:editMenu", array('id' => $m['id'])) ?>">编辑子菜单</a>
                        </td>
                    </tr>
                <?php endforeach ?>

                <?php foreach ($data["un_save_menu"] as $k => $m) : ?>
                    <tr>
                        <td><p class="form-control-static">+</p></td>
                        <td>
                            <input type="hidden" name="addNav[<?php echo $k + 1 ?>][type]" value="1">
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][name]" value="<?php echo $m['name'] ?>">
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="addNav[<?php echo $k + 1 ?>][link]" value="<?php echo $m['link'] ?>">
                        </td>
                        <td style="text-align:center">
                            <?php echo $this->checkbox($data['displayConfig'], 0, array('name' => sprintf("addNav[%s][display]", $k + 1)), array('style' => 'margin-top:5px;')) ?>
                        </td>
                        <td>
                            <p class="form-control-static">类菜单</p>
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
                    <td><p class="form-control-static">+</p></td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][name]">
                        <input type="hidden" class="form-control" name="addNav[0][type]" value="2">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="addNav[0][link]" id="">
                    </td>
                    <td style="text-align:center">
                        <?php echo $this->checkbox($data['displayConfig'], 0, array('name' => 'addNav[0][display]'), array('style' => 'margin-top:5px;')) ?>
                    </td>
                    <td>
                        <p class="form-control-static">自定义菜单</p>
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
