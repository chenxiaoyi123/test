<?php
define('AppId', 'wx7d0494284af1af9c');
define('AppSecret', 'cb4eaa39f11be828d8241ec877ce7009');
function doGet($url)
{//初始化

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // 执行后不直接打印出来
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // 不从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    return $output;
}
function doPost($url, $post_data, $header = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // 执行后不直接打印出来
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 设置请求方式为post
    curl_setopt($ch, CURLOPT_POST, true);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    // 请求头，可以传数组
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HEADER, $header);
    }
    // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // 不从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
function return_err($data){
   $dataarr=array(
       'status'=>0,
       'msg'=>"$data"
   );
   echo json_encode($dataarr);
   exit();
}
function  return_data($list){
    $dataarr=array(
        'status'=>1,
        'data'=>$list
    );
    echo json_encode($dataarr);
    exit();
}
 function Qrcode($oid)
{//生成二维码
    $access_token = Getaccess_token();
    $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
    $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' . $oid . '}}}';
    $return_data = doPost($url, $data);
    $return_data = json_decode($return_data);
    $ticket_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $return_data->ticket;
    $imageInfo =downloadImageFromWeiXin($ticket_url);
    $filename = $oid . '_' . rand(0, 99999999999) . '.jpg';
    $local_file = fopen('./Uploads/Qrcode_img/' . $filename, 'w');
    if (false !== $local_file) {
        if (false !== fwrite($local_file, $imageInfo['body'])) {
            fclose($local_file);
        }
    }
    return "/Uploads/Qrcode_img/" . $filename;
} //下载二维码到服务器
 function downloadImageFromWeiXin($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    return array_merge(array('body' => $package), array('header' => $httpinfo));
}
function Getaccess_token()//获取微信票据
{
    $appid = AppId;
    $secret = AppSecret;
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
    $json = doGet($url);
    $array = json_decode($json);
    $access_token = $array->access_token;
    return $access_token;
}
function getnonceStr($num = 16)//16位随机数
{
    $nonceStr = "";
    $str = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    for ($i = 0; $i <= $num; $i++) {
        $rand = rand(0, count($str) - 1);
        $nonceStr .= $str[$rand];
    }
    return $nonceStr;
}

function GetRoomname($rid)//获取预订房间名
{
    $room = M('room');
    $roominfo = $room->field('title')->where('id=' . $rid)->find();
    return $roominfo['title'];
}
function GetMemberLevel($lid){//获取会员等级
    $level = M('level');
    $levelinfo = $level->field('name')->where('id=' . $lid)->find();
    return $levelinfo['name'];
}
/**
 * 作用：生成单号
 */
 function GetOrder_no($id,$herad='') {
    $nonceStr = "";
    if(empty($herad)){
        $herad="shcm";
    }
    if($id>0){
        $uid=$id;
    }
    else{
        $uid=0;
    }
    $num=4;
    $str = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    for($i = 0; $i <= $num; $i++){
        $rand = rand(0, count($str));
        $nonceStr .= $str[$rand];
    }
    $order_no=$herad."_".$uid."_".time().$nonceStr;
    return $order_no;
}
//返回当前的毫秒时间戳
function msectime() {
   list($msec, $sec) = explode(' ', microtime());
   $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
   return $msectime;
}

function createNonceStr($length = 16,$allnum = false) {
    if($allnum){
        $chars = "01234567890123456789012345678901234567890123456789890123456789";        
    }else{
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}
function sendSMS($to,$num,$long){
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        $accountSid= '8a48b551532ffdb401533212ce4205c5';

        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        $accountToken= 'c8556f722a804a32b8c572bc5ac87b45';

        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        $appId='aaf98f89532ff0cc015332174b6205dc';

        //请求地址
        //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
        //生产环境（用户应用上线使用）：app.cloopen.com
        $serverIP='app.cloopen.com';


        //请求端口，生产环境和沙盒环境一致
        $serverPort='8883';

        //REST版本号，在官网文档REST介绍中获得。
        $softVersion='2013-12-26';
        
        // 初始化REST SDK
        import('Org.Util.REST');
        $rest = new \REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);

        // 发送模板短信
        $datas = array($num,$long);
        $tempId = 212037;        
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
            return "result error!";
        }
        if($result->statusCode!=0) {
            return "error code :" . $result->statusCode .",error msg :" . $result->statusMsg;
         }else{
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            $dateCreated =  $smsmessage->dateCreated;
            $smsMessageSid = $smsmessage->smsMessageSid;
            return 'success';
        }        
 }
 
/* 
*功能：php完美实现下载远程图片保存到本地 
*参数：文件url,保存文件目录,保存文件名称，使用的下载方式 
*当保存文件名称为空时则使用远程文件原来的名称 
*/ 
function getImage($url,$save_dir='',$filename='',$type=0){ 
    if(trim($url)==''){ 
        return array('file_name'=>'','save_path'=>'','error'=>1); 
    } 
    if(trim($save_dir)==''){ 
        $save_dir='./'; 
    } 
    if(trim($filename)==''){//保存文件名 
        $filename=time().rand(10000,99999).'.png'; 
    } 
    if(!strrpos($save_dir,'/')){ 
        $save_dir.='/'; 
    } 
    //创建保存目录 
    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){ 
        return array('file_name'=>'','save_path'=>'','error'=>5); 
    } 
    //获取远程文件所采用的方法  
     if($type){ 
        $ch=curl_init(); 
        $timeout=5; 
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_HEADER, 0);        
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
        $img=curl_exec($ch); 
        curl_close($ch);      
    }else{ 
        ob_start();  
        readfile($url); 
        $img=ob_get_contents();  
        ob_end_clean();  
    } 
    //$size=strlen($img); 
    //文件大小  
    $fp2=@fopen($save_dir.$filename,'a'); 
    fwrite($fp2,$img); 
    fclose($fp2); 
    unset($img,$url); 
    return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0); 
} 
/*
 * 支持get/post请求
 * $url 请求地址   application/x-www-form-urlencoded   multipart/form-data  application/json
 * $data 数据
 * $header 请求头参数
 */
/*证书请求*/
function curl_post_ssl($url, $xml, $second=30,$aHeader=array())
{
    $ch = curl_init();
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
    //设置header
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //使用证书：cert 与 key 分别属于两个.pem文件
    curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
    curl_setopt($ch,CURLOPT_SSLCERT, 'D:/llwtcert/apiclient_cert.pem');
    curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
    curl_setopt($ch,CURLOPT_SSLKEY, 'D:/llwtcert/apiclient_key.pem');
    curl_setopt($ch,CURLOPT_SSLCERTPASSWD,'1251297501');
    //post提交方式
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    //运行curl
    $data = curl_exec($ch);
    //返回结果
    if($data){
        curl_close($ch);
        return $data;
    }
    else {
        $error = curl_errno($ch);
        curl_close($ch);
        echo "call faild, errorCode:$error\n";
        return false;
    }
}
function http_request($url,$data='',$header='',$cookie='',$timeout = 60,$isssl = false){   
    if(!defined('U_WRAP_SYMBOL')){
        $OS = strtoupper(substr(PHP_OS,0,3));
        if($OS == 'WIN'){//WIN服务器
            $wrap_symbol = "\r\n";
        }else{//直接指向linux服务器
            $wrap_symbol = "\n";
        } 
        define('U_OS',$OS);
        define('U_WRAP_SYMBOL',$wrap_symbol);         
    } 
    if(empty($url)){
        \Think\Log::record('url为空!');
        return '';
    }
    $ch = curl_init();    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);    
    if($isssl){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);            
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
    curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);    
    curl_setopt($ch, CURLINFO_HEADER_OUT,true);//设置打印请求头
    if(!empty($data)){//post请求
        curl_setopt($ch, CURLOPT_POST, 1);        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if(!empty($cookie)){
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);  
    }
    $output = curl_exec($ch);  
    $request_header = curl_getinfo( $ch, CURLINFO_HEADER_OUT);//打印请求头信息
    \Think\Log::record('request head:'.U_WRAP_SYMBOL.$request_header);
    if(!empty($data)){
        \Think\Log::record('request data:'.U_WRAP_SYMBOL.$data);//打印请求体数据
    }
    if(curl_errno($ch)){//curl执行失败        
        \Think\Log::record('request fail, errno='.curl_errno($ch).',errormsg='.curl_error($ch));
        $output = array('error'=>'curl error');
    }else{//curl执行成功，但是响应结果不一定是200
        \Think\Log::record('Curl response info:'.U_WRAP_SYMBOL.$output);    //打印全部响应信息
        list($header,$body) = explode(U_WRAP_SYMBOL.U_WRAP_SYMBOL, $output, 2);
        $header_data = explode(U_WRAP_SYMBOL,$header);
        $headers = array();
        $response_line = $header_data[0];        
        list($version,$code,$msg) = explode(' ',$response_line,3);
        $k = 0;
        for($i=1;$i<count($header_data);$i++){
            list($name,$content) = explode(':', $header_data[$i]);
            if($name == 'Set-Cookie'){
                $headers['Set-Cookie'][$k] = ltrim($content);
                $k++;
            }else{
                $headers[$name] = ltrim($content);
            }
        }
        unset($k);
        $output = array(
            'version' => $version,
            'code' => $code,
            'msg' => $msg,
            'headers' => $headers,
            'content' => $body
        );                     
        //\Think\Log::record('Curl response content:'.U_WRAP_SYMBOL.$output['content']);    
        if(strpos($output['content'],'HTTP/1.1 200 OK') === 0){//避免100响应
            list($sub_header,$sub_body) = explode(U_WRAP_SYMBOL.U_WRAP_SYMBOL, $output['content'], 2); 
            $output['code'] = 200;
            $output['content'] = $sub_body;
            \Think\Log::record('Curl response sub_content:'.U_WRAP_SYMBOL.$output['content']); 
            if(strpos($output['content'],'HTTP/1.1 200 OK') === 0){//避免100响应
                list($sub_header,$sub_body) = explode(U_WRAP_SYMBOL.U_WRAP_SYMBOL, $output['content'], 2); 
                $output['code'] = 200;
                $output['content'] = $sub_body;
                \Think\Log::record('Curl response sub_sub_content:'.U_WRAP_SYMBOL.$output['content']); 
                if(strpos($output['content'],'HTTP/1.1 200 OK') === 0){//避免100响应
                    list($sub_header,$sub_body) = explode(U_WRAP_SYMBOL.U_WRAP_SYMBOL, $output['content'], 2); 
                    $output['code'] = 200;
                    $output['content'] = $sub_body;
                    \Think\Log::record('Curl response sub_sub_sub_content:'.U_WRAP_SYMBOL.$output['content']); 
                }                 
            }            
        }        
    }
    curl_close($ch);    
    return $output;
}
//获取用户真实IP
function getIp() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
                $ip = getenv("REMOTE_ADDR");
            else
                if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
                    $ip = $_SERVER['REMOTE_ADDR'];
                else
                    $ip = "unknown";
    return ($ip);
}

    
    