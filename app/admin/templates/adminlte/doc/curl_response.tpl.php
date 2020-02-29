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
if (isset($data['data']) && is_array($data['data'])) {
    $jsonView = true;
    $content = json_encode($data['data']);
} else {
    $content = &$data['data'];
}
?>

<div class="container-fluid" style="margin:15px">
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

<link href="<?php echo $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">
<script src="<?php echo $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
<script src="<?php echo $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
<script>
    $(document).ready(function () {
        <?php if($jsonView): ?>
        $('#response').JSONView(<?= $content ?>, {collapsed: false});
        <?php endif ?>
    });
</script>
