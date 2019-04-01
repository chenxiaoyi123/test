<?php
/*
 * 支付中心：获取T日订购失败的订单笔数
 */
include_once 'curl.php';
$url = 'http://localhost:20012/Home/Balance/checkPurchaseFail.html';
http_curl($url);
