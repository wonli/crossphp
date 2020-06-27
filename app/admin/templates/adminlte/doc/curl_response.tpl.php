<?php
/**
 * @author wonli <wonli@live.com>
 * curl_response.tpl.php
 */

$url = '';
$method = '';
$params = '-';
if (!empty($data['curl_params'])) {
    $url = &$data['curl_params']['url'];
    $method = &$data['curl_params']['method'];
    $params = &$data['curl_params']['params'];
    if (is_array($params) && !empty($params)) {
        $p = [];
        foreach ($params as $k => $v) {
            $p[] = "{$k} -> {$v}";
        }
        $params = implode(',', $p);
    }
}

$jsonView = false;
$data = &$data['data'];

if (!empty($data) && isset($data['curl']) && is_array($data['curl'])) {
    $jsonView = true;
    $content = json_encode($data['curl']);
} else {
    $content = &$data['curl'];
}
?>
<div class="container-fluid curl-container">
    <div class="response-slide">
        <div class="response-menu">
            <div class="menu-box-wrap">
                <div class="nav-item active" data-type="curl">
                    <img src="<?= $this->res('images/icon/curl.svg') ?>" alt="curl结果">
                </div>
                <div class="nav-item" data-type="ori">
                    <img src="<?= $this->res('images/icon/code.svg') ?>" alt="原始数据">
                </div>
                <div class="nav-item" data-type="struct">
                    <img src="<?= $this->res('images/icon/struct.svg') ?>" alt="结构体">
                </div>

                <div class="nav-item" data-type="golang">
                    <img src="<?= $this->res('images/icon/golang.svg') ?>" alt="golang结构">
                </div>
                <div class="nav-item" data-type="flutter">
                    <img src="<?= $this->res('images/icon/flutter.svg') ?>" alt="flutter结构">
                </div>
            </div>
        </div>
    </div>
    <div class="response-content">
        <div id="curl" class="response-segment" style="display: block">
            <?php if (!$jsonView) : ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>

                            <strong class="btn btn-info"><?= strtoupper($method) ?></strong>
                            <?= $url ?>

                            <strong class="btn btn-warning">参数</strong>
                            <?= $params ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="row">
                <div class="col-md-12" id="response"><?= (!$jsonView) ? $content : '' ?></div>
            </div>
        </div>
        <div id="ori" class="response-segment">
            <label for="response-textarea"></label>
            <div><?= $content ?></div>
        </div>
        <div id="struct" class="response-segment">
            <pre>
                <code class="code json"><?= json_encode($data['struct'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?></code>
            </pre>
        </div>
        <div id="golang" class="response-segment">
            <pre>
                <code class="code go"><?= htmlentities($data['go']) ?></code>
            </pre>
        </div>
        <div id="flutter" class="response-segment">
            <pre>
                <code class="flutter"><?= htmlentities($data['flutter']) ?></code>
            </pre>
        </div>
    </div>
</div>

<link href="<?= $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">
<script src="<?= $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
<script src="<?= $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
<script>
    $(document).ready(function () {
        <?php if($jsonView): ?>
        $('#response').JSONView(<?= $content ?>, {collapsed: false});
        <?php endif ?>

        $('.nav-item').on('click', function () {
            var type = $(this).attr('data-type');
            $('.nav-item').each(function () {
                var currentType = $(this).attr('data-type');
                if (currentType === type) {
                    $(this).addClass('active');
                    $('#' + currentType).show();
                } else {
                    $(this).removeClass('active');
                    $('#' + currentType).hide();
                }
            });
        })
    });
</script>
