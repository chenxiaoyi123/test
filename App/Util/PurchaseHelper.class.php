<?php
namespace Util;
use Think\Log;
/*
 * 充值送费辅助类，统一返回格式
 * array(
 *      errorno    => 1,
 *      errmsg     => 'msg',
 *      content    => 附加信息,出错时为空
 * )
 */
class PurchaseHelper{
    /*
     * 测试
     */
    public function showname(){
        echo 'ysx';
    }
    
    /*
     * 订购活动查询 【暂时不需要】
     */
    public function queryPurchase($tel){
        if(empty($tel)){
            Log::record('判断用户套餐时，电话号码为空，$tel='.$tel);
            return array(
                'errorno' => -1,
                'errmsg'  => '电话号码不能为空',
                'content' => '',
            );
        }
        //加解密时使用
        $flag = 'esim_queryPurchase';
        //接口方法名
        $method = 'ai.pub.inbus.user.scheme.qry';
        //业务参数
        $data_post = array(
            'qry_type' => "1",
            'service_class_code' => '0000',
            'service_num' => $tel,
        ); 
        $result = $this->invokeAPI($flag,$method,$data_post);
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result['status'] == -1){
            Log::record('查询活动的订购情况时，发生异常');
            return array(
                'errorno' => -1,
                'errmsg'  => '查询活动的订购发生异常',
                'content' => '',
            );            
        }
        $content = $result['content'];   
        if($content['respCode'] == '00000' && $content['result']['resp_code'] == '0000'){
            foreach($content['result']['productInfo'] as $product_info){
                //此处可能需要根据调用结果，再修改
                if(in_array($product_info['product_id'],array('80000775','80000776'))){//10元套餐
                    return array(
                        'errorno' => 1,
                        'errmsg'  => 'success',
                        'content' => '10',
                    );
                }
                if(in_array($product_info['product_id'],array('80000777','80000778'))){//20元套餐
                    return array(
                        'errorno' => 1,
                        'errmsg'  => 'success',
                        'content' => '20',
                    );
                }
            }
        }else if($content['respCode'] == '00000' && $content['result']['resp_code'] != '0000'){
            Log::record('调用能力汇聚平台查询活动的订购情况时，发生异常，respCode='.$content['respCode'].',resp_code='.$content['result']['resp_code'].',resp_desc='.$content['result']['resp_desc']);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询活动的订购情况时发生异常',
                'content' => '',
            );                
        }else if($content['respCode'] != '00000'){
            Log::record('调用能力汇聚平台查询活动的订购情况时，发生异常，响应出错，respCode='.$content['respCode']);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询活动的订购情况时发生异常',
                'content' => '',
            );               
        }        
    }
    
    /*
     * 判断用户套餐
     */
    public function chargePackage($tel){
        if(empty($tel)){
            Log::record('判断用户套餐时，电话号码为空，$tel='.$tel);
            return array(
                'errorno' => -1,
                'errmsg'  => '电话号码不能为空',
                'content' => '',
            );
        }
        //加解密时使用
        $flag = 'esim_checkpackage';
        //接口方法名
        $method = 'ailk.grp.ecs.user.business.get';
        //业务参数
        $data_post = array(
            'channel_id' => C('CHANNEL_ID'),
            'user_code'  => C('USER_CODE'),
            'orig_domain' => 'SHCM',
            'qry_type' => "1",
            'service_class_code' => '0000',
            'serial_number' => $tel,
        );  
        $result = $this->invokeAPI($flag,$method,$data_post);
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result['status'] == -1){
            Log::record('调用能力汇聚平台用户业务接口查询套餐时，发生异常');
            return array(
                'errorno' => -1,
                'errmsg'  => '查询套餐发生异常',
                'content' => '',
            );            
        }
        $content = $result['content'];   
        if($content['respCode'] == '00000' && $content['result']['resp_code'] == '0000'){
            /*foreach($content['result']['user_info'] as $user_info){
                foreach($user_info['product'] as $product){
                    if($product['product_type'] == 1 && in_array($product['product_id'],array('80000775','80000776'))){//10元套餐     
                        return array(
                            'errorno' => 1,
                            'errmsg'  => 'success',
                            'content' => '10',
                        );
                    }
                    if($product['product_type'] == 1 && in_array($product['product_id'],array('80000777','80000778'))){//20元套餐
                        return array(
                            'errorno' => 1,
                            'errmsg'  => 'success',
                            'content' => '20',
                        );
                    }
                }
            }*/
            $packages = array();
            foreach($content['result']['user_info'] as $user_info){
                if($user_info['subscrb_type'] == 2){//用户类型为：OCS
                    foreach($user_info['product'] as $product){
                        $inactive_date = strtotime($product['inactive_date']);
                        $now = time();
                        if($product['product_type'] == 1 && strcmp($inactive_date,$now) > 0){
                            $packages[] = array(
                                'active_date' => $product['active_date'],
                                'product_id' => $product['product_id']
                            );
                        } 
                    }                    
                }
            }
            if(empty($packages)){
                Log::record('没有获取到subscrb_type=2即OCS侧的用户套餐类型，$tel='.$tel);
                return array(
                    'errorno' => 1,
                    'errmsg'  => 'success',
                    'content' => 'else',
                );                  
            }else{
                if(count($packages) == 1){
                    Log::record('只有一个元素，product_id='.$packages[0]['product_id']);                    
                    if(in_array($packages[0]['product_id'],array('80000775','80000776'))){//10元套餐
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => '10',
                            );                        
                    }else if(in_array($packages[0]['product_id'],array('80000777','80000778'))){//20元套餐
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => '20',
                            );                        
                    }else{
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => 'else',
                            );                           
                    }
                }else{
                    $active_date = '';
                    $product_id = '';
                    foreach($packages as $package){
                        if(empty($active_date)){
                            $active_date = $package['active_date'];
                            $product_id = $package['product_id'];
                        }else{
                            $tmp = strtotime($package['active_date']);
                            $now_active_time = strtotime($active_date);
                            if($tmp > $now_active_time){
                                $active_date = $package['active_date'];
                                $product_id = $package['product_id'];                                
                            }
                        }
                    }
                    Log::record('product_id='.$product_id.',active_date='.$active_date);                    
                    if(in_array($product_id,array('80000775','80000776'))){//10元套餐
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => '10',
                            );                        
                    }else if(in_array($product_id,array('80000777','80000778'))){//20元套餐
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => '20',
                            );                        
                    }else{
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => 'else',
                            );                           
                    }
                }                        
            }            
        }else if($content['respCode'] == '00000' && $content['result']['resp_code'] != '0000'){
            Log::record('调用能力汇聚平台用户业务接口查询套餐时，发生异常，respCode='.$content['respCode'].',resp_code='.$content['result']['resp_code'].',resp_desc='.$content['result']['resp_desc']);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询套餐发生异常',
                'content' => '',
            );                
        }else if($content['respCode'] != '00000'){
            Log::record('调用能力汇聚平台用户业务接口查询套餐时，发生异常，响应出错，respCode='.$content['respCode']);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询套餐发生异常',
                'content' => '',
            );               
        } 
    }
    
    /*
     * 判断用户是否已订购
     */
    public function chargePurchased($tel){
        if(empty($tel)){
            Log::record('判断用户是否已订购时，$tel未传值');
            return array(
                'errorno' => -1,
                'errmsg'  => '判断用户是否已订购时，$tel未传值',
                'content' => '',
            );
        }     
        $giveBillOrders = M('GiveBillOrders');
        $result = $giveBillOrders->where("phone_num='$tel' and orderstatus = ".C('ORDER_STATUS.PURCHASE_SUCCESS'))->find();
        if($result === false){
            Log::record('判断用户是否已订购时，查询出错');
            return array(
                'errorno' => -1,
                'errmsg'  => '判断用户是否已订购时，查询出错',
                'content' => '',
            );            
        }else if($result === NULL){
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'no',
            );            
        }else{
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'yes',
            );             
        }
    }    
    
    /*
     * 判断号码是否可充值
     */
    public function chargeTelCanRecharge($tel){
        if(empty($tel)){
            Log::record('判断号码是否可充值时，电话号码为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '判断号码是否可充值时，电话号码为空',
                'content' => '',
            );
        }
        $aif_request_url = C('BILL_URL').'/recharge/checkUser';
        $data_post = array(
            'channelId' => C('RECHARGE_CHECK_CHANNELID'),
            'streamingNo' => 'ES'.date('YmdHis'). createNonceStr(4, true),
            'serialNumber' => $tel,
        );
        $aif_header = array(
                    'Content-Type: application/json'
                );
        $digest_url = C('USERNAMETOKEN_DIGEST_URL').'?str=';//摘要算法地址
        $aif_key = C('AIFKEY');
        $aif_secret = C('AIFSECRET');
        $result = invokeUsernameTokenAPI($digest_url, $aif_key, $aif_secret, $aif_request_url, json_encode($data_post),$aif_header);//接收的content就是已经decode的响应content
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result['status'] == -1){
            Log::record('调用能力开放平台话费充值接口判断用户是否可充值时，发生异常');
            return array(
                'errorno' => -1,
                'errmsg'  => '判断号码是否可充值发生异常',
                'content' => '',
            );            
        }
        $content = $result['content'];        
        if($content['code'] != '0000'){
            Log::record('调用联通能力开放平台号码是否能充值接口，该号码不可充值，code='.$content['code'].',description='.$content['description']);
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'no',
            );              
        }else if($content['code'] == '0000'){
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'yes',
            );             
        }
    }
    
    /*
     * 判断是否2/3G用户
     */
    public function is23GUser($tel){
        if(empty($tel)){
            Log::record('判断是否2/3G用户时，电话号码为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '电话号码不能为空',
                'content' => '',
            );
        }
        //加解密时使用
        $flag = 'esim_check23g';
        //接口方法名
        $method = 'ai.pub.inbus_checkUserIs4G.get';
        //业务参数
        $data_post = array(
            'channel_id' => C('CHANNEL_ID'),
            'user_code'  => C('USER_CODE'),             
            'qry_type' => "1",
            'service_class_code' => '0000',
            'serial_number' => $tel,
        );  
        $result = $this->invokeAPI($flag,$method,$data_post);
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result['status'] == -1){
            Log::record('调用能力汇聚平台判断2/3G用户时，发生异常');
            return array(
                'errorno' => -1,
                'errmsg'  => '判断2/3G发生异常',
                'content' => '',
            );            
        }
        $content = $result['content'];        
        if($content['respCode'] == '00000' && $content['result']['resp_code'] == '0000'){
            if($content['result']['flag_23g'] == '1'){//是2/3G用户
                return array(
                    'errorno' => 1,
                    'errmsg'  => 'success',
                    'content' => 'yes',
                );                      
            }else{
                return array(
                    'errorno' => 1,
                    'errmsg'  => 'success',
                    'content' => 'no',
                );                 
            }                                                              
        }else if($content['respCode'] == '00000' && $content['result']['resp_code'] != '0000'){
            Log::record('调用能力汇聚平台判断2/3G用户时，发生异常，，respCode='.$content['respCode'].',respDesc='.$content['respDesc'].',resp_code='.$content['result']['resp_code'].',resp_desc='.$content['result']['resp_desc']);
            return array(
                'errorno' => -1,
                'errmsg'  => '判断2/3G用户时，发生异常',
                'content' => '',
            );                
        }else if($content['respCode'] != '00000'){
            Log::record('调用能力汇聚平台判断2/3G用户时，发生异常，respCode='.$content['respCode'].',respDesc='.$content['respDesc']);
            return array(
                'errorno' => -1,
                'errmsg'  => '判断2/3G用户时，发生异常',
                'content' => '',
            );                
        }     
    }
    
    /*
     * 保存预订单
     */
    public function saveBeforeOrder($orderInfo){
        $giveBillOrders = M('GiveBillOrders');
        $result = $giveBillOrders->add($orderInfo);
        if($result){ 
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'success',
            );              
        }else{
            Log::record('保存预订单时，发生异常,orderInfo='.json_encode($orderInfo,JSON_UNESCAPED_UNICODE));            
            return array(
                'errorno' => -1,
                'errmsg'  => '保存预支付订单时，发生异常',
                'content' => '',
            );            
        }
    }   
    
    /*
     * 给用户充值
     */
    public function recharge($payno){
        //根据支付流水号，查到用户的订单信息
        if(empty($payno)){
            Log::record('给用户充值时，支付流水号为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '支付流水号为空',
                'content' => '',
            );
        }
        $giveBillOrders = M('GiveBillOrders');        
        $result = $giveBillOrders->where("payno='$payno'")->find();
        if($result === FALSE){
            Log::record('给用户充值时，查询订单出错,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询订单出错',
                'content' => '',
            );             
        }else if($result === NULL){
            Log::record('给用户充值时，没有查询到对应的订单,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '没有查询到对应的订单',
                'content' => '',
            );              
        }
        $streamingNo = 'ES'.date('YmdHis'). createNonceStr(4, true);
        $rechargedate = date('Ymd');
        $data = array(
            'channelId' => C('RECHARGE_CHANNELID'),
            'streamingNo'=> $streamingNo,
            'serialNumber' => $result['phone_num'],
            'money' => $result['amount'],
            'date' => date('Ymd'),
        );   
        Log::record('话费充值数据='.json_encode($data,JSON_UNESCAPED_UNICODE));
        //调用java新加解密地址，生成签名  SHA-256
        $new_encode_url = C('NEW_ENCODE_URL');
        $new_encode_key = C('NEW_ENCODE_KEY');
        ksort($data);
        $str = '';
        foreach($data as $key=>$value){
            $str .=$key.$value;
        }
        $flag = 'esim_encode';
        $sign = md5($str.$new_encode_key);//生成sign，确保送往java项目加解密的参数不会被篡改
        $url = $new_encode_url.'?flag='.$flag.'&str='.$str.'&sign='.$sign;
        $resp_encode = http_request($url);        
        if(!empty($resp_encode['error'])){
            \Think\Log::record('加密生成sign时，curl出错,$url='.$url);            
            return array(
                'errorno' => -1,
                'errmsg'  => '出错了',
                'content' => '',
            );             
        }else{
            if(empty($resp_encode['content'])){
               \Think\Log::record('充值时，加密生成sign时，响应内容为空,$url='.$url);
                return array(
                    'errorno' => -1,
                    'errmsg'  => '出错了',
                    'content' => '',
                );
            }          
        }
        //开始充值          
        $data['sign'] = $resp_encode['content'];
        $aif_header = array(
                    'Content-Type: application/json'
                );
        $digest_url = C('USERNAMETOKEN_DIGEST_URL').'?str=';//摘要算法地址
        $aif_key = C('AIFKEY');
        $aif_secret = C('AIFSECRET');
        $aif_request_url = C('BILL_URL').'/recharge/recharge';   
        Log::record('话费充值，从服务器返回的请求sign='.$resp_encode['content']);
        $result_invoke = invokeUsernameTokenAPI($digest_url, $aif_key, $aif_secret, $aif_request_url, json_encode($data),$aif_header);//接收的content就是已经decode的响应content
        Log::record('话费充值接口已返回数据结果，程序继续执行');        
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result_invoke['status'] == -1){
            Log::record('调用能力开放平台话费充值接口进行充值时，发生异常，充值信息未更新，充值流水号streamingNo='.$streamingNo.',payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '充值发生异常',
                'content' => '',
            );            
        }
        $content = $result_invoke['content'];    
        if($content['code'] == '00'){//充值成功            
            //更新充值流水号、充值时间、充值日期（对账专用）、订单状态      
            $giveBillOrders->where("payno='$payno'")->save(
                    array(
                        'rechargeno' => $streamingNo,
                        'rechargetime' => date('Y-m-d H:i:s'),
                        'rechargedate' => $rechargedate,
                        'orderstatus' => C('ORDER_STATUS.CHARGE_SUCCESS'),                    
                    ));            
                return array(
                    'errorno' => 1,
                    'errmsg'  => 'success',
                    'content' => 'yes',
                );
        }else{//充值失败
            Log::record('调用联通能力开放平台充值时，充值失败，充值流水号streamingNo='.$streamingNo.',payno='.$payno.',code='.$content['code'].',description='.$content['description']);            
            //更新充值流水号、充值时间、充值日期（对账专用）、订单状态      
            $giveBillOrders->where("payno='$payno'")->save(
                    array(
                        'rechargeno' => $streamingNo,
                        'rechargetime' => date('Y-m-d H:i:s'),
                        'rechargedate' => $rechargedate,
                        'orderstatus' => C('ORDER_STATUS.CHARGE_FAIL'),              
                    ));                         
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'no',
            );
        }
    }    
    
    /*
     * 订购
     */
    public function purchase($payno){
        //根据支付流水号，查到用户的订单信息
        if(empty($payno)){
            Log::record('订购时，支付流水号为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '支付流水号为空',
                'content' => '',
            );                
        }
        $giveBillOrders = M('GiveBillOrders');
        $result = $giveBillOrders->where("payno='$payno'")->find();
        if($result === FALSE){
            Log::record('订购时，查询订单出错,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询订单出错',
                'content' => '',
            );             
        }else if($result === NULL){
            Log::record('订购时，没有查询到对应的订单,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '没有查询到对应的订单',
                'content' => '',
            );              
        }
        $order_id = date('YmdHis'). createNonceStr(7, true);        //订购流水号
        $nowtime = msectime();//精确到毫秒;
        $nowdate = date('YmdHis',substr($nowtime,0, strlen($nowtime)-3));
        $nowtime_s = substr($nowtime, strlen($nowtime)-3,3);//毫秒部分            
        $timestamp = $nowdate. ''.$nowtime_s;
        //echo 'nowtime='.$nowtime.',nowdate='.$nowdate.',nowtime_s='.$nowtime_s.',timestamp='.$timestamp;        
        //订购时间，带毫秒        
        if($result['package_type'] == 1){
            $scheme_id = '145049';
        }else if($result['package_type'] == 2){
            $scheme_id = '145050';
        }
        
        $operate_time = $timestamp;
        $data = array(
            'service_num' => $result['phone_num'],
            'scheme_id'=> $scheme_id,
            'opt_type' => '0',
            'channel_id'=> C('CHANNEL_ID'),
            'user_code'=> C('USER_CODE'),
            'start_enable' => '1',
            'operate_time' => $operate_time,
            'order_id' => $order_id,            
            'goods_flag' => '0',
        );
        //加解密时使用
        $flag = 'esim_purchase';
        //接口方法名
        $method = 'ai.pub.single.scheme.change';
        //业务参数
        $data_post = $data;
        $result_invoke = $this->invokeAPI($flag,$method,$data_post);
        /*
         * 以下内容，修改部分内容，即可各个方法通用
         */
        if($result_invoke['status'] == -1){
            Log::record('调用能力汇聚平台订购接口时，发生异常');
            return array(
                'errorno' => -1,
                'errmsg'  => '订购时发生异常',
                'content' => '',
            );            
        }
        //更新订购流水号和订购时间
        $giveBillOrders->where("payno='$payno'")->save(
                array(
                    'purchaseno' => $order_id,
                    'purchasetime' => date('Y-m-d H:i:s',substr($nowtime,0, strlen($nowtime)-3)),
                ));        
        $content = $result_invoke['content'];        
        if($content['respCode'] == '00000' && $content['result']['error_code'] == '0000'){
            //更新订单状态为 订购成功
            $giveBillOrders->where("payno='$payno'")->save(
                array(
                    'orderstatus' => C('ORDER_STATUS.PURCHASE_SUCCESS'),
                ));            
            return array(
                'errorno' => 1,
                'errmsg'  => 'success',
                'content' => 'yes',
            );                                                              
        }else if($content['respCode'] == '00000' && $content['result']['error_code'] != '0000'){
            Log::record('调用能力汇聚平台订购时，发生异常，，respCode='.$content['respCode'].',respDesc='.$content['respDesc'].',error_code='.$content['result']['error_code'].',reasons_desc='.$content['result']['reasons_desc']);            
            //更新订单状态为 订购失败
            $giveBillOrders->where("payno='$payno'")->save(
                array(
                    'orderstatus' =>  C('ORDER_STATUS.PURCHASE_FAIL'),
                ));              
            return array(
                'errorno' => -1,
                'errmsg'  => '订购失败',
                'content' => '',
            );                
        }else if($content['respCode'] != '00000'){
            Log::record('调用能力汇聚平台订购时，发生异常，，respCode='.$content['respCode'].',respDesc='.$content['respDesc']);
            //更新订单状态为 订购失败
            $giveBillOrders->where("payno='$payno'")->save(
                array(
                    'orderstatus' => C('ORDER_STATUS.PURCHASE_FAIL'),
                ));              
            return array(
                'errorno' => -1,
                'errmsg'  => '订购失败',
                'content' => '',
            );               
        }
    }

    /*
     * 生成联通支付参数
     */
    public function createUnicomPayParams($orderInfo){
        if(empty($orderInfo)){
            Log::record('生成联通支付参数时，orderInfo为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '订单信息为空',
                'content' => '',
            );
        }
       
        //1.RSA加密支付参数
        $payInfo = array(
            'outTradeNo' => $orderInfo['payno'],
            'tradeType' => '05',
            'openid' => $orderInfo['openid'],
            'totalAmount' => $orderInfo['amount'],
            'actualAmount' => $orderInfo['amount'],
            'outTradeDesc' => C('OUTTRADEDESC'),
            'serviceId' => '00000015',
            'notifyUrl' => U('Purchase/paynotify','',true,true),
            'returnUrl' => U('Purchase/showpayresult','',true,true),//需要根据同步通知结果，判断resultCode从而判断显示哪个真正的支付结果页面
            'busiInfo' => array(
                    'provinceCode' => '31',
                    'regionId' => '310',
                    'orderInfos' => array(
                        array(
                            'orderId' => $orderInfo['orderno'],
                            'orderDesc' => 'eSIM',
                            'orderPrice' => $orderInfo['amount'],                            
                        ),
                    ),
            ),
        );        
        $new_encode_url = C('NEW_ENCODE_URL'); 
        $new_encode_key = C('NEW_ENCODE_KEY');
        $flag = 'encode';
        $json_payInfo = json_encode($payInfo);
        $sign = md5($json_payInfo.$new_encode_key);//生成sign，确保送往java项目加解密的参数不会被篡改
        $post_info = 'flag='.$flag.'&str='. $json_payInfo.'&sign='.$sign;
        $resp_encode = http_request($new_encode_url,$post_info);
        if(!empty($resp_encode['error'])){
            \Think\Log::record('生成联通支付参数，curl出错,$post_info='.$post_info);
            return array(
                'errorno' => -1,
                'errmsg'  => '出错了',
                'content' => '',
            );
        }else{
            if($resp_encode['code'] == 200 && !empty($resp_encode['content'])){
                return array(
                    'errorno' => 1,
                    'errmsg'  => 'success',
                    'content' => $resp_encode['content'],
                );                  
            }else{
               \Think\Log::record('生成联通支付参数时，响应内容为空，或者http状态非200,http code='.$resp_encode['code'].',$post_info='.$post_info);
                return array(
                    'errorno' => -1,
                    'errmsg'  => '出错了',
                    'content' => '',
                );              
            }
        }
    }     
    
    
    /*
     * 处理支付、退款异步通知结果
     */
    public function notifyHandle($notify_data,$type='pay'){       
        //1.无论有没有异步通知参数，都要先回执支付中心，告诉其已接收到数据
        $new_encode_url = C('NEW_ENCODE_URL'); 
        $new_encode_key = C('NEW_ENCODE_KEY');
        $flag = 'encode';
        if($type == 'pay'){
            $resultDesc = 'esim receive pay notify success';
        }else if($type == 'refund'){
            $resultDesc = 'esim receive pay notify refund success';
        }
        $notify_back_info = array(
            'resultCode' => '0000',
            'resultDesc' => $resultDesc,
        );
        $json_notify_back_info = json_encode($notify_back_info,JSON_UNESCAPED_UNICODE);
        $sign = md5($json_notify_back_info.$new_encode_key);//生成sign，确保送往java项目加解密的参数不会被篡改        
        $return_data = 'flag='.$flag.'&str='.$json_notify_back_info.'&sign='.$sign;
        $resp_notify_back = http_request($new_encode_url,$return_data);
        if(key_exists('code',$resp_notify_back) &&  $resp_notify_back['code'] == 200 && !empty($resp_notify_back['content'])){        
            Log::record('pay notify enter');
            $resp_payCenter_info = array(
                'data' => $resp_notify_back['content'],
            );  
            echo json_encode($resp_payCenter_info);//给支付中心返回已接收到异步通知的通知 
        }else{            
            //没有回执成功，记录日志，但继续后续处理
            Log::record('从java项目中获取给支付中心异步通知的回执的加密结果时curl出错，$return_data='.$return_data.',$resp_notify_back='.json_encode($resp_notify_back,JSON_UNESCAPED_UNICODE));
            return;
        }
        
        //2.RSA解密异步通知参数
        if(empty($notify_data)){
            \Think\Log::record('接收支付or退款异步通知参数开始进行解析时，没有获取到加密参数');
            return array(
                'errorno' => -1,
                'errmsg'  => '出错了',
                'content' => '',
            );
        }
        $flag_parse = 'decode';
        $str_parse = $notify_data;
        $sign_parse = md5($str_parse.$new_encode_key);     
        $parse_data = 'flag='.$flag_parse.'&str='.urlencode($str_parse).'&sign='.$sign_parse;//urlencode是必要的
        $resp_notify_parse_info = http_request($new_encode_url,$parse_data);
        Log::record('解析后的数据为='. json_encode($resp_notify_parse_info));
        if(key_exists('code',$resp_notify_parse_info) && $resp_notify_parse_info['code'] == 200 && !empty($resp_notify_parse_info['content'])){
                Log::record('enter 开始处理乱码了');
                $resp_notify_parse_arr = explode('_&&_', urldecode($resp_notify_parse_info['content']));              
                //返回的格式是 json字符串&sign=sign值，$resp_notify_parse_arr[0]是解密出的联通异步通知返回的json字符串；$resp_notify_parse_arr[1]是签名，具体为'sign=sign值',sign值为实际值
                $remoteSign = $resp_notify_parse_arr[1];
                $localSign = 'sign='.md5($resp_notify_parse_arr[0].$new_encode_key);
                if($remoteSign == $localSign){
                    Log::record('接收数据，签名验证验证通过');
                    //因为支付中心返回的结果，暂时无法解决乱码问题，所以直接提取出所需的参数值
                    //{"tradeNo":"050000001520180119100228044819","outTradeNo":"2018011915481901589442482824","tradeStatus":"2","notifyUrl":"http://shbs10014.shwo10016.cn:20011/index.php/Home/Purchase/paynotify.html","serviceId":"00000015","resultDesc":"鏀粯鍗曟垚鍔?,"payInfo":[{"payDesc":"寰俊鍏紬鍙锋敮浠?,"payFinishDate":"20180119","payAmount":1,"payTypeId":"WXPayWap","tradeSerialNo":"20180119pcs1000024409"}],"tradeType":"1"}
                    $receive_parse_data = $resp_notify_parse_arr[0]; //解析出的支付通知明文（但是包含乱码）
                    $notify_params = array();
                    
                    //取tradeNo  支付中心生成的的订单号，显示在支付界面上  外层 （文档上没有，不确定是否有）
                    $name = 'tradeNo';
                    $value = $this->fetchData($receive_parse_data, $name,'out');
                    $notify_params[$name] =$value;
                    
                    //取outTradeNo  外部交易流水号  外层
                    $name = 'outTradeNo';
                    $value = $this->fetchData($receive_parse_data, $name,'out');
                    $notify_params[$name] =$value;
                    
                    //取tradeType 支付类型 外层
                    $name = 'tradeType';
                    $value = $this->fetchData($receive_parse_data, $name,'out');
                    $notify_params[$name] =$value;
                    
                    //取tradeStatus 交易状态  外层
                    $name = 'tradeStatus';
                    $value = $this->fetchData($receive_parse_data, $name,'out');
                    $notify_params[$name] =$value;                      
                    
                    //取tradeSerialNo  支付中心商户号  内层 ，可能会返回null，因为有时候没有这个字段
                    $name = 'tradeSerialNo';
                    $value = $this->fetchData($receive_parse_data, $name,'in');
                    $notify_params[$name] =$value;    
                    
                    //取payFinishDate  交易完成时间支付中心对账专用  内层 ，可能会返回null，因为有时候没有这个字段
                    $name = 'payFinishDate';
                    $value = $this->fetchData($receive_parse_data, $name,'in');
                    $notify_params[$name] =$value;                        
                    
                    Log::record('从支付通知乱码明文中取出的需要的参数的值为：$notify_params='.json_encode($notify_params));
                    
                    
                    $payno = $notify_params['outTradeNo'];//支付流水号                     
                    $giveBillOrders = M('GiveBillOrders');                     
                    if($notify_params['tradeType'] == 1){//支付
                        
                        Log::record('开始加锁，time='.time());
                        //加锁
                        $fp = fopen('./lock.lock', 'w+');
                        flock($fp, LOCK_EX);                        
                        
                        $result_findAlreadyPayDeal = $giveBillOrders->where("payno='$payno' and paytime = ''")->find();
                        if(empty($result_findAlreadyPayDeal)){//已经有支付时间，说明已经处理过
                            Log::record('该笔支付订单已处理，不能重复处理，支付编号payno='.$payno);
                            
                            //释放文件锁
                            flock($fp, LOCK_UN);
                            fclose($fp);                              
                            Log::record('订单已处理过，不能重复处理，释放锁，time='.time());                            
                            
                            return array(
                                'errorno' => -1,
                                'errmsg'  => '订单可能重复通知，不予处理',
                                'content' => '',
                            );                        
                        }
                        $orderstatus = '';
                        if($notify_params['tradeStatus'] == 2){//支付成功
                            $orderstatus = C('ORDER_STATUS.PAY_SUCCESS');                            
                        }else if($notify_params['tradeStatus'] == 3){//支付交易关闭，等同于未支付
                            $orderstatus = C('ORDER_STATUS.WAIT_PAY');                             
                        }else{
                            Log::record('哇靠，竟然没有对应的支付状态，则认为支付失败,$payno='.$payno);
                            $orderstatus = C('ORDER_STATUS.PAY_FAIL');                            
                        }
                        if($notify_params['tradeStatus'] == 2){
                            $giveBillOrders->where("payno='$payno'")->save(
                                        array(                                            
                                            'orderstatus' => $orderstatus,
                                            'paytime' => date('Y-m-d H:i:s'),
                                            'tradeno' => $notify_params['tradeSerialNo'], //支付中心生成的微信支付商户号
                                            'paycenter_orderno' => $notify_params['tradeNo'], //支付中心在界面上生成的订单号
                                            'payfinshdate' => $notify_params['payFinishDate'],                                            
                                        )
                                    );
                        }else{
                            $giveBillOrders->where("payno='$payno'")->save(
                                        array(                                            
                                            'orderstatus' => $orderstatus,
                                            'paytime' => date('Y-m-d H:i:s'),
                                        )
                                    );                              
                        }    
                        
                        //释放文件锁
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        Log::record('订单处理完成，释放锁，time='.time());                        
                        
                        if($notify_params['tradeStatus'] != 2){
                            return array(
                                'errorno' => -1,
                                'errmsg'  => '支付交易关闭或支付失败',
                                'content' => '',
                            );
                        }else{
                            return array(
                                'errorno' => 1,
                                'errmsg'  => 'success',
                                'content' => array(
                                    'payno' => $payno,
                                ),
                            );                            
                        }
                    }else if($notify_params['tradeType']  == 2){//退款
                        $refundno = $notify_params['outTradeNo'];//退款流水号
                        $result_findAlreadyRefundDeal = $giveBillOrders->where("refundno='$refundno' and refundtime = ''")->find();
                        if(empty($result_findAlreadyRefundDeal)){//已经有退款时间，说明已经处理过
                            Log::record('该笔支付订单已退款处理，不能重复处理，退款编号refundno='.$refundno);
                            return array(
                                'errorno' => -1,
                                'errmsg'  => '退款订单可能重复通知，不予处理',
                                'content' => '',
                            );
                        }
                        $orderstatus = '';                        
                        if($notify_params['tradeStatus'] == 2){//退款成功
                            $orderstatus = C('ORDER_STATUS.REFUND_SUCCESS');
                        }else if($notify_params['tradeStatus'] == 3){//退款失败
                            $orderstatus = C('ORDER_STATUS.REFUND_FAIL');
                        }else if($notify_params['tradeStatus'] == 4){
                            Log::record('需二次退款，则订单状态设置为退款失败，退款流水号refundno='.$refundno);
                            $orderstatus =  C('ORDER_STATUS.REFUND_FAIL');
                        }else{
                            Log::record('哇靠，竟然没有对应的退款状态，那就默认为退款成功喽，退款流水号refundno='.$refundno);
                            $orderstatus =  C('ORDER_STATUS.REFUND_SUCCESS');                            
                        }
                        $giveBillOrders->where("refundno='$refundno'")->save(
                                    array(
                                        'orderstatus' => $orderstatus,
                                        'refundtime' => date('Y-m-d H:i:s'),
                                        'payfinshdate' => empty($notify_params['payFinishDate']) ? '' : $notify_params['payFinishDate'],                                             
                                    )
                                );
                    }else{    
                        Log::record('没有对应的状态，可能是解析出错了，$resp_notify_parse_arr[0]='.$resp_notify_parse_arr[0]);
                    }
                }else{
                    Log::record('java项目解析支付中心异步请求参数，参数签名验证不正确，程序退出,$resp_notify_parse_arr='.json_encode($resp_notify_parse_arr,JSON_UNESCAPED_UNICODE).',本地签名：'.$localSign);
                    return array(
                        'errorno' => -1,
                        'errmsg'  => '出错了',
                        'content' => '',
                    );                                 
                }            
        }else{
            Log::record('java项目解析支付中心异步请求参数curl出错，$resp_notify_parse_info='.json_encode($resp_notify_parse_info,JSON_UNESCAPED_UNICODE));
            return array(
                'errorno' => -1,
                'errmsg'  => '出错了',
                'content' => '',
            );
        }
    }    
    
    
    /*
     * 退款
     */
    public function refund($payno){
        //1.根据支付流水号查订单信息
        if(empty($payno)){
            Log::record('退款时，支付流水号为空');
            return array(
                'errorno' => -1,
                'errmsg'  => '支付流水号为空',
                'content' => '',
            );
        }
        $giveBillOrders = M('GiveBillOrders');        
        $result = $giveBillOrders->where("payno='$payno'")->find();
        if($result === FALSE){                       
            Log::record('退款时，查询订单出错,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '查询订单出错',
                'content' => '',
            );             
        }else if($result === NULL){
            Log::record('退款时，没有查询到对应的订单,$payno='.$payno);
            return array(
                'errorno' => -1,
                'errmsg'  => '没有查询到对应的订单',
                'content' => '',
            );              
        }
        
        //2.RSA加密退款参数
        $outRefundNo = date('YmdHis'). createNonceStr(14, true);//退款流水号
        $refundInfo = array(
            'outTradeNo' => $payno,
            'outRefundNo' => $outRefundNo,
            'tradeType' => '04',//APP
            'refundPrice' => intval($result['amount']),
            'outTradeDesc' => 'eSIM退款',
            'serviceId' => '00000015',
            'notifyUrl' => U('Purchase/refundnotify','',true,true),
            'busiInfo' => array(
                'provinceCode' => '31',
                'regionId' => '310',
                'busiStaffId' => '',
                'orderInfos' => array(
                    array(
                        'orderId' => $result['orderno'],
                        'orderDesc' => 'eSIM',
                        'orderPrice' => intval($result['amount']),
                    ),
                ),
            ),
        );
        $new_encode_url = C('NEW_ENCODE_URL'); 
        $new_encode_key = C('NEW_ENCODE_KEY');
        $flag = 'encode';
        $json_refundInfo = json_encode($refundInfo);
        $sign = md5($json_refundInfo.$new_encode_key);
        $post_info = 'flag='.$flag.'&str='.$json_refundInfo.'&sign='.$sign;
        $resp_encode = http_request($new_encode_url,$post_info);                   
        if(key_exists('code',$resp_encode) &&  $resp_encode['code'] == 200 && !empty($resp_encode['content'])){      
                //保存退款流水号
                $giveBillOrders->where("payno='$payno'")->save(
                            array(
                                'refundno' => $outRefundNo,
                            )
                        );                 
                //3.退款
                $refund_data = array(
                    'serviceId' => '00000015',
                    'data' => $resp_encode['content'],
                );
                $resp_refund = http_request(C('REFUND_URL'), http_build_query($refund_data),'','',30,true);
                if(key_exists('code',$resp_refund) && $resp_refund['code'] == 200 && !empty($resp_refund['content'])){     
                    
                }else{
                    \Think\Log::record('退款发生异常，curl出错,$resp_refund='. json_encode($resp_refund,JSON_UNESCAPED_UNICODE));            
                    return array(
                        'errorno' => -1,
                        'errmsg'  => '出错了',
                        'content' => '',
                    );             
                }
        }else{
            \Think\Log::record('退款时生成RSA加密参数时，curl出错,$post_info='.$post_info);            
            return array(
                'errorno' => -1,
                'errmsg'  => '出错了',
                'content' => '',
            );            
        }  
    }
    
    /*
     * 能力汇聚平台联通API调用（包含本地加解密，比如摘要算法，签名生成等），可能某些能力开放平台的api调用也适用，后续会全部适配
     */
    private function invokeAPI($flag,$method,$data_post){
        $digest_url = C('USERNAMETOKEN_DIGEST_URL').'?str=';//摘要算法地址
        $aif_key = C('AIFKEY');
        $aif_secret = C('AIFSECRET');
        $aif_request_url = C('AIFURL').'?%s';     //真正业务请求联通接口时的地址         
        //公共参数
        $data_pub['method'] = $method;
        $data_pub['format'] = 'json';
        $data_pub['appId'] = C('AIFAPPID');
        $data_pub['version'] = C('AIFVERSION');
        $data_pub['timestamp'] = date('YmdHis');         
        //整合业务参数
        $post_array = array_merge($data_pub, $data_post);
        $post_array['flag'] = $flag;
        $res_request = http_request(C('USERNAMETOKEN_DIGEST_URL'), http_build_query($post_array));  
        if(key_exists('code',$res_request) &&  $res_request['code'] == 200 && !empty($res_request['content'])){
             $data_pub['sign'] = $res_request['content'];
        }else{
            Log::record('生成签名出错!$flag='.$flag.',$method='.$method.',$data_post='. json_encode($data_post,JSON_UNESCAPED_UNICODE));
            return array(
                 'status' => -1,
                 'msg'  =>'生成签名出错！',
            );           
        }                               
        //组装request_url 
        foreach ($data_pub as $key => $row) {
            $dataReturn[] = sprintf("%s=%s", $key, $row);
        }        
        $aif_request_url = sprintf($aif_request_url, implode('&', $dataReturn)); 
        $aif_header = array(
                    'Content-Type: application/json'
                );
//        Log::record('l='.$aif_request_url);
//        return;
        $res_invoke = invokeUsernameTokenAPI($digest_url, $aif_key, $aif_secret, $aif_request_url, json_encode($data_post),$aif_header);//接收的content就是已经decode的响应content
        return $res_invoke;
    } 
    
    public function fetchData($data,$name,$out){
        $result = $this->getvalue($data, $name);
        if($result['value'] === null){
            Log::record($name.'参数在数据'.$data.'中不存在，直接返回null');                
            return null;
        }
        $value = $this->checkSame($data, $name, $result['value'], $result['start_pos'],$out);
        Log::record('最终值为$value='.$value);   
        return $value;
    }     
    
    public function checkSame($data,$name,$value,$pos,$out){
        /*
         * 屏蔽payInfo中参数和payInfo外参数重名可能引起的错误的方法
         * $data      原始数据
         * $name      参数名称
         * $value     参数值
         * $pos       需要检查的参数的位置
         * $out       外参数为 out，内参数为 in，必须注意不能传错该值
         */
        $p_payInfo = strpos($data, 'payInfo":[');
        $p_payInfo_last = strpos($data,']', $p_payInfo);
        if($p_payInfo === FALSE){
            //不需要检查重名
            Log::record('dont need check same name,because payInfo is not exist');
            return $value;
        }        
        Log::record($p_payInfo.','.$p_payInfo_last);
        if($out == 'out'){//外参数
            Log::record('enter out');
            if($pos > $p_payInfo && $pos < $p_payInfo_last){ 
                Log::record('重新取值'); 
                $result = $this->getvalue($data, $name, $p_payInfo_last);
                $value = $result['value'];       
            }
        }else if($out == 'in'){//内参数
            Log::record('enter inner'); 
            if($pos < $p_payInfo || $pos > $p_payInfo_last){                
                Log::record('重新取值'); 
                $result = $this->getvalue($data, $name, $p_payInfo);
                $value = $result['value'];                
            }
        }              
        return $value;
    }
    
    public function getvalue($data,$name,$start = 0){
        /*
         * 获取各参数值的方法
         * $data    原始数据
         * $name    参数名称
         * $start   开始查找该参数名称的起始位置
         */
        $p_name = strpos($data,$name,$start);
        if($p_name === FALSE){
            return array('name'=>$name,'value'=>null,'start_pos'=>$p_name);            
        }
        $p_first_yin = $p_name - 1 + strlen($name) + 3 + 1;
        $p_last_yin = strpos($data, '"',$p_first_yin);
        $len = $p_last_yin - $p_first_yin;
        $result_value = substr($data, $p_first_yin, $len);
        /*Log::record($name.'起始位置$p_name='.$p_name.',第一个引号后一位的位置$p_first_yin='.$p_first_yin
                .'，最后一个引号位置$p_last_yin='.$p_last_yin.',参数值长度$len='.$len.',参数值$result_value='.$result_value);  */
        return array('name'=>$name,'value'=>$result_value,'start_pos'=>$p_name);
    }              
    
}
