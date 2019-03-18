<?php
/**
 * @author wonli <wonli@live.com>
 * nav.tpl.php
 */

$current_child = current($data['child']);
?>
<div class="panel panel-api-case">
    <div class="panel-heading">
        <h3>
            <a href="javascript:void(0)" onclick="apiClassList('<?php echo $current_child['class'] ?>')">
                <?php echo $data['name'] ?>
            </a>
        </h3>
    </div>
    <div class="panel-body menu-list" id="<?php echo $current_child['class'] . 'MenuList' ?>" style="display: none">
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
