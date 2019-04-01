<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
        <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <script src="/Public/Js/112js.js"></script>
        <link rel="stylesheet" href="/Public/Css/112css.css">
        <title>存费送费</title>
    </head>

    <body>
        <div class="endbg">
            <img src="/Public/Image/112img/iconwc.png" class="endimg" />
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="ordermess">
                    <p>订单详情</p>
                </div>
                <div class="endtit">
                    <img src="/Public/Image/112img/icondd.png" width="6%" />
                    <span><?php echo ($vo["package_name"]); ?></span>
                </div>
                <div class="endcontent">
                    <p>订单号<span><?php echo ($vo["orderno"]); ?></span></p>
                    <p>充值eSIM号<span><?php echo ($vo["phone_num"]); ?></span></p>
                    <p>下单时间<span><?php echo ($vo["ordertime"]); ?></span></p>
                    <p>充值金额<span><?php echo ($vo["amount"]); ?></span></p>
                </div>
                <p class="allmoney">总计：<span style="color:rgb(18,149,219)"><?php echo ($vo["amount"]); ?>元</span></p><?php endforeach; endif; else: echo "" ;endif; ?>            
        </div>
    </body>

</html>