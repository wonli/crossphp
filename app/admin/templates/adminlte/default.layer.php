<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title><?= $title ?? 'CPAdmin' ?></title>

    <link rel="stylesheet" href="<?= $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= $this->res("adminlte/2.4.3/dist/css/AdminLTE.min.css") ?>">
    <link rel="stylesheet" href="<?= $this->res("adminlte/2.4.3/dist/css/skins/_all-skins.min.css") ?>">
    <link rel="stylesheet" href="<?= $this->res('libs/font-awesome/4.7.0/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= $this->res("libs/toggle/2.3.2/css/bootstrap-toggle.min.css") ?>">
    <link rel="stylesheet" href="<?= $this->res('libs/nprogress/0.2.0/nprogress.css') ?>">
    <link rel="stylesheet" href="<?= $this->res('libs/layer/theme/default/layer.css') ?>">
    <link rel="stylesheet" href="<?= $this->res('libs/pop/pop.min.css') ?>">
    <link rel="stylesheet" href="<?= $this->res('css/style.css') ?>">

    <script src="<?= $this->res('libs/jquery/1.12.4/jquery.min.js') ?>"></script>
</head>
<!-- sidebar-collapse ! layout-boxed ! fixed ! skin-[blue|black|purple|green|red|yellow]-light -->
<body class="<?= $this->getTheme() ?> sidebar-mini" style="display:none">
<div class="wrapper">
    <header class="main-header">
        <a href="" class="logo">
            <span class="logo-mini">
                <img src="<?= $this->res("images/mini_logo.png") ?>" alt="logo"/>
            </span>
            <span class="logo-lg">
                <img src="<?= $this->res("images/logo.png") ?>" alt="logo"/>
                <b>CP</b>Admin
            </span>
        </a>
        <nav class="navbar navbar-static-top">
            <a href="#" id="sidebar-toggle" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="<?= $this->url("main:logout") ?>" target="_top">
                            <?= $this->loginInfo['name'] ?> <i class="fa fa-sign-out"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">工作台</li>
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

    <div class="content-wrapper" id="content-wrapper">
        <section class="content-header">
            <h1>
                <?= $controller_menu_name ?>
                <small><?= $action_menu_name ?></small>
            </h1>
            <ol class="breadcrumb">
                <?= $this->getTitleBread() ?>
            </ol>
        </section>

        <section class="content">
            <?php $this->noticeBlock() ?>
            <?= $content ?? '' ?>
        </section>
    </div>
</div>
<script src="<?= $this->res('libs/bootstrap/3.3.7/js/bootstrap.min.js') ?>"></script>
<script src="<?= $this->res('libs/toggle/2.3.2/js/bootstrap-toggle.min.js') ?>"></script>
<script src="<?= $this->res('libs/store.js/2.0.12/store.legacy.min.js') ?>"></script>
<script src="<?= $this->res('libs/nprogress/0.2.0/nprogress.js') ?>"></script>
<script src="<?= $this->res('libs/pop/pop.min.js') ?>"></script>
<script src="<?= $this->res('libs/layer/layer.js') ?>"></script>
<script src="<?= $this->res('adminlte/2.4.3/dist/js/adminlte.min.js') ?>"></script>
<script src="<?= $this->res('adminlte/2.4.3/plugins/slimScroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?= $this->res('adminlte/2.4.3/plugins/fastclick/fastclick.min.js') ?>"></script>
<script src="<?= $this->res('js/cpa.js') ?>"></script>
<script>
    NProgress.configure({
        template: '<div class="bar" role="bar"><div class="peg"></div></div>'
    });

    NProgress.start();
    $(function () {
        var body = $('body');
        if ($(document).width() > 767) {
            if (store.get('collapse')) {
                body.addClass('sidebar-collapse');
            } else {
                body.removeClass('sidebar-collapse');
            }

            $('#sidebar-toggle').click(function () {
                var v = (store.get('collapse') === 1) ? 0 : 1;
                store.set('collapse', v);
            })
        }

        NProgress.done();
        body.show();
    });
</script>
</body>
</html>
