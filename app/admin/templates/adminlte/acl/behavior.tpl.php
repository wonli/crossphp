<table class="table table-bordered">
    <thead>
    <tr>
        <th style="width:100px;min-width:100px;">类名</th>
        <th style="width:800px;min-width:800px;">方法列表</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data['menu_list'] as $l) : ?>
        <tr>
            <td>
                <div class="checkbox">
                    <label>
                        <?php
                        $checkboxParams = array(
                            'class' => 'selectChild',
                            'action' => "action_{$l['link']}_class",
                            'value' => $l['id'],
                            'name' => 'menu_id[]',
                        );

                        if (isset($data['menu_select']) && in_array($l['id'], $data['menu_select'])) {
                            $checkboxParams['checked'] = true;
                        }

                        echo $this->input('checkbox', $checkboxParams) . $l['name'];
                        ?>
                    </label>
                </div>
            </td>
            <td>
                <div class="checkbox">
                    <?php
                    if (isset($l['method'])) {
                        foreach ($l['method'] as $link => $m) {
                            if (!empty($m)) {
                                $checkboxParams = array(
                                    'class' => "action_{$l['link']}_class_children",
                                    'value' => $m['id'],
                                    'style' => 'margin-left:-15px',
                                    'name' => 'menu_id[]'
                                );

                                if (isset($data['menu_select']) && in_array($m['id'], $data['menu_select'])) {
                                    $checkboxParams['checked'] = true;
                                }

                                if (empty($m['name'])) {
                                    $labelName = "*{$link}";
                                } else {
                                    $labelName = $m['name'];
                                }

                                echo $this->wrap('label', array('@content' => $labelName, 'style' => 'margin-right:5px'), true)
                                    ->input('checkbox', $checkboxParams);
                            }
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<script type="text/javascript">
    $(function () {
        $('.selectChild').on('click', function () {
            var that = $(this), action_name = $(this).attr('action');
            $('.' + action_name + '_children').each(function () {
                $(this)[0].checked = !!(that[0].checked);
            })
        });
    })
</script>
