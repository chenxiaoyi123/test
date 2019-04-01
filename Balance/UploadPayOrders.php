<?php
/*
 * 支付中心：上传支付对账文件
 */
include_once 'curl.php';
$url = 'http://localhost:20012/Home/Balance/uploadPayOrders.html';
http_curl($url);