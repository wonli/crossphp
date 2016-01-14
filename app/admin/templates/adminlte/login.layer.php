<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CPAdmin 登录</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?php echo $this->res('styles/adminlte/2.3.0/plugins/bootstrap/3.3.5/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res("styles/adminlte/2.3.0/dist/css/AdminLTE.min.css") ?>">

    <!--[if lt IE 9]>
    <script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/html5shiv/3.7.3/html5shiv.min.js') ?>"></script>
    <script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/respond/1.4.2/respond.min.js') ?>"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <img src="<?php echo $this->res("styles/adminlte/2.3.0/dist/img/logo.png") ?>" alt="cross php framework" style="width:200px;"/>
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
</body>
</html>




