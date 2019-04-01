(function (doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            var clientWidth = docEl.clientWidth;
            if (!clientWidth) return;
            if(clientWidth>=640){
                docEl.style.fontSize = '100px';
            }else{
                docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
            }
        };

    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener('DOMContentLoaded', recalc, false);
})(document, window);


function buttonCountdown($el, msNum, timeFormat) {
    var text = $el.data("text") || $el.text(),
        timer = 0;
    $el.prop("disabled", true).addClass("disabled")
        .on("bc.clear", function () {
            clearTime();
        });

    (function countdown() {
        var time = showTime(msNum)[timeFormat];
        $el.text(time);
        if (msNum <= 0) {
            msNum = 0;
            clearTime();
        } else {
            msNum -= 1000;
            timer = setTimeout(arguments.callee, 1000);
        }
    })();

    function clearTime() {
        clearTimeout(timer);
        $el.prop("disabled", false).removeClass("disabled").text(text);
    }

    function showTime(ms) {
        var d = Math.floor(ms / 1000 / 60 / 60 / 24),
                h = Math.floor(ms / 1000 / 60 / 60 % 24),
                m = Math.floor(ms / 1000 / 60 % 60),
                s = Math.floor(ms / 1000 % 60),
                ss = Math.floor(ms / 1000);

        return {
            d: d + "天",
            h: h + "小时",
            m: m + "分",
            ss: ss + "秒",
            "d:h:m:s": d + "天" + h + "小时" + m + "分" + s + "秒",
            "h:m:s": h + "小时" + m + "分" + s + "秒",
            "m:s": m + "分" + s + "秒"
        };
    }

    return this;
}
$(function(){
    var radioclick=1;
    $("#radio").click(function(){
        if(radioclick%2==0){
            $(this).prop("checked",false);
        }
        radioclick++;
    });
})