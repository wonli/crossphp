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
    <link href="<?php echo $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?php echo $this->res('css/doc-default-theme.css') ?>" rel="stylesheet">

    <link href="<?php echo $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">

    <link href="<?php echo $this->res('libs/highlight/styles/default.css') ?>" rel="stylesheet">
    <link href="<?php echo $this->res('libs/highlight/styles/github.css') ?>" rel="stylesheet">
    <script src="<?php echo $this->res('libs/highlight/highlight.pack.js') ?>"></script>

    <script src="<?php echo $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/bootstrap/3.3.7/js/bootstrap.min.js') ?>"></script>
    <script src="<?php echo $this->res('libs/bootstrap-validator/0.11.8/validator.min.js') ?>"></script>
</head>
<body>

<div class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
    <?php $this->docHeader() ?>
</div>

<div class="mainWrap">
    <div class="container mainContainer">
        <div class="row">
            <?php $this->docData() ?>
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
            <form class="form-horizontal"
                  action="<?= $this->url('doc:saveCommonParams', array('doc_id' => $this->data['doc_id'])) ?>"
                  method="post">
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
                                <div class="col-xs-3">表单字段名</div>
                                <div class="col-xs-6">值</div>
                                <div class="col-xs-3">名称</div>
                            </div>
                            <?php $this->formParams('global_params') ?>
                        </div>
                        <div class="tab-pane fade" id="headerParams">
                            <div class="form-group">
                                <div class="col-xs-3">参数名</div>
                                <div class="col-xs-6">值</div>
                                <div class="col-xs-3">名称</div>
                            </div>
                            <?php $this->formParams('header_params') ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" class="hash-flag" name="hash">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存配置</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade fadeIn" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
     id="codeSegmentModal"></div>

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
        var docId = '<?= $this->e($this->data, 'doc_id', '') ?>';
        var hashContent = window.location.hash.substring(2);
        if (hashContent) {
            hashContent = decodeURI(hashContent);
            $('.hash-flag').val(hashContent);
            showContent(hashContent);
        }

        $('#collapseBtn').on('click', function () {
            $('.leftContainer').toggle();
        });

        $('.request-action').on('click', function () {
            $(this).select();
        });

        $('.change-server-flag').on('click', function () {
            $.get('<?= $this->url('doc:changeApiServer') ?>', {
                'doc_id': $(this).attr('doc_id'),
                'sid': $(this).attr('sid')
            }, function (d) {
                window.location.reload();
            });
        });

        $('.gen-code-flag').on('click', function () {
            var f = $(this).closest('form'), action = f[0].action, method = f[0].method,
                fParams = f.serializeArray(),
                params = {};
            if (fParams.length > 0) {
                for (var i in fParams) {
                    if (fParams.hasOwnProperty(i)) {
                        var d = fParams[i];
                        params[d.name] = d.value;
                    }
                }
            }

            var p = {params: params, method: method, action: action, doc_id: docId};
            $.post('<?= $this->url('doc:codeSegment') ?>', p, function (d) {
                $('#codeSegmentModal').html(d).modal('toggle');
            });
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
