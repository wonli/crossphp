<?php echo $data['action']; ?>
<?php echo $data['do_action'] ?>
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
    <link href="<?php echo $data['asset_server'] ?>libs/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $data['asset_server'] ?>css/default-theme.css" rel="stylesheet">
    <script src="<?php echo $data['asset_server'] ?>libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="<?php echo $data['asset_server'] ?>libs/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="<?php echo $data['asset_server'] ?>libs/bootstrap-validator/0.11.8/validator.min.js"></script>
</head>
<body>

<div class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
    <?php echo $data['head'] ?>
</div>

<div class="mainWrap">
    <div class="container mainContainer">
        <div class="row">
            <div class="col-md-4">
                <div class="leftContainer navbar-collapse collapse">
                    <?php echo $data['nav'] ?>
                </div>
            </div>
            <div class="col-md-8">
                <div class="rightContainer">
                    <?php echo $data['main'] ?>
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
                    <div class="form-group">
                        <div class="col-sm-3">表单字段名</div>
                        <div class="col-sm-4">值</div>
                        <div class="col-sm-5">名称</div>
                    </div>
                    <?php echo '<?php globalParams($global_config) ?>' ?>
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
