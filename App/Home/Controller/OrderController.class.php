<?php
/**
 * Created by PhpStorm.
 * User: 97558
 * Date: 2018/11/21
 * Time: 15:40
 */

namespace Home\Controller;

use Think\Controller;
use Think\Log;
use Common\Core\Message;

define('AppId', 'wx7d0494284af1af9c');
define('AppSecret', 'cb4eaa39f11be828d8241ec877ce7009');
header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:http://localhost:8080');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class OrderController extends Controller
{
    public function GetReservationTime()
    {//返回出当前房间的信息
        $newtime = I('newtime');
        $room_id = I('gid');
        if (!$room_id > 0) {
            return_err('房间id错误');
        }
        if (empty($newtime)) {
            $newtime = date('Y-m-d');
        }
        $this->OrderStatus($room_id);
        $room = M('room');
        $order = M('order');
        $ordermap['gid'] = $room_id;
        $ordermap['cometime'] = $newtime;
        $ordermap['status'] = array(0, 1, 2, 3, 'or');
        $banner = M('banner');
        $bannerlist = $banner->field('banner_url,target_link')->where(array('rid' => $room_id, 'is_show' => 1))->select();
        foreach ($bannerlist as $value => &$k) {
            $k['banner_url'] = 'https://shbs10014.shwo10016.cn' . $k['banner_url'];
        }
        unset($k);
        $roominfo = $room->field('title,thumb_url,deposit,starttime,endtime')->where('id=' . $room_id)->find();
        if ($roominfo == false) {
            return_err('参数错误~');
        }

        $orderlist = $order->field('comestime,comeetime')->where($ordermap)->select();
        $status = 0;
        $newH = intval(date('H'));
        for ($i = $roominfo['starttime']; $i <= $roominfo['endtime']; $i++) {
            if (count($orderlist) > 0) {
                for ($j = 0; $j < count($orderlist); $j++) {
                    $comestime = $orderlist[$j]['comestime'];
                    $comeetime = $orderlist[$j]['comeetime'];
                    if ($i >= $comestime && $i < $comeetime) {
                        $status = 1;
                        break;
                    } else if (strtotime($newtime) == strtotime(date('Y-m-d')) && $newH > intval($i)) {
                        $status = 1;
                        break;
                    } else {
                        $status = 0;
                    }
                }
            } else {
                if (strtotime($newtime) == strtotime(date('Y-m-d')) && intval($i) < $newH) {
                    $status = 1;
                } else {
                    $status = 0;
                }
            }
            $begin_array[] = array('time' => intval($i), 'status' => $status);
        }
        for ($i = $roominfo['starttime']; $i <= $roominfo['endtime']; $i++) {
            for ($j = 0; $j < count($orderlist); $j++) {
                $comestime = $orderlist[$j]['comestime'];
                $comeetime = $orderlist[$j]['comeetime'];
                if ($i > $comestime && $i <= $comeetime) {
                    $status = 1;
                    break;
                } else {
                    $status = 0;
                }
            }
            $end_array[] = array('time' => intval($i), 'status' => $status);
        }
        $return_data = array(
            'title' => $roominfo['title'],
            'thumb_url' => $bannerlist,
            'deposit' => $roominfo['deposit'],
            'begin_list' => $begin_array,
            'end_list' => $end_array
        );
        return_data($return_data);
    }

    private function OrderStatus($room_id)
    {//释放出已经超时未支付的订单
        $order = M('order');
        $sys = M('sys_set');
        $ordermap['gid'] = $room_id;
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
    }

    public function PostOrder()
    {//保存订单
        $order = M('order');
        $orderdata['uid'] = I('uid');
        $orderdata['gid'] = I('gid');//房间
        $orderdata['cometime'] = I('cometime');
        $orderdata['comestime'] = I('comestime');
        $orderdata['comeetime'] = I('comeetime');
        $orderdata['sumhour'] = $orderdata['comeetime'] - $orderdata['comestime'];
        $orderdata['username'] = I('username');
        $orderdata['mobile'] = I('mobile');
        $orderdata['num'] = I('num');
        $orderdata['money'] = I('money');
        $orderdata['addtime'] = date('Y-m-d H:i:s');
        $map['id'] = I('uid');
        $map['status'] = 0;
        $member = M('member');
        $memberinfo = $member->field('id')->where($map)->find();
        if ($memberinfo == false) {
            return_err('会员已经被禁用');
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
        if (!$orderdata['uid'] > 0) {
            return_err('UID参数错误');
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
        if (!$orderdata['money'] > 0) {
            return_err('money参数错误');
        }
        if (empty($orderdata['cometime'])) {
            return_err('cometime参数错误');
        }
        $add = $order->add($orderdata);
        if ($add) {
            $ordermap['order_no'] = "$order_no";
            //    Log::record('订单单号：'.$order_no);
            $orderinfo = $order->field('id')->where($ordermap)->find();
            //  Log::record('mysql'.$orderinfo);die();
            if ($orderinfo == false) {
                return_err('订单参数有误');
            }
            return_data($orderinfo);

        } else {
            return_err('订单提交失败');
        }
    }

    public function CountDown()//未支付订单保留倒计时
    {
        $oid = intval(I('oid'));
        $order = M('order');
        $sys = M('sys_set');
        $member = M('member');
        $orderinfo = $order->field('money,addtime,uid')->where('id=' . $oid)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误');
        }
        $memberinfo = $member->field('balance')->where('id=' . $orderinfo['uid'])->find();
        if ($memberinfo == false) {
            return_err('会员参数错误');
        }
        $sysinfo = $sys->field('prepaid_time')->where('id=1')->find();
        if ($sysinfo) {
            if ($sysinfo['prepaid_time'] > 0) {
                $prepaid_time = $sysinfo['prepaid_time'];//未支付的订单保留时间
            }
        }
        $newTime = time();
        $ordertime = strtotime($orderinfo['addtime']);
        $time = $newTime - $ordertime;
        $return_time = $prepaid_time * 60 - $time;//剩余时间
        if ($return_time < 0) {
            $return_time = 0;
        }
        $arr = array(
            'money' => $orderinfo['money'],
            'balance'=>$memberinfo['balance'],
            'CountDownTime' => $return_time,
        );
        return_data($arr);
    }

    public function GetOrderInfo()
    {//订单详情
        $oid = I('oid');
        $order = M('order');
        $ordermap['id'] = $oid;
        $ordermap['status'] = 1;
        $orderinfo = $order->where($ordermap)->find();
        if ($orderinfo == false) {
            return_err('订单参数错误~');
        }
        if (empty($orderinfo['qrcode_url'])) {
            $qrcode_url = Qrcode($oid);
            $qrcodeupdate = $order->where($ordermap)->setField(array('qrcode_url' => $qrcode_url));
            if ($qrcodeupdate == false) {
                return_err('二维码生成出错~请刷新页面');
            }
        } else {
            $qrcode_url = $orderinfo['qrcode_url'];
        }
        $sys = M('sys_set');
        $sysinfo = $sys->where('id=1')->find();
        $data = array(
            'username' => $orderinfo['username'],
            'mobile' => $orderinfo['mobile'],
            'cometime' => $orderinfo['cometime'] . ' ' . $orderinfo['comestime'] . '-' . $orderinfo['comeetime'],
            'money' => $orderinfo['money'],
            'unsubscribe_order_time' => $sysinfo['refund_time'],
            'qrcode_url' => 'https://shbs10014.shwo10016.cn/book' . $qrcode_url,
        );
        return_data($data);
    }

    public function Departure()//用户提交离店申请
    {
        $oid = I('oid');//订单id
        if (!$oid > 0) {
            return_err('订单参数错误');
        }
        $order = M('order');
        $orderinfo = $order->field('gid,username,mobile')->where("id=$oid")->find();
        if ($orderinfo == false) {
            return_err('订单参数错误');
            Log::record('离店时查询定订单数据错误oid:' . $oid);
        }
        /*------------------店员*发送离店结算申请*消息模板---------------------*/
        $saleser = M('saleser');
        $saleserlist = $saleser->field('openid,username')->where('openid is not null')->select();
        if ($saleserlist == false) {
            return_err('发送离店消息失败，店员参数错误');
            Log::record('发送离店消息失败，店员参数错误data:' . json_encode($saleserlist));
        }
        foreach ($saleserlist as $value => &$k) {
            $data = array(
                'openid' => $k['openid'],
                'url' => 'https://shbs10014.shwo10016.cn/book/bookOrder/clerk/index.html#/leaveShop?oid=' . $oid,
                'first' => $k['username'] . '店员您好，' . $orderinfo['username'] . '先生/女士，申请离店结算，信息如下。',
                'roomname_username' => GetRoomname($orderinfo['gid']) . '房间:' . $orderinfo['username'] . '先生/女士',
                'excuse' => '离店结算',
                'remark' => '如有问题请尽快处理并通知客户。客户手机号:' . $orderinfo['mobile'],
            );
            $message = new Message;
            $message->ApplyPayment($data);
        }
        unset($k);
        return_data('success');
    }
}