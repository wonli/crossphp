<?php
$menu = &$data['menu'];
$methodList = &$data['methodList'];
$customMenuNamePrefix = sprintf('customMenu[%s][0]', $menu['id']);
?>
<form class="pure-form" action="" method="post">
    <div class="box">
        <div class="box-header">
            <div class="box-title">
                <?php printf("%s (%s)", $menu['name'], $menu['link']) ?>
            </div>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-hover">
                <tr>
                    <th style="min-width:80px;width:80px;">ID</th>
                    <th style="min-width:250px;width:250px;">菜单名称</th>
                    <th style="min-width:260px;">方法名称(超链接)</th>
                    <th style="min-width:76px;width:76px;">是否显示</th>
                    <th style="min-width:100px;width:100px;">排序</th>
                    <th style="min-width:100px;width:100px;">菜单类型</th>
                    <th style="min-width:60px;width:90px;">操作</th>
                </tr>
                <?php if (!empty($methodList)) {
                    foreach ($methodList as $m => $set) {
                        $namePrefix = sprintf("menu[%s][%s]", $menu['id'], $m);
                        ?>
                        <tr>
                            <td>
                                <p class="form-control-static">
                                    <?php echo $this->e($set, 'id', '+') ?>
                                </p>
                                <input type="hidden" name="<?php echo $namePrefix ?>[id]"
                                       value="<?php echo $this->e($set, 'id') ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="<?php echo $namePrefix ?>[name]"
                                       value="<?php echo $this->e($set, 'name') ?>"/>
                            </td>
                            <td>
                                <?php
                                if (!empty($set) && $set['type'] == 2) {
                                    echo $this->input('text', array(
                                        'class' => 'form-control', 'name' => "{$namePrefix}[link]", 'value' => $this->e($set, 'link')
                                    ));
                                } else {
                                    echo $this->input('hidden', array(
                                        'name' => "{$namePrefix}[link]", 'value' => $m
                                    ));
                                    echo $this->htmlTag('p', array('class' => 'form-control-static', '@content' => $m));
                                }
                                ?>
                            </td>
                            <td>
                                <p class="form-control-static text-center">
                                    <input type="checkbox" name="<?php echo $namePrefix ?>[display]"
                                           <?php if (isset($set["display"]) && $set["display"] == 1) : ?>checked<?php endif; ?>/>
                                </p>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="<?php echo $namePrefix ?>[order]"
                                       value="<?php echo $this->e($set, 'order') ?>"/>
                            </td>
                            <td>
                                <p class="form-control-static">
                                    <?php
                                    if (!empty($set) && $set['type'] == 2) {
                                        echo '自定义菜单';
                                    } else {
                                        echo '类菜单';
                                    }

                                    echo $this->input('hidden', array(
                                        'name' => "{$namePrefix}[type]", 'value' => $this->e($set, 'type', 1)
                                    ));
                                    ?>
                                </p>
                            </td>
                            <td>
                                <p class="form-control-static text-center">
                                    <?php
                                    $text = '-';
                                    $class = 'pop-alert-flag';
                                    $title = '删除或修改请编辑控制器类中对应的方法';
                                    $action = '';
                                    if (!empty($set) && $set['type'] == 2) {
                                        $text = '删除';
                                        $title = '确实要删除吗?';
                                        $class = 'confirm-href-flag';
                                        $action = $this->link("acl:del", array('id' => $set['id'], 'e' => $menu['id']));
                                    }

                                    echo $this->a($text, 'javascript:void(0)', array(
                                        'title' => $title,
                                        'class' => $class,
                                        'action' => $action
                                    ))
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    }
                } ?>

                <tr>
                    <td>+<input type="hidden" name="<?php echo $customMenuNamePrefix ?>[type]" value="2"></td>
                    <td>
                        <input type="text" class="form-control" name="<?php echo $customMenuNamePrefix ?>[name]"/>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="<?php echo $customMenuNamePrefix ?>[link]"/>
                    </td>
                    <td>
                        <p class="form-control-static text-center">
                            <input type="checkbox" name="<?php echo $customMenuNamePrefix ?>[display]"/>
                        </p>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="<?php echo $customMenuNamePrefix ?>[order]"/>
                    </td>
                    <td>
                        <p class="form-control-static">自定义菜单</p>
                    </td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="box-footer">
            <input class="btn btn-default" type="submit" value="提交"/>
        </div>
    </div>
</form>
