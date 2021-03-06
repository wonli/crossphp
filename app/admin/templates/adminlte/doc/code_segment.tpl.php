<?php
/**
 * @author wonli <wonli@live.com>
 * code_segment.tpl.php
 */

$isTransform = false;
$emptyTip = '获取API数据失败';
if(!empty($data['t']) && $data['t'] == 'generator') {
    $isTransform = true;
    $emptyTip = 'JSON格式不正确';
}

$curlData = '{}';
$structData = '{}';
if(!empty($data['data'])) {
    $data = &$data['data'];
    if (($curlData = json_encode($data['curl'])) === false || empty($data['curl'])) {
        $curlData = '{}';
    }

    if (($structData = json_encode($data['struct'])) === false || empty($data['struct'])) {
        $structData = '{}';
    }

    $tabs = [];
    if (!empty($data)) {
        $tabs = array_keys($data);
    }

    $data['curl'] = '';
    $data['struct'] = '';
    $current_tab_name = '';
} else {
    $data = [];
}
?>
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <?php if($isTransform): ?>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <a href="<?= $this->url('doc:generator') ?>">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </button>
            <?php else : ?>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <?php endif ?>
        </div>
        <div class="modal-body">
            <?php if(!empty($data)) : ?>
            <ul id="paramsTab" class="nav nav-tabs">
                <?php
                foreach ($tabs as $i => $t) {
                    $attr = [];
                    if ($i == 0) {
                        $attr['class'] = 'active';
                        $current_tab_name = $t;
                    }

                    echo $this->wrap('li', $attr)->a(ucfirst($t), "#{$t}", array(
                        'data-toggle' => 'tab'
                    ));
                }
                ?>
            </ul>
            <div id="paramsTabContent" class="tab-content" style="margin-top:15px">
                <?php
                if (!empty($data)) {
                    $lng_name_map = ['struct' => 'json', 'curl' => 'json', 'flutter' => 'dart', 'go' => 'go'];
                    foreach ($data as $name => $d) {
                        $class = 'tab-pane fade';
                        if ($name == $current_tab_name) {
                            $class = 'tab-pane fade in active';
                        }

                        $lng_class_name = 'json';
                        if (isset($lng_name_map[$name])) {
                            $lng_class_name = $lng_name_map[$name];
                        }
                        ?>
                        <div class="<?= $class ?>" id="<?= $name ?>">
                            <pre><code class="<?= $lng_class_name ?>"><?= htmlentities($d) ?></code></pre>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php else : ?>
            <div><?= $emptyTip ?></div>
            <?php endif ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#curl').JSONView(<?= $curlData ?>, {collapsed: false});
        $('#struct').JSONView(<?= $structData ?>, {collapsed: false});
    });
</script>