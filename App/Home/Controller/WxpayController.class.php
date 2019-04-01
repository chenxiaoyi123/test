<?php
/**
 * Created by PhpStorm.
 * User: 97558
 * Date: 2018/11/15
 * Time: 15:17
 */

namespace Home\Controller;

use Think\Log;
use Think\Controller;
use Common\Core\Message;


define('mchid', '1251297501');
define('mchid_key', 'hy3bVtUFhtMIccDg7IEhGf883vneZmTn');
define('openID', 'oAzy9swhz-Geee_EeuJbocnasHZ0');
define('Wxcard', 'gh_fe23502e3400');
header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:*');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class WxpayController extends Controller
{
    private $wxpayConfig;
    private $parameters;

    public function _initialize()
    {
        $this->wxpayConfig = array('CURL_TIMEOUT' => 30);
        $this->wxpayConfig['appid'] = AppId; // 微信公众号身份的唯一标识appid
        $this->wxpayConfig['appsecret'] = AppSecret; // APP密钥
        $this->wxpayConfig['mchid'] = mchid; // 微信支付商户号
        $this->wxpayConfig['key'] = mchid_key; // 商户支付密钥Key
        $this->wxpayConfig['RechargeNotify'] = "https://shbs10014.shwo10016.cn/book/index.php/Home/Wxpay/RechargeNotify"; //充值异步通知地址
        $this->wxpayConfig['OrderNotify'] = "https://shbs10014.shwo10016.cn/book/index.php/Home/Wxpay/OrderNotify"; //押金支付异步通知地址
        $this->wxpayConfig['ReservationNotify'] = "https://shbs10014.shwo10016.cn/book/index.php/Home/Wxpay/ReservationNotify";//支付单支付异步通知地址
        $this->wxpayConfig['SignType'] = "MD5"; //加密方式
        $this->wxpayConfig['url'] = "https://api.mch.weixin.qq.com/pay/unifiedorder";//统一下单接口
        $this->wxpayConfig['refund_url'] = "https://api.mch.weixin.qq.com/secapi/pay/refund";//退款接口
        $this->wxpayConfig['RefundNotify'] = "https://shbs10014.shwo10016.cn/book/index.php/Home/Wxpay/RefundNotify"; //退款通知地址
    }

    public function Url()//接受微信发送消息
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
            echo $_GET['echostr'];
            exit();
        }
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $xmlString = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $return_data = json_decode(json_encode($xmlString), TRUE);
        if ($return_data['Event'] == 'SCAN') {//扫码核销类型
            Log::record('扫码消息 xml:' . $return_data);
            $oid = $return_data['EventKey'];
            $openid = $return_data['FromUserName'];//店员的openID
            if ($oid > 0) {
                /*------------------核销*订单参数查询--------------------*/
                $order = M('order');
                $orderinfo = $order->field('money,username,cometime,comestime,comeetime,uid,order_no')->where('id=' . $oid)->find();
                if ($orderinfo == false) {
                    Log::record('订单查询错误~');
                    $time = time();
                    $Wxcard = Wxcard;
                    echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
                    die();
                }
                /*------------------核销*会员openID查询--------------------*/
                $member=M('member');
                $memberinfo=$member->field('openid')->where(array('id'=>$orderinfo['uid']))->find();
                if ($memberinfo==false){
                    Log::record('会员openID查询错误~');
                    $time = time();
                    $Wxcard = Wxcard;
                    echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
                    die();
                }
                //开启事务
                $order->startTrans();
                /*------------------核销*变更订单状态--------------------*/
                $saleser = M('saleser');
                $salesermap['openid'] = "$openid";
                $saleserinfo = $saleser->field('id')->where($salesermap)->find();
                if ($saleserinfo == false) {
                    //任一执行失败，执行回滚操作，相当于均不执行
                    $order->rollback();
                    Log::record('店员不存在');
                    $time = time();
                    $Wxcard = Wxcard;
                    echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
                    die();
                }
                /*------------------核销*插入核销流水--------------------*/
                $verification = M('verification');
                $data['saleser_id'] = $saleserinfo['id'];
                $data['oid'] = $oid;
                $data['type'] = 1;
                $data['addtime'] = date('Y-m-d H:i:s');
                $add = $verification->add($data);
                if ($add == false) {
                    //任一执行失败，执行回滚操作，相当于均不执行
                    $order->rollback();
                    Log::record('核销失败');
                    $time = time();
                    $Wxcard = Wxcard;
                    echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
                    die();
                }
                /*------------------核销*变更订单状态--------------------*/
                $ordermap['id'] = $oid;
                $ordermap['status'] = 1;
                $orderupdate = $order->where($ordermap)->setField(array('status' => 2));
                if ($orderupdate == false) {
                    //任一执行失败，执行回滚操作，相当于均不执行
                    $order->rollback();
                    Log::record('订单修改状态错误');
                    $time = time();
                    $Wxcard = Wxcard;
                    echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
                    die();
                }
                //提交事务
                $order->commit();
                /*------------------核销*店员*消息模板发送--------------------*/
                $money = $orderinfo['money'] / 100;
                $time = $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . ':00' . '-' . $orderinfo['comeetime'] . ':00';
                $data = array(
                    'openid' => $openid,
                    'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/verificated',
                    'first' => '订单已核销成功，信息如下。',
                    'time' => date("Y-m-d H:i:s"),
                    'name' => '单号：'.$orderinfo['order_no'].'预订时间：' . $time,
                    'money' => $money,
                    'remark' => '如对上述订单核销有变动，请及时联系用户。',
                );
                $message = new Message;
                $returndata = $message->Verification($data);
                if ($returndata->errcode != 0) {
                    Log::record('核销*店员消息模板发送失败' . json_encode($data));
                }
                /*------------------核销*会员*消息模板发送--------------------*/
                $money = $orderinfo['money'] / 100;
                $time = $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . ':00' . '-' . $orderinfo['comeetime'] . ':00';
                $data = array(
                    'openid' => $memberinfo['openid'],
                    'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/usingOrder',
                    'first' => $orderinfo['username'] . '您好，您的预定订单已经被核销，信息如下。',
                    'time' => date("Y-m-d H:i:s"),
                    'name' => '预订时间：' . $time,
                    'money' => $money,
                    'remark' => '如对上述订单核销有异议，请联系客服人员协助处理。',
                );
                $message = new Message;
                $returndata = $message->Verification($data);
                if ($returndata->errcode != 0) {
                    Log::record('核销*会员消息模板发送失败' . json_encode($data));
                }
            } else {
                Log::record('核销失败，oid参数错误');
                $time = time();
                $Wxcard = Wxcard;
                echo "<xml> 
                 <ToUserName><![CDATA[$openid]]></ToUserName>  
                 <FromUserName><![CDATA[$Wxcard]]></FromUserName> 
                 <CreateTime>$time</CreateTime> 
                 <MsgType><![CDATA[text]]></MsgType> 
                 <Content><![CDATA[核销失败，请重试~]]></Content>  
                 </xml>";
            }
        }
    }

    public function PayOrder()//订单支付
    {
        $paytype = I('paytype');//0 微信支付 1  余额支付
        $ordertype = I('ordertype');// 0 押金支付  1  订单支付
        $uid = I('uid');
        $oid = I('oid');
        $order = M('order');
        $reservation_order = M('reservation_order');
        if ($ordertype == 0) {
            $orderinfo = $order->field('status,money')->where(array('oid' => $oid, 'status' => 0))->find();
            if ($orderinfo == false) {
                return_err('订单不存在11111');
                Log::record('订单参数错误~支付未查到订单');
            }
            $money = $orderinfo['money'];
        } else {
            $reservation_orderinfo = $reservation_order->field('status,sum_money')->where(array('oid' => $oid, 'status' => 0))->find();
            if ($reservation_orderinfo == false) {
                return_err('订单不存在22');
                Log::record('订单参数错误~支付未查到订单');
            }
            $money = $reservation_orderinfo['sum_money'];
        }
        if ($paytype == 0) {//微信支付方式
            $this->JsApiPay($uid, $ordertype, $oid, $money);
        } else if ($paytype == 1) {//余额支付方式
            $this->VacancyPay($uid, $ordertype, $oid, $money);
        } else {
            return_err('参数错误~');
        }
    }

    private function VacancyPay($uid, $ordertype, $oid, $money)//余额支付
    {
        $payment = M('payment');
        $order = M('order');
        $member = M('member');
        $out_trade_no = GetOrder_no($uid);
        $memberinfo = $member->field('balance,openid,level_id,balance')->where('id=' . $uid)->find();
        if ($memberinfo == false) {
            return_err('member参数错误');
        }
        if ($memberinfo['balance'] < $money) {
            return_err('余额不足');
        }
        //开启事务
        $member->startTrans();
        /*------------------支付*扣减用户的余额---------------------*/
        $memberupdate = $member->where('id=' . $uid)->setDec('balance', $money);
        if ($memberupdate == false) {
            //任一执行失败，执行回滚操作，相当于均不执行
            $member->rollback();
            return_err('扣减余额失败');
        }/*------------------插入支付流水---------------------*/
        $paymentdata['ordertype'] = $ordertype;// 0 押金支付  1  订单支付
        $paymentdata['paytype'] = 1;
        $paymentdata['uid'] = $uid;
        $paymentdata['oid'] = $oid;
        $paymentdata['status'] = 1;
        $paymentdata['out_trade_no'] = "$out_trade_no";
        $paymentdata['money'] = $money;
        $paymentdata['addtime'] = date("Y-m-d H:i:s");
        $paymentdata['updatetime'] = date("Y-m-d H:i:s");
        $paymentadd = $payment->add($paymentdata);
        if ($paymentadd == false) {
            //任一执行失败，执行回滚操作，相当于均不执行
            $member->rollback();
            return_err('插入支付流水失败');
        }
        /*------------------押金支付*更改订单状态---------------------*/
        if ($ordertype == 0) {
            $orderupdate = $order->where(array('id' => $oid))->setField(array('ispay' => 1, 'status' => 1, 'paytype' => 1));
            if ($orderupdate == false) {
                //任一执行失败，执行回滚操作，相当于均不执行
                $member->rollback();
                return_err('更订单状态失败');
                Log::record('更订单状态失败uid' . $uid . 'oid=' . $oid);
            }
            //执行成功，提交事务
            $member->commit();
            /*------------------会员*发送余额变动*消息模板---------------------*/
            $balance = ($memberinfo['balance'] - $money) / 100;
            $data = array(
                'openid' => $memberinfo['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/',
                'first' => $memberinfo['username'] . '您好，您的余额发生了变动，变动信息如下。',
                'level' => GetMemberLevel($memberinfo['level_id']),
                'type' => '预订押金付款',
                'content' => '会员余额扣减：' . $money / 100,
                'change_money' => $money / 100,
                'balance' => $balance,
                'remark' => '如对上述订单退款有异议，请联系客服人员协助处理。',
            );
            $message = new Message;
            $message->AccountBalance($data);
            /*------------------会员*发送订单预订成功*消息模板---------------------*/
            $orderinfo = $order->field('money,username,cometime,comestime,comeetime,order_no,mobile,gid')->where('id=' . $oid)->find();
            if ($orderinfo == false) {
                Log::record('订单查询错误~');
            }
            $money = $orderinfo['money'] / 100;
            $time = $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . ':00' . '-' . $orderinfo['comeetime'] . ':00';
            $data = array(
                'openid' => $memberinfo['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/unuseOrder',
                'first' => $orderinfo['username'] . '您好，您的预定订单已经成功，信息如下。',
                'order_no' => $orderinfo['order_no'],
                'money' => $money,
                'time' => $time,
                'remark' => '如对上述订单有异议，请联系客服人员协助处理。',
            );
            $message = new Message;
            $message->BookingSuccessful($data);
            /*------------------店员*发送订单支付成功*消息模板---------------------*/
            $saleser = M('saleser');
            $saleserlist = $saleser->field('openid,username')->where('openid is not null')->select();
            foreach ($saleserlist as $value => &$k) {
                $data = array(
                    'openid' => $k['openid'],
                    'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/unverificate',
                    'first' => $k['username'] . '店员您好，' . $orderinfo['username'] . '预定订单支付成功，信息如下。',
                    'username' => $orderinfo['username'],
                    'order_no' => $orderinfo['order_no'],
                    'money' => $money,
                    'content' => '预订房间：' . GetRoomname($orderinfo['gid']) . ',预订时间：' . $time,
                    'remark' => '如有问题请尽快处理并通知客户。客户手机号:' . $orderinfo['mobile'],
                );
                $message = new Message;
                $message->PaySuccessful($data);
            }
            unset($k);
            /*------------------订单结算*修改订单状态---------------------*/
        } else {
            $reservation_order = M('reservation_order');
            $reservation_orderupdate = $reservation_order->where(array('oid' => $oid))->setField(array('status' => 1));
            if ($reservation_orderupdate == false) {
                //任一执行失败，执行回滚操作，相当于均不执行
                $member->rollback();
                return_err('更订单状态失败11');
                Log::record('更订单状态失败11uid' . $uid . 'oid=' . $oid);
            }
            $orderupdate = $order->where(array('id' => $oid))->setField(array('status' => 3));
            if ($orderupdate == false) {
                //任一执行失败，执行回滚操作，相当于均不执行
                $member->rollback();
                return_err('更订单状态失败');
                Log::record('更订单状态失败uid' . $uid . 'oid=' . $oid);
            }
            //执行成功，提交事务
            $member->commit();
            /*------------------会员*发送余额变动*消息模板---------------------*/
            $balance = ($memberinfo['balance'] - $money) / 100;
            $data = array(
                'openid' => $memberinfo['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/',
                'first' => $memberinfo['username'] . '您好，您的余额发生了变动，变动信息如下。',
                'level' => GetMemberLevel($memberinfo['level_id']),
                'type' => '离店结算付款',
                'content' => '会员余额扣减：' . $money / 100,
                'change_money' => $money / 100,
                'balance' => $balance,
                'remark' => '如对上述订单退款有异议，请联系客服人员协助处理。',
            );
            $message = new Message;
            $message->AccountBalance($data);
            /*------------------店员*发送订单支付成功*消息模板---------------------*/
            $reservation_orderinfo = $reservation_order->field('order_no,username,saleser_id,mobile')->where(array('oid' => $oid))->find();
            $saleser = M('saleser');
            $saleserinfo = $saleser->field('openid,username')->where('id=' . $reservation_orderinfo['saleser_id'])->find();
            $data = array(
                'openid' => $saleserinfo['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/completedOrder',
                'first' => $saleserinfo['username'] . '店员您好，' . $reservation_orderinfo['username'] . '订单支付成功，信息如下。',
                'username' => $reservation_orderinfo['username'],
                'order_no' => $reservation_orderinfo['order_no'],
                'money' => $money/100,
                'content' => '客户离店结算',
                'remark' => '如有问题请尽快处理并通知客户。客户手机号:' . $reservation_orderinfo['mobile'],
            );
            $message = new Message;
            $message->PaySuccessful($data);
            /*------------------会员*发送订单支付成功*消息模板---------------------*/
            $data = array(
                'openid' => $memberinfo['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/completedOrder',
                'first' => $reservation_orderinfo['username'] . '您好，订单支付成功，信息如下。',
                'username' => $reservation_orderinfo['username'],
                'order_no' => $reservation_orderinfo['order_no'],
                'money' => $money/100,
                'content' => '离店结算',
                'remark' => '谢谢您的惠顾，欢迎下次光临~',
            );
            $message = new Message;
            $message->PaySuccessful($data);
        }
        //  Qrcode($oid);
        return_data('SUCCESS');
    }

    public function Recharge()//充值接口
    {
        $uid = I('uid');
        $total_fee = I('money');
        if (!$uid > 0) {
            return_err('uid参数错误');
        }
        if (!$total_fee > 0) {
            return_err('uid参数错误');
        }
        $this->JsApiPay($uid, 2, 0, $total_fee);
    }

    private function JsApiPay($uid, $type, $oid, $total_fee)//微信支付
    {
        $appid = $this->wxpayConfig['appid'];
        $mchid = $this->wxpayConfig['mchid'];
        if ($type == 2) {//微信支付类型 type=2 充值  0押金支付  1 订单支付
            $notify_url = $this->wxpayConfig['RechargeNotify'];
        } else if ($type == 0) {
            $notify_url = $this->wxpayConfig['OrderNotify'];
        } else {
            $notify_url = $this->wxpayConfig['ReservationNotify'];
        }
        $out_trade_no = GetOrder_no($uid);
        $member = M('member');
        $memberinfo = $member->field('openid')->where('id=' . $uid)->find();
        // $total_fee=1;
        $attach = "支付测试";
        $body = "JSAPI支付测试";
        $openid = $memberinfo['openid'];
        Log::record('openid:' . $openid);
        $spbill_create_ip = getIp();
        $nonce_str = $this->getnonceStr();
        $this->parameters["appid"] = $appid; // 公众账号ID
        $this->parameters["mch_id"] = $mchid; // 商户号
        $this->parameters["spbill_create_ip"] = $spbill_create_ip;// 终端ip
        $this->parameters["nonce_str"] = $nonce_str; // 随机字符串
        $this->parameters["attach"] = "$attach";
        $this->parameters["body"] = "$body";
        $this->parameters["notify_url"] = $notify_url;
        $this->parameters["openid"] = "$openid";
        $this->parameters["out_trade_no"] = "$out_trade_no";
        $this->parameters["total_fee"] = $total_fee;
        $this->parameters["trade_type"] = "JSAPI";
        $sign = $this->Sign($this->parameters);
        $data = "<xml>
           <appid>$appid</appid>
           <attach>$attach</attach>
           <body>$body</body>
           <mch_id>$mchid</mch_id>
           <nonce_str>$nonce_str</nonce_str>
           <notify_url>$notify_url</notify_url>
           <openid>$openid</openid>
           <out_trade_no>$out_trade_no</out_trade_no>
           <spbill_create_ip>$spbill_create_ip</spbill_create_ip>
           <total_fee>$total_fee</total_fee>
           <trade_type>JSAPI</trade_type>
           <sign>$sign</sign>
           </xml>";
        $xml = curl_post_ssl($this->wxpayConfig['url'], $data);
        $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $return_data = json_decode(json_encode($xmlString), TRUE);
        if ($return_data) {
            if ($type == 2) {
                $recharge = M('recharge');
                $rechargedata['uid'] = $uid;
                $rechargedata['type'] = 0;
                $rechargedata['out_trade_no'] = "$out_trade_no";
                $rechargedata['return_code'] = $return_data['return_code'];
                if ($return_data['return_code'] == 'SUCCESS') {
                    $rechargedata['result_code'] = $return_data['result_code'];
                    if ($return_data['result_code'] == 'SUCCESS') {
                        //Qrcode($oid);
                        $rechargedata['prepay_id'] = $return_data['prepay_id'];
                    } else {
                        $rechargedata['err_code'] = $return_data['err_code'];
                        $rechargedata['err_code_des'] = $return_data['err_code_des'];
                    }
                } else {
                    $rechargedata['return_msg'] = $return_data['return_msg'];
                }
                $rechargedata['money'] = $total_fee;
                $rechargedata['addtime'] = date("Y-m-d H:i:s");
                $rechargedata['updatetime'] = date("Y-m-d H:i:s");
                $recharge->add($rechargedata);
            } else {
                $payment = M('payment');
                $paymentdata['uid'] = $uid;
                $paymentdata['oid'] = $oid;
                $paymentdata['ordertype'] = $type;
                $paymentdata['paytype'] = 0;
                $paymentdata['out_trade_no'] = "$out_trade_no";
                $paymentdata['return_code'] = $return_data['return_code'];
                if ($return_data['return_code'] == 'SUCCESS') {
                    $paymentdata['result_code'] = $return_data['result_code'];
                    if ($return_data['result_code'] == 'SUCCESS') {
                        $paymentdata['prepay_id'] = $return_data['prepay_id'];
                    } else {
                        $paymentdata['err_code'] = $return_data['err_code'];
                        $paymentdata['err_code_des'] = $return_data['err_code_des'];
                    }
                } else {
                    $paymentdata['return_msg'] = $return_data['return_msg'];
                }
                $paymentdata['money'] = $total_fee;
                $paymentdata['addtime'] = date("Y-m-d H:i:s");
                $paymentdata['updatetime'] = date("Y-m-d H:i:s");
                $payment->add($paymentdata);
            }
        }
        $jsApiParameters = $this->GetJsApiParameters($return_data);
        return_data($jsApiParameters);

    }

    public function CancelOrder()//取消订单
    {
        $oid = I('oid');
        $status = I('status');//订单状态  0 未支付  1  已经支付
        $order = M('order');
        if (!$oid > 0) {
            return_err('参数错误');
        }
        if ($status == 0) {
            $update = $order->where('id=' . $oid)->setField(array('status' => 4));
            if ($update == false) {
                return_err('修改状态失败');
            }
            return_data('success');
        } else if ($status == 1) {
            $this->Refund($oid);
        } else {
            return_err('订单状态参数错误');
        }
    }

    public function Refund($oid)//申请退款
    {
        $order = M('order');
        $map['id'] = $oid;
        $map['status'] = 1;
        $orderinfo = $order->field('paytype')->where($map)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误~');
        }
        if ($orderinfo['paytype'] == 0) {
            $this->WxRefund(0, $oid);
        } elseif ($orderinfo['paytype'] == 1) {
            $this->Yuereund($oid);
        } else {
            return_err('订单参数错误~');
        }
    }

    private function Yuereund($oid)//余额退款
    {
        $order = M('order');
        $map['id'] = $oid;
        $map['status'] = 1;
        $orderinfo = $order->field('money,uid,order_no')->where($map)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误~');
        }
        $refund = M('refund');
        $out_trade_no = GetOrder_no($orderinfo['uid']);
        $refundmap['uid'] = $orderinfo['uid'];
        $refundmap['oid'] = $oid;
        $refundmap['out_trade_no'] = $out_trade_no;
        $refundmap['money'] = $orderinfo['money'];
        $refundmap['type'] = 1;
        $refundmap['refund_recv_accout'] = "支付用户余额";
        $refundmap['status'] = 1;
        $refundmap['success_time'] = date('Y-m-d H:i:s');
        $refundmap['addtime'] = date('Y-m-d H:i:s');
        $add = $refund->add($refundmap);
        if ($add == false) {
            return_err('插入流水失败~');
        }
        //开启事务
        $order->startTrans();
        $orderupdate = $order->where(array('id' => $oid))->setField(array('status' => 5));
        if ($orderupdate == false) {
            //任一执行失败，执行回滚操作，相当于均不执行
            $order->rollback();
            return_err('修改订单参数错误~');
        }
        $member = M('member');
        $update = $member->where('id=' . $orderinfo['uid'])->setInc('balance', $orderinfo['money']); // 用户的余额增加
        if ($update == false) {
            //任一执行失败，执行回滚操作，相当于均不执行
            $order->rollback();
            return_err('修改用户余额错误~');
        }
        //执行成功，提交事务
        $order->commit();
        /*---------------------------会员*余额退款*发送消息模板---------------------------------*/
        $memberinfo = $member->field('openid,level_id,balance')->where('id=' . $orderinfo['uid'])->find();
        $data = array(
            'openid' => $memberinfo['openid'],
            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/',
            'first' => $memberinfo['username'] . '您好，您的余额发生了变动，变动信息如下。',
            'level' => GetMemberLevel($memberinfo['level_id']),
            'type' => '预订押金退款',
            'content' => '订单单号：' . $orderinfo['order_no'],
            'change_money' => $orderinfo['money'] / 100,
            'balance' => $memberinfo['balance'] / 100,
            'remark' => '如对上述订单退款有异议，请联系客服人员协助处理。',
        );
        $message = new Message;
        $message->AccountBalance($data);
        return_data('SUCCESS');
    }

    private function WxRefund($num = 0, $oid)//微信支付订单退款
    {
        $i = 0;
        $payment = M('payment');
        $map['oid'] = "$oid";
        $map['status'] = 1;
        $paymentinfo = $payment->field('money,transaction_id,uid')->where($map)->find();
        if ($paymentinfo) {
            $money = $paymentinfo['money'];
            $transaction_id = $paymentinfo['transaction_id'];
            $uid = $paymentinfo['uid'];
        } else {
            return_err('微信支付订单参数错误~');
        }
        $appid = $this->wxpayConfig['appid'];
        $mchid = $this->wxpayConfig['mchid'];
        $notify_url = $this->wxpayConfig['RefundNotify'];
        $out_trade_no = GetOrder_no($uid);
        $nonce_str = $this->getnonceStr();
        $this->parameters["appid"] = $appid; // 公众账号ID
        $this->parameters["mch_id"] = $mchid; // 商户号
        $this->parameters["nonce_str"] = $nonce_str; // 随机字符串
        $this->parameters["notify_url"] = $notify_url;
        if ($num == 1) {
            $this->parameters["refund_account"] = "REFUND_SOURCE_RECHARGE_FUNDS";
            $i = $num;
        }
        $this->parameters["out_refund_no"] = "$out_trade_no";
        $this->parameters["refund_fee"] = $money;
        $this->parameters["total_fee"] = $money;
        $this->parameters["transaction_id"] = $transaction_id;
        $sign = $this->Sign($this->parameters);
        if ($num == 1) {
            $data = "<xml>
       <appid>$appid</appid>
       <mch_id>$mchid</mch_id>
       <nonce_str>$nonce_str</nonce_str>
       <notify_url>$notify_url</notify_url>
       <out_refund_no>$out_trade_no</out_refund_no>
       <refund_fee>$money</refund_fee>
       <total_fee>$money</total_fee>
       <refund_account>REFUND_SOURCE_RECHARGE_FUNDS</refund_account>
       <transaction_id>$transaction_id</transaction_id>
       <sign>$sign</sign>
       </xml>";
        } else {
            $data = "<xml>
          <appid>$appid</appid>
          <mch_id>$mchid</mch_id>
          <nonce_str>$nonce_str</nonce_str>
          <notify_url>$notify_url</notify_url>
          <out_refund_no>$out_trade_no</out_refund_no>
          <refund_fee>$money</refund_fee>
          <total_fee>$money</total_fee>
          <transaction_id>$transaction_id</transaction_id>
          <sign>$sign</sign>
          </xml>";
        }
        $xml = curl_post_ssl($this->wxpayConfig['refund_url'], $data);
        $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $return_data = json_decode(json_encode($xmlString), TRUE);
        if ($return_data['result_code'] == 'FAIL' && $return_data['err_code'] == 'NOTENOUGH' && $i == 0) {
            $this->WxRefund(1, $oid);
        }
        $refunddata['oid'] = $oid;
        $refunddata['uid'] = $uid;
        $refunddata['out_trade_no'] = $out_trade_no;
        $refunddata['transaction_id'] = $transaction_id;
        if ($refunddata['return_code'] == 'FAIL') {
            $refunddata['return_msg'] = $return_data['return_msg'];
        } else {
            if ($return_data['result_code'] == 'FAIL') {
                $refunddata['err_code'] = $return_data['err_code'];
                $refunddata['err_code_des'] = $return_data['err_code_des'];
                $status = 1;
                $msg = $return_data['err_code'];
            } else {
                $status = 1;
                $msg = "SUCCESS";
            }
        }
        $refunddata['money'] = $money;
        $refunddata['type'] = 0;
        $refunddata['status'] = 0;
        $refunddata['addtime'] = date('Y-m-d H:i:s');
        $refund = M('refund');
        $add = $refund->add($refunddata);
        if ($add == false) {
            $status = 0;
            $msg = '数据插入失败，请重试~';
        }
        $returndata = array(
            'status' => $status,
            'msg' => $msg
        );
        echo json_encode($returndata);
        die();

    }

    public function RechargeNotify()//微信充值接收异步消息
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            # 如果没有数据，直接返回失败
            Log::record('微信充值接收异步消息，没有数据');
        } else {
            //获取通知的数据
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            Log::record("更新 XML: " . $xml);
            $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $return_data = json_decode(json_encode($xmlString), TRUE);
            $recharge = M('recharge');
            $map['out_trade_no'] = $return_data['out_trade_no'];
            if ($return_data['return_code'] == 'SUCCESS') {
                if ($return_data['return_code'] == 'SUCCESS') {
                    $sava = array("status" => 1, 'transaction_id' => $return_data['transaction_id'], 'updatetime' => date("Y-m-d H:i:s"));
                    $rechargemap['transaction_id'] = $return_data['transaction_id'];
                    $rechargeinfo = $recharge->field('transaction_id')->where($rechargemap)->find();
                    if ($rechargeinfo) {
                        echo "<xml> 
                       <return_code><![CDATA[SUCCESS]]></return_code>
                       <return_msg><![CDATA[OK]]></return_msg>
                       </xml>";
                        exit();
                    } else {
                        $member = M('member');
                        $membermap['openid'] = $return_data['openid'];
                        $memberinfo = $member->field('balance,total,openid,level_id')->where($membermap)->find();
                        if ($memberinfo == false) {
                            Log::record("没有找到充值用户XML: " . $xml);
                        }
                        $total_fee = $return_data['total_fee'];
                        $membersava = array('balance' => $memberinfo['balance'] + $total_fee, 'total' => $memberinfo['total'] + $total_fee);
                        $member->where($membermap)->setField($membersava);
                        /*---------------------------会员*余额退款*发送消息模板---------------------------------*/
                        $balance = ($memberinfo['balance'] + $total_fee) / 100;
                        $data = array(
                            'openid' => $memberinfo['openid'],
                            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/',
                            'first' => $memberinfo['username'] . '您好，您的余额发生了变动，变动信息如下。',
                            'level' => GetMemberLevel($memberinfo['level_id']),
                            'type' => '微信充值',
                            'content' => '会员充值' . ($total_fee / 100) . '元，到账户余额',
                            'change_money' => $total_fee / 100,
                            'balance' => $balance,
                            'remark' => '如对上述充值信息有异议，请联系客服人员协助处理。',
                        );
                        $message = new Message;
                        $message->AccountBalance($data);

                    }
                } else {
                    $sava = array('err_code' => $return_data['err_code'], 'err_code_des' => $return_data['err_code_des']);
                }
            } else {
                $sava = array('return_code' => $return_data['return_code'], 'return_msg' => $return_data['return_msg'], 'updatetime' => date("Y-m-d H:i:s"));
            }
            $rechargeupdate = $recharge->where($map)->setField($sava);
            if ($rechargeupdate) {

                echo "<xml> 
                       <return_code><![CDATA[SUCCESS]]></return_code>
                       <return_msg><![CDATA[OK]]></return_msg>
                       </xml>";
                exit();
            } else {
                Log::record("更新支付数据失败 XML: " . $xml);
            }
        }
    }

    public function OrderNotify()//微信押金支付接收异步消息
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            # 如果没有数据，直接返回失败
            Log::record('没有数据11');
        } else {
            //获取通知的数据
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $return_data = json_decode(json_encode($xmlString), TRUE);
            $payment = M('payment');
            $map['out_trade_no'] = $return_data['out_trade_no'];
            if ($return_data['return_code'] == 'SUCCESS') {
                if ($return_data['return_code'] == 'SUCCESS') {
                    $sava = array("status" => 1, 'transaction_id' => $return_data['transaction_id'], 'updatetime' => date("Y-m-d H:i:s"));
                    $paymentinfo = $payment->field('oid')->where($map)->find();
                    if ($paymentinfo) {
                        Log::record("未找到支付单 XML: " . $xml);
                    }
                    $oid = $paymentinfo['oid'];
                    $order = M('order');
                    $ordersava = array('ispay' => 1, 'status' => 1);
                    $orderupdate = $order->where(array('id' => $oid))->setField($ordersava);
                    if ($orderupdate == false) {
                        Log::record("更改订单状态失败 XML: " . $xml);
                    }
                    /*------------------会员*发送订单预订成功*消息模板---------------------*/
                    $orderinfo = $order->field('uid,money,username,cometime,comestime,comeetime,order_no,mobile,gid')->where('id=' . $oid)->find();
                    if ($orderinfo == false) {
                        Log::record('订单查询错误222~');
                    }
                    $member = M('member');
                    $memberinfo = $member->field('openid')->where('id=' . $orderinfo['uid'])->find();
                    if ($orderinfo == false) {
                        Log::record('会员数据查询错误222~');
                    }
                    $money = $orderinfo['money'] / 100;
                    $time = $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . ':00' . '-' . $orderinfo['comeetime'] . ':00';
                    $data = array(
                        'openid' => $memberinfo['openid'],
                        'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/unuseOrder',
                        'first' => $orderinfo['username'] . '您好，您的预定订单已经成功，信息如下。',
                        'order_no' => $orderinfo['order_no'],
                        'money' => $money,
                        'time' => $time,
                        'remark' => '如对上述订单有异议，请联系客服人员协助处理。',
                    );
                    $message = new Message;
                    $message->BookingSuccessful($data);
                    /*------------------店员*发送订单支付成功*消息模板---------------------*/
                    $saleser = M('saleser');
                    $saleserlist = $saleser->field('openid,username')->where('openid is not null')->select();
                    foreach ($saleserlist as $value => &$k) {
                        $data = array(
                            'openid' => $k['openid'],
                            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/unverificate',
                            'first' => $k['username'] . '店员您好，' . $orderinfo['username'] . '预定订单支付成功，信息如下。',
                            'username' => $orderinfo['username'],
                            'order_no' => $orderinfo['order_no'],
                            'money' => $money,
                            'content' => '预订房间：' . GetRoomname($orderinfo['gid']) . ',预订时间：' . $time,
                            'remark' => '如有问题请尽快处理并通知客户。客户手机号:' . $orderinfo['mobile'],
                        );
                        $message = new Message;
                        $message->PaySuccessful($data);
                    }
                    unset($k);
                } else {
                    $sava = array('err_code' => $return_data['err_code'], 'err_code_des' => $return_data['err_code_des']);
                }
            } else {
                $sava = array('return_code' => $return_data['return_code'], 'return_msg' => $return_data['return_msg'], 'updatetime' => date("Y-m-d H:i:s"));
            }
            $paymentupdate = $payment->where($map)->setField($sava);
            if ($paymentupdate == false) {
                Log::record("更新支付数据失败 XML: " . $xml);
            }
            echo "<xml> 
                       <return_code><![CDATA[SUCCESS]]></return_code>
                       <return_msg><![CDATA[OK]]></return_msg>
                       </xml>";
            exit();
        }
    }

    public function ReservationNotify()//离店结算支付接收异步消息
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            # 如果没有数据，直接返回失败
            Log::record('没有数据22');
        } else {
            //获取通知的数据
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $return_data = json_decode(json_encode($xmlString), TRUE);
            $payment = M('payment');
            $map['out_trade_no'] = $return_data['out_trade_no'];
            if ($return_data['return_code'] == 'SUCCESS') {
                if ($return_data['return_code'] == 'SUCCESS') {
                    $sava = array("status" => 1, 'transaction_id' => $return_data['transaction_id'], 'updatetime' => date("Y-m-d H:i:s"));
                    $paymentinfo = $payment->field('oid')->where($map)->find();
                    if ($paymentinfo) {
                        $oid = $paymentinfo['oid'];
                        $order = M('order');
                        $ordersava = array('status' => 3);
                        $orderupdate = $order->where(array('id' => $oid))->setField($ordersava);
                        if ($orderupdate == false) {
                            Log::record("更改订单状态失败 XML: " . $xml);
                        }
                        $reservation_order = M('reservation_order');
                        $reservation_orderupdate = $reservation_order->where("oid=$oid")->setField(array('status' => 1));
                        if ($reservation_orderupdate == false) {
                            Log::record('更订单状态失败5555 oid=' . $oid);
                        }

                        /*------------------店员*离店结算*消息模板---------------------*/
                        $reservation_orderinfo = $reservation_order->field('order_no,username,saleser_id,mobile,sum_money')->where(array('oid' => $oid))->find();
                        $saleser = M('saleser');
                        $saleserinfo = $saleser->field('openid,username')->where('id=' . $reservation_orderinfo['saleser_id'])->find();
                        $data = array(
                            'openid' => $saleserinfo['openid'],
                            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/completedOrder',
                            'first' => $saleserinfo['username'] . '店员您好，' . $reservation_orderinfo['username'] . '订单支付成功，信息如下。',
                            'username' => $reservation_orderinfo['username'],
                            'order_no' => $reservation_orderinfo['order_no'],
                            'money' => $reservation_orderinfo['sum_money'] / 100,
                            'content' => '客户离店结算完成',
                            'remark' => '如有问题请尽快处理并通知客户。客户手机号:' . $reservation_orderinfo['mobile'],
                        );
                        $message = new Message;
                        $message->PaySuccessful($data);
                        /*------------------会员*离店结算完成*消息模板---------------------*/
                        $data = array(
                            'openid' => $return_data['openid'],
                            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/me/completedOrder',
                            'first' => $reservation_orderinfo['username'] . '您好，订单支付成功，信息如下。',
                            'username' => $reservation_orderinfo['username'],
                            'order_no' => $reservation_orderinfo['order_no'],
                            'money' => $reservation_orderinfo['sum_money'] / 100,
                            'content' => '离店结算完成',
                            'remark' => '谢谢您的惠顾，欢迎下次光临~',
                        );
                        $message = new Message;
                        $message->PaySuccessful($data);

                    }
                } else {
                    $sava = array('err_code' => $return_data['err_code'], 'err_code_des' => $return_data['err_code_des']);
                }
            } else {
                $sava = array('return_code' => $return_data['return_code'], 'return_msg' => $return_data['return_msg'], 'updatetime' => date("Y-m-d H:i:s"));
            }
            $paymentupdate = $payment->where($map)->setField($sava);
            if ($paymentupdate) {
                echo "<xml> 
                       <return_code><![CDATA[SUCCESS]]></return_code>
                       <return_msg><![CDATA[OK]]></return_msg>
                       </xml>";
                exit();
            } else {
                Log::record("更新支付数据失败 XML: " . $xml);
            }
        }
    }

    public function RefundNotify()//微信退款异步消息
    {
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            # 如果没有数据，直接返回失败
            Log::record('没有数据33');
        } else {
            //获取通知的数据
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            $xmlString = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $return_data = json_decode(json_encode($xmlString), TRUE);
            if ($return_data['return_code'] == 'SUCCESS') {
                $data = $return_data['req_info'];
                $decrypt = base64_decode($data, true);
                $xml2 = (openssl_decrypt($decrypt, 'aes-256-ecb', md5($this->wxpayConfig['key']), OPENSSL_RAW_DATA));
                $xmlString = simplexml_load_string($xml2, 'SimpleXMLElement', LIBXML_NOCDATA);
                $return_data = json_decode(json_encode($xmlString), TRUE);
                if ($return_data['refund_status'] == 'SUCCESS') {
                    $sava = array('refund_recv_accout' => $return_data['refund_recv_accout'], 'status' => 1, 'success_time' => $return_data['success_time']);
                    $refund = M('refund');
                    $transaction_id = $return_data['transaction_id'];
                    $map['transaction_id'] = "$transaction_id";
                    $refund->where($map)->setField($sava);
                    $refundinfo = $refund->field('oid')->where($map)->find();
                    $oid = $refundinfo['oid'];
                    $order = M('order');
                    $updateorder = $order->where(array('id' => $oid))->setField(array('status' => 5));
                    if ($updateorder == false) {
                        Log::record("退款更改订单状态失败XML: " . $xml);
                    }
                }
            }
            echo "<xml> 
                       <return_code><![CDATA[SUCCESS]]></return_code>
                       <return_msg><![CDATA[OK]]></return_msg>
                       </xml>";
            exit();
        }
    }

    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throwsWxPayException
     *
     * @returnjson数据，可直接填入js函数作为参数
     */
    public function GetJsApiParameters($UnifiedOrderResult)
    {
        if (!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "") {
            return_err("参数错误");
        }
        $prepay_id = $UnifiedOrderResult['prepay_id'];
        $nonceStr = $this->getNonceStr(32);
        $data = array(
            "appId" => $this->wxpayConfig['appid'],
            "nonceStr" => $nonceStr,
            "package" => "prepay_id=" . $prepay_id,
            "signType" => "MD5",
            "timeStamp" => time(),
        );
        $sign = $this->Sign($data);
        $data = array(
            "appId" => $this->wxpayConfig['appid'],
            "nonceStr" => $nonceStr,
            "package" => "prepay_id=" . $prepay_id,
            "signType" => "MD5",
            "timeStamp" => time(),
            'paySign' => $sign
        );
        $parameters = $data;
        return $parameters;
    }

    private function Sign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->wxpayConfig['key'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     * 作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    private function getnonceStr($num = 16)
    {
        $nonceStr = "";
        $str = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        for ($i = 0; $i <= $num; $i++) {
            $rand = rand(0, count($str));
            $nonceStr .= $str[$rand];
        }
        return $nonceStr;
    }
}