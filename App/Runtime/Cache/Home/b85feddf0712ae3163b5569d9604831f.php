<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
        <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <script src="/Public/layer-v3.0.3/layer/layer.js"></script>          
        <script src="/Public/Js/112js.js"></script>
        <link rel="stylesheet" href="/Public/Css/112css.css">
        <title>存费送费</title>
        <style>
            html,body{
                overflow: hidden;
            }
            a:hover, a:visited, a:link, a:active {
                text-decoration:none;
                color:#fff;
            }    
            a{
                font-size: .24rem;
            }
            #radio {
                width: .2rem;
                height: 0px;
                background-color: #000;
                margin-right: .4rem;
                border-radius: 50%;
                position: relative;
                left:.1rem;
                top:-.28rem
            }
            #radio:before, #radio:after {
                content: '';
                display: block;
                position: absolute;
                border-radius: 50%;
                transition: .3s ease;
            }
            #radio:before {
                top: 0px;
                left: 0px;
                width: 18px;
                height: 18px;
                background-color: #fff;
                border: 1px solid #000;
            }
            #radio:after {
                top: 6px;
                left: 6px;
                width: 8px;
                height: 8px;
                background-color: #fff; 
            }
            #radio:checked:after {
                top: 4px;
                left: 4px;
                width: 12px;
                height: 12px;
                background-color:#4fdbd0; 
            }
            #radio:checked:before {
                border-color:#4fdbd0; 
            }            
        </style>
    </head>

    <body>
        <div class="p3bg">
            <div class="showesim">
                <span>您的eSIM账号为:</span>
                <span id="esimnum"><?php echo ($tel); ?></span>                 
            </div>
            <div class="list-group">
                <span class="tit">套餐</span>
                <span class="tit">预存</span>
                <span class="tit">赠送</span>
                <span class="tit">共返还</span>
                <span class="tit">每月返还</span>

            </div>
            <div class="list-group2">
                <span class="tit">20元/月</span>
                <span class="tit">120元</span>
                <span class="tit">120元</span>
                <span class="tit">240元</span>
                <span class="tit">20元</span>
            </div>
            <span class="zs">注：共返还12个月</span>
            <div class="agree">
                <input id="radio" name="Fruit" type="radio" value="" /><span style="color:#fff;position:relative;top:-0.05rem;">我已阅读并同意<span class='rule' style="color:rgb(11, 80, 140)">业务规则</span></span>
            </div>
            <input type='hidden' name='phone_type' value="<?php echo ($phone_type); ?>"/>        
            <button class="p2now">立即办理 </button>
            <span class="myorder" onclick="location = '<?php echo U("Purchase/allorders","",true,true);?>'">我的订单</span><span class="myorder" id="shu">|</span><span class="helpnum"><a href="tel:4000210356">客服热线</a></span>
            <form id='go' action='./pay.html' method='post'>
                <input type='hidden' id='telnum' name='tel' value='<?php echo ($tel); ?>'/>
                <input type='hidden' id='phonetype' name='phonetype' value='<?php echo ($phone_type); ?>'/>
                <input type='hidden' id='openID' name='openID' value='<?php echo ($openID); ?>'/>
                <input type='hidden' id='sub' name='sub' value='go'/>                
            </form>
        </div>
        <script>
            $(function(){
                $(".rule").click(function(){
                layer.confirm('1、即日起—2月28日，用户关注eSIM微信公众号“联通eSIM智能穿戴”，通过公众号给已激活的eSIM号卡充值，赠送与充值金额相同的话费。<br>2、此充值业务共分两档，预存60元赠60元，预存120元赠120元。<br>3、预存60赠60元，共分12个月返还，每月返还10元；预存120元赠120元，共分12个月返还，每月返还20元。<br>4、赠款费用可抵扣除月租外其他费用。<br>5、此业务充值成功后不支持退订功能。', {
                btn: ['我知道了'], //按钮
                        title:'业务规则'
                }, function () {
                layer.closeAll();
                });
                })

                $('.p2now').click(function(){
                    if(!$('#radio')[0].checked){
                        layer.msg('请您先同意业务规则', {
                            icon: 2
                            , time: 3 * 1000,
                            shift: 6
                        });     
                        return;
                    }

                    var tel = $.trim($('#esimnum').text());
                    if(tel == ''){
                        layer.msg('请您从公众号菜单重新进入购买', {
                            icon: 2
                            , time: 3 * 1000,
                            shift: 6
                        });
                        return;                
                    }

                    layer.load(2);
                    $(".p2now").attr('disabled', 'disabled');
                    var url = './pay.html?check=go';
                    $.ajax({
                        type: "get",
                        url: url,
                        data: {tel:tel},
                        success: function (res) {                        
                            layer.closeAll('loading'); //关闭加载层       
                            $(".p2now").removeAttr('disabled');                            
                            if (res.errorno == 1) {                               
                                $('#go').submit();
                            } else {
                                layer.msg(res.errmsg, {
                                    icon: 2
                                    , time: 3 * 1000,
                                    shift: 6
                                });
                                return;
                            }
                        },
                        error: function () {
                            layer.closeAll('loading'); //关闭加载层                        
                            $(".p2now").removeAttr('disabled'); 
                            layer.msg('抱歉，请您稍后再试', {
                                icon: 2
                                , time: 3 * 1000,
                                shift: 6
                            });
                            return;
                        }
                    });
                });
            });
        </script>  
    </body>

</html>