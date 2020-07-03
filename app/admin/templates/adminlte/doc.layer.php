<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= isset($title) ? $title : '' ?></title>
    <meta name="Keywords" content="<?= isset($keywords) ? $keywords : ''; ?>"/>
    <meta name="Description" content="<?= isset($description) ? $description : ''; ?>"/>
    <link href="<?= $this->res('libs/bootstrap/3.3.7/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/font-awesome/4.7.0/css/font-awesome.min.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/jquery/jquery.jsonview.min.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/layer/theme/default/layer.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/highlight/styles/default.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('libs/highlight/styles/github.css') ?>" rel="stylesheet">
    <link href="<?= $this->res('css/doc-default-theme.css') ?>" rel="stylesheet">

    <script src="<?= $this->res('libs/jquery/3.2.1/jquery.min.js') ?>"></script>
    <script src="<?= $this->res('libs/jquery/jquery.jsonview.min.js') ?>"></script>
    <script src="<?= $this->res('libs/highlight/highlight.pack.js') ?>"></script>
    <script src="<?= $this->res('libs/bootstrap/3.3.7/js/bootstrap.min.js') ?>"></script>
    <script src="<?= $this->res('libs/bootstrap-validator/0.11.8/validator.min.js') ?>"></script>
    <script src="<?= $this->res('libs/clipboard/clipboard.min.js') ?>"></script>
    <script src="<?= $this->res('libs/layer/layer.js') ?>"></script>
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid navContainer">
        <div class="navbar-header">
            <button id="collapseBtn" type="button" class="navbar-toggle">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php $this->docInfo() ?>
        </div>
        <div class="navbar-collapse collapse">
            <?php $this->genCommonParams() ?>
            <?php $this->genApiServerList() ?>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="<?= $this->url('doc:generator') ?>" target="_blank">代码生成</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    function getTestCase(groupKey, id) {
        var hashData = getHashData()
        if (hashData.category !== groupKey) {
            apiClassList(groupKey);
        }

        var lid = layer.load();
        $.get('<?= $this->url('doc:makeTestForm') ?>', {'id': id}, function (d) {
            showFormOrTestCase(true, d)
            layer.close(lid)
        })

        window.location.hash = '!' + groupKey + '/' + id
    }

    function showFormOrTestCase(showForm, content) {
        if (showForm) {
            $('#testForm').show()
            if (content) {
                $('#testForm').html(content)
            }

            $('#contentContainer').hide()
        } else {
            $('#testForm').hide()
            $('#contentContainer').show()
        }
    }

    function getHashData() {
        var localHash = window.location.hash.substring(2);
        if (localHash) {
            var hashData = localHash.split('/')
            return {"category": hashData[0], "id": hashData[1] ? hashData[1] : null}
        }

        return {};
    }

    function apiClassList(className) {
        var classID = 'category' + className, target = $('#' + classID);
        target.toggle();
        if (target.is(':visible')) {
            target.addClass('current');
            var hData = getHashData(), hashData = '';
            if (hashData.id) {
                hashData = className + '/' + hData.id
            } else {
                hashData = className
            }
            window.location.hash = '!' + hashData;
        } else {
            target.removeClass('current');
            window.location.hash = '';
        }

        // target.parent().addClass("current").siblings().removeClass("current");
        $('.menu-list').each(function () {
            var id = $(this).attr('id');
            if (id !== classID) {
                $('#' + id).removeClass('.current').hide();
            }
        });
    }
</script>
<div class="mainContainer">
    <div id="leftContainerWrap">
        <div class="leftContainer navbar-collapse collapse">
            <div class="menuContainer scroll">
                <?= $this->docCategory() ?>
            </div>
        </div>
    </div>
    <div class="rightContainer">
        <div id="testForm"></div>
        <div id="contentContainer" class="contentContainer">
            <?= $this->docApiList() ?>
        </div>
    </div>
</div>

<div id="goTop"><i class="glyphicon glyphicon-circle-arrow-up"></i></div>
<div id="fold" status="0"><i class="glyphicon glyphicon-info-sign"></i></div>

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
<div id="mask"></div>
<script>
    $(function () {
        var docId = '<?= $this->e($this->data, 'doc_id', '') ?>', hashData = getHashData();
        if (hashData.category) {
            apiClassList(hashData.category)
        }

        $('#collapseBtn').on('click', function () {
            $('#leftContainerWrap').show();
            $('#mask').toggle();

            $('.leftContainer').toggle();
        });

        var c = new ClipboardJS('.request-action', {
            text: function (a) {
                return $(a).attr('value');
            }
        });

        c.on('success', function (a) {
            layer.tips('已复制', a.trigger, {
                tips: [3, '#666'],
                time: 1000
            });
            a.trigger.select();
        });

        $('.change-server-flag').on('click', function () {
            $.get('<?= $this->url('doc:changeApiServer') ?>', {
                'doc_id': $(this).attr('doc_id'),
                'sid': $(this).attr('sid')
            }, function (d) {
                window.location.reload();
            });
        });

        $(document).on("click", ".gen-code-flag", function () {
            var apiId = $(this).attr('api-id'),
                fParams = $(this).closest('form').serialize();

            window.location.href = '<?= $this->url('doc:curlRequest') ?>' + '?' + fParams + '&id=' + apiId;
        });

        $('#fold').click(function () {
            if ($(this).attr('status') == 0) {
                showFormOrTestCase(false)
                $('.cache-panel').hide()
                $('.action-form').hide()
                $('.menu-list').hide()
                $(this).attr('status', 1)
            } else {
                apiClassList(true);
                $(this).attr('status', 0);
            }
        });

        $('#goTop').click(function () {
            $("html, body").animate({scrollTop: 0}, 200);
        });

        $('#commonModalSwitch').click(function () {
            $('#commonModal').modal('toggle');
        });

        $('#menuSwitch').click(function () {
            $('#leftContainerWrap').toggle();
        });

        $('pre code').each(function (i, block) {
            hljs.highlightBlock(block);
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
