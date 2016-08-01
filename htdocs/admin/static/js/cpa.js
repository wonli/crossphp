var hashData = {
    read: function () {
        var hashData = document.location.hash;
        hashData = hashData.slice(1);
        var tempArr = new Array();
        tempArr = hashData.split(",");
        var value = 0;
        var object = {};
        for (var i = 0; i < tempArr.length; i++) {
            if (!tempArr[i])
                continue;
            var cutNum = (tempArr[i]).indexOf("=");//参数分割符号
            var menuName = (tempArr[i]).substr(0, cutNum);
            object[menuName] = (tempArr[i]).substr(cutNum + 1);
        }
        return object;
    },
    write: function (a, v) {
        var hd = hashData.read();
        var hashStr = "#";
        var flag = 0;
        for (var attr in hd) {
            if (a == attr) {
                if (v != null) {
                    hashStr = hashStr + attr + "=" + v + ",";
                }
                flag = 1;
            }
            else {
                hashStr = hashStr + attr + "=" + hd[attr] + ",";
            }
        }
        if (flag == 0 && v != null) {
            hashStr = hashStr + a + "=" + v + ",";
        }
        document.location.hash = hashStr;
    }
};

$.jheartbeat = {
    options: {delay: 10000},
    beatfunction: function () {
    },
    timeoutobj: {id: -1},

    set: function (options, onbeatfunction) {
        if (this.timeoutobj.id > -1) {
            clearTimeout(this.timeoutobj);
        }
        if (options) {
            $.extend(this.options, options);
        }
        if (onbeatfunction) {
            this.beatfunction = onbeatfunction;
        }

        this.timeoutobj.id = setTimeout("$.jheartbeat.beat();", this.options.delay);
    },

    beat: function () {
        this.timeoutobj.id = setTimeout("$.jheartbeat.beat();", this.options.delay);
        this.beatfunction();
    }
};

function timer(func, interval) {
    $.jheartbeat.set({delay: interval}, func);
}

var pop = {
    getOption: function () {
        return {title: '消息', lock: true, background: '#333', fixed: true, opacity: 0.17, id: 'global-pop-id'}
    },
    alert: function (type, msg, time) {
        time = time ? time : 2;
        $.dialog.tips(type, msg, time);
    },
    open: function (url, title) {
        $.get(url, function (data) {
            var option = pop.getOption();
            if (title) {
                option.title = title;
            }

            option.content = data;
            $.dialog(option);
        });
    },
    iFrame: function (url, config) {
        var option = $.extend(pop.getOption(), config);
        $.dialog.open(url, option);
    },
    display: function (data, title) {
        var option = pop.getOption();
        option.id = 'display';
        option.title = title || '消息';
        option.content = data;
        $.dialog(option);
    },
    confirm: function (t, s) {
        var option = pop.getOption();
        option.content = t || '确认执行此操作吗?';
        option.ok = s;
        option.cancelVal = '关闭';
        option.cancel = true;
        $.dialog(option);
    },
    delayTips: function (t, is, ref) {
        var option = pop.getOption();
        option.r = ref || true;
        option.content = t || '操作成功';
        option.init = function () {
            var that = this, i = is || 3;
            var fn = function () {
                that.title(i + '秒后关闭');
                !i && that.close();
                i--;
            };
            timer = setInterval(fn, 1000);
            fn();
        };
        option.close = function () {
            clearInterval(timer);
            if (option.r) {
                window.location.reload();
            }
        };
        $.dialog(option);
    },
    close: function () {
        var list = art.dialog.list;
        for (var i in list) {
            list[i].close();
        }
    }
};

$(function () {
    $('.confirm-href-flag').on('click', function () {
        var t = $(this).attr('title'), a = $(this).attr('action');
        pop.confirm(t, function () {
            location.href = a;
        });
    });

    $('.pop-alert-flag').on('click', function(){
        pop.alert($(this).attr('title'));
    });
});
