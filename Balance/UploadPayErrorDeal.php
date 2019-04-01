<?php
/*
 * 支付中心：上传支付差异回盘文件
 */
include_once 'curl.php';
$url = 'http://localhost:20012/Home/Balance/uploadPayErrorDeal.html';
http_curl($url);