<?php
$id = microtime(true);
$name = &$data['t'];
?>
<tr>
    <td class="col-xs-1">
        <input type="text" name="<?= $name ?>[<?= $id ?>][key]"
               value="<?= $this->e($data, 'key') ?>"
               class="form-control"
               placeholder="参数">
    </td>
    <td class="col-xs-3">
        <input type="text" name="<?= $name ?>[<?= $id ?>][name]"
               value="<?= $this->e($data, 'name') ?>"
               class="form-control"
               placeholder="参数名">
    </td>
    <td class="col-xs-1">
        <a class="btn btn-warning del-node-flag">删除</a>
    </td>
</tr>