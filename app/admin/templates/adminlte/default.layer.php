<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?php echo isset($title) ? $title : 'CPAdmin' ?></title>

    <link rel="stylesheet" href="<?php echo $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res("adminlte/2.4.0/dist/css/AdminLTE.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res("adminlte/2.4.0/dist/css/skins/_all-skins.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res('libs/font-awesome/4.7.0/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res("libs/toggle/2.2.2/css/bootstrap-toggle.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res('libs/nprogress/0.2.0/nprogress.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res('css/style.css') ?>">

    <script src="<?php echo $this->res('libs/jquery/1.12.4/jquery.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/nprogress/0.2.0/nprogress.js') ?>"></script>
    <script src="<?php echo $this->res('libs/layer/3.1.0/layer.js') ?>"></script>
    <script src="<?php echo $this->res('js/cpa.js') ?>"></script>

    <!--[if lt IE 9]>
    <script src="<?php echo $this->res('libs/html5shiv/3.7.3/html5shiv.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/respond/1.4.2/respond.min.js') ?>"></script>
    <![endif]-->
</head>
<!-- sidebar-collapse ! layout-boxed ! fixed ! skin-[blue|black|purple|green|red|yellow]-light -->
<body class="hold-transition skin-black sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <a href="" class="logo">
            <span class="logo-mini">
                <img src="<?php echo $this->res("images/mini_logo.png") ?>"/>
            </span>
            <span class="logo-lg">
                <img src="<?php echo $this->res("images/logo.png") ?>"/>
                <b>CP</b>Admin
            </span>
        </a>
        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="<?php echo $this->url("main:logout") ?>" target="_top">
                            <?php echo $_SESSION['u'] ?> <i class="fa fa-sign-out"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header"></li>
                <?php
                $action_menu_name = $this->action;
                $controller_menu_name = $this->controller;
                $this->renderNavMenu($controller_menu_name, $action_menu_name);
                ?>
            </ul>
            <ul class="sidebar-menu">
                <li class="header">使用帮助</li>
                <li>
                    <a href="//document.crossphp.com/skeleton/" target="_blank">
                        <i class="fa fa-circle-o text-aqua"></i>
                        <span>帮助文档</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper" id="content-wrapper" style="display: none">
        <section class="content-header">
            <h1>
                <?php echo $controller_menu_name ?>
                <small><?php echo $action_menu_name ?></small>
            </h1>
            <ol class="breadcrumb">
                <?php echo $this->getTitleBread() ?>
            </ol>
        </section>

        <section class="content">
            <?php if ($this->data['status'] != 1) : ?>
                <div class="callout callout-info">
                    <h4>提示!</h4>
                    <?php $this->notice($this->data['status'], '%s'); ?>
                </div>
            <?php endif ?>

            <?php echo isset($content) ? $content : ''; ?>
        </section>
    </div>
</div>
<script src="<?php echo $this->res('libs/bootstrap/3.3.7/js/bootstrap.min.js') ?>"></script>
<script src="<?php echo $this->res('libs/toggle/2.2.2/js/bootstrap-toggle.min.js') ?>"></script>
<script src="<?php echo $this->res('adminlte/2.4.0/dist/js/adminlte.min.js') ?>"></script>
<script src="<?php echo $this->res('adminlte/2.4.0/plugins/slimScroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?php echo $this->res('adminlte/2.4.0/plugins/fastclick/fastclick.min.js') ?>"></script>
<script>
    NProgress.configure({
        template: '<div class="bar" role="bar"><div class="peg"></div></div>'
    });

    NProgress.start();
    $(function () {
        NProgress.done();
        $('#content-wrapper').show();
    });
</script>
</body>
</html>
