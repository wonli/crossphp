<form role="form" class="form-horizontal" method="post">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body" style="margin-top: 30px">
                    <div class="form-group">
                        <label for="name" class="col-lg-3 col-sm-2 control-label">名称</label>
                        <div class="col-lg-5 col-sm-8">
                            <input type="text" name="name" id="name" value="<?= $this->e($data, 'name', '') ?>"
                                   class="form-control" placeholder="请填写名称">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="docToken" class="col-lg-3 col-sm-2 control-label">接口签名</label>
                        <div class="col-lg-5 col-sm-8">
                            <input type="text" id="docToken" name="doc_token"
                                   value="<?= $this->e($data, 'doc_token', '') ?>"
                                   class="form-control" placeholder="请填写获取内容的Token">
                            <p class="help-block">在获取接口列表时的签名，请在对应app的init配置文件中查看</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-sm-2 control-label">公共参数</label>
                        <div class="col-lg-6 col-sm-8">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="col-xs-2">参数</th>
                                    <th class="col-xs-4">参数名</th>
                                    <th style="min-width: 105px">操作</th>
                                </tr>
                                </thead>
                                <tbody id="globalParams">
                                <?php
                                if (!empty($data['global_params'])) {
                                    $i = 0;
                                    foreach ($data['global_params'] as $key => $name) {
                                        $this->makeParamsNode([
                                            't' => 'global',
                                            'key' => $key,
                                            'name' => $name,
                                            'i' => $i++
                                        ]);
                                    }
                                } else {
                                    $this->makeParamsNode(['t' => 'global', 'i' => 0]);
                                }
                                ?>
                                </tbody>
                            </table>
                            <p class="help-block">每个请求必传的公共参数，参数名用于页面显示</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-sm-2 control-label">Header参数</label>
                        <div class="col-lg-6 col-sm-8">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="col-xs-2">参数</th>
                                    <th class="col-xs-4">参数名</th>
                                    <th style="min-width: 105px">操作</th>
                                </tr>
                                </thead>
                                <tbody id="headerParams">
                                <?php
                                if (!empty($data['header_params'])) {
                                    $i = 0;
                                    foreach ($data['header_params'] as $key => $name) {
                                        $this->makeParamsNode([
                                            't' => 'header',
                                            'key' => $key,
                                            'name' => $name,
                                            'i' => $i++
                                        ]);
                                    }
                                } else {
                                    $this->makeParamsNode(['t' => 'header', 'i' => 0]);
                                }
                                ?>
                                </tbody>
                            </table>
                            <p class="help-block">需要通过HTTP请求的header来传参时指定，CURL时生效</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 col-sm-2 control-label">部署服务器列表</label>
                        <div class="col-lg-7 col-sm-9">
                            <div class="row">
                                <div class="col-md-12" style="margin-bottom: 10px;">
                                    <a href="javascript:void(0)" id="addDevServer" class="btn btn-success">
                                        <i class="fa fa-plus"></i>
                                        增加部署服务器
                                    </a>
                                </div>

                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="col-xs-1">默认</th>
                                            <th class="col-xs-3">名称</th>
                                            <th class="col-xs-6">服务器地址</th>
                                            <th style="min-width: 170px">操作</th>
                                        </tr>
                                        </thead>

                                        <tbody id="devServer">
                                        <?php
                                        if (!empty($data['servers'])) {
                                            foreach ($data['servers'] as $s) {
                                                $this->makeDevServerNode($s);
                                            }
                                        } else {
                                            $this->makeDevServerNode();
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="form-group">
                        <label for="submit" class="col-lg-3 col-sm-2"></label>
                        <div class="col-sm-6">
                            <button id="submit" class="btn btn-primary">保存</button>
                            <a href="<?= $this->url('doc') ?>" class="reload btn btn-danger">取消</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(function () {
        var name = $('#name'), docToken = $('#docToken');
        $("form").submit(function () {
            if (!name.val().trim()) {
                layer.msg('请填写名称');
                return false;
            }

            if (!docToken.val().trim()) {
                layer.msg('doc token不能为空');
                return false;
            }

            return true;
        });

        $(document).on('click', '.get-data-flag', function () {
            var vm = $(this),
                parent = vm.closest('tr'),
                server_name = parent.find('.server_name').val().trim(),
                api_addr = parent.find('.api_addr').val().trim(),
                doc_token = docToken.val().trim();

            if (!doc_token) {
                layer.msg('DocToken不能为空');
                return;
            }

            if (!server_name) {
                layer.msg('服务器名称不能为空');
                return;
            }

            if (!api_addr) {
                layer.msg('服务器地址不能为空');
                return;
            }

            $.post('<?= $this->url('doc:initApiData') ?>', {
                api_addr: api_addr,
                doc_token: doc_token
            }, function (d) {
                console.log(d);
                if (!d.status) {
                    layer.msg('返回数据出错, 请联系技术部');
                } else if (d.status !== 1) {
                    var msg = '';
                    if (typeof d.message !== 'string') {
                        msg = JSON.stringify(d.message, null, 2);
                    } else {
                        msg = d.message;
                    }

                    layer.msg(msg, {
                        time: 0,
                        shade: 0.5,
                        closeBtn: 2
                    });
                } else {
                    layer.msg('获取数据成功');
                    vm[0].innerHTML = '更新数据';
                    var vTd = vm.closest('td');
                    vTd.find('.cache_name').val(d.data.cache_name);
                    vTd.find('.cache_at').val(d.data.cache_at);
                    vTd.find('.user').val(d.data.user);
                }
            })
        });

        $(document).on('click', '.del-node-flag', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '.addParams', function () {
            var t = $(this).attr('t'), container = '#' + t + 'Params';
            $.get('<?= $this->url('doc:makeParamsNode') ?>', {t: t}, function (d) {
                $(container).append(d);
            });
        });

        $('#addDevServer').on('click', function () {
            $.get('<?= $this->url('doc:makeDevServerNode') ?>', function (d) {
                $('#devServer').append(d);
                $('.newToggle').bootstrapToggle();
            });
        })
    })
</script>