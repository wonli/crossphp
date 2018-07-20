<?php
function savePostData($name)
{
    if (!empty($_POST)) {
        $custom_config = array();
        if (!empty($_POST[$name])) {
            foreach ($_POST[$name] as $g) {
                $g = array_map('trim', $g);
                if (!empty($g['f'])) {
                    $custom_config[$g['f']] = $g['v'];
                }
            }

            if (!empty($custom_config)) {
                setcookie("__api_{$name}_params__", json_encode($custom_config), time() + 86400000, '/');
            }
        }
    } elseif (!empty($_COOKIE["__api_{$name}_params__"])) {
        $custom_config = json_decode($_COOKIE["__api_{$name}_params__"], true);
    } else {
        $custom_config = array();
    }

    $config_data = array();
    if (file_exists(".{$name}.json")) {
        $config_data = json_decode(file_get_contents(".{$name}.json"), true);
        foreach ($config_data as &$g) {
            if (isset($custom_config[$g['f']])) {
                $g['v'] = $custom_config[$g['f']];
            }
        }
    }

    return $config_data;
}

function formParams($data, $name = 'global')
{
    if (!empty($data)) {
        foreach ($data as $k => $d) {
            ?>
            <div class="form-group">
                <div class="col-sm-3">
                    <input type="hidden" name="<?php echo $name ?>[<?php echo $k ?>][f]" value="<?php echo $d['f'] ?>">
                    <label for="" class="form-control-static"><?php echo $d['f'] ?></label>
                </div>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="<?php echo $name ?>[<?php echo $k ?>][v]"
                           value="<?php echo $d['v'] ?>" placeholder="值">
                </div>
                <div class="col-sm-3 text-left form-control-static">
                    <?php echo $d['t'] ?>
                </div>
            </div>
            <?php
        }
    }
}

function globalParamsInput($global_config)
{
    if (!empty($global_config)) {
        foreach ($global_config as $k => $global) {
            ?>
            <tr>
                <td>
                    <div class="form-control-static">
                        <?php echo $global['f'] ?>
                    </div>
                </td>
                <td>
	                <div class="form-group col-lg-12">
	                    <input type="text" class="form-control" required="1" name="<?php echo $global['f'] ?>"
	                           value="<?php echo $global['v'] ?>"
	                           placeholder="<?php echo $global['f'] ?>">
	                    <span class="hidden-xs">
	                        <b style="form-control-static">*</b>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="form-control-static">
                        <span class="visible-xs"><b style="form-control-static">*</b></span>
                        <span class="hidden-xs"><?php echo $global['t'] ?></span>
                    </div>
                </td>
            </tr>
            <?php
        }
    }
}

$global_config = savePostData('global');
$header_config = savePostData('header');
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title></title>
    <meta name="Keywords" content=""/>
    <meta name="Description" content=""/>
    <link href="libs/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/default-theme.css" rel="stylesheet">
    <script src="libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="libs/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="libs/bootstrap-validator/0.11.8/validator.min.js"></script>
</head>
<body>

<div class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
    <div class="container">
    <div class="navbar-header">
        <button id="collapseBtn" type="button" class="navbar-toggle" data-toggle="collapse" data-target="leftContainer">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="" title="生成时间 2018-07-20 10:26:09">
            API文档            <small><sup>v1.0</sup></small>
        </a>
    </div>

    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
                    </ul>

        <ul class="nav navbar-nav navbar-right">
                    </ul>
    </div>
</div>
</div>

<div class="mainWrap">
    <div class="container mainContainer">
        <div class="row">
            <div class="col-md-4">
                <div class="leftContainer navbar-collapse collapse">
                    <div class="panel panel-api-case">
    <div class="panel-heading">
        <h3>
            <a href="javascript:void(0)" onclick="apiClassList('main')">
                默认            </a>
        </h3>
    </div>
    <div class="panel-body menu-list" id="mainMenuList" style="display: none">
        <div class="row" style="margin:10px 0">
    <a href="javascript:void(0)" onclick="apiActionList('main_index')">
         获取框架当前版本号    </a>
</div>
    </div>
</div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="rightContainer">
                    <div class="panel-api-case">
    <div class="action-list" id="mainActionList">
        <div class="action-list-container" id="main_index">
    <form class="form-inline" data-toggle="validator" role="form" target="_blank"
          method="get"
          action="//127.0.0.1/skeleton/htdocs/api/main/index" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-12" style="margin:10px 0">
                <span class="badge">get</span>
                <a href="javascript:void(0)" onclick="apiActionList('main_index')">
                    获取框架当前版本号                </a>
                <span class="hidden-xs">
                    (/main/index)
                </span>
            </div>
        </div>

        <div class="action-form" id="main_index_action_list" style="display: none">
            <div class="row" style="margin-top:10px;">
                <div class="col-md-12">
                    <div class="panel panel-default">

                        <div class="panel-heading">
                            <div class="form-group" style="width:100%">
                                <div class="input-group input-group-lg" style="width:100%">
                                    <span class="input-group-addon" style="width:1%">
                                        GET                                    </span>
                                    <input type="text" class="form-control request-action"
                                           value="/main/index"
                                           placeholder="/main/index">
                                </div>
                            </div>
                        </div>

                        <div class="panel-body">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>参数</th>
                                    <th>值</th>
                                    <th><span class="hidden-xs">名称</span></th>
                                </tr>
                                </thead>
                                <tbody>
                                                                        <tr>
                                            <td>
                                                <div class="form-control-static">
                                                    t                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group col-lg-12">
                                                    <input name="t" type="text" class="form-control" required="1" placeholder="t">                                                    <span class="hidden-xs">
                                                    <b style="form-control-static">*</b>                                                </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-control-static">
                                                <span class="visible-xs">
                                                    <b style="form-control-static">*</b>                                                </span>
                                                    <span class="hidden-xs">
                                                    当前时间                                                </span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php globalParamsInput($global_config) ?>                                </tbody>
                            </table>
                            <div class="row">
                                                            </div>
                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary">试一试</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


    </div>
</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="footWrap">
    <div class="container">
        <div class="row">
        </div>
        <div class="row" style="position:relative">
            <div id="goTop"><i class="glyphicon glyphicon-circle-arrow-up"></i></div>
            <div id="fold" status="0"><i class="glyphicon glyphicon-info-sign"></i></div>
        </div>
    </div>
</div>

<div class="modal fade" id="commonModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-horizontal" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">公共参数配置</h4>
                </div>
                <div class="modal-body">
                    <ul id="paramsTab" class="nav nav-tabs">
                        <li class="active">
                            <a href="#globalParams" data-toggle="tab">公共参数</a>
                        </li>
                        <li>
                            <a href="#headerParams" data-toggle="tab">Header参数</a>
                        </li>
                    </ul>
                    <div id="paramsTabContent" class="tab-content" style="margin-top:15px">
                        <div class="tab-pane fade in active" id="globalParams">
                            <div class="form-group">
                                <div class="col-sm-3">表单字段名</div>
                                <div class="col-sm-6">值</div>
                                <div class="col-sm-3">名称</div>
                            </div>
                            <?php formParams($global_config) ?>
                        </div>
                        <div class="tab-pane fade" id="headerParams">
                            <div class="form-group">
                                <div class="col-sm-3">参数名</div>
                                <div class="col-sm-6">值</div>
                                <div class="col-sm-3">名称</div>
                            </div>
                            <?php formParams($header_config, "header") ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存配置</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    function apiActionList(action) {
        var targetId = action + '_action_list', target = $('#' + targetId);
        target.show();

        var className = action.split('_')[0], classID = className + 'ActionList', menuID = className + 'MenuList';
        $('#' + classID).show();
        $('#' + menuID).show();

        window.location.hash = '!' + action;
        $('.action-list-container').each(function () {
            var id = $(this).attr('id');
            if (id != action) {
                $('#' + id).hide();
            } else {
                $('#' + id).show();
            }
        });

        $('.action-form').each(function () {
            var id = $(this).attr('id');
            if (id != targetId) {
                $('#' + id).hide();
            }
        });

        $('html, body').animate({scrollTop: 0}, 5);
        $('.leftContainer').hide();
    }

    function apiClassList(className) {
        var classID = className + 'MenuList', target = $('#' + classID);
        target.toggle();
        if (target.is(':visible')) {
            window.location.hash = '!' + className;
        } else {
            window.location.hash = '';
        }

        // target.parent().addClass("current").siblings().removeClass("current");

        $('.menu-list').each(function () {
            var id = $(this).attr('id');
            if (id != classID) {
                $('#' + id).hide();
            }
        });
    }

    function showContent(contentID) {
        if (contentID.indexOf('_') > 0) {
            var hashInfo = contentID.split('_');
            apiClassList(hashInfo[0]);
            apiActionList(contentID)
        } else {
            apiClassList(contentID);
        }
    }

    $(function () {
        var hashContent = window.location.hash.substring(2);
        if (hashContent) {
            showContent(hashContent);
        }

        $('#collapseBtn').on('click', function () {
            $('.leftContainer').toggle();
        });

        $('.request-action').on('click', function () {
            $(this).select();
        });

        $('#fold').click(function () {
            if ($(this).attr('status') == 0) {
                $('.action-list-container').show();
                $('.action-form').hide();
                $('.menu-list').hide();
                $(this).attr('status', 1);
            } else {
                showContent(hashContent);
                $(this).attr('status', 0);
            }
        });

        $('#goTop').click(function () {
            $("html, body").animate({scrollTop: 0}, 200);
        });

        $('#commonModalSwitch').click(function () {
            $('#commonModal').modal('toggle');
        });

        $(window).bind("scroll", function () {
            if (($(document).scrollTop() > 10)) {
                $('#goTop').show();
            } else {
                $('#goTop').hide();
            }
        });
    })
</script>
</body>
</html>
