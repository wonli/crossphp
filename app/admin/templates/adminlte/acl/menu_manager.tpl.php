<form class="pure-form" action="" method="post">
    <div class="box">
        <div class="box-body table-responsive">
            <table class="table">
                <?php
                if (!empty($data)) {
                    foreach ($data as $menu) {
                        ?>
                        <tr>
                            <td class="col-sm-2" style="vertical-align: middle">
                                <?php printf("%s (%s)", $menu['name'], $menu['link']); ?>
                            </td>

                            <td class="col-sm-10">
                                <table class="table table-bordered border-hover">
                                    <tr>
                                        <th>方法名称</th>
                                        <th>名称</th>
                                        <th>是否显示</th>
                                        <th>排序</th>
                                    </tr>

                                    <?php if (!empty($menu["method"])) {
                                        foreach ($menu["method"] as $m => $set) {
                                            ?>
                                            <tr>
                                                <td><?php echo $m ?></td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="menu[<?php echo $menu["id"] ?>][<?php echo $m ?>][name]"
                                                           value="<?php echo $this->e($set, 'name') ?>"
                                                           style="min-width: 100px;"
                                                           title="action name"/>
                                                </td>
                                                <td>
                                                    <input type="checkbox"
                                                           class="minimal"
                                                           name="menu[<?php echo $menu["id"] ?>][<?php echo $m ?>][display]"
                                                           <?php if (isset($set["display"]) && $set["display"] == 1) : ?>checked<?php endif; ?>
                                                           id="" style="width:50px;" title="is display"/>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           class="form-control"
                                                           name="menu[<?php echo $menu["id"] ?>][<?php echo $m ?>][order]"
                                                           <?php if (!empty($set["order"])) : ?>value="<?php echo $set["order"]; ?>"<?php endif; ?>
                                                           style="width:50px;" title="display order"/>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } ?>
                                </table>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>

        <div class="box-footer">
            <input class="btn btn-default" type="submit" value="提交"/>
        </div>
    </div>
</form>
