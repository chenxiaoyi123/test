<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<head>
    <meta charset="UTF-8">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">

    <link href="<?php echo ($public_url); ?>/Css/css.css?v=2.5" rel="stylesheet" />
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
    <title>账号管理</title>
</head>
<body>
    <div class="ma-list" id="phone_num">
        <img src="<?php echo ($public_url); ?>/Image/m.png" width="11%" class="ma-image" /><span style="margin-right: 1.0em;">手机号码</span>
            <span id="sub_phone_num">
                <?php if(empty($phone_num)): ?>未绑定
                <?php else: echo ($phone_num); endif; ?>
            </span>
    </div>
    
    <div class="ma-list c" id="eSIM">
        <img src="<?php echo ($public_url); ?>/Image/eSIM.png" width="11%" class="ma-image" /><span style="margin-right: 1.0em;">出门问问手表账号</span>
            <span id="sub_eSIM" >
                <?php if(empty($eSIM)): ?>未绑定
                    <?php else: echo ($eSIM); endif; ?>
            </span>        
    </div>
    <div class="ma-list " id="hw">
        <img src="<?php echo ($public_url); ?>/Image/hwm.png" width="11%" class="ma-image" /><span style="margin-right: 1.0em;">华为手表账号</span>
            <span id="sub_hw">
                <?php if(empty($hw)): ?>未绑定
                <?php else: echo ($hw); endif; ?>
            </span>         
    </div>
</body>
<script>
    $(function(){
        $("#phone_num").click(function(){
            location.href = "<?php echo U('home/index/showAccount/accountType/phoneNum','',false,true);?>";
        })
        
        $("#eSIM").click(function(){
            location.href = "<?php echo U('home/index/showAccount/accountType/esim','',false,true);?>";           
        })
        
        $("#hw").click(function(){
            location.href = "<?php echo U('home/index/showAccount/accountType/hw','',false,true);?>";        
        })        
    })
</script>
</html>