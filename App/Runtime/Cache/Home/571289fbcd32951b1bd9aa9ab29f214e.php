<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cmn-Hans">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title>资费查询</title>
    <link rel="stylesheet" href="<?php echo ($public_url); ?>/Css/weui.css" />
    <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="<?php echo ($public_url); ?>/layer-v3.0.3/layer/layer.js"></script>    
    <style>
        .placeholder {
            text-align: center;
        }

        .place {
            position: relative;
            left: -20px;
            font-size: 14px;
        }

        .placenum {
            color: rgb(15, 182, 126);
            font-size: 30px;
        }

        .more {
            display: none;
            padding-top: 5px;
            padding-bottom: 5px;
        }

        .showmore {
            color: #676565;
            font-size: 14px;
            text-align: center;
            height:30px;
            line-height:30px;
        }
        .borderb{
        width:100%;
        border-bottom:1px solid #b5abab;
        }
        .borderm{
             width:100%;border-bottom:6px solid rgb(225,235,245)
        }
        .bottom_seat{
            height:30px;
        }
        .weui-navbar__item.weui-bar__item_on{background-color:rgb(100, 206, 170);}
    </style>
</head>

<body>
    <div class="page">
        <div class="page__bd" style="height: 100%;">
            <div class="weui-tab">
                <div class="weui-navbar">
                    <div class="weui-navbar__item weui-bar__item_on" id="tabesim">
                        出门问问手表
                    </div>
                    <div class="weui-navbar__item" id="tabhw">
                        华为手表
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="weui-panel__hd">&nbsp;</div>
    <div class="weui-panel__bd">
        <div class="weui-media-box weui-media-box_small-appmsg">
            <div class="weui-cells">
                <a class="weui-cell weui-cell_access" href="javascript:;">
                    <div class="weui-cell__hd"><img src="<?php echo ($headimage_url); ?>"
                                                    alt="" style="width:40px;border-radius:50%;display:block"></div>
                    <div class="weui-cell__bd weui-cell_primary">
                        <p style="margin-left:10px"><span id='tel'></span></p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <!--流量-->
    <div class="page__bd page__bd_spacing">
        <a class="weui-cell weui-cell_access" href="javascript:;">
            <div class="weui-cell__hd" ><img src="<?php echo ($public_url); ?>/Image/liuliang.png" alt="" style="width:20px;display:block"></div>
            <div class="weui-cell__bd weui-cell_primary">
                <p style="margin-left:10px">流量</p>
            </div>
        </a>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder  place">本地流量剩余:</div>
            </div>
            <div class="weui-flex__item">
                <div class="placeholder  place">国内流量剩余:</div>
            </div>
        </div>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder placenum"><span id='local_flow'>0MB</span></div>
            </div>
            <div class="weui-flex__item">
                <div class="placeholder placenum"><span id='internal_flow'>0MB</span></div>
            </div>
        </div>
    </div>
    <div class="weui-flex borderb"  ></div>
    <p class="page__desc showmore " id="flow_more">展开详情</p>
    <div class="more" id="flow_info">
    </div>
    <div class="weui-flex borderm" ></div>
    <!--实时话费-->
    <div class="page__bd page__bd_spacing">
        <a class="weui-cell weui-cell_access" href="javascript:;">
            <div class="weui-cell__hd" ><img src="<?php echo ($public_url); ?>/Image/huafei.png" alt="" style="width:20px;display:block"></div>
            <div class="weui-cell__bd weui-cell_primary">
                <p style="margin-left:10px">实时话费</p>
            </div>
        </a>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder  place">实时话费:</div>
            </div>
        </div>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder placenum"><span id='real_allfee'>0元</span></div>
            </div>
        </div>
    </div>
    <div class="weui-flex borderb"  ></div>
    <p class="page__desc showmore " id="real_more">展开详情</p>
    <div class="more" id="real_info">
    </div>
    <div class="weui-flex borderm" ></div>          
    <!--账户余额-->
    <div class="page__bd page__bd_spacing">
        <a class="weui-cell weui-cell_access" href="javascript:;">
            <div class="weui-cell__hd"><img src="<?php echo ($public_url); ?>/Image/yue.png" alt="" style="width:20px;display:block"></div>
            <div class="weui-cell__bd weui-cell_primary">
                <p style="margin-left:10px">账户余额</p>
            </div>
        </a>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder  place">账户余额:</div>
            </div>
        </div>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder placenum"><span id='account_balance'>0元</span></div>
            </div>
        </div>
    </div>
    <div class="weui-flex borderb" ></div>
    <p class="page__desc showmore " id="account_more">展开详情</p>
    <div class="more" id="account_info">
    </div>
    <div class="weui-flex borderm" ></div>              
    <!--历史账单-->
    <div class="page__bd page__bd_spacing">
        <a class="weui-cell weui-cell_access" href="javascript:;">
            <div class="weui-cell__hd"><img src="<?php echo ($public_url); ?>/Image/zhangdan.png" alt="" style="width:20px;display:block"></div>
            <div class="weui-cell__bd weui-cell_primary">
                <p style="margin-left:10px">历史账单</p>
            </div>
        </a>
        <div class="weui-flex">
            <div class="weui-flex__item">
                <div class="placeholder placenum" id='history'>点击查询<input type='hidden' id='token'/></div>
            </div>
        </div>
    </div>
    <div class="weui-flex borderm" >&nbsp;</div> 
    <div class='bottom_seat'></div>
    <div id="loadingToast" style="display:none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <i class="weui-loading weui-icon_toast"></i>
            <p class="weui-toast__content">数据加载中</p>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#loadingToast').show();
            $('#token').val("<?php echo U('home/userInfo/showexpensepage/flag/history?title=esim','',false,true);?>");
            getInfo("<?php echo U('home/userInfo/getexpense/type/esim','',false,true);?>",'esim');
            $('#history').click(function(){
                location.href=$('#token').val();
            });            
            $('.weui-navbar__item').on('click', function () {
                $(this).addClass('weui-bar__item_on').siblings('.weui-bar__item_on').removeClass('weui-bar__item_on');
            });
            $("#flow_more").click(function () {
                $("#flow_info").slideToggle("10000");
            });
            $("#real_more").click(function () {
                $("#real_info").slideToggle("10000");
            });
            $("#account_more").click(function () {
                $("#account_info").slideToggle("10000");
            });              
            $("#tabhw").click(function () {
                    $('#loadingToast').show();
                    $('#token').val("<?php echo U('home/userInfo/showexpensepage/flag/history?title=hw','',false,true);?>");
                    getInfo("<?php echo U('home/userInfo/getexpense/type/hw','',false,true);?>",'hw');
            })
            $("#tabesim").click(function () {
                    $('#loadingToast').show();
                    $('#token').val("<?php echo U('home/userInfo/showexpensepage/flag/history?title=esim','',false,true);?>");
                    getInfo("<?php echo U('home/userInfo/getexpense/type/esim','',false,true);?>",'esim');             
            })
        });
        
        function getInfo(url,flag){
           var type = flag;
           var res = sessionStorage.getItem(type);
           if(res){
                res = JSON.parse(res);
                setInfo(res);
                return;
           }
           $.ajax({
                type: "get",
                url: url,
                success: function (res) {
                    $('#loadingToast').hide();
                    if(res.status && res.status == -2){
                        var msg = '';
                        if(type=='hw'){
                            msg = '您还未绑定华为手表账号，是否前往绑定？';
                        }else{
                            msg = '您还未绑定出门问问手表账号，是否前往绑定？';
                        }
                        //未绑定账号
                        layer.confirm(msg, {    
                                    skin: 'layui-layer-molv', //样式类名
                                    skin: 'demo-class',
                                    btn: ['绑定','取消'] //按钮
                                }, 
                                function(){
                                    location.href="<?php echo U('home/index/showAccount/accountType/"+type+"','',false,true);?>";
                                }
                        );
                    }else if(res.status){
                        //出错
                        layer.alert(res.msg, {
                             skin: 'layui-layer-lan', //样式类名
                             skin: 'demo-class'
                            ,closeBtn: 0
                        });
                    }else{
                        sessionStorage.setItem(type,JSON.stringify(res));
                        setInfo(res);
                    }
                },
                error: function () {
                    $('#loadingToast').hide();
                    layer.alert('抱歉出错了，请稍后再试', {
                         skin: 'layui-layer-lan', //样式类名
                         skin: 'demo-class'
                        ,closeBtn: 0
                    });                    
                }
            });
        }
        function setInfo(res){
            //流量                        
            $('#local_flow').text(res.flow.GPRSLeftLocal+'MB');
            $('#internal_flow').text(res.flow.GPRSLeftInternal+'MB');
            $('#flow_info').html(
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>已使用套餐内免费本地流量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSUsedLocal+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>已使用套餐内免费国内流量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSUsedInternal+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>已使用免费定向流量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSUsedDirectional+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>剩余免费定向流量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSLeftDirectiona+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>漫游流量使用量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSUsedRoam+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>漫游流量剩余量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSLeftRoam+'MB</div></div>'+
                                '<div class="weui-cell"><div class="weui-cell__bd"><p>套餐外流量</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.flow.GPRSOver+'MB</div></div>'
                                
            );
            //实时话费
            $('#real_allfee').text(res.realtime_bill.RealFee+'元');
            var itemInfo = res.realtime_bill.ItemInfo;
            if( itemInfo == ''){                            
                $('#real_info').html("<p style='text-align:center;'>暂无记录</p>");
            }else{
                var str = '';
                for(var i=0;i<itemInfo.length;i++){                    
                    str += '<div class="weui-cell"><div class="weui-cell__bd"><p>'+itemInfo[i].IntegrateItem+'</p></div><div class="weui-cell__ft" style="font-size:14px;">'+itemInfo[i].Fee+'元</div></div>'
                }   
                $('#real_info').html(str);
            }
            //账户余额
            $('#account_balance').text(res.telephone_bill.TotalBalance+'元');
            $('#account_info').html(                    
                        '<div class="weui-cell"><div class="weui-cell__bd"><p>可用赠款总额</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.telephone_bill.PromTotalBalance+'元</div></div>'+
                        '<div class="weui-cell"><div class="weui-cell__bd"><p>专项赠款</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.telephone_bill.SpecialPromBalance+'元</div></div>'+
                        '<div class="weui-cell"><div class="weui-cell__bd"><p>普通赠款</p></div><div class="weui-cell__ft" style="font-size:14px;">'+res.telephone_bill.PromBalance+'元</div></div>'
            );
            //电话号码
            $('#tel').text(res.tel);
            $('#loadingToast').hide();
        }        
    </script>




</body>

</html>