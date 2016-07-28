<div class="box">
    <div class="box-body table-responsive">
        <form class="pure-form" action="" method="post">
            <table class="table table-bordered border-hover">
                <thead>
                <tr>
                    <th>角色名称</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['role_list'] as $r) : ?>
                    <tr>
                        <td><?php echo $r['name'] ?></td>
                        <td>
                            <a href="<?php echo $this->url('acl:editRole', array('rid' => $r['id'])) ?>">编辑</a>
                            <a href="javascript:void(0)" class="confirm-href-flag" title="确定删除该角色吗?"
                               action = "<?php echo $this->link('acl:delRole', array('rid' => $r['id'])) ?>">删除</a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
