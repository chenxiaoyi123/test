<?php
/**
 * Created by PhpStorm.
 * User: 97558
 * Date: 2018/11/14
 * Time: 9:21
 */

namespace Home\Controller;

use Think\Log;
use Think\Controller;
use Common\Core\Message;

define('AppId', 'wx7d0494284af1af9c');
define('AppSecret', 'cb4eaa39f11be828d8241ec877ce7009');
define('openID', 'oAzy9swhz-Geee_EeuJbocnasHZ0');
header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:http://localhost:8080');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class UserController extends Controller
{
    public function Login()
    {//登录
        $openid = I('openid');
        $mobile = I('mobile');
        $code = I('code');
        /*=============== 验证验证码开始=================*/
        $session_time = session("stime");
        $sesssion_telnum = session("telnum");
        $session_num = session("num");
        $time = time();
        Log::record("session：" . $session_time . "sesssion_telnum:" . $sesssion_telnum);
        if (empty($session_time) || empty($session_num) || empty($sesssion_telnum)) {
            return_err("抱歉，请先获取验证码");
        } else if (($time - $session_time) >= 3 * 60) {
            return_err("已超时，验证码失效!");
        } else if ($code != $session_num) {
            return_err("验证码不一致!");
        }
        /*=============== 验证验证码结束=================*/
        $map['openid'] = "$openid";
        $member = M('member');
        $userdata = $member->where($map)->find();
        if ($userdata['mobile'] != $mobile) {
            $update = $member->where($map)->setField(array('mobile' => $mobile));
            if ($update == false) {
                return_err('加入会员失败');
            }
            $userdata = $member->where($map)->find();
        }
        return_data($userdata);
    }

    public function GetUserInfo()
    {//个人中心
        $uid = I('uid');
        $map['id'] = "$uid";
        $map['status'] = 0;
        $member = M('member');
        if (!$uid > 0) {
            require_cache('id参数错误');
        }
        $memberinfo = $member->field('mobile,balance,headimgurl,nickname')->where($map)->find();
        if ($memberinfo == false) {
            return_err('参数错误');
        }
        $sys = M('sys_set');
        $sysinfo = $sys->field('is_cash')->where('id=1')->find();
        $data = array(
            'headimgurl' => $memberinfo['headimgurl'],
            'nickname' => $memberinfo['nickname'],
            'mobile' => $memberinfo['mobile'],
            'balance' => $memberinfo['balance'],
            'is_cash' => $sysinfo['is_cash']//允许提现：0- no accept cash;1-yes
        );
        return_data($data);
    }

    public function UserOrder()
    {//查询用户订单
        $status = I('status');//0  待付款  1  待使用  2  已核销   3   已完成   4  已作废  5 已退款
        $uid = I('uid');
        $p = I('page');//页数
        $row = I('row');//每页条数
        $p = empty($p) ? 1 : $p;
        $row = empty($row) ? 5 : $row;//每页显示条数
        $offset = ($p - 1) * $row;
        $limit = $offset . ", " . $row;
        $order = M('order');
        $map['uid'] = $uid;
        $map['status'] = $status;
        if ($status == 1) {
            $this->orderInvalid($uid);
        }
        $orderlist = $order->where($map)->order('id desc')->limit($limit)->select();
        foreach ($orderlist as $value => &$k) {
            $k['roomname'] = $this->GetRoomname($k['gid']);
        }
        unset($k);
        return_data($orderlist);
    }

    private function orderInvalid($uid)//作废掉 过了预订结束时间未使用订单
    {
        $newH = date('H');
        $newtime = strtotime(date('Y-m-d'));
        $map['UNIX_TIMESTAMP(cometime)'] = array('lt', $newtime);
        $map['uid'] = $uid;
        $map['status'] = 1;
        $order = M('order');
        $order->where($map)->setField(array('status'=>4));
        $map1['UNIX_TIMESTAMP(cometime)']=$newtime;
        $map1['uid'] = $uid;
        $map1['status'] = 1;
        $map1['comeetime'] = array('lt', $newH);
        $order->where($map1)->setField(array('status'=>4));
    }

    private function GetRoomname($rid)
    {
        $room = M('room');
        $roominfo = $room->field('title')->where('id=' . $rid)->find();
        //释放出已经超时未支付的订单
        $order = M('order');
        $sys = M('sys_set');
        $ordermap['gid'] = $rid;
        $ordermap['status'] = 0;
        $newTime = time();
        $prepaid_time = 15;
        $sysinfo = $sys->field('prepaid_time')->where('id=1')->find();
        if ($sysinfo) {
            if ($sysinfo['prepaid_time'] > 0) {
                $prepaid_time = $sysinfo['prepaid_time'];
            }
        }
        $time = $newTime - $prepaid_time * 60;
        $newdate = date("Y-m-d H:i:s", $time);
        $ordermap['addtime'] = array('lt', $newdate);
        $order->where($ordermap)->setField(array('status' => 4));
        return $roominfo['title'];
    }

    public function DepartureLook()
    {//会员端离店查看订单详情
        $oid = I('oid');//订单的id
        if (!$oid > 0) {
            return_err('参数错误');
        }
        $reservation_order = M('reservation_order');
        $reservation_orderinfo = $reservation_order->where('oid=' . $oid)->order('id desc')->find();
        if ($reservation_orderinfo == false) {
            return_err('订单参数错误');
        }
        return_data($reservation_orderinfo);
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

    public function index()
    {
        $appid = AppId;
        $backurl = urlencode("https://shbs10014.shwo10016.cn/book/index.php/Home/User/GetopenID");
        $url = " https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$backurl&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
        header('Location:' . $url);
        exit;
    }

    public function GetopenID()
    {//获取微信openID
        $code = I('code');
        if (empty($code)) {
            return_err('code不能为空');
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
        $insert = $this->InsertUser($openid);
        if ($insert) {
            return_data($openid);
        } else {
            return_err('插入用户信息失败！');
        }

    }

    private function InsertUser($openid)
    {//微信用户信息
        $member = M('member');
        $map['openid'] = "$openid";
        $userinfo = $member->where($map)->find();
        if ($userinfo) {
            return true;
        } else {
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
                $userdata['openid'] = $output->openid;
            } elseif ($subscribe == "1") {
                $userdata['subscribe'] = $output->subscribe;
                $userdata['openid'] = $output->openid;
                $userdata['nickname'] = $output->nickname;
                $userdata['sex'] = $output->sex;
                $userdata['city'] = $output->city;
                $userdata['province'] = $output->province;
                $userdata['country'] = $output->country;
                $userdata['headimgurl'] = $output->headimgurl;
                $userdata['subscribe_time'] = date("Y-m-d H:i:s", $output->subscribe_time);
                $userdata['addtime'] = date("Y-m-d H:i:s");
                $userdata['updatetime'] = date("Y-m-d H:i:s");
            }
            $add = $member->add($userdata);
            if ($add) {
                return true;
            } else {
                Log::record('插入微信数据失败！');
                return false;
            }
        }
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