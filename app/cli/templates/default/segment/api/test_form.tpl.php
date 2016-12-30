<?php
/**
 * @Auth: wonli <wonli@live.com>
 * test_form.tpl.php
 */
$api = &$data['api'];
$api_host = &$this->data['api_host'];
$formFields = &$data['request'];
$apiInfo = $formFieldsArray = array();
$list_container_id = $data['controller'] . '_' . $data['action'];

//表单字段
if (!empty($formFields)) {
    $fs = explode(',', $formFields);
    foreach ($fs as $f) {
        $d = array();
        @list($d['name'], $d['txt'], $d['is_require']) = explode('|', $f);
        $formFieldsArray[] = array_map('trim', $d);
    }
}

//表单头信息
if (!empty($api)) {
    @list($apiInfo['method'], $apiInfo['action'], $apiInfo['desc']) = explode(',', $api);
}

$apiInfo['action'] = trim($apiInfo['action']);
$action = '//' . $api_host . $apiInfo['action'];
?>
<div class="action-list-container" id="<?php echo $list_container_id; ?>">

    <form class="form-inline" data-toggle="validator" role="form"
          method="<?php echo $apiInfo['method'] ?>"
          action="<?php echo $action ?>" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12" style="margin:10px 0">
                <span class="badge"><?php echo $apiInfo['method'] ?></span>
                <a href="javascript:void(0)" onclick="apiActionList('<?php echo $list_container_id; ?>')">
                    <?php echo $apiInfo['desc'] ?>
                </a>
                (<?php echo $apiInfo['action'] ?>)
            </div>
        </div>

        <div class="action-form" id="<?php echo $list_container_id ?>_action_list" style="display: none">

            <div class="row" style="margin-top:10px;">
                <div class="col-md-12">
                    <div class="panel panel-default">

                        <div class="panel-heading">
                            <div class="form-group" style="width:100%">
                                <div class="input-group input-group-lg" style="width:100%">
                                    <span class="input-group-addon" style="width:1%">
                                        <?php echo strtoupper($apiInfo['method']) ?>
                                    </span>
                                    <input type="text" class="form-control request-action"
                                           value="<?php echo $apiInfo['action'] ?>"
                                           placeholder="<?php echo $apiInfo['action'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="panel-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>参数</th>
                                    <th>值</th>
                                    <th>名称</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($formFieldsArray as $field) {
                                    if (false !== strpos($field['name'], ':')) {
                                        @list($field_name, $input_type, $options) = explode(':', $field['name']);
                                    } else {
                                        $field_name = $field['name'];
                                        $input_type = 'text';
                                        $options = '';
                                    }

                                    $input_tag_data = array(
                                        'name' => $field_name,
                                        'type' => $input_type,
                                        'class' => 'form-control',
                                    );

                                    $input_addition_html = '';
                                    if ($field['is_require'] == 1) {
                                        $input_tag_data['required'] = 1;
                                        $input_addition_html = '&nbsp;<span style="form-control-static">*</span>';
                                    }

                                    switch ($input_type) {
                                        case 'textarea':
                                            $input_ele_type = 'textarea';
                                            $input_tag_data['style'] = 'width:300px;height:90px;';
                                            $input_tag_data['placeholder'] = $field_name;
                                            $input = $this->htmlTag($input_ele_type, $input_tag_data);
                                            break;

                                        case 'select':
                                            $input_ele_type = 'select';
                                            $options = explode(' ', trim($options));
                                            $selectOptions = array();
                                            if (!empty($options)) {
                                                foreach ($options as $op) {
                                                    list($opValue, $opTxt) = explode('-', $op);
                                                    $selectOptions[$opValue] = $opTxt;
                                                }
                                            }

                                            $input = $this->select($selectOptions, null, $input_tag_data);
                                            break;

                                        case 'multi_file':
                                            $input_ele_type = 'input';
                                            $input_tag_data['name'] = sprintf('%s[]', $field_name);
                                            $input_tag_data['type'] = 'file';
                                            $input_tag_data['placeholder'] = $field_name;
                                            $input_tag_data['multiple'] = true;
                                            $input = $this->htmlTag($input_ele_type, $input_tag_data);
                                            break;

                                        default:
                                            $input_ele_type = 'input';
                                            $input_tag_data['placeholder'] = $field_name;
                                            $input = $this->htmlTag($input_ele_type, $input_tag_data);
                                    }

                                    ?>
                                    <tr>
                                        <td> <?php echo $field_name ?> </td>
                                        <td>
                                            <div class="form-group">
                                                <?php echo $input . $input_addition_html ?>
                                            </div>
                                        </td>
                                        <td> <?php echo $field['txt'] ?> </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td>platform</td>
                                    <td>
                                        <input type="text" class="form-control" name="platform"
                                               value="<?php echo '<?php echo $_COOKIE["platform"] ?>' ?>"
                                               placeholder="platform">
                                        &nbsp;<span style="form-control-static">*</span>
                                    </td>
                                    <td>平台</td>
                                </tr>
                                <tr>
                                    <td>channel</td>
                                    <td>
                                        <input type="text" class="form-control" name="channel"
                                               value="<?php echo '<?php echo $_COOKIE["channel"] ?>' ?>"
                                               placeholder="channel">
                                        &nbsp;<span style="form-control-static">*</span>
                                    </td>
                                    <td>渠道</td>
                                </tr>
                                <tr>
                                    <td>version</td>
                                    <td>
                                        <input type="text" class="form-control" name="version"
                                               value="<?php echo '<?php echo $_COOKIE["version"] ?>' ?>"
                                               placeholder="version">
                                        &nbsp;<span style="form-control-static">*</span>
                                    </td>
                                    <td>客户端版本号</td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <?php if (!empty($data['desc'])) {
                                    printf('<div class="col-md-12">%s</div>', $data['desc']);
                                } ?>
                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary">试一试</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


