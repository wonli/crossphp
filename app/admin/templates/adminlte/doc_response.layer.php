<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= isset($title) ? $title : '' ?></title>
    <meta name="Keywords" content="<?= isset($keywords) ? $keywords : ''; ?>"/>
    <meta name="Description" content="<?= isset($description) ? $description : ''; ?>"/>
    <link href="<?= $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('css/doc-default-theme.css') ?>" rel="stylesheet">

    <link href="<?= $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">

    <link href="<?= $this->res('libs/highlight/styles/default.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/highlight/styles/github.css') ?>" rel="stylesheet">
    <script src="<?= $this->res('libs/highlight/highlight.pack.js') ?>"></script>

    <script src="<?= $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
    <script src="<?= $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
    <script src="<?= $this->res('libs/bootstrap/3.3.7/js/bootstrap.min.js') ?>"></script>
    <script src="<?= $this->res('libs/bootstrap-validator/0.11.8/validator.min.js') ?>"></script>
</head>
<body>
    <?= empty($content)?'':$content ?>
</body>
<script>
    $(function(){
        $('pre code').each(function (i, block) {
            hljs.highlightBlock(block);
        });
    })
</script>
</html>
