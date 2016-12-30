<?php
/**
 * @Auth: wonli <wonli@live.com>
 * test_form.tpl.php
 */
$api = &$data['api'];
$apiInfo = $formFieldsArray = array();
$list_container_id = $data['controller'] . '_' . $data['action'];

//表单头信息
if (!empty($api)) {
    @list($apiInfo['method'], $apiInfo['action'], $apiInfo['desc']) = explode(',', $api);
}
?>
<div class="row" style="margin:10px 0">
    <a href="javascript:void(0)" onclick="apiActionList('<?php echo $list_container_id; ?>')">
        <?php echo $apiInfo['desc'] ?>
    </a>
</div>
