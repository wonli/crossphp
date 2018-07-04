<?php
/**
 * @author wonli <wonli@live.com>
 * case.tpl.php
 */

$info = &$data['info'];
$class = $info['class'];
$api_title = !empty($info['desc'])?$info['desc']:$info['class'];

$action_annotate = &$data['action_annotate'];
?>
<div class="container" id="<?php echo $class ?>">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-api-case">
                <div class="panel-heading">
                    <h3>
                        <a href="javascript:void(0)" onclick="apiClassList('<?php echo $class ?>')">
                            <?php echo $api_title ?>
                        </a>
                    </h3>
                </div>

                <div class="panel-body action-list" id="<?php echo $class ?>ActionList" style="display: none">
                    <?php
                    if (!empty($action_annotate)) {
                        foreach($action_annotate as $action => $d) {
                            $this->renderTpl('segment/api/test_form', $d);
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
