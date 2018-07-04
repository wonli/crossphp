<?php
/**
 * @author wonli <wonli@live.com>
 * case.tpl.php
 */
$class = &$data['info']['class'];
$action_annotate = &$data['action_annotate'];
?>
<div class="panel-api-case">
    <div class="action-list" id="<?php echo $class ?>ActionList">
        <?php
        if (!empty($action_annotate)) {
            foreach($action_annotate as $action => $d) {
                $d['api_host'] = &$data['api_host'];
                $this->renderTpl('segment/api/test_form', $d);
            }
        }
        ?>
    </div>
</div>
