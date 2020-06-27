<?php
/**
 * @author wonli <wonli@live.com>
 * nav.tpl.php
 */

$current_child = current($data['child']??[]);
?>
<div class="panel panel-nav-case">
    <div class="panel-heading">
        <a href="javascript:void(0)" class="nav-title" onclick="apiClassList('<?= $current_child['class'] ?>')">
            </i><?= $data['name'] ?>
        </a>
    </div>
    <div class="panel-body menu-list" id="<?= $current_child['class'] . 'MenuList' ?>" style="display: none">
        <?php
        if (!empty($data['child'])) {
            foreach ($data['child'] as $name => $action) {
                $this->renderTpl('doc/nav_menu', [
                    'name' => $name,
                    'class' => $action['class'],
                    'action' => $action['action']
                ]);
            }
        }
        ?>
    </div>
</div>
