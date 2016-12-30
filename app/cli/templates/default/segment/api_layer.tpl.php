<?php
echo '<?php' . PHP_EOL;
echo $data['action'];
echo '?>' . PHP_EOL
?>
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
    <link href='libs/bootstrap/3.3.6/css/bootstrap.min.css' rel="stylesheet">
    <link href='css/default-theme.css' rel="stylesheet">
    <script src='libs/jquery/1.11.1/jquery.min.js'></script>
    <script src='libs/bootstrap/3.3.6/js/bootstrap.min.js'></script>
    <script src='libs/bootstrap-validator/0.11.8/validator.min.js'></script>
</head>
<body>

<div class="navbar navbar-default navbar-static-top" role="navigation">
    <?php echo $data['head'] ?>
</div>

<div class="commonWrap" style="display: none;text-align: center">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-heading">
                        <h2>公共参数设置</h2>
                    </div>
                    <div class="panel-body">
                        <form class="form-inline" method="post">
                            <div class="form-group">
                                <label for="platform">platform</label>
                                <input type="text" class="form-control" id="platform"
                                       value="<?php echo '<?php echo $_COOKIE["platform"] ?>' ?>" name="platform"
                                       placeholder="平台">
                            </div>
                            <div class="form-group">
                                <label for="channel">channel</label>
                                <input type="text" class="form-control" id="channel"
                                       value="<?php echo '<?php echo $_COOKIE["channel"] ?>' ?>" name="channel"
                                       placeholder="渠道">
                            </div>
                            <div class="form-group">
                                <label for="version">version</label>
                                <input type="text" class="form-control" id="version"
                                       value="<?php echo '<?php echo $_COOKIE["version"] ?>' ?>" name="version"
                                       placeholder="版本号">
                            </div>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mainWrap">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <?php echo $data['nav'] ?>
            </div>
            <div class="col-md-9">
                <?php echo $data['main'] ?>
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

        $('#commonWrapSwitch').click(function () {
            $('.commonWrap').toggle();
            $('.mainWrap').toggle();
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
