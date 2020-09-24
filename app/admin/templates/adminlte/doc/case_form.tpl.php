<?php
/**
 * @author wonli <wonli@live.com>
 * case_form.tpl.php
 */

$name = $data['api']['api_name'] ?? '';
$action = $data['api']['api_path'] ?? '';
$method = $data['api']['api_method'] ?? '';

$apiId = $data['api']['id'] ?? '';

$headerParams = $data['doc']['header_params'] ?? [];
$useGlobalParams = $data['doc']['global_params'] ?? [];
$formFields = $data['api']['api_params'] ?? [];
?>
<div class="action-list-container">
    <div class="form-container-wrap">
        <div class="form-container">
            <form class="form-inline" data-toggle="validator" role="form" target="_blank"
                  method="<?= $this->getApiActionMethod($data ?? []) ?>"
                  action="<?= $this->getApiActionUrl($data ?? []) ?>" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12 case-title" style="margin:10px 0">
                        <span class="badge"><?= $method ?></span>
                        <a class="api-url-name" href="javascript:void(0)">
                            <?= $action ?>
                        </a>
                        <span class="api-name hidden-xs">(<?= $name ?>)</span>
                    </div>
                </div>

                <div class="action-form">
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-12">
                            <div class="form-group" style="width:100%;margin-bottom:20px">
                                <div class="input-group input-group-lg" style="width:100%">
                                    <span class="input-group-addon" style="width:1%">
                                        <?= strtoupper($method) ?>
                                    </span>
                                    <input type="text" class="form-control request-action"
                                           value="<?= $action ?>"
                                           placeholder="<?= $action ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">
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
                                            foreach ($formFields as $name => $field) {
                                                $name = $field['field'];
                                                if (false !== strpos($name, ':')) {
                                                    @list($field_name, $input_type) = explode(':', $name);
                                                    $options = $field['label'];
                                                } else {
                                                    $field_name = $name;
                                                    $input_type = 'text';
                                                    $options = '';
                                                }

                                                $input_tag_data = array(
                                                    'name' => $field_name,
                                                    'type' => $input_type,
                                                    'class' => 'form-control',
                                                    'style' => 'min-width:75%'
                                                );

                                                $input_addition_html = '';
                                                if ($field['required'] == 1) {
                                                    $input_tag_data['required'] = 1;
                                                    $input_addition_html = '<b style="form-control-static">*</b>';
                                                }

                                                switch ($input_type) {
                                                    case 'textarea':
                                                        $input_ele_type = 'textarea';
                                                        $input_tag_data['rows'] = 5;
                                                        $input_tag_data['style'] = 'min-width:85%';
                                                        $input_tag_data['placeholder'] = $field_name;
                                                        $input = $this->htmlTag($input_ele_type, $input_tag_data);
                                                        break;

                                                    case 'select':
                                                        $input_tag_data['style'] = 'min-width:50%';
                                                        if (!empty($options)) {
                                                            $selectOptions = [];
                                                            $options = explode(' ', trim($options));
                                                            foreach ($options as $op) {
                                                                list($opValue, $opTxt) = explode('-', $op);
                                                                $selectOptions[$opValue] = $opTxt;
                                                            }
                                                            $input = $this->select($selectOptions, null, $input_tag_data);
                                                        } else {
                                                            $input_ele_type = 'input';
                                                            $input_tag_data['placeholder'] = $field_name;
                                                            $input = $this->htmlTag($input_ele_type, $input_tag_data);
                                                        }

                                                        break;

                                                    case 'multi_file':
                                                        $input_ele_type = 'input';
                                                        $input_tag_data['name'] = sprintf('%s[]', $field_name);
                                                        $input_tag_data['type'] = 'file';
                                                        $input_tag_data['placeholder'] = $field_name;
                                                        $input_tag_data['multiple'] = true;
                                                        $input_tag_data['style'] = 'min-width:80%';
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
                                                            <?= $field_name ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-group" style="width:100%">
                                                            <?= $input ?>
                                                            <span class="hidden-xs">
                                                                <?= $input_addition_html ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-control-static">
                                                            <span class="visible-xs">
                                                                <?= $input_addition_html ?>
                                                            </span>
                                                            <span class="hidden-xs">
                                                                <?= $field['label'] ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }

                                        //公共参数表单
                                        if ($useGlobalParams) {
                                            $this->globalParams();
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
                                    <?php if (empty($headerParams)) : ?>
                                        <button type="submit" class="btn btn-primary">试一试</button>
                                    <?php endif ?>
                                    <button type="button" api-id="<?= $apiId ?>" class="gen-code-flag btn btn-success">
                                        CURL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


