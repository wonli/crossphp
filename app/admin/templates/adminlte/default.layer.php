<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?php echo isset($title) ? $title : 'CPAdmin' ?></title>
    <link rel="stylesheet" href="<?php echo $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>">

    <link rel="stylesheet" href="<?php echo $this->res("adminlte/2.3.5/dist/css/AdminLTE.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res("adminlte/2.3.5/dist/css/skins/_all-skins.min.css") ?>">
    <link rel="stylesheet"
          href="<?php echo $this->res('adminlte/2.3.5/plugins/font-awesome/4.6.3/css/font-awesome.min.css') ?>">

    <script src="<?php echo $this->res('libs/jquery/1.11.3/jquery.min.js') ?>"></script>
    <script src="<?php echo $this->res('js/cpa.js') ?>"></script>
    <script src="<?php echo $this->res('libs/artDialog/jquery.artDialog.js?skin=idialog') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->res('libs/artDialog/plugins/iframeTools.js') ?>"></script>

    <!--[if lt IE 9]>
    <script src="<?php echo $this->res('adminlte/2.3.5/plugins/html5shiv/3.7.3/html5shiv.min.js') ?>"></script>
    <script src="<?php echo $this->res('adminlte/2.3.5/plugins/respond/1.4.2/respond.min.js') ?>"></script>
    <![endif]-->
</head>
<!-- fixed -->
<!-- layout-boxed -->
<!-- sidebar-collapse -->
<!-- skin-blue|black|purple|green|red|yellow|blue-light|black-light|purple-light|green-light|red-light|yellow-light  -->
<body class="hold-transition skin-black sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <a href="" class="logo">
            <span class="logo-mini">
                <img src="<?php echo $this->res("adminlte/2.3.5/dist/img/logo.png") ?>" alt="cross php framework"
                     style="width:50px;"/>
            </span>
            <span class="logo-lg">
                <img src="<?php echo $this->res("adminlte/2.3.5/dist/img/logo.png") ?>" alt="cross php framework"
                     style="width:50px;"/>
                <b>CP</b>Admin
            </span>
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
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
            <ul class="sidebar-menu">
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
                    <a href="http://document.crossphp.com/skeleton/" target="_blank">
                        <i class="fa fa-circle-o text-aqua"></i>
                        <span>帮助文档</span>
                    </a>
                </li>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
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
<script src="<?php echo $this->res('adminlte/2.3.5/plugins/slimScroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?php echo $this->res('adminlte/2.3.5/plugins/fastclick/fastclick.min.js') ?>"></script>
<script src="<?php echo $this->res('adminlte/2.3.5/dist/js/app.min.js') ?>"></script>
</body>
</html>
