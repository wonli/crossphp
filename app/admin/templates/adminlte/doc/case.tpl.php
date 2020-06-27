<?php
/**
 * @author wonli <wonli@live.com>
 * case.tpl.php
 */

$current_child = current($data['child']);
$class = $current_child['class'];

$child = &$data['child'];
?>
<div class="panel-api-case">
    <div class="action-list" id="<?= $class ?>ActionList">
        <?php
        if (!empty($child)) {
            foreach ($child as $name => $child_data) {
                $child_data['name'] = $name;
                $this->renderTpl('doc/case_form', $child_data);
            }
        }
        ?>
    </div>
</div>