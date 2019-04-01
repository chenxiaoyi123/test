<?php
/**
 * Created by PhpStorm.
 * User: 13838
 * Date: 2018/11/13
 * Time: 8:43
 */
namespace Home\Controller;
use Common\Core\Fun;
use Common\Core\Method;
use Common\Core\Message;
use Think\Controller;
use Think\Log;

header('Access-Control-Allow-Origin:*');

class ApiController extends Controller{
    /* ---------------------------登录------------------------- */
    public function login(){
        $username = Fun::request('username');
        $password = Fun::request('password');

        if (empty($username)) Method::return_err("用户名为空");
        if (empty($password)) Method::return_err("密码不允许为空");

        $password = Method::encryptPassword($password);

        $AdminModel = M('Admin');
        $rs = $AdminModel->where("username='".$username."' and password='".$password."'")->find();
        if ($rs){
            Method::return_data(array('data'=>$rs));
        }else{
            Method::return_err("登录失败");
        }
    }

    public function modifyPass(){
        $username = Fun::request('username');
        $oldpass = Fun::request('oldpass');
        $newpass = Fun::request('newpass');
        $repass = Fun::request('repass');

        $newpass = Method::encryptPassword($newpass);
        $repass = Method::encryptPassword($repass);

        $AdminModel = M('Admin');
        $admin = $AdminModel->where("username='".$username."'")->find();

        $data = array();
        $data['password'] = $newpass;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $AdminModel->where("username='".$username."'")->save($data);
        if ($rs) {
            $info = $AdminModel->where("username='".$username."'")->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("修改密码失败");
        }
    }

    /* ---------------------------系统设置------------------------- */
    public function updateSysSet(){
        $is_cash = Fun::requestInt('is_cash', 0);
        $page_num = Fun::requestInt('page_num', 0);
        $prepaid_time = Fun::requestInt('prepaid_time', 0);
        $refund_time = Fun::request('refund_time');
        $online_qrcode = Fun::request('online_qrcode');
        $operator = Fun::request('operator');

        if (empty($page_num)) Method::return_err("每页显示数不允许为空");
        if (empty($prepaid_time)) Method::return_err("预支付时间不允许为空");

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->field(array('is_cash','page_num','prepaid_time'))->find();

        $data = array();
        $data['is_cash'] = $is_cash;
        $data['page_num'] = $page_num;
        $data['prepaid_time'] = $prepaid_time;
        $data['refund_time'] = $refund_time;
        $data['online_qrcode'] = $online_qrcode;
        $rs = $SysSetModel->where("id=1")->save($data);
        if ($rs){
            $record = "管理员更新了系统设置：原记录：".json_encode($sys_set_info)."; 更新记录：".json_encode($data);
            $this->insertRecord($operator,$record,1);
            Method::return_data(array('data'=>$rs));
        }else{
            $record = "管理员试图更新系统设置失败：原记录：".json_encode($sys_set_info)."; 更新记录：".json_encode($data);
            $this->insertRecord($operator,$record,0);
            Method::return_err("设置失败");
        }
    }

    public function getSysSetInfo(){
        $SysSetModel = M('SysSet');
        $info = $SysSetModel->where("id=1")->find();
        Method::return_data(array('data'=>$info));
    }

    /* ---------------------------包间------------------------- */
    public function insertRoom(){
        $title = Fun::request('title');
        $summary = Fun::request('summary');
        $starttime = Fun::request('starttime');
        $endtime = Fun::request('endtime');
        $sid = Fun::requestInt('sid',1);
        $deposit = Fun::requestInt('deposit', 0);
        $price = Fun::requestInt('price',0);
        $sell = Fun::requestInt('sell',0);
        $thumb_url = Fun::request('thumb_url');
        $status = Fun::requestInt('status');

        if (empty($title)) Method::return_err("包间名称不允许为空");
        if (empty($sell)) Method::return_err("售价不允许为空");
        if (empty($deposit)) Method::return_err("押金不允许为空");
        if (empty($thumb_url)) Method::return_err("包间缩略图不允许为空");

        $starttime_array = explode(":", $starttime);
        $endtime_array = explode(":", $endtime);
        $starttime_str = $starttime_array[0];
        $endtime_str = $endtime_array[0];

        $data = array();
        $data['title'] = $title;
        $data['summary'] = $summary;
        $data['starttime'] = $starttime_str;
        $data['endtime'] = $endtime_str;
        $data['sid'] = $sid;
        $data['deposit'] = $deposit * 100;
        $data['price'] = $price * 100;
        $data['sell'] = $sell * 100;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['thumb_url'] = $thumb_url;
        $data['status'] = $status;

//        echo json_encode(array('data'=>$data));exit;

        $RoomModel = M('Room');
        $rs = $RoomModel->add($data);
        if ($rs){
            $data['id'] = $rs;
            Method::return_data(array('data'=>$data));
        }else{
            Method::return_err("添加包间失败");
        }
    }

    public function updateRoom(){
        $id = Fun::requestInt('id');
        $title = Fun::request('title');
        $summary = Fun::request('summary');
        $starttime = Fun::request('starttime');
        $endtime = Fun::request('endtime');
        $deposit = Fun::requestInt('deposit', 0);
        $sid = Fun::requestInt('sid',1);
        $price = Fun::requestInt('price',0);
        $sell = Fun::requestInt('sell',0);
        $thumb_url = Fun::request('thumb_url');
        $status = Fun::requestInt('status');

        if (empty($title)) Method::return_err("包间名称不允许为空");
        if (empty($sell)) Method::return_err("售价不允许为空");
        if (empty($deposit)) Method::return_err("押金不允许为空");
        if (empty($thumb_url)) Method::return_err("包间缩略图不允许为空");

        $starttime_array = explode(":", $starttime);
        $endtime_array = explode(":", $endtime);
        $starttime_str = $starttime_array[0];
        $endtime_str = $endtime_array[0];

        $data = array();
        $data['title'] = $title;
        $data['summary'] = $summary;
        $data['starttime'] = $starttime_str;
        $data['endtime'] = $endtime_str;
        $data['sid'] = $sid;
        $data['deposit'] = $deposit * 100;
        $data['price'] = $price * 100;
        $data['sell'] = $sell * 100;
        $data['updatetime'] = date('Y-m-d H:i:s');
        $data['thumb_url'] = $thumb_url;
        $data['status'] = $status;

//        echo json_encode(array('data'=>$data));exit;

        $RoomModel = M('Room');
        $rs = $RoomModel->where("id=".$id)->save($data);
        if ($rs){
            $info = $RoomModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("编辑包间失败");
        }
    }

    public function getRoomList(){
        $page = Fun::requestInt('page', 1);
        $keywords = Fun::request('keywords');

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($keywords)){
            $where .= " and title like '%".$keywords."%'";
        }

        $RoomModel = M('Room');
        $list = $RoomModel->where($where)->order('sort DESC,id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['price'] = Method::getFloatYuanByFen($row['price']);
            $row['sell'] = Method::getFloatYuanByFen($row['sell']);
            $row['deposit'] = Method::getFloatYuanByFen($row['deposit']);
            $row['id'] = intval($row['id']);
            $row['starttime'] = $row['starttime'].":00";
            $row['endtime'] = $row['endtime'].":00";
            $row['is_show'] = intval($row['is_show']);
            $row['sid'] = intval($row['sid']);
            $row['status'] = intval($row['status']);
            $row['sort'] = intval($row['sort']);
        }
        $count = $RoomModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>intval($count),'page_size'=>intval($limit)));
    }

    public function getRoomDetail(){
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("包间id不允许为空");

        $RoomModel = M('Room');
        $info = $RoomModel->where("id=".$id)->find();
        $info['starttime'] = $info['starttime'].":00";
        $info['endtime'] = $info['endtime'].":00";
        Method::return_data(array('data'=>$info));
    }

    public function deleteRoom(){
        $id = Fun::request('id');

        if (empty($id)) Method::return_err("包间id不允许为空");

        $RoomModel = M('Room');
        $rs = $RoomModel->where("id in(".$id.")")->delete();
        Method::return_data(array('data'=>$rs));
    }

    public function changeRoomShow(){
        $operator = Fun::request('operator');
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("包间id不允许为空");

        $RoomModel = M('Room');
        $room_info = $RoomModel->where("id=".$id)->find();

        $data = array();
        $data['is_show'] = $room_info['is_show'] == 1 ? 0 : 1;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $RoomModel->where("id=".$id)->save($data);
        if ($rs){
            $record = "管理员{$operator}改变包间id为{$id}的展示状态";
            $this->insertRecord($operator, $record, 1);
            $data['id'] = $id;
            Method::return_data(array('data'=>$data));
        }else{
            $record = "管理员{$operator}试图改变包间id为{$id}的展示状态失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("更改包间状态失败");
        }

    }

    public function saveRoomSort(){
        $sort = Fun::requestInt('sort', 0);
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("包间id不允许为空");

        $RoomModel = M('Room');

        $data = array();
        $data['sort'] = $sort;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $RoomModel->where("id=".$id)->save($data);
        if ($rs){
            $data['id'] = $id;
            Method::return_data(array('data'=>$data));
        }else{
            Method::return_err("更改包间排序失败");
        }

    }

    /* ------------------ 茶楼 ------------------------ */
    /* 茶楼详情 */
    public function getTeahouseDetail(){
        $TeahouseModel = M('Teahouse');
        $info = $TeahouseModel->where("id=1")->find();
        Method::return_data(array('data'=>$info));
    }

    public function updateTeahouse(){
        $id = Fun::requestInt('id', 0);
        $name = Fun::request('name');
        $tag = Fun::request('tag');
        $status = Fun::requestInt('status', 0);

        $data = array();
        $data['name'] = $name;
        $data['tag'] = $tag;
        $data['status'] = $status;
        $TeahouseModel = M('Teahouse');
        $rs = $TeahouseModel->where("id=".$id)->save($data);
        if ($rs) {
            $teahouse = $TeahouseModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$teahouse));
        }else{
            Method::return_err('更新失败');
        }

    }

    /* ------------------- 店员 ------------------------- */
    public function getSaleserList(){
        $page = Fun::requestInt('page', 1);
        $keywords = Fun::request('keywords');

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($keywords)){
            $where .= " and mobile like '%".$keywords."%'";
        }

        $SaleserModel = M('Saleser');
        $list = $SaleserModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        $count = $SaleserModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    public function getSaleserDetail(){
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("店员id不允许为空");

        $SaleserModel = M('Saleser');
        $info = $SaleserModel->where("id=".$id)->find();
        Method::return_data(array('data'=>$info));
    }

    public function insertSaleser(){
        $username = Fun::request('username');
        $password = Fun::request('password');
        $mobile = Fun::request('mobile');
        $avatar = Fun::request('avatar');

        if (empty($username)) Method::return_err("店员名称不允许为空");
        if (empty($mobile)) Method::return_err("店员手机号不允许为空");
        $avatar = !empty($avatar) ? $avatar : '/book/Uploads/2018-11-20/5bf3760562c3a.jpg';

        $SaleserModel = M('Saleser');
        $isMobileExist = $SaleserModel->where("mobile=".$mobile)->find();
        if ($isMobileExist) Method::return_err("该手机号已经存在");

        $data = array();
        $data['username'] = $username;
        $data['password'] = md5(sha1(md5($password)));
        $data['mobile'] = $mobile;
        $data['avatar'] = $avatar;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = 1;

//        echo json_encode(array('data'=>$data));exit;


        $rs = $SaleserModel->add($data);
        if ($rs){
            $data['id'] = $rs;
            Method::return_data(array('data'=>$data));
        }else{
            Method::return_err("添加店员失败");
        }
    }

    public function updateSaleser(){
        $id = Fun::requestInt('id');
        $username = Fun::request('username');
        $mobile = Fun::request('mobile');
        $avatar = Fun::request('avatar');

        if (empty($username)) Method::return_err("店员名称不允许为空");
        if (empty($mobile)) Method::return_err("店员手机号不允许为空");
        $avatar = !empty($avatar) ? $avatar : '/book/Uploads/2018-11-20/5bf3760562c3a.jpg';

        $data = array();
        $data['username'] = $username;
        $data['mobile'] = $mobile;
        $data['avatar'] = $avatar;
        $data['updatetime'] = date('Y-m-d H:i:s');
        $data['status'] = 1;

//        echo json_encode(array('data'=>$data));exit;

        $SaleserModel = M('Saleser');
        $rs = $SaleserModel->where("id=".$id)->save($data);
        if ($rs){
            $info = $SaleserModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("编辑店员失败");
        }
    }

    public function deleteSaleser(){
        $id = Fun::request('id');

        if (empty($id)) Method::return_err("店员id不允许为空");

        $SaleserModel = M('Saleser');
        $rs = $SaleserModel->where("id in(".$id.")")->delete();
        Method::return_data(array('data'=>$rs));
    }

    public function modifySaleser(){
        $id = Fun::requestInt('id', 0);
        $oldpass = Fun::request('oldpass');
        $newpass = Fun::request('newpass');
        $repass = Fun::request('repass');

        $newpass = Method::encryptPassword($newpass);
        $repass = Method::encryptPassword($repass);

        $SaleserModel = M('Saleser');
        $saleser = $SaleserModel->where("id=".$id)->find();
        if ($saleser['password'] != $oldpass) Method::return_err('原密码错误');
        if ($oldpass == $newpass) Method::return_err('密码未修改');
        if ($newpass != $repass) Method::return_err('2次密码不一致');

        $data = array();
        $data['password'] = $newpass;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $SaleserModel->where("id=".$id)->save($data);
        if ($rs) {
            $info = $SaleserModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("修改密码失败");
        }
    }


    /* -------------------- 会员 ------------------------- */
    public function getMemberList(){
        $page = Fun::requestInt('page', 1);
        $username = Fun::request('username');
        $sex = Fun::requestInt('sex', 0);
        $mobile = Fun::request('mobile');

        $SysSetModel = M('SysSet');
        $LevelModel = D('Level');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($username)) $where .= " and username='".$username."'";
        if (!empty($sex)) $where .= " and sex=".$sex;
        if (!empty($mobile)) $where .= " and mobile like '%".$mobile."%'";

        $MemberModel = M('Member');
        $list = $MemberModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['balance'] = Method::getFloatYuanByFen($row['balance']);
            $row['total'] = Method::getFloatYuanByFen($row['total']);
            $row['level_name'] = $LevelModel->getLevelNameById($row['level_id']);
        }
        $count = $MemberModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    public function getMemberDetail(){
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("会员id不允许为空");

        $MemberModel = M('Member');
        $info = $MemberModel->where("id=".$id)->find();
        Method::return_data(array('data'=>$info));
    }

    public function disabledMember(){
        $id = Fun::requestInt('id', 0);
        $operator = Fun::request('operator');

        if (empty($id)) Method::return_err('参数错误,会员id为空');

        $MemberModel = M('Member');

        $data = array();
        $params = array();
        $data['status'] = 1;  //禁用状态
        $data['update'] = date('Y-m-d H:i:s');
        $params['addtime'] = date('Y-m-d H:i:s');
        $rs = $MemberModel->where("id=".$id)->save($data);
        if ($rs){
            $record = "管理员admin禁用了id为{$id}的用户";
            $this->insertRecord($operator, $record, 1);
            Method::return_data(array('data'=>$rs));
        }else{
            $record = "管理员admin试图禁用了id为{$id}的用户失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err('禁用用户失败');
        }

    }

    public function cancelDisabledMember(){
        $id = Fun::requestInt('id', 0);
        $operator = Fun::request('operator');

        if (empty($id)) Method::return_err('参数错误,会员id为空');

        $MemberModel = M('Member');

        $data = array();
        $data['status'] = 0;  //正常状态
        $data['update'] = date('Y-m-d H:i:s');

        $rs = $MemberModel->where("id=".$id)->save($data);
        if ($rs){
            $record = "管理员admin取消禁用了id为{$id}的用户";
            $this->insertRecord($operator, $record, 1);
            Method::return_data(array('data'=>$rs));
        }else{
            $record = "管理员admin试图取消禁用了id为{$id}的用户失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err('禁用用户失败');
        }

    }

    public function recharge(){
        $uid = Fun::requestInt('uid', 0);
        $out_trade_no = 'HT'.Method::getRandomOrderNo($uid);
        $money = Fun::requestInt('money', 0);
        $operator = Fun::request('operator');

        if (empty($uid)) Method::return_err("会员id不允许为空");
        if (empty($money)) Method::return_err("充值金额不允许为空");

        $money = $money*100;  //元转化分
        $data = array();
        $data['uid'] = $uid;
        $data['out_trade_no'] = $out_trade_no;
        $data['type'] = 1;
        $data['money'] = $money;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['result_code'] = 'SUCCESS';
        $data['status'] = 1;

        $RechargeModel = M('Recharge');
        $MemberModel = D('Member');
        $LevelModel = D('Level');
        $rs = $RechargeModel->add($data);
        if ($rs){
            $member = $MemberModel->where("id=".$uid)->find();
            $params = array();
            $params['balance'] = $member['balance']+$money;
            $params['total'] = $member['total']+$money;
            /* 金额可能提升会员等级 */
            $level_array = $LevelModel->order("id ASC")->select();
            if (($member['total']+$money) >= $level_array[2]['upgrade_money'] ) {
                $params['level_id'] = 3;
                if ($member['level_id'] != 3){
                    $record = "管理员admin为id为{$rs}的用户充值了".Method::getFloatYuanByFen($money)."元, 会员等级提升为{$level_array[2]['name']}。";
                    $this->insertRecord($operator, $record, 1);
                }
            }elseif (($member['total']+$money) < $level_array[0]['upgrade_money']) {
                $params['level_id'] = 1;
            }else{
                $params['level_id'] =2;
                if ($member['level_id'] != 2) {
                    $record = "管理员admin为id为{$rs}的用户充值了".Method::getFloatYuanByFen($money)."元, 会员等级提升为{$level_array[1]['name']}。";
                    $this->insertRecord($operator, $record, 1);
                }
            }

            $record = "管理员admin为id为{$rs}的用户充值了".Method::getFloatYuanByFen($money)."元, 会员等级不变。";
            //添加操作记录
            $this->insertRecord($operator, $record, 1);

            $MemberModel->where("id=".$uid)->save($params);

            //最新用户信息
            $member = $MemberModel->where("id=".$uid)->find();
            $member['level_name'] = $LevelModel->getLevelNameById($member['level_id']);
            //余额变动消息模板
            $message = new Message();
            $message_array = array();
            $message_array['openid'] = $member['openid'];
            $message_array['first'] = "{$member['username']}您好，您的账户余额发生变动，信息如下。";
            $message_array['level'] = $member['level_name'];
            $message_array['url'] = "";
            $message_array['type'] = "充值账户";
            $message_array['content'] = "用户充值了".Method::getFloatYuanByFen($money)."元, 会员等级提升为".$member['level_name'];
            $message_array['change_money'] = Method::getFloatYuanByFen($money);
            $message_array['balance'] = Method::getFloatYuanByFen($member['balance']);
            $message_array['remark'] = "如对上述余额变动有异议，请联系客服人员协助处理。";
            Log::record('-----HT-recharge----');
            Log::record(json_encode($message_array));
            $rr = $message->AccountBalance($message_array);
            Log::record($rr);


            $data['id'] = $rs;
            Method::return_data(array('data'=>$data, 'message'=> $message_array, 'rr'=>$rr));
        }else{
            $record = "管理员admin试图为id为{$rs}的用户充值".Method::getFloatYuanByFen($money)."元失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("充值失败");
        }
    }

    /* -------------------- 会员等级 ------------------------- */
    public function getLevelList(){
        $page = Fun::requestInt('page', 1);

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($name)) $where .= " and name like '%".$name."%'";

        $LevelModel = D('Level');
        $list = $LevelModel->where($where)->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['upgrade_money'] = Method::getFloatYuanByFen($row['upgrade_money']);
        }
        $count = $LevelModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    public function getLevelDetail(){
        $id = Fun::requestInt('id', 0);
        if (empty($id)) Method::return_err("等级id不允许为空");

        $LevelModel = D('Level');
        $info = $LevelModel->where("id=".$id)->find();
        $info['discount'] = $info['discount'] * 100;
        Method::return_data(array('data'=>$info));
    }

    public function insertLevel(){
        $name = Fun::request('name');
        $discount = Fun::requestInt('discount', 0);
        $status = Fun::requestInt('status');

        if (empty($name)) Method::return_err("名称不允许为空");
        if (empty($discount)) Method::return_err("折扣不允许为空");

        $data = array();
        $data['name'] = $name;
        $data['discount'] = $discount / 100;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = $status;

//        echo json_encode(array('data'=>$data));exit;

        $LevelModel = D('Level');
        $rs = $LevelModel->add($data);
        if ($rs){
            $data['id'] = $rs;
            Method::return_data(array('data'=>$data));
        }else{
            Method::return_err("添加会员等级失败");
        }
    }

    public function updateLevel(){
        $id = Fun::requestInt('id', 0);
        $name = Fun::request('name');
        $discount = Fun::requestInt('discount', 0);
        $status = Fun::requestInt('status');

        if (empty($id)) Method::return_err("等级id不允许为空");
        if (empty($name)) Method::return_err("名称不允许为空");
        if (empty($discount)) Method::return_err("折扣不允许为空");

        $data = array();
        $data['name'] = $name;
        $data['discount'] = $discount / 100;
        $data['updatetime'] = date('Y-m-d H:i:s');
        $data['status'] = $status;

//        echo json_encode(array('data'=>$data));exit;

        $LevelModel = D('Level');
        $rs = $LevelModel->where("id=".$id)->save($data);
        if ($rs){
            $info = $LevelModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("编辑会员等级失败");
        }
    }

    public function deleteLevel(){
        $id = Fun::request('id');

        if (empty($id)) Method::return_err("等级id不允许为空");

        $LevelModel = D('Level');
        $rs = $LevelModel->where("id in(".$id.")")->delete();
        if ($rs){
            Method::return_data(array('data'=>$rs));
        }else{
            Method::return_err(array('data'=>'删除失败'));
        }
    }

    /* -------------------- 订单 ------------------------- */
    public function getOrderList(){
        $page = Fun::requestInt('page', 1);
        $order_no = Fun::request('order_no');
        $mobile = Fun::request('mobile');
        $ispay = Fun::request('ispay');
        $paytype = Fun::request('paytype');
        $status = Fun::request('status');

        $RoomModel = D('Room');
        $MemberModel = D('Member');
        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1 and is_del=0";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($order_no)) $where .= " and order_no like '%".$order_no."%'";
        if (!empty($mobile)) $where .= " and mobile like '%".$mobile."%'";
        if ($status != null) $where .= " and status=".$status;
        if ($ispay != null) $where .= " and ispay=".$ispay;
        if ($paytype != null) $where .= " and paytype=".$paytype;

        $OrderModel = M('Order');
        $list = $OrderModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['g_name'] = $RoomModel->getRoomNameById($row['gid']);
            $row['username'] = $MemberModel->getMemberNameById($row['uid']);
            $row['money'] = Method::getFloatYuanByFen($row['money']);
        }
        $count = $OrderModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count, 'where'=>$where));
    }

    public function deleteOrder(){
        $id = Fun::requestInt('id');
        $operator = Fun::request('operator');

        if (empty($id)) Method::return_err('参数错误，订单id为空');

        $OrderModel = M('Order');

        $data = array();
        $data['is_del'] = 1;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $OrderModel->where("id=".$id)->save($data);
        if ($rs) {
            $record = "管理员{$operator}删除了id为{$id}的订单";
            $this->insertRecord($operator, $record, 1);
            $info = $OrderModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        } else {
            $record = "管理员{$operator}试图删除id为{$id}的订单失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("删除订单错误");
        }
    }

    public function cancelOrder(){
        $id = Fun::requestInt('id');
        $operator = Fun::request('operator');

        if (empty($id)) Method::return_err('参数错误，订单id为空');

        $OrderModel = M('Order');

        $order = $OrderModel->where("id=".$id)->find();
        if ($order['status'] != 0 && $order['status'] != 1) Method::return_err("订单状态错误，不允许取消操作");
//        if ($order['status'] == 1) {
//            //待使用，需退款
//        }
        $data = array();
        $data['status'] = 4;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $OrderModel->where("id=".$id)->save($data);
        if ($rs) {
            $record = "管理员{$operator}取消了id为{$id}的订单";
            $this->insertRecord($operator, $record, 1);
            $info = $OrderModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        } else {
            $record = "管理员{$operator}试图取消id为{$id}的订单失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("删除订单错误");
        }
    }

    public function finishedOrder(){
        $id = Fun::requestInt('id');
        $operator = Fun::request('operator');

        if (empty($id)) Method::return_err('参数错误，订单id为空');

        $OrderModel = M('Order');

        $order = $OrderModel->where("id=".$id)->find();
        if ($order['status'] != 2) Method::return_err("订单状态错误，不允许完成操作");

        $data = array();
        $data['status'] = 3;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $OrderModel->where("id=".$id)->save($data);
        if ($rs) {
            $record = "管理员{$operator}完成了id为{$id}的订单";
            $this->insertRecord($operator, $record, 1);
            $info = $OrderModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        } else {
            $record = "管理员{$operator}试图完成id为{$id}的订单失败了";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("删除订单错误");
        }
    }

    /* -------------------- 核销 ------------------------- */
    public function getHexiaoList(){
        $page = Fun::requestInt('page', 1);
        $oid = Fun::request('oid');
        $saleser_id = Fun::requestInt('saleser_id', 0);
        $type = Fun::requestInt('type', 0);
        $status = Fun::requestInt('status', 0);

        $SysSetModel = M('SysSet');
        $SaleserModel = D('Saleser');
        $OrderModel = D('Order');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1 and status=".$status." and type=".$type;
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($oid)) $where .= " and name like '%".$oid."%'";
        if (!empty($saleser_id)) $where .= " and saleser_id=".$saleser_id;

        $HexiaoModel = M('Verification');
        $list = $HexiaoModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['saleserName'] = $SaleserModel->getSaleserNameById($row['saleser_id']);
            $row['order'] = $OrderModel->getOrderInfoById($row['oid']);
        }
        $count = $HexiaoModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    /* -------------------- 广告位 ------------------------- */
    public function getRefundList(){
        $page = Fun::requestInt('page', 1);
        $keywords = Fun::request('keywords');

        $SysSetModel = M('SysSet');
        $MemberModel = D('Member');
        $OrderModel =D('Order');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($keywords)) $where .= " and out_trade_no like '%".$keywords."%'";

        $RefundModel = M('Refund');
        $list = $RefundModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row) {
            $row['user'] = $MemberModel->getMemberInfoById($row['uid']);
            $row['order'] = $OrderModel->getOrderInfoById($row['oid']);
        }
        $count = $RefundModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }


    /* -------------------- 广告位 ------------------------- */
    public function getBannerList(){
        $page = Fun::requestInt('page', 1);
        $keywords = Fun::request('keywords');

        $SysSetModel = M('SysSet');
        $TeahouseModel = D('Teahouse');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($keywords)) $where .= " and name like '%".$keywords."%'";

        $BannerModel = M('Banner');
        $list = $BannerModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['teahouseName'] = $TeahouseModel->getTeahouseNameById($row['sid']);
        }
        $count = $BannerModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    public function getBannerDetail(){
        $id = Fun::requestInt('id', 0);

        if (empty($id)) Method::return_err("广告位id不允许为空");

        $BannerModel = M('Banner');
        $info = $BannerModel->where("id=".$id)->find();
        Method::return_data(array('data'=>$info));
    }

    public function insertBanner(){
        $name = Fun::request('name');
        $short_title = Fun::request('short_title');
        $sid = Fun::requestInt('sid', 1);
        $type = Fun::requestInt('type', 0);
        $rid = Fun::requestInt('rid', 0);
        $is_show = Fun::requestInt('is_show', 0);
        $is_hot = Fun::requestInt('is_hot', 0);
        $banner_url = Fun::request('banner_url');
        $target_link = Fun::request('target_link');

        if (empty($name)) Method::return_err("广告位名称不允许为空");
        if (empty($type)) Method::return_err("类型不允许为空");
        if (empty($banner_url)) Method::return_err("图片不允许为空");

        $rid = $type==1 ? 0 : $rid;
        $sid = $type==2 ? 0 : $sid;

        $data = array();
        $data['name'] = $name;
        $data['short_title'] = $short_title;
        $data['sid'] = $sid;
        $data['type'] = $type;
        $data['rid'] = $rid;
        $data['is_show'] = $is_show;
        $data['is_hot'] = $is_hot;
        $data['banner_url'] = $banner_url;
        $data['target_link'] = $target_link;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = 1;

//        echo json_encode(array('data'=>$data));exit;

        $BannerModel = M('Banner');
        $rs = $BannerModel->add($data);
        if ($rs){
            $data['id'] = $rs;
            Method::return_data(array('data'=>$data));
        }else{
            Method::return_err("添加广告失败");
        }
    }

    public function updateBanner(){
        $id = Fun::requestInt('id', 0);
        $name = Fun::request('name');
        $short_title = Fun::request('short_title');
        $sid = Fun::requestInt('sid', 1);
        $type = Fun::requestInt('type', 0);
        $rid = Fun::requestInt('rid', 0);
        $is_show = Fun::requestInt('is_show', 0);
        $is_hot = Fun::requestInt('is_hot', 0);
        $banner_url = Fun::request('banner_url');
        $target_link = Fun::request('target_link');

        if (empty($name)) Method::return_err("广告位名称不允许为空");
        if (empty($type)) Method::return_err("类型不允许为空");
        if (empty($banner_url)) Method::return_err("图片不允许为空");

        $data = array();
        $data['name'] = $name;
        $data['short_title'] = $short_title;
        $data['sid'] = $sid;
        $data['type'] = $type;
        $data['rid'] = $rid;
        $data['is_show'] = $is_show;
        $data['is_hot'] = $is_hot;
        $data['banner_url'] = $banner_url;
        $data['target_link'] = $target_link;
        $data['addtime'] = date('Y-m-d H:i:s');

//        echo json_encode(array('data'=>$data));exit;

        $BannerModel = M('Banner');
        $rs = $BannerModel->where("id=".$id)->save($data);
        if ($rs){
            $info = $BannerModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$info));
        }else{
            Method::return_err("编辑广告失败");
        }
    }

    public function changeShowStatus(){
        $id = Fun::requestInt('id', 0);
        $operator = Fun::request('operator');
        $BannerModel = M('Banner');
        $info = $BannerModel->where("id=".$id)->find();
        $data = array();
        $data['is_show'] = $info['is_show'] == 1 ? 0 : 1;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $BannerModel->where("id=".$id)->save($data);

        if ($rs){
            $record = "管理员{$operator}更改了id为{$id}的展示状态。";
            $this->insertRecord($operator, $record, 1);
            $info['is_show'] = $data['is_show'];
            $info['updatetime'] = $data['updatetime'];
            Method::return_data(array('data'=>$info));
        }else{
            $record = "管理员{$operator}试图更改id为{$id}的展示状态失败。";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("更改展示状态失败");
        }

    }

    public function changeHotStatus(){
        $id = Fun::requestInt('id', 0);
        $operator = Fun::request('operator');
        $BannerModel = M('Banner');
        $info = $BannerModel->where("id=".$id)->find();
        $data = array();
        $data['is_hot'] = $info['is_hot'] == 1 ? 0 : 1;
        $data['updatetime'] = date('Y-m-d H:i:s');

        $rs = $BannerModel->where("id=".$id)->save($data);

        if ($rs){
            $record = "管理员{$operator}更改了id为{$id}的推荐状态。";
            $this->insertRecord($operator, $record, 1);
            $info['is_hot'] = $data['is_hot'];
            $info['updatetime'] = $data['updatetime'];
            Method::return_data(array('data'=>$info));
        }else{
            $record = "管理员{$operator}试图更改id为{$id}的展示推荐失败。";
            $this->insertRecord($operator, $record, 0);
            Method::return_err("更改展示状态失败");
        }

    }

    public function deleteBanner(){
        $id = Fun::request('id');

        if (empty($id)) Method::return_err("广告位id不允许为空");

        $BannerModel = M('Banner');
        $rs = $BannerModel->where("id in (".$id.")")->delete();
        if ($rs){
            Method::return_data(array('data'=>$rs));
        }else{
            Method::return_err(array('data'=>'删除失败'));
        }
    }

    /* -------------------- 充值 ------------------------- */
    public function getRechargeList(){
        $page = Fun::requestInt('page', 1);
        $out_trade_no = Fun::request('out_trade_no');
        $type = Fun::requestInt('type', 0);

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1 and type=".$type;
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($out_trade_no)) $where .= " and out_trade_no like '%".$out_trade_no."%'";

        $RechargeModel = M('Recharge');
        $list = $RechargeModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['money'] = Method::getFloatYuanByFen($row['money']);
        }
        $count = $RechargeModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    /* -------------------- 提现 ------------------------- */
    public function getCashList(){
        $page = Fun::requestInt('page', 1);
        $keywords = Fun::request('keywords');

        $SysSetModel = M('SysSet');
        $MemberModel = D('Member');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($keywords)) $where .= " and mobile like '%".$keywords."%'";

        $CashModel = M('Cash');
        $list = $CashModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['user'] = $MemberModel->getMemberInfoById($row['uid']);
            $row['amount'] = Method::getFloatYuanByFen($row['amount']);
        }
        $count = $CashModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count));
    }

    public function checkCashSuccess(){
        $id = Fun::requestInt('id', 0);
        $uid = Fun::requestInt('uid', 0);
        $money = Fun::requestInt('amount', 0);
        $operator = Fun::request('operator');
        if (empty($id)) Method::return_err('参数错误：提现编号为空');
        if (empty($uid)) Method::return_err("提现的用户参数为空");
        if (empty($money)) Method::return_err("提现金额为0");

        Method::return_data(array('data'=>$_POST));exit;
        $money = $money * 100;  //元转化为分
        $MemberModel = D('Member');
        $LevelModel = D('Level');
        $CashModel = M('Cash');

        $cash = $CashModel->where("id=".$id)->find();
        if ($cash['status'] != 0){
            Method::return_err("该记录已经审核过，不允许再次审核");
        }

        $member_info = $MemberModel->where("id=".$uid)->find();
        //提现金额 < 用户余额
        if ($money > $member_info['balance']) {
            Method::return_err("提现金额大于账户余额");
        }

        $rs = $CashModel->where("id=".$id)->save(array('status'=>1));
        if ($rs) {
            //账户余额变动
            $ret = $MemberModel->where("id=".$uid)->save(array('balance'=>$member_info['balance']-$money));
            if ($ret) {
                //提现
                $this->ALtransfers($member_info['mobile'], $money, $member_info['username']);

                $member = $MemberModel->where("id=".$uid)->find();
                $member['level_name'] = $LevelModel->getLevelNameById($member['level_id']);
                //余额变动消息模板
                $message = new Message();
                $message_array = array();
                $message_array['openid'] = $member['openid'];
                $message_array['first'] = "{$member['username']}您好，您的账户余额发生变动，信息如下。";
                $message_array['level'] = $member['level_name'];
                $message_array['url'] = "";
                $message_array['type'] = "提现账户";
                $message_array['content'] = "用户提现了".Method::getFloatYuanByFen($money)."元。";
                $message_array['change_money'] = Method::getFloatYuanByFen($money);
                $message_array['balance'] = Method::getFloatYuanByFen($member['balance']);
                $message_array['remark'] = "如对上述余额变动有异议，请联系客服人员协助处理。";
                Log::record('-----HT-recharge----');
                Log::record(json_encode($message_array));
                $rr = $message->AccountBalance($message_array);
                Log::record($rr);
            }
            $record = "管理员{$operator}审核提现id为{$id}的记录为成功";
            $this->insertRecord($operator,$record,1);
            $cash_info = $CashModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$cash_info));
        }else{$record = "管理员{$operator}试图审核提现id为{$id}的记录为成功失败了";
            $this->insertRecord($operator,$record,1);
            Method::return_err('提现审核操作失败');
        }
    }


    public function checkCashError(){
        $id = Fun::requestInt('id', 0);
        $uid = Fun::requestInt('uid', 0);
        $money = Fun::requestInt('amount', 0);
        $operator = Fun::request('operator');
        if (empty($id)) Method::return_err('参数错误：提现编号为空');
        if (empty($uid)) Method::return_err("提现的用户参数为空");
        if (empty($money)) Method::return_err("提现金额为0");

        $money = $money * 100;  //元转化为分
        $MemberModel = D('Member');
        $CashModel = M('Cash');

        $cash = $CashModel->where("id=".$id)->find();
        if ($cash['status'] != 0){
            Method::return_err("该记录已经审核过，不允许再次审核");
        }

        $member_info = $MemberModel->where("id=".$uid)->find();
        //提现金额 < 用户余额
        if ($money > $member_info['balance']) {
            Method::return_err("提现金额大于账户余额");
        }

        $rs = $CashModel->where("id=".$id)->save(array('status'=>2));
        if ($rs) {
            //账户余额变动
            $MemberModel->where("id=".$uid)->save(array('balance'=>$member_info['balance']+$money));
            //管理员记录
            $record = "管理员{$operator}审核提现id为{$id}的记录为未通过";
            $this->insertRecord($operator,$record,1);
            $cash_info = $CashModel->where("id=".$id)->find();
            Method::return_data(array('data'=>$cash_info));
        }else{
            $record = "管理员{$operator}试图审核提现id为{$id}的记录为未通过失败了";
            $this->insertRecord($operator,$record,0);
            Method::return_err('提现审核操作失败');
        }
    }



    /* -------------------- 记录 ------------------------- */
    public function getRecordList(){
        $page = Fun::requestInt('page', 1);
        $record = Fun::request('record');
        $operator = Fun::request('operator');

        $SysSetModel = M('SysSet');
        $sys_set_info = $SysSetModel->where("id=1")->find();

        $where = "1=1";
        $limit = $sys_set_info['page_num'];
        $offset = ($page-1) * $limit;

        if (!empty($record)) $where .= " and record like '%".$record."%'";
        if (!empty($operator)) $where .= " and operator='".$operator."'";

        $RecordModel = M('Record');
        $list = $RecordModel->where($where)->order('id DESC')->limit($offset, $limit)->select();
        foreach ($list as &$row){
            $row['status_text'] = intval($row['status']) == 1 ? 'SUCCESS' : 'ERROR';
        }
        $count = $RecordModel->where($where)->count();
        Method::return_data(array('data'=>$list,'count'=>$count,'page_size'=>$limit));
    }


    public function insertRecord($operator,$record,$status){
        $data = array();
        $data['operator'] = $operator;
        $data['record'] = $record;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = $status;
        $RecordModel = M('Record');
        $rs = $RecordModel->add($data);
        return $rs;
    }



    /* ------------------------上传图片------------------ */
    public function upload(){
        $file = $_FILES;
        $info = $this->uploadImg();
        foreach($info as &$file){
            $file['url'] = '/book/Uploads'.$file['savepath'].$file['savename'];
        }

//        $json_array = array('code'=>0,'msg'=>'成功','data'=>array('src'=>$info['file']['url']),'file'=>$info);
        Method::return_data(array('data'=>array('src'=>$info['file']['url']),'file'=>$info));
//        echo json_encode($json_array);exit;
    }

    //
    public function uploadImg(){
        $upload = new \Think\Upload();  // 实例化上传类
        $upload->maxSize = 314572800; // 设置附件上传大小
        $upload->exts = array('jpg','gif','png','jpeg');

        $upload->savePath = '/';  // 设置附件上传目录
        //上传文件
        $info = $upload->upload();
        if(!$info){
            return $this->error($upload->getError());
        }else{
            return $info;
        }
    }

    public function test(){
        die(json_encode(array('test')));
    }


    public function ALtransfers($tel,$amount,$re_user_name)
    {
        $cash = M('cash');
        $max = $cash->field('MAX(id) as sum')->find();
        $order_no = time() . $max['sum'];
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018073060792711';
        $aop->rsaPrivateKey = 'MIIEpgIBAAKCAQEAqFLbeWZqkHbpxybJzmPkT7+k0Qgxi1S3Nb+wCaJRDxDNjbopIJe7LpWuTJX3LK/2FDqBscyrcSTCutFe9xi3BnULaVGWVJJlBKZLJexll6TpyOxX4j+u/2sX93Hq90qxQECge+7AhQ9fQFtSp/excgj8mwdJJik9CnV2Z5VWS/rllFAczeckaeF72YlvbQY+MpsywGba3ZsnNm1kDLS32yucCr/eyhptq60dZlXudG0nZx+H/+OUNd9mL3RJzt0KE+/x+5YFtICKujWlFXvP4zXMFSFUN1+QuMGDUAknjWgRachOAdcg7eMLHh9/+M3m279eSBJizyA3OrWjeUe9mQIDAQABAoIBAQCGigvRAjKFG/cJ7o/5PtC7iYPUbIclReZWuMudN7cwoo6aDMVYvs6nko5JushhWJgJXSZTFjOmcOqQ5k7QlFmeeKlRWhwdpxHFYKHKQySEzBTtOzOXrK1UjKYQa2aSmIoKwF1GEfShpkLNLvFnPkz/x/0YcP9f2DBpDrBAZRYgDbNblNvP+dXRlEc/rM5o+QwSTTDTuIb/g/X+nM1jEK41k9GEJIY0uBkSkXKdb19csTiMewKAfviBxfCe4FTxjkIloGDu8iW7GzxtR9VJig145+tWqk9kMPeB7PpeEmLwyGsfjxxqoQjMzdLdzBOeLmF3rgb3itxVu1UYqPI8yKhNAoGBANuOqexhgk0xAFFtIVABWzqZq7sWmXWbex26MQfSjy584CwVp8s5K0FCzXtXIOP77SBxCZz1+2mw4vkkmIaiXy2iqTM3Oi00xlxVcqSjfjIvBanhrDrVOjobWNSXwkMHzmt2COZvs/jofHi0vrvDW/gdAWKaGLQi+pYufT7aEdcHAoGBAMRDMi68+U0t/Zv/3UfDt2IO38O9rSWl2KOhfbIWw4ocI1ugmKEhCcUeMQgIqJeQuPckq+NZThTOs1o1YFSLpGMuDU34unTPndajfbR9CelwbT2PmGS0nRVFLhhAUJ9usBNVvMaDcc6S2WzCLnJv1f4Pl3IAuWczbEwQ+ac4MP5fAoGBAK+9sBH/svbqpCCJQ8Lwcv+jBa0JV+ilfZS79ocWaXmCh0WCR/8JUbA5MpTplvAmNRZkpJc45fchmWxneJc73QeATgMqz6xjs+swkkVqgJbWwKfMdbnZ93OPdDknCF3zH60wm8sn2l0AarGLq6hLpZAiV3t/cQqvfPk9WQ84KlN9AoGBAL1bU8ySWTok2F6t49J8u68pSK2zkJ4VQErH4d10Zx8WfOrHrNsxZBrCQW5N5FOvtzYENK96l4It2A9+Fj4cKPPkF8QV6dgQBGp1fTApv+lxpoRRyifHtxMxlwKg8uiQQ+OzwhoJ8kDroEl1pJiW3HFum6DLoBY5IBDYA/dZmLOjAoGBAKFxyNqP9uVSw5KHlBGxIfkh2utK9IBgBuOHGMNpaii8UQMR99v5BBtC5h0b5/XicM4FA+cJ+RoL2zBgxBGvAeS6rZ3zw0z36AxFshkAA+wbUIOSqr1pP0JEdTEoekI9ZOVO/LwY9FxJjBn2R3PUwmoP2x/mgIlvisuRrGOJyHiv';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqFLbeWZqkHbpxybJzmPkT7+k0Qgxi1S3Nb+wCaJRDxDNjbopIJe7LpWuTJX3LK/2FDqBscyrcSTCutFe9xi3BnULaVGWVJJlBKZLJexll6TpyOxX4j+u/2sX93Hq90qxQECge+7AhQ9fQFtSp/excgj8mwdJJik9CnV2Z5VWS/rllFAczeckaeF72YlvbQY+MpsywGba3ZsnNm1kDLS32yucCr/eyhptq60dZlXudG0nZx+H/+OUNd9mL3RJzt0KE+/x+5YFtICKujWlFXvP4zXMFSFUN1+QuMGDUAknjWgRachOAdcg7eMLHh9/+M3m279eSBJizyA3OrWjeUe9mQIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $money = ($amount / 100);
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent("{" .
            "\"out_biz_no\":\"$order_no\"," .
            "\"payee_type\":\"ALIPAY_LOGONID\"," .
            "\"payee_account\":\"$tel\"," .
            "\"amount\":\"$money\"," .
            "\"payer_show_name\":\"提现成功\"," .
            "\"payee_real_name\":\"$re_user_name\"," .
            "\"remark\":\"任务广场余额提现\"" .
            "}");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultdata['code'] = $result->$responseNode->code;
        $resultdata['sub_msg'] = $result->$responseNode->sub_msg;
        return $resultdata;
    }
}