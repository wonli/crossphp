<?php echo $data['basic_auth'] ?>
<?php echo $data['action']; ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo isset($title) ? $title : '' ?></title>
    <meta name="Keywords" content="<?php echo isset($keywords) ? $keywords : ''; ?>"/>
    <meta name="Description" content="<?php echo isset($description) ? $description : ''; ?>"/>
    <link href="<?php echo $data['asset_server'] ?>../libs/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $data['asset_server'] ?>../libs/jquery/jquery.jsonview.min.css" rel="stylesheet">
    <link href="<?php echo $data['asset_server'] ?>../css/default-theme.css" rel="stylesheet">
    <script src="<?php echo $data['asset_server'] ?>../libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="<?php echo $data['asset_server'] ?>../libs/jquery/jquery.jsonview.min.js"></script>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div id="response"></div>
            <div id="responseTxt" style="display: none">
                <?php echo $this->phpCode('if($isHTML) :') ?>
                <?php echo $this->phpCode('echo print_r($data, true)') ?>
                <?php echo $this->phpCode('else :') ?>
                <div class="alert alert-danger" role="alert" style="margin-top:50px;">
                    <?php echo $this->phpCode('echo print_r($data, true)') ?>
                </div>
                <?php echo $this->phpCode('endif') ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        var msg = $('#responseTxt'), response = $('#response');
        <?php echo $this->phpCode('if($isJSON) :') ?>
        msg.hide();
        response.css("padding", "10px");
        response.JSONView(<?php echo '<?php echo $data ?>' ?>);
        <?php echo $this->phpCode('else :') ?>
        msg.show();
        <?php echo $this->phpCode('endif') ?>
    })
</script>
</body>
</html>
