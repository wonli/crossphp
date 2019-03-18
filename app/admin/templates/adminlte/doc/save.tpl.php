<form role="form" method="post">
    <div class="row">
        <div class="col-md-9">
            <div class="box">
                <div class="box-body">
                    <div class="form-group">
                        <label>名称</label>
                        <input type="text" name="name" id="name" value="<?= $this->e($data, 'name', '') ?>"
                               class="form-control" placeholder="请填写名称">
                    </div>

                    <div class="form-group">
                        <label>DocToken</label>
                        <input type="text" id="docToken" name="doc_token"
                               value="<?= $this->e($data, 'doc_token', '') ?>"
                               class="form-control" placeholder="请填写获取内容的Token">
                    </div>

                    <div class="form-group">
                        <label>公共参数</label>
                        <a href="javascript:void(0)" t="global" class="addParams" style="margin-left:20px">+ 增加参数</a>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tbody id="globalParams">
                                    <?php
                                    if (!empty($data['global_params'])) {
                                        foreach ($data['global_params'] as $key => $name) {
                                            $this->makeParamsNode(array(
                                                't' => 'global',
                                                'key' => $key,
                                                'name' => $name,
                                            ));
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="col-xs-1">
                                                <input type="text" name="global[1][key]" class="form-control"
                                                       placeholder="参数">
                                            </td>
                                            <td class="col-xs-3">
                                                <input type="text" name="global[1][name]" class="form-control"
                                                       placeholder="参数名">
                                            </td>
                                            <td class="col-xs-1">
                                                <a class="btn btn-warning del-node-flag">删除</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Header参数(CURL时生效)</label>
                        <a href="javascript:void(0)" t="header" class="addParams" style="margin-left:20px">+ 增加参数</a>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tbody id="headerParams">
                                    <?php
                                    if (!empty($data['header_params'])) {
                                        foreach ($data['header_params'] as $key => $name) {
                                            $this->makeParamsNode(array(
                                                't' => 'header',
                                                'key' => $key,
                                                'name' => $name,
                                            ));
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="col-xs-1">
                                                <input type="text" name="header[1][key]" class="form-control"
                                                       placeholder="参数">
                                            </td>
                                            <td class="col-xs-3">
                                                <input type="text" name="header[1][name]" class="form-control"
                                                       placeholder="参数名">
                                            </td>
                                            <td class="col-xs-1">
                                                <a class="btn btn-warning del-node-flag">删除</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>服务器地址</label>
                        <a href="javascript:void(0)" id="addDevServer" style="margin-left:20px">+ 增加调试服务器</a>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th class="col-xs-1">默认</th>
                                        <th class="col-xs-2">名称</th>
                                        <th class="col-xs-6">服务器地址</th>
                                        <th class="col-xs-3">获取数据</th>
                                    </tr>
                                    </thead>

                                    <tbody id="devServer">

                                    <?php
                                    if (!empty($data['servers'])) {
                                        foreach ($data['servers'] as $s) {
                                            $this->makeDevServerNode($s);
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td>
                                                <label>
                                                    <input data-on="是" data-off="否" type="checkbox"
                                                           data-toggle="toggle" data-onstyle="success"
                                                           data-offset="danger"
                                                           name="dev[1][is_default]">
                                                </label>
                                            </td>
                                            <td>
                                                <input type="text" name="dev[1][server_name]"
                                                       class="server_name form-control"
                                                       placeholder="名称">
                                            </td>
                                            <td>
                                                <input type="text" name="dev[1][api_addr]" class="api_addr form-control"
                                                       placeholder="请填写服务器地址">
                                            </td>
                                            <td>
                                                <input type="hidden" name="dev[1][cache_name]" class="cache_name"
                                                       value="">
                                                <input type="hidden" name="dev[1][cache_at]" class="cache_at" value="">
                                                <input type="hidden" name="dev[1][user]" class="user" value="">
                                                <a class="btn btn-primary get-data-flag">获取数据</a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button class="btn btn-primary">提交</button>
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
                server_name: server_name,
                api_addr: api_addr,
                doc_token: doc_token
            }, function (d) {
                if (!d.status) {
                    layer.msg('返回数据出错, 请联系技术部');
                } else if (d.status != 1) {
                    layer.msg(d.message);
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

        $('.addParams').on('click', function () {
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