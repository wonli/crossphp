<?php
/**
 * @author wonli <wonli@live.com>
 * case.tpl.php
 */
$apiHost = &$data['api_host'];
$curlRequest = &$data['use_curl'];
$class = &$data['info']['class'];
$action_annotate = &$data['action_annotate'];
?>
<div class="panel-api-case">
    <div class="action-list" id="<?php echo $class ?>ActionList">
        <?php
        if (!empty($action_annotate)) {
            foreach ($action_annotate as $action => $d) {
                if ($curlRequest) {
                    $d['formMethod'] = 'post';
                    $d['formAction'] = 'request/?method=' . $d['method'] . '&api=' . urlencode($d['apiUrl']);
                } else {
                    $d['formMethod'] = $d['method'];
                    $d['formAction'] = rtrim($apiHost, '/') . '/' . ltrim($d['apiUrl'], '/');
                }

                $this->renderTpl('doc/api/test_form', $d);
            }
        }
        ?>
    </div>
</div>
