<div class="box">
    <div class="box-body table-responsive">
        <form class="pure-form" action="" method="post">
            <table class="table table-bordered border-hover">
                <thead>
                <tr>
                    <th style="width:20%;min-width:100px;">角色名称</th>
                    <th style="width:80%;min-width:160px;">操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['role_list'] as $r) : ?>
                    <tr>
                        <td><?php echo $r['name'] ?></td>
                        <td>
                            <a href="<?php echo $this->url('acl:editRole', array('rid' => $r['id'])) ?>">编辑</a>
                            <?php echo $this->confirmUrl('acl:delRole', array('rid' => $r['id']), '删除', '确定删除该角色吗?') ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
