<?php
/**
 * @author wonli <wonli@live.com>
 * case_form.tpl.php
 */
$list_container_id = $data['class'] . '_' . $data['action'];

?>
<div class="row" style="margin:10px 0">
    <a href="javascript:void(0)" onclick="apiActionList('<?php echo $list_container_id; ?>')">
        <?php echo $data['name'] ?>
    </a>
</div>
