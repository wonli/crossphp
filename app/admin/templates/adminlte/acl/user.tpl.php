<div class="box">
    <div class="box-body table-responsive">
        <form action="" class="form" method="post">
            <table class="table table-bordered border-hover">
                <thead>
                <tr>
                    <th>id</th>
                    <th>用户名</th>
                    <th>密码</th>
                    <th>状态</th>
                    <th>角色</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['u'] as $u) : ?>
                    <tr>
                        <td>
                            <p class="form-control-static"><?php echo $u['id'] ?></p>
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="a[<?php echo $u['id'] ?>][name]" value="<?php echo $u['name'] ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="a[<?php echo $u['id'] ?>][password]" value="<?php echo $u['password'] ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control"
                                   name="a[<?php echo $u['id'] ?>][t]" value="<?php echo $u['t'] ?>" />
                        </td>
                        <td>
                            <select name="a[<?php echo $u['id'] ?>][rid]"  class="form-control">
                                <?php foreach ($data['roles'] as $r) : ?>
                                    <?php if ($r['id'] == $u['rid']): ?>
                                        <option value="<?php echo $r['id'] ?>" selected>
                                            <?php echo $r['name'] ?>
                                        </option>
                                    <?php else : ?>
                                        <option value="<?php echo $r['id'] ?>"><?php echo $r['name'] ?></option>
                                    <?php endif ?>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td style="vertical-align: middle">
                            <a href="javascript:void(0)" class="confirm-href-flag" title="确定删除该用户吗"
                               action = "<?php echo $this->link('acl:delUser', array('uid' => $u['id'])) ?>">删除</a>
                        </td>
                    </tr>
                <?php endforeach ?>
                <tr>
                    <td>
                        <p class="form-control-static">+</p>
                    </td>
                    <td><input type="text" name="a[+][name]" value="" class="form-control"/></td>
                    <td><input type="text" name="a[+][password]" value="" class="form-control"/></td>
                    <td><input type="text" name="a[+][t]" value="" class="form-control"/></td>
                    <td>
                        <select name="a[+][rid]" class="form-control">
                            <?php foreach ($data['roles'] as $r) : ?>
                                <option value="<?php echo $r['id'] ?>"><?php echo $r['name'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <div style="padding-top:10px;text-align:left;">
                <input class="pure-button" type="submit" value="保存"/>
            </div>
        </form>
    </div>
</div>
