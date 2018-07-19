<?php
/**
 * @author wonli <wonli@live.com>
 * case.tpl.php
 */
$info = &$data['info'];
$class = &$info['class'];
$api_title = !empty($info['desc'])?$info['desc']:$info['class'];

$action_annotate = &$data['action_annotate'];
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
                $this->renderTpl('doc/api/child_menu', $d);
            }
        }
        ?>
    </div>
</div>
