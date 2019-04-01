<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<head>
    <meta charset="UTF-8">
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">

    <link href="<?php echo ($public_url); ?>/CSS/css.css?v=2.5" rel="stylesheet" />
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

     </script>
    <title>扫码关注</title>
</head>
<body>
<div class="fous">
<img src="<?php echo ($public_url); ?>/Image/code.jpg" width="36%" class="erweima"/>
</div>
</body>
</html>