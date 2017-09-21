$(function () {
    $('.confirm-href-flag').on('click', function () {
        var t = $(this).attr('title'), a = $(this).attr('action');
        layer.msg(t, {
            time: 0,
            btn: ['确定', '取消'],
            yes: function (index) {
                layer.close(index);
                location.href = a;
            }
        });
    });

    $('.pop-alert-flag').on('click', function () {
        layer.msg($(this).attr('title'));
    });
});
