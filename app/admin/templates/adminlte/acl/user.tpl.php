<form action="" class="form" method="post">
    <div class="box">
        <div class="box-header">
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered border-hover acl-table">
                <thead>
                <tr>
                    <th>id</th>
                    <th class="user">用户名</th>
                    <th class="password" title="输入明文密码, 在保存时自动加密">
                        登录密码 <i class="fa fa-question-circle-o"></i>
                    </th>
                    <th class="status">状态</th>
                    <th class="status" title="是否允许解绑密保卡">
                        密保卡 <i class="fa fa-question-circle-o"></i>
                    </th>
                    <th class="role">角色</th>
                    <th class="act">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['u']??[] as $u) : ?>
                    <tr>
                        <td>
                            <p class="form-control-static"><?= $u['id'] ?></p>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="a[<?= $u['id'] ?>][name]" value="<?= $u['name'] ?>"/>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="a[<?= $u['id'] ?>][password]" value="<?= $u['password'] ?>"/>
                        </td>
                        <td>
                            <?php $this->statusCheckbox($u['id'], $u['t']) ?>
                        </td>
                        <td>
                            <?php $this->securityCheckbox($u['id'], $u['usc']) ?>
                        </td>
                        <td>
                            <?php $this->roleSelect($u['id'], $u['rid']) ?>
                        </td>
                        <td style="vertical-align: middle" class="td-op">
                            <?php
                            echo $this->confirmUrl('acl:delUser', array('uid' => $u['id']), '删除', '确定删除该用户吗?');

                            if ($u['bind_id']) {
                                $class = 'text-red';
                                $title = '点击解除绑定';
                                $txt = '密保卡已绑定';
                                $op = 'unbind';
                            } else {
                                $class = 'text-green';
                                $title = '点击绑定密保卡';
                                $txt = '密保卡未绑定';
                                $op = 'bind';
                            }

                            $url = $this->url('acl:userSecurityCard', array('user' => $u['name'], 'op' => $op));
                            echo $this->a($txt, $url, array('class' => $class, 'title' => $title));
                            ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <tr>
                    <td>
                        <p class="form-control-static">+</p>
                    </td>
                    <td><input type="text" name="a[+][name]" value="" class="form-control"/></td>
                    <td><input type="text" name="a[+][password]" placeholder="保存时自动加密" value="" class="form-control"/>
                    </td>
                    <td>
                        <?php $this->statusCheckbox('+', 1) ?>
                    </td>
                    <td>
                        <?php $this->securityCheckbox('+', 1) ?>
                    </td>
                    <td>
                        <?php $this->roleSelect('+') ?>
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <input class="btn btn-primary pull-left" type="submit" value="保存"/>
            <?= $this->page($this->data['page'], 'pagination pagination-sm no-margin pull-right') ?>
        </div>
    </div>
</form>