$(function () {
    //顶部居中的确认弹窗
    $('.confirm-href-flag').on('click', function () {
        pop.confirm($(this).attr('title'), {
            btn: {yes: '确认', no: '取消'},
            actions: {
                yes: function () {
                    location.href = $(this).attr('action') || location.href;
                }
            }
        })
    });

    //顶部居中提示信息
    $('.pop-alert-flag').on('click', function () {
        pop.alert($(this).attr('title'));
    })
});
