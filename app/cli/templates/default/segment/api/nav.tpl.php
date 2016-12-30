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
<div class="panel panel-api-case">
    <div class="panel-heading">
        <h3>
            <a href="javascript:void(0)" onclick="apiClassList('<?php echo $class ?>')">
                <?php echo $api_title ?>
            </a>
        </h3>
    </div>
    <div class="panel-body menu-list" id="<?php echo $class.'MenuList' ?>" style="display: none">
        <?php
        if (!empty($action_annotate)) {
            foreach($action_annotate as $action => $d) {
                $this->renderTpl('segment/api/child_menu', $d);
            }
        }
        ?>
    </div>
</div>
