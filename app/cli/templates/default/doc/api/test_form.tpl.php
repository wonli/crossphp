<?php
/**
 * @author wonli <wonli@live.com>
 * test_form.tpl.php
 */
$api = &$data['api'];
$formFields = &$data['apiParams'];
$global_params_status = &$data['global_params'];
$list_container_id = $data['controller'] . '_' . $data['action'];
?>
<div class="action-list-container" id="<?php echo $list_container_id; ?>">
    <form class="form-inline" data-toggle="validator" role="form" target="_blank"
          method="<?php echo $data['formMethod'] ?>"
          action="<?php echo $data['formAction'] ?>" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12" style="margin:10px 0">
                <span class="badge"><?php echo $data['method'] ?></span>
                <a href="javascript:void(0)" onclick="apiActionList('<?php echo $list_container_id; ?>')">
                    <?php echo $data['apiDesc'] ?>
                </a>
                <span class="hidden-xs">
                    (<?php echo $data['apiUrl'] ?>)
                </span>
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
                                        <?php echo strtoupper($data['method']) ?>
                                    </span>
                                    <input type="text" class="form-control request-action"
                                           value="<?php echo $data['apiUrl'] ?>"
                                           placeholder="<?php echo $data['apiUrl'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="panel-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>参数</th>
                                    <th>值</th>
                                    <th><span class="hidden-xs">名称</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if (!empty($formFields)) {
                                    foreach ($formFields as $field) {
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
                                            $input_addition_html = '<b style="form-control-static">*</b>';
                                        }

                                        switch ($input_type) {
                                            case 'textarea':
                                                $input_ele_type = 'textarea';
                                                $input_tag_data['rows'] = 5;
                                                $input_tag_data['style'] = 'min-width:80%';
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
                                            <td>
                                                <div class="form-control-static">
                                                    <?php echo $field_name ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group col-lg-12">
                                                    <?php echo $input ?>
                                                    <span class="hidden-xs">
                                                    <?php echo $input_addition_html ?>
                                                </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-control-static">
                                                <span class="visible-xs">
                                                    <?php echo $input_addition_html ?>
                                                </span>
                                                    <span class="hidden-xs">
                                                    <?php echo $field['txt'] ?>
                                                </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }

                                //公共参数表单
                                if ($global_params_status) {
                                    echo '<?php globalParamsInput($global_config) ?>';
                                }
                                ?>
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


