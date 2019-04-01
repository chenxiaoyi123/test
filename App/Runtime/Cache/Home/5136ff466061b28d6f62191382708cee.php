<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <script src="/Public/Js/112js.js"> </script>
    <link rel="stylesheet" href="/Public/Css/112css.css">
    <title>支付成功</title>
<style>
    html,body{
   overflow: hidden;
    }
            a:hover, a:visited, a:link, a:active {
                 text-decoration:none;
                 color:#fff;
                }       
</style>
</head>

<body>
    <div class="p4bg">
            <button class="p4now" onclick="location='<?php echo U("userInfo/showexpensepage","",true,true);?>'">立即查询 </button>
            <span class="p4myorder" onclick="location='<?php echo U("Purchase/allorders","",true,true);?>'">我的订单</span><span class="p4myorder" id="shu">|</span><span class="p4helpnum"><a href="tel:4000210356">客服热线</a></span>
    </div>
</body>

</html>