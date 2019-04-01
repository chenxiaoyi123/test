<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <script src="<?php echo ($public_url); ?>/layer-v3.0.3/layer/layer.js"></script>
    <link href="<?php echo ($public_url); ?>/CSS/css.css?v=2.4" rel="stylesheet" />
    <script>
        (function (doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function () {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    if (clientWidth >= 640) {
                        docEl.style.fontSize = '100px';
                    } else {
                        docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
                    }
                };

            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);
    </script>
    <script>
        $(function () {

            $("#getnum").on("click", function () {             
                    var telnum = $.trim($("#mobile").val());
                    if(telnum == ''){
                        layer.msg('请先输入手机号', function(){
                        });   
                        return;
                    }else if(!(/^1[345678]\d{9}$/.test(telnum))){ 
                        layer.msg('请输入正确的手机号', function(){
                         });
                         return;
                    }
                    $("#getnum").css("background", "rgb(240,240,240)");
                    $("#getnum").css("color", "#666");
                    $("#getnum").css("border", "none");

                    buttonCountdown($(this), 1000 * 60 * 1, "ss");
                    setTimeout(function () { $("#getnum").css({ "background": "#fff", "color": "rgb(20,157,241)", "border": "rgb(20,157,241) solid 1px" }) }, 60100);
            });
            
            function buttonCountdown($el, msNum, timeFormat) {

                var text = $el.data("text") || $el.val(),
                    timer = 0;
                $el.prop("disabled", true).addClass("disabled")
                    .on("bc.clear", function () {
                        clearTime();
                    });
                var telnum = $.trim($("#mobile").val());
                $.getJSON("<?php echo U('home/index/sendSMS','',false,true);?>",{telnum:telnum},function(res){
                    if(res.status !=1){
                        layer.msg(res.msg, function(){

                        });                        
                    }
                });

                (function countdown() {
                    var time = showTime(msNum)[timeFormat];
                    $el.val(time + '后可重新获取');
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
                    $el.prop("disabled", false).removeClass("disabled").val(text);
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
        })
    </script>
    <?php if(empty($phone_num)): ?><title>手机绑定</title><?php endif; ?>
    <?php if(!empty($phone_num)): ?><title>解绑手机号</title><?php endif; ?>
    
</head>

<body>
    <div class="title">
        <img class="titpic" src="<?php echo ($headimgurl); ?>" />
<form name="mobileform">
        <?php if(empty($phone_num)): ?><input type="number" class="inputlist number-unband " placeholder="请输入手机号" name="mobile" id="mobile" maxLength="11" /><?php endif; ?>
        <?php if(!empty($phone_num)): ?><input type="number" class="inputlist number-unband " placeholder="请输入手机号" name="mobile" id="mobile" maxLength="11" readonly  value="<?php echo ($phone_num); ?>"/><?php endif; ?>    
        <input type="number" class="inputlist call-unband " placeholder="请输入验证码" id='num'/>
        <input type="button" style="position:relative;top:-1.45rem;text-align:center;" class="button" id="getnum" value='获取验证码'/>
        <!--<input type="button" name="mobileform" onclick="bandshuju()" style="position:relative;top:-1.5rem" class="band" value="绑 定"/>-->
        <?php if(empty($phone_num)): ?><INPUT name="action" type="hidden" value=mobile> <INPUT class=band name=B1 type='button' id='bind' value="绑 定" style="position:relative;top:-1.5rem"><?php endif; ?>        
        <?php if(!empty($phone_num)): ?><INPUT name="action" type="hidden" value=mobile> <INPUT class=band name=B1 type='button' id='bind' value="解 绑" style="position:relative;top:-1.5rem"><?php endif; ?>         
</form>
    </div>
    <script>
            
    $('#bind').click(function(){    
            var $bind = $(this);        
            var telnum = $.trim($("#mobile").val());
            var num = $.trim($("#num").val());
            if(telnum == ''){
                 layer.msg('请先输入手机号', function(){
                 });   
                 return;
            }else if(!(/^1[345678]\d{9}$/.test(telnum))){ 
                layer.msg('请输入有效的手机号', function(){
                 });
                 return;
             }else if(num == ''){
                layer.msg('请先输入验证码', function(){
                });
                return;
            }else if(!(/^\d{6}$/.test(num))){ 
                layer.msg('请输入有效的验证码', function(){
                 });
                 return;
            }        
            <?php if(isset($phone_num)){ echo 'var url = \''.U('home/index/verifyCode/type/unBind','',false,true).'\';'; }else{ echo 'var url = \''.U('home/index/verifyCode/type/bind','',false,true).'\';'; } ?>
            $.getJSON(url,{telnum:telnum,num:num},function(res){
                if(res.status != 1){
                    layer.msg(res.msg, function(){
                    });
                }else if(res.status == 1){
                    $bind.attr('disabled',true);                      
                    layer.msg(res.msg, {icon:1},function(){
                        var url = "<?php echo U('home/index/index','',false,true);?>";   
                        setTimeout("location.href='"+url+"'",300);                           
                    });
                }
            }) 
     })     
    </script>
</body>

</html>