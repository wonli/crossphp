<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= $title ?? '' ?></title>
    <meta name="Keywords" content="<?= $keywords ?? ''; ?>"/>
    <meta name="Description" content="<?= $description ?? ''; ?>"/>

    <link href="<?= $this->res('libs/bootstrap/3.3.5/css/bootstrap.min.css') ?>" rel="stylesheet">
    <script src="<?= $this->res('libs/jquery/1.11.1/jquery.min.js') ?>"></script>
</head>
<body>
<?= $content ?? '' ?>
<script src="<?= $this->res('libs/bootstrap/3.3.5/js/bootstrap.min.js') ?>"></script>
</body>
</html>
