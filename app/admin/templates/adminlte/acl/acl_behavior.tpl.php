<table class="table table-bordered">
    <thead>
    <tr>
        <th>类名</th>
        <th>方法列表</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data['menu_list'] as $l) : ?>
        <tr>
            <td align="left" style="width:130px;padding:10px;">
                <label>
                    <?php if (isset($data['menu_select']) && in_array($l['id'], $data['menu_select'])) : ?>
                        <input type="checkbox" checked onclick="selectAll(this)"
                               class="<?php echo "token_{$l['link']}_class" ?>" value="<?php echo $l['id'] ?>"
                               name="menu_id[]" id=""/>
                    <?php else : ?>
                        <input type="checkbox" onclick="selectAll(this)"
                               class="<?php echo "token_{$l['link']}_class" ?>"
                               value="<?php echo $l['id'] ?>" name="menu_id[]" id=""/>
                    <?php endif ?>

                    <?php echo $l ['name'] ?>
                </label>
            </td>
            <td>
                <?php
                if (isset($l['method'])) {
                    foreach ($l['method'] as $link => $m) {
                        if (!empty($m)) {
                            ?>
                            <span style="float:left;padding:5px;">
                                <label>
                                    <?php if (isset($data['menu_select']) && in_array($m['id'], $data['menu_select'])) : ?>
                                        <input checked class="<?php echo "token_{$l['link']}_class_children" ?>"
                                               type="checkbox" value="<?php echo $m['id'] ?>" name="menu_id[]"/>
                                    <?php else : ?>
                                        <input class="<?php echo "token_{$l['link']}_class_children" ?>"
                                               type="checkbox" value="<?php echo $m['id'] ?>" name="menu_id[]"/>
                                    <?php endif ?>

                                    <?php echo empty($m['name']) ? "*{$link}" : $m['name'] ?>
                                </label>
                            </span>
                            <?php
                        }
                    }
                }
                ?>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<script type="text/javascript">
    function selectAll(o) {
        var token_name = $(o).attr('class');
        $('.' + token_name + '_children').each(function () {
            $(this)[0].checked = !!($(o)[0].checked);
        })
    }
</script>
