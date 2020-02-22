<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" id="response"></div>
    </div>
</div>

<link href="<?php echo $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">
<script src="<?php echo $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
<script src="<?php echo $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
<script>
    $(document).ready(function () {
        $('#response').JSONView(<?= json_encode($data['data']) ?>, {collapsed: false});
    });
</script>
<?php
/**
 * @author wonli <wonli@live.com>
 * curl_response.tpl.php
 */
