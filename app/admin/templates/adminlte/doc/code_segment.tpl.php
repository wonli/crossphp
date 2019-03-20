<?php
/**
 * @author wonli <wonli@live.com>
 * code_segment.tpl.php
 */

if(!empty($data['data'])) {
    $data = &$data['data'];
    if (($curlData = json_encode($data['curl'])) === false || empty($data['curl'])) {
        $curlData = array();
    }

    if (($structData = json_encode($data['struct'])) === false || empty($data['struct'])) {
        $structData = array();
    }

    $tabs = [];
    if (!empty($data)) {
        $tabs = array_keys($data);
    }

    $data['curl'] = '';
    $data['struct'] = '';
    $current_tab_name = '';
} else {
    $data = array();
}
?>
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
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
                        <div class="<?php echo $class ?>" id="<?php echo $name ?>">
                            <pre><code class="<?php echo $lng_class_name ?>"><?php echo htmlentities($d) ?></code></pre>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php else : ?>
            <div>服务器返回结果为空</div>
            <?php endif ?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#curl').JSONView(<?php echo $curlData ?>, {collapsed: true});
        $('#struct').JSONView(<?php echo $structData ?>, {collapsed: true});
        $('pre code').each(function (i, block) {
            hljs.highlightBlock(block);
        });
    });
</script>