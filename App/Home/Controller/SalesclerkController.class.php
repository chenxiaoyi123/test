<?php
/**
 * Created by PhpStorm.
 * User: 97558
 * Date: 2018/11/26
 * Time: 9:07
 */

namespace Home\Controller;

use Think\Log;
use Think\Controller;
use Common\Core\Message;

define('AppId', 'wx7d0494284af1af9c');
define('AppSecret', 'cb4eaa39f11be828d8241ec877ce7009');
define('openID', 'oAzy9swhz-Geee_EeuJbocnasHZ0');
define('teahouse', '晨曦茶室');
header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:http://localhost:8080');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class SalesclerkController extends Controller
{
    public function Login()
    {//登录
        $mobile = I('mobile');
        $code = I('code');//验证码
        $openid = I('openid');
        if (empty($openid)) {
            return_err('请在微信环境打开');
        }
        /*=============== 验证验证码开始=================*/
        $session_time = session("stime");
        $sesssion_telnum = session("telnum");
        $session_num = session("num");
        $time = time();
        if (empty($session_time) || empty($session_num) || empty($sesssion_telnum)) {
            return_err("抱歉，请先获取验证码");
        } else if (($time - $session_time) >= 3 * 60) {
            return_err("已超时，验证码失效!");
        } else if ($code != $session_num) {
            return_err("验证码不一致!");
        }
        /*=============== 验证验证码结束=================*/
        $map['mobile'] = "$mobile";
        $saleser = M('saleser');
        $userdata = $saleser->field('id')->where($map)->find();
        if ($userdata == false) {
            return_err('用户不存在');
        }
        $access_token = $this->Getaccess_token();
        $get_userifo_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=$openid&lang=zh_CN";
        $output = doGet($get_userifo_url);
        //如果调用失败，那么再重复调用一次
        if (isset($output->errcode)) {
            if (empty($access_token)) {
                Log::record('再次获取微信用户信息时，获取不到accesstoken!');
                exit;
            }
            $output = doGet($get_userifo_url);
            if (isset($output->errcode)) {
                Log::record('再次调用微信接口获取微信用户信息时出错，程序退出，url=' . $get_userifo_url . ',errcode=' . $output->errcode . ',errmsg=' . $output->errmsg);
                exit;
            }
        }
        $output = json_decode($output);
        $subscribe = $output->subscribe;
        if ($subscribe == "0") {
            return_err('请先关注公众号');
        }
        $avatar = $output->headimgurl;
        $update = $saleser->where($map)->setField(array('openid' => "$openid", 'avatar' => "$avatar", 'updatetime' => date('Y-m-d H:i:s')));
        if ($update == false) {
            return_err('更新头像失败');
        }
        return_data($userdata['id']);
    }

    public function GetUserInfo()
    {//店员个人中心
        $mobile = I('mobile');
        $map['mobile'] = "$mobile";
        $saleser = M('saleser');
        $userdata = $saleser->where($map)->find();
        if ($userdata == false) {
            return_err('用户不存在');
        }
        $data = array(
            'username' => $userdata['username'],
            'mobile' => $userdata['mobile'],
            'avatar' => $userdata['avatar']
        );
        return_data($data);
    }

    public function GetopenID()
    {//获取微信openID
        $code = I('code');
        if (empty($code)) {
            return_err('code参数错误');
        }
        $appid = AppId;
        $secret = AppSecret;
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $code . '&grant_type=authorization_code';
        $json_josn = doGet($get_token_url);
        $json_obj = json_decode($json_josn);
        if (isset($output->errcode)) {
            $output = doGet($get_token_url);
            if (isset($output->errcode)) {
                Log::record('再次调用微信接口获取微信用户信息时出错，程序退出,errcode=' . $output->errcode . ',errmsg=' . $output->errmsg);
                return_err('调用微信接口获取微信用户信息时出错');
            }
        }
        $openid = $json_obj->openid;
        return_data($openid);
    }

    public function OrderQuery()
    {//订单查询
        $status = I('status');//0  待付款  1  待核销 2  已核销   3   已完成   4  已作废  5 已退款  -1 全部订单
        $group_query = I('group_query');
        $p = I('page');//页数
        $row = I('row');//显示数据条数
        $mobile = I('mobile');
        $map['mobile'] = "$mobile";
        $saleser = M('saleser');
        $userdata = $saleser->where($map)->find();
        if ($userdata == false) {
            return_err('用户不存在');
        }
        $p = empty($p) ? 1 : $p;
        $row = empty($row) ? 5 : $row;//每页显示条数
        $offset = ($p - 1) * $row;
        $limit = $offset . ", " . $row;
        if ($status > 0) {
            $ordermap['status'] = $status;
        }
        if (!empty($group_query)) {
            $where['username'] = array('like', '%' . $group_query . '%');
            $where['mobile'] = "$group_query";
            $where['order_no'] = "$group_query";
            $where['_logic'] = 'or';
            $ordermap['_complex'] = $where;
        }
        // print_r($ordermap);die();
        $order = M('order');
        $orderlist = $order->field('order_no,cometime,comestime,comeetime,username,mobile,num,money,status,gid,id,replace_id')->where($ordermap)->order('id desc')->limit($limit)->select();
        foreach ($orderlist as $value => &$k) {
            $k['time'] = $k['cometime'] . ' ' . $k['comestime'] . ':00' . '-' . $k['comeetime'] . ':00';
            $k['roomname'] = GetRoomname($k['gid']);
        }
        unset($k);
        return_data($orderlist);
    }

    public function Verification()
    {//订单核销
        $mobile = I('mobile');//店员手机号
        $oid = I('oid');
        $saleser = M('saleser');
        $verification = M('verification');
        $salesermap['mobile'] = "$mobile";
        /*------------------核销*订单参数查询--------------------*/
        $order = M('order');
        $orderinfo = $order->field('money,username,cometime,comestime,comeetime,uid')->where('id=' . $oid)->find();
        if ($orderinfo == false) {
            return_err('订单查询错误~');
        }
        /*------------------核销*会员openID查询--------------------*/
        if ($orderinfo['uid'] > 0) {
            $member = M('member');
            $memberinfo = $member->field('openid')->where(array('id' => $orderinfo['uid']))->find();
            if ($memberinfo == false) {
                return_err('会员openID查询错误~');
            }
            $memberinfoopenid = $memberinfo['openid'];
        } else {
            $memberinfoopenid = '';
        }
        $saleserinfo = $saleser->field('id,openid')->where($salesermap)->find();
        if ($saleserinfo == false) {
            return_err('店员不存在');
        }
        $data['saleser_id'] = $saleserinfo['id'];
        $data['oid'] = $oid;
        $data['type'] = 0;
        $data['addtime'] = date('Y-m-d H:i:s');
        $add = $verification->add($data);
        if ($add == false) {
            return_err('核销失败');
        }
        $ordermap['id'] = $oid;
        $ordermap['status'] = 1;
        $orderupdate = $order->where($ordermap)->setField(array('status' => 2));
        if ($orderupdate == false) {
            return_err('订单修改状态错误');
        }
        /*------------------核销*店员*消息模板发送--------------------*/
        $money = $orderinfo['money'] / 100;
        $time = $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . ':00' . '-' . $orderinfo['comeetime'] . ':00';
        $data = array(
            'openid' => $saleserinfo['openid'],
            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/verificated',
            'first' => '订单已核销成功，信息如下。',
            'time' => date("Y-m-d H:i:s"),
            'name' => '预订时间：' . $time,
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
            'openid' => $memberinfoopenid,
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
        return_data('success');
    }

    //微信SDK接口 调取微信扫码接口
    public function GetSign()
    {
        $url = I('url');
        $appId = AppId;
        $timestamp = time();
        $nonceStr = getnonceStr();
        $ticket = $this->getjsapi_ticket();
        // $url = "https://shbs10014.shwo10016.cn/book/index.php/Home/Order/GetSign";
        $str = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($str);
        $data['appId'] = $appId;
        $data['timestamp'] = $timestamp;
        $data['nonceStr'] = $nonceStr;
        $data['signature'] = $signature;
        return_data($data);
        // $this->assign('list', $data);
        // $this->display('Order:order');
    }

    private function getjsapi_ticket()
    {
        $access_token = getAccess_token();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
        $date = doGet($url);
        $jsapi_ticket = json_decode($date);
        $ticket = $jsapi_ticket->ticket;
        return $ticket;
    }

    public function DepartureShow()
    {//离店结算展示
        $is_submit = 0;
        $sum_money = 0;
        $time = 0;
        $service_charge = 0;
        $remark = '';
        $oid = I('oid');
        if (!$oid > 0) {
            return_err('参数错误');
        }
        $order = M('order');
        $room = M('room');
        $member = M('member');
        $verification = M('verification');//通过核销记录表  查询出进店核销时间
        $level = M('level');
        $reservation_order = M('reservation_order');
        $reservation_orderinfo = $reservation_order->field('sum_money,time,service_charge,remark')->where('oid=' . $oid)->find();
        if ($reservation_orderinfo) {
            $is_submit = 1;
            $sum_money = $reservation_orderinfo['sum_money'];
            $time = $reservation_orderinfo['time'];
            $service_charge = $reservation_orderinfo['service_charge'];
            $remark = $reservation_orderinfo['remark'];
        }
        $verificationinfo = $verification->field('addtime')->where('oid=' . $oid)->find();
        if ($verificationinfo == false) {
            return_err('核销流水不存在');
        }
        $orderinfo = $order->field('gid,uid,order_no,username,mobile')->where('id=' . $oid)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误');
        }
        $roominfo = $room->field('title,deposit,sell')->where('id=' . $orderinfo['gid'])->find();
        $memberinfo = $member->field('level_id')->where('id=' . $orderinfo['uid'])->find();
        if ($memberinfo == false) {
            return_err('会员参数错误');
        }
        $levelinfo = $level->field('name,discount')->where('id=' . $memberinfo['level_id'])->find();
        if ($levelinfo == false) {
            return_err('等级参数错误');
        }
        $return_data = array(
            'title' => $roominfo['title'],
            'levelname' => $levelinfo['name'],
            'order_no' => $orderinfo['order_no'],
            'username' => $orderinfo['username'],
            'mobile' => $orderinfo['mobile'],
            'cometime' => $verificationinfo['addtime'],
            'leavetime' => date('Y-m-d H:i:s'),
            'sell' => $roominfo['sell'],
            'deposit' => $roominfo['deposit'],
            'discount' => $levelinfo['discount'],
            'sum_money' => $sum_money,
            'time' => $time,
            'service_charge' => $service_charge,
            'remark' => $remark,
            'is_submit' => $is_submit
        );
        return_data($return_data);
    }

    public function DepartureSubmit()
    {//离店数据提交
        $title = I('title');
        $mobile = I('mobile');//店员手机号
        $oid = I('oid');
        $stime = I('stime');
        $etime = I('etime');
        $time = I('time');
        $service_charge = I('service_charge');
        $sum_money = I('sum_money');
        $discount = I('discount');
        $remark = I('remark');
        $sell = I('sell');//单价
        $order = M('order');
        $saleser = M('saleser');
        $member = M('member');
        $saleserinfo = $saleser->field('id')->where(array('mobile' => "$mobile"))->find();
        if ($saleserinfo == false) {
            return_err('店员参数错误');
        }
        $orderinfo = $order->where('id=' . $oid)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误');
        }
        $reservation_order = M('reservation_order');
        $reservation_orderinfo = $reservation_order->field('id')->where('oid=' . $oid)->find();
        if ($reservation_orderinfo) {
            return_err('订单已经被结算');
        }
        $memberinfo = $member->field('level_id,openid')->where('id=' . $orderinfo['uid'])->find();
        if ($memberinfo == false) {
            return_err('会员参数错误');
        }
        $data['title'] = $title;
        $data['uid'] = $orderinfo['uid'];
        $data['oid'] = $oid;
        $data['saleser_id'] = $saleserinfo['id'];//店员id
        $data['order_no'] = $orderinfo['order_no'];
        $data['username'] = $orderinfo['username'];
        $data['mobile'] = $orderinfo['mobile'];
        $data['level_id'] = $memberinfo['level_id'];//会员等级id
        $data['stime'] = $stime;
        $data['etime'] = $etime;
        $data['time'] = $time;
        $data['service_charge'] = $service_charge;
        $data['total_price'] = $sell;//单价
        $data['deposit'] = $orderinfo['money'];//定金
        $data['discount'] = $discount;//折扣
        $data['sum_money'] = $sum_money;
        $data['remark'] = $remark;
        $data['addtime'] = date('Y-m-d H:i:s');
        $reservation_order = M('reservation_order');
        $add = $reservation_order->add($data);
        if ($add == false) {
            return_err('生成支付单失败');
        }
        /*------------------离店*会员*消息模板发送--------------------*/
        $money = $sum_money / 100;
        $data = array(
            'openid' => $memberinfo['openid'],
            'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/user/index.html#/leaveShop?oid=' . $oid,
            'first' => $orderinfo['username'] . '您好，您有一个待支付订单需要支付，信息如下。',
            'order_no' => $orderinfo['order_no'],
            'roomname' => GetRoomname($orderinfo['gid']),
            'money' => $money,
            'remark' => '如对上述订单核销有异议，请联系客服人员协助处理。',
        );
        $message = new Message;
        $returndata = $message->OrderAwait($data);
        if ($returndata->errcode != 0) {
            Log::record('离店*会员*消息模板发送失败' . json_encode($data));
        }
        return_data('success');
    }

    public function Departure()//店员离店  完成订单
    {
        $mobile = I('mobile');//店员手机号
        $oid = I('oid');
        $type = I('type');//1  线上  2  线下 支付
        $order = M('order');
        $saleser = M('saleser');
        $saleserinfo = $saleser->field('id')->where(array('mobile' => "$mobile"))->find();
        if ($saleserinfo == false) {
            return_err('店员参数错误');
        }
        $upate = $order->where("id=$oid")->setField(array('saleser_id' => $saleserinfo['id'], 'saleser_paytype' => $type, 'status' => 3));
        if ($upate == false) {
            return_err('店员参数错误');
        }
        return_data('success');
    }

    public function PostOrder()//店员代客下单
    {
        $saleser = M('saleser');
        $order = M('order');
        $orderdata['replace_id'] = I('saleser_id');//代客下单店员id
        $orderdata['gid'] = I('gid');//房间
        $orderdata['cometime'] = I('cometime');
        $orderdata['comestime'] = I('comestime');
        $orderdata['comeetime'] = I('comeetime');
        $orderdata['sumhour'] = $orderdata['comeetime'] - $orderdata['comestime'];
        $orderdata['username'] = I('username');
        $orderdata['mobile'] = I('mobile');
        $orderdata['num'] = I('num');
        $orderdata['status'] = 1;//店员下单不用支付直接到待使用状态
        // $orderdata['money'] = I('money');
        $orderdata['addtime'] = date('Y-m-d H:i:s');
        $map['id'] = I('saleser_id');
        $map['status'] = 1;
        $saleserinfo = $saleser->field('id')->where($map)->find();
        if ($saleserinfo == false) {
            return_err('店员参数错误');
        }
        $ordermap['gid'] = $orderdata['gid'];
        $ordermap['cometime'] = $orderdata['cometime'];
        $ordermap['comestime'] = $orderdata['comestime'];
        $ordermap['comeetime'] = $orderdata['comeetime'];
        $ordermap['status'] = array(0, 1, 2, 'or');
        $orderselect = $order->field('id')->where($ordermap)->find();
        if ($orderselect) {
            return_err('该时间已经被预订');
        }
        $order_no = GetOrder_no($orderdata['uid']);
        $orderdata['order_no'] = $order_no;
        if (!$orderdata['replace_id'] > 0) {
            return_err('saleser参数错误');
        }
        if (strtotime($orderdata['cometime']) < strtotime(date('Y-m-d'))) {
            return_err('预订时间错误');
        }
        if (!$orderdata['gid'] > 0) {
            return_err('GID参数错误');
        }
        if (!$orderdata['sumhour'] > 0) {
            return_err('Time参数错误');
        }
        /* if (!$orderdata['money'] > 0) {
             return_err('money参数错误');
         }*/
        if (empty($orderdata['cometime'])) {
            return_err('cometime参数错误');
        }
        $add = $order->add($orderdata);
        if ($add) {
            $ordermap['order_no'] = "$order_no";
            $orderinfo = $order->field('id')->where($ordermap)->find();
            if ($orderinfo == false) {
                return_err('订单参数有误');
            }
            return_data($orderinfo);

        } else {
            return_err('订单提交失败');
        }
    }

    public function orderInvalid()//店员作废订单
    {
        $saleser_id = I('saleser_id');//代客下单店员id
        $oid = I('oid');//代客下单店员id
        $order=M('order');
        if (!$saleser_id > 0) {
            return_err('店员参数错误');
        }
        if (!$oid > 0) {
            return_err('订单参数错误');
        }
        $map['id']=$oid;
        $update=$order->where($map)->setField(array('status'=>4,'saleser_id'=>$saleser_id));
        if ($update==false){return_err('请重试');}
        return_data('success');
    }

    //发送验证码
    public function SendCode()
    {
        $mobile = I('mobile');
        if (empty($mobile)) {
            return_err('手机号不能为空');
        }
        $to = $mobile;
        $num = rand(100000, 999999);
        $long = 3;
        $send_res = $this->Send($to, $num, $long);
        if ($send_res == 'success') {
            $time = time();
            session("num", $num);
            session("telnum", $to);
            session("stime", $time);
            $snum = session("num");
            $stime = session("stime");
            $data = array(
                'code' => $snum,
                'sendtime' => $stime,
                "session_id" => session_id(),
                'content' => '验证码发送成功,请注意短信通知!!!',
            );
            Log::record(json_encode($data));
            return_data($data);
        } else {
            return_err('抱歉，验证码发送可能过于频繁，请稍后再试');
        }
    }

    public function Send($to, $num, $long)
    {
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        $accountSid = '8a48b551532ffdb401533212ce4205c5';
        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        $accountToken = 'c8556f722a804a32b8c572bc5ac87b45';
        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        $appId = 'aaf98f89532ff0cc015332174b6205dc';
        //请求地址
        //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
        //生产环境（用户应用上线使用）：app.cloopen.com
        $serverIP = 'app.cloopen.com';
        //请求端口，生产环境和沙盒环境一致
        $serverPort = '8883';
        //REST版本号，在官网文档REST介绍中获得。
        $softVersion = '2013-12-26';
        // 初始化REST SDK
        import('Org.Util.REST');
        $rest = new \REST($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $datas = array($num, $long);
        $tempId = 167215;
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
        if ($result == NULL) {
            return "result error!";
        }
        if ($result->statusCode != "000000") {
            return "error code :" . $result->statusCode . ",error msg :" . $result->statusMsg;
        } else {
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            $dateCreated = $smsmessage->dateCreated;
            $smsMessageSid = $smsmessage->smsMessageSid;
            return 'success';
        }
    }

    public function OnlineQrcode()//线下收款
    {
        $teahouse = teahouse;
        $sys_set = M('sys_set');
        $sys_setinfo = $sys_set->field('online_qrcode')->where('online_qrcode is not null')->find();
        if ($sys_setinfo == false) {
            return_err('商家暂时没有上传收款码~');
        }
        $data = array(
            'roomname' => $teahouse,
            'online_qrcode' => 'https://shbs10014.shwo10016.cn' . $sys_setinfo['online_qrcode']
        );
        return_data($data);
    }

    public function Getaccess_token()
    {
        $appid = AppId;
        $secret = AppSecret;
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $json = doGet($url);
        $array = json_decode($json);
        $access_token = $array->access_token;
        log::record("access_token:" . "$access_token");
        return $access_token;
    }
}