<?php
/**
 * @author wonli <wonli@live.com>
 * case_form.tpl.php
 */
$list_container_id = $data['class'] . '_' . $data['action'];

?>
<div class="row a-nav-menu">
    <i class="fa dot"></i>
    <a href="javascript:void(0)" class="<?= $list_container_id ?>"  onclick="apiActionList('<?= $list_container_id; ?>', this)">
        <?php echo $data['name'] ?>
    </a>
</div>
