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

    <!-- Bootstrap -->
    <link href="<?php echo $this->res('libs/bootstrap/3.3.5/css/bootstrap.min.css') ?>" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="<?php echo $this->res('libs/html5shiv/3.7.2/html5shiv.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/respond.js/1.4.2/respond.min.js') ?>"></script>
    <![endif]-->
</head>
<body>
<?php echo empty($content) ? "" : $content ?>
<script src="<?php echo $this->res('libs/jquery/1.11.1/jquery.min.js') ?>"></script>
<script src="<?php echo $this->res('libs/bootstrap/3.3.5/js/bootstrap.min.js') ?>"></script>
</body>
</html>
