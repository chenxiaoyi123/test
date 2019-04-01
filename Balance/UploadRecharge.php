<?php
/*
 * 话费充值：上传充值对账文件
 */
include_once 'curl.php';
$url = 'http://localhost:20012/Home/Balance/uploadRecharge.html';
http_curl($url);