<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>CPAdmin 登录</title>
    <link rel="stylesheet" href="<?php echo $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res("adminlte/2.4.3/dist/css/AdminLTE.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res("css/style.css") ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <img src="<?php echo $this->res("images/logo.png") ?>" alt="crossphp framework"/>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">
            <?php
            if ($this->data['status'] != 1) {
                $this->notice($this->data['status']);
            }
            ?>
        </p>

        <form action="" method="post">
            <?php echo empty($content) ? '暂无内容' : $content; ?>
            <div class="row">
                <div class="col-xs-8">

                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">登录</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="bg"></div>
</body>
</html>




