<?php
/**
 * 左侧导航菜单
 */
if (empty($data)) {
    return;
}

$add_icon = '';
if ($data['child_node_num'] > 0) {
    $add_icon = '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>';
}
?>

<li class="<?php echo $data['class'] ?>">
    <a href="<?php echo $data['link'] ?>" target="<?php echo $data['target'] ?>">
        <i class="<?php echo $data['icon_name'] ?>"></i>
        <span><?php echo $data['name'] ?></span>
        <?php echo $add_icon ?>
    </a>

    <?php
    if ($data['child_node_num'] > 0) {
        $this->renderTpl('nav/child_menu', $data['child_menu']);
    }
    ?>
</li>
