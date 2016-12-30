<?php
/**
 * @Auth: wonli <wonli@live.com>
 * case.tpl.php
 */
$info = &$data['info'];
$class_annotate = &$data['class_annotate'];
$parent_annotate = &$data['parent_annotate'];
$action_annotate = &$data['action_annotate'];

if (!empty($parent_annotate)) {
    $class_annotate = array_merge($class_annotate, $parent_annotate);
}

$class = $info['class'];
$api_title = !empty($info['desc'])?$info['desc']:$info['class'];
?>
<div class="panel-api-case">
    <div class="action-list" id="<?php echo $class ?>ActionList">
        <?php
        if (!empty($action_annotate)) {
            foreach($action_annotate as $action => $d) {
                $this->renderTpl('segment/api/test_form', $d);
            }
        }
        ?>
    </div>
</div>
