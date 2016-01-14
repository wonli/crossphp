<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($title) ? $title : 'CPAdmin' ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="<?php echo $this->res('styles/adminlte/2.3.0/plugins/bootstrap/3.3.5/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?php echo $this->res('styles/adminlte/2.3.0/plugins/font-awesome/4.5.0/css/font-awesome.min.css') ?>">

    <link rel="stylesheet" href="<?php echo $this->res("styles/adminlte/2.3.0/dist/css/AdminLTE.min.css") ?>">
    <link rel="stylesheet" href="<?php echo $this->res("styles/adminlte/2.3.0/dist/css/skins/_all-skins.min.css") ?>">

    <script src="<?php echo $this->res('libs/jquery/1.11.3/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->res('libs/cp/base.js') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->res('libs/artDialog/jquery.artDialog.js?skin=idialog') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->res('libs/artDialog/plugins/iframeTools.js') ?>"></script>
    <script type="text/javascript">
        var cp = {
            url:<?php echo json_encode( $this->config->get('url', array('ext', 'dot', 'request', 'full_request')) ); ?>,
            link: function (controller, action, params) {
                var c = typeof controller === 'undefined' ? this.url['full_request'] : this.url['request'] + '/', a = action || 'index', p = params || [];
                return c + (a == 'index' ? [controller] : [controller, a]).concat(p).join(this.url['dot']) + this.url['ext'];
            }
        }
    </script>

    <!--[if lt IE 9]>
    <script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/html5shiv/3.7.3/html5shiv.min.js') ?>"></script>
    <script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/respond/1.4.2/respond.min.js') ?>"></script>
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
                <img src="<?php echo $this->res("styles/adminlte/2.3.0/dist/img/logo.png") ?>" alt="cross php framework"
                     style="width:50px;"/>
            </span>
            <span class="logo-lg">
                <img src="<?php echo $this->res("styles/adminlte/2.3.0/dist/img/logo.png") ?>" alt="cross php framework"
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
                        <a href="<?php echo $this->link("main:logout") ?>" target="_top">
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
                $action_menu_name = '';
                $controller_menu_name = '';
                $child_menu_name_map = array();
                $controller = lcfirst($this->controller);

                function li_menu($class, $content, $child_menu = '')
                {
                    if ($child_menu) {
                        return sprintf('<li class="%s">%s%s</li>', $class, $content, $child_menu);
                    } else {
                        return sprintf('<li class="%s">%s</li>', $class, $content);
                    }
                }

                function li_menu_content($link, $icon, $name)
                {
                    return sprintf('<a href="%s"><i class="%s"></i><span>%s</span><i class="fa fa-angle-left pull-right"></i></a>', $link, $icon, $name);
                }

                function li_child_menu($class, $link, $icon, $name)
                {
                    return sprintf('<li class="%s"><a href="%s"><i class="fa fa-circle-o"></i>%s</a></li>', $class, $link, $name);
                }

                foreach ($this->getAllMenu() as $m) {
                    if ($m['status'] != 1) continue;
                    $icon_name = !empty($m['icon']) ? $m['icon'] : 'fa fa-circle-o';
                    $child_node_num = count($m['child_menu']);

                    $li_class = "treeview";
                    if ($controller == $m['link']) {
                        $controller_menu_name = $m['name'];
                        $li_class = "treeview active";
                    }

                    if ($child_node_num > 0) {
                        $li_menu = '';
                        foreach ($m['child_menu'] as $mu) {
                            $child_menu_name_map[$m['link']][$mu['link']] = $mu['name'];
                            if ($mu['link'] == $this->action) {
                                $li_menu .= li_child_menu('active', $this->link("{$m['link']}:{$mu['link']}"), $icon_name, $mu['name']);
                            } else {
                                $li_menu .= li_child_menu('', $this->link("{$m['link']}:{$mu['link']}"), $icon_name, $mu['name']);
                            }
                        }

                        $child_ul_menu = sprintf('<ul class="treeview-menu">%s</ul>', $li_menu);
                    } else {
                        $child_ul_menu = '';
                    }
                    echo li_menu($li_class, li_menu_content($this->link($m['link']), $icon_name, $m['name']), $child_ul_menu);
                }

                if (isset($child_menu_name_map[$controller]) && isset($child_menu_name_map[$controller][$this->action])) {
                    $action_menu_name = $child_menu_name_map[$controller][$this->action];
                }
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
                <?php
                if ($controller_menu_name) {
                    echo $controller_menu_name;
                }
                if ($action_menu_name) {
                    printf('<small>%s</small>', $action_menu_name);
                }
                ?>
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

<script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/bootstrap/3.3.5/js/bootstrap.min.js') ?>"></script>
<script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/slimScroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?php echo $this->res('styles/adminlte/2.3.0/plugins/fastclick/fastclick.min.js') ?>"></script>
<script src="<?php echo $this->res('styles/adminlte/2.3.0/dist/js/app.min.js') ?>"></script>
</body>
</html>
