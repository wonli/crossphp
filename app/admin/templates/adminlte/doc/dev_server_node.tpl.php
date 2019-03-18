<?php
$id = microtime(true);
$isChecked = isset($data['is_default']) ? true : false;
?>
<tr>
    <td>
        <label>
            <input class="newToggle" data-on="是" data-off="否" type="checkbox"
                   data-toggle="toggle" data-onstyle="success" data-offset="danger"
                <?= $isChecked ? 'checked' : '' ?> name="dev[<?= $id ?>][is_default]">
        </label>
    </td>
    <td>
        <input type="text" name="dev[<?= $id ?>][server_name]"
               value="<?= $this->e($data, 'server_name') ?>"
               class="server_name form-control"
               placeholder="名称">
    </td>
    <td>
        <input type="text" name="dev[<?= $id ?>][api_addr]"
               value="<?= $this->e($data, 'api_addr') ?>"
               class="api_addr form-control"
               placeholder="请填写服务器地址">
    </td>
    <td>
        <input type="hidden" name="dev[<?= $id ?>][cache_name]" class="cache_name"
               value="<?= $this->e($data, 'cache_name') ?>">
        <input type="hidden" name="dev[<?= $id ?>][cache_at]" class="cache_at"
               value="<?= $this->e($data, 'cache_at') ?>">
        <input type="hidden" name="dev[<?= $id ?>][user]" class="user" value="<?= $this->e($data, 'user') ?>">
        <?php
        $cache_name = $this->e($data, 'cache_name', '');
        if (!empty($cache_name)) {
            echo $this->a('更新数据', 'javascript:void(0)', array(
                'class' => 'btn btn-success get-data-flag'
            ));
        } else {
            echo $this->a('获取数据', 'javascript:void(0)', array(
                'class' => 'btn btn-primary get-data-flag'
            ));
        }
        ?>
        <a class="btn btn-warning del-node-flag">删除</a>
    </td>
</tr>