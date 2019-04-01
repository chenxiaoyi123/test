<?php

namespace Home\Controller;

use Think\Controller;

header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:http://localhost:8080');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class IndexController extends Controller
{
    public function GetAds()
    {//首页广告banner
        $banner = M('banner');
        $map['rid'] = 0;
        $map['is_show'] = 1;
        $list = $banner->field('name,short_title,banner_url,target_link')->where($map)->select();
        foreach ($list as $value => &$k) {
            $k['banner_url'] = "https://" . $_SERVER['HTTP_HOST'] . $k['banner_url'];
        }
        unset($k);
        if ($list) {
            return_data($list);
        } else {
            return_err('列表为空');
        }
    }

    public function RoomList()
    {//首页房间列表
        $room = M('room');
        $teahouse = M('teahouse');
        $teahouseinfo = $teahouse->where('id=1')->find();
        $map['sid'] = 1;
        $map['status'] = 1;
        $map['is_show'] = 1;
        $list = $room->field('sell,thumb_url,title,summary,id')->where($map)->order('sort desc')->select();
        foreach ($list as $value => &$k) {
            $k['thumb_url'] = "https://shbs10014.shwo10016.cn" . $k['thumb_url'];
        }
        unset($k);
        return_data(array('housename' => $teahouseinfo['name'], 'list' => $list));
        die();
    }
}

    
