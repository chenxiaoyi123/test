<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<head>
    <meta charset="UTF-8">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">

    <link href="<?php echo ($public_url); ?>/Css/css.css?v=2.4" rel="stylesheet" />
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
    <title>个人中心</title>
</head>
<body>
<div class="un-title">
<img src="<?php echo ($headimgurl); ?>" class="un-titpic" />
   <input  class="username" style="background-color:transparent;border:none;font-size:0.28rem;color:#fff"  disabled value="<?php echo ($nickname); ?>" />
    <input  class="usernumber"  style="background-color:transparent;border:none ;font-size:0.28rem;color:#fff"  disabled value="<?php echo ($phone_num); ?> "/>
</div>
<div class="un-inplist">
    <p class="plist" id="p1" onclick="window.open('<?php echo U("home/UserInfo/accountManage","",false,true);?>','_self')"><img src="<?php echo ($public_url); ?>/Image/icon.png" width="22px" style="position: relative;left: -0.2rem; margin-top: 2px;"/>账号管理 </p>
</div>
</body>
</html>