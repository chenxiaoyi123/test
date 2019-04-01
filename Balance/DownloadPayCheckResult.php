<?php
/*
 * 支付中心：下载支付对账结果文件
 */
include_once 'curl.php';
$url = 'http://localhost:20012/Home/Balance/downloadPayCheckResult.html';
http_curl($url);



