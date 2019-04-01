<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <script src="<?php echo ($public_url); ?>/layer-v3.0.3/layer/layer.js"></script>
    <script>
        (function (doc, win) {
            var docEl = doc.documentElement,
                resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
                recalc = function () {
                    var clientWidth = docEl.clientWidth;
                    if (!clientWidth) return;
                    if (clientWidth >= 640) {
                        docEl.style.fontSize = '100px';
                    } else {
                        docEl.style.fontSize = 100 * (clientWidth / 640) + 'px';
                    }
                };
            if (!doc.addEventListener) return;
            win.addEventListener(resizeEvt, recalc, false);
            doc.addEventListener('DOMContentLoaded', recalc, false);
        })(document, window);
    </script>
    <script>
        $(function () {
            var v = '0923';
            
            var now = new Date();
            var nowMonth = now.getMonth() + 1; //当前月

            var lis = document.querySelectorAll("ul li");
            for (var i = 0; i <= lis.length; i++) {
            nowMonth--;
            if (nowMonth <= 0) {
            console.log( nowMonth+12)
            nowMonth=nowMonth+12
            }
            $(lis[i]).html("<span>"+nowMonth + "</span>月");
            }            
            
            
            $("li").click(function () {
                if($('div[type=dialog]').size()>0){
                    return;
                }
                $(this).siblings().removeClass("addclick");
                $(this).attr("class", "addclick");
                $('#telnum').text('');
                $('#payname').text('');
                $('#allfee').text('');
                $('#cycleId').text('');                
                var type = $.trim($('#flag').text());
                var month = $(this).find('span').text();
                if(month.length == 1){
                    month = '0'+month;
                }
                var year = new Date().getFullYear();
                var cycleId = year+''+month; 
                if(localStorage.getItem(v+'_'+type+'_'+cycleId)){
                    var res = JSON.parse(localStorage.getItem(v+'_'+type+'_'+cycleId)); 
                    var tel = res.tel;
                    var s_tel = $.trim($('#tel').text());
                    if(tel == s_tel){
                        $('#telnum').text(res.history_bill.accountBillInfo[0].serialNumber);
                        $('#payname').text(res.history_bill.accountBillInfo[0].payName);
                        $('#allfee').text(res.history_bill.accountBillInfo[0].allFee+'元');     
                        $('#cycleId').text(res.history_bill.accountBillInfo[0].cycleId);
                        return;
                   }
                }
                layer.msg('正在查询中 . . .', {
                    icon: 16
                    , time: 30000
                }); 
                $.ajax({
                    type: "get",
                    url: "<?php echo U('home/userInfo/getexpense/type/"+type+"/cycleId/"+cycleId+"','',false,true);?>",
                    dataType: "json",
                    success: function (res) {
                         //弹框消失
                        layer.closeAll("dialog");
                        if(res.status && res.status == -3){
                            layer.alert('抱歉，暂未查询到该月份账单', {
                                 skin: 'layui-layer-lan', //样式类名
                                 skin: 'demo-class'
                                ,closeBtn: 0
                            });
                        }else if(res.status && res.status == -2){
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
                            layer.alert(res.msg, {
                                 skin: 'layui-layer-lan', //样式类名
                                 skin: 'demo-class'
                                ,closeBtn: 0
                            });
                        }else{
                            localStorage.setItem(v+'_'+type+'_'+cycleId,JSON.stringify(res));
                            $('#telnum').text(res.history_bill.accountBillInfo[0].serialNumber);
                            $('#payname').text(res.history_bill.accountBillInfo[0].payName);
                            $('#allfee').text(res.history_bill.accountBillInfo[0].allFee+'元');     
                            $('#cycleId').text(res.history_bill.accountBillInfo[0].cycleId); 
                            $('#tel').text(res.tel);
                        }
                    },
                    error: function () {
                        layer.closeAll("dialog");
                        layer.alert('抱歉，请稍后再试', {
                            skin: 'layui-layer-lan', //样式类名
                            skin: 'demo-class'
                           ,closeBtn: 0
                       });
                    }
                })
            });
            $("li:eq(0)").click();            
        })
    </script>
    <style>
        html,
        body {
            font-size: 0.24rem;
            margin: 0;
            padding: 0;
            background: rgb(254, 254, 254);
            color:rgb(58,58,58)
        }

        a {
            text-decoration: none;
        }

        ul {
            display: flex;
            padding: 0;
            border-bottom: 2px solid rgb(213, 213, 213)
        }

        li {
            height: 0.4rem;
            list-style: none;
            float: left;
            flex: 1;
            text-align: center;
        }

        p {
            margin: 0.15rem;
        }

        .addclick {
            color: rgb(236, 80, 20);
             border-bottom: 1px solid rgb(236, 80, 20);
        }

        .show1 {
            border-top: 5px solid rgb(213, 213, 213)
        }

        .show {
            border-bottom: 1px solid rgb(213, 213, 213)
        }

        .show p:nth-child(1) {
            display: inline-block;
            margin-left: 5%;
        }

        .show p:nth-child(2) {
            float: right;
            margin-right: 5%;
        }

        .show p:nth-child(3) {
            text-align: right;
            margin-right: 5%;
        }
        .telenum{
            text-align: center;
            font-size: 0.4rem;
        
        }
    </style>
    <title><?php echo ($title); ?>-历史账单</title>
</head>

<body>
    <div id='flag' style='display:none'><?php echo ($flag); ?></div>
    <div id="tel" style='display:none;'><?php echo ($tel); ?></div>
    <div class="tit-list">
        <ul>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
            <li></li>
        </ul>
        <div class="datashow">
            <div class="show1 show">
                <p>电话号码</p>
                <P><span style="color:rgb(236,80,20);" id='telnum'></span></P>
            </div>
            <div class=" show">
                <p>客户名称</p>
                <P><span style="color:rgb(236,80,20);" id='payname'></span></P>
            </div>     
             <div class="show">
                <p>账单日</p>
                <P><span style="color:rgb(236,80,20);" id='cycleId'></span></P>
            </div>           
            <div class="show">
                <p>费用合计</p>
                <P><span style="color:rgb(236,80,20);" id='allfee'></span></P>
            </div>
        </div>
    </div>
</body>

</html>