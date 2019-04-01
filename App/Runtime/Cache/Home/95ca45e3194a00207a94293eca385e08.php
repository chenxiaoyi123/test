<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html>
<html>

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Write an awesome description for your new site here. You can edit this line in _config.yml. It will appear in your document head meta (for Google search results) and in your feed.xml site description.
              ">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <link rel="stylesheet" href="/Public/Css/112mycss.css?v=1.1">

        <link rel="stylesheet" href="/Public/jquery-weui-build/dist/lib/weui.min.css">
        <link rel="stylesheet" href="/Public/jquery-weui-build/dist/demos/css/demos.css">
        <title>我的订单</title>
    </head>

    <body ontouchstart>
        <div class="titlist">
            <span class="all" onclick="window.open('<?php echo U("Purchase/allorders","",true,true);?>', '_self')">全部</span>
            <span class="dontpay" onclick="window.open('<?php echo U("Purchase/waitorders","",true,true);?>', '_self')">待支付</span>
            <span class="havepay">已完成</span>
        </div>

        <div class="alllist p2">
            <?php if(empty($list)): ?>您还没有已完成订单<?php endif; ?>              
            <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="container">
                    <img src="/Public/Image/112img/icondd.png" width="12%;" />
                    <p class="money"><?php echo ($vo["package_name"]); ?>
                        <span class="pay">
                            <b>订购成功</b>
                        </span>
                    </p>
                    <p class="time">下单时间：
                        <span class="paytime"><?php echo ($vo["ordertime"]); ?></span>
                    </p>
                    <p class="total">总价：
                        <span><?php echo ($vo["amount"]); ?>元</span>
                    </p>
                    <input type="hidden" value="<?php echo ($vo["id"]); ?>"/>
                    <button class="checkmore">查看详情</button>
                </div><?php endforeach; endif; else: echo "" ;endif; ?>            
            <input type="hidden" id='page' value="<?php echo ($page); ?>"/>   
        </div>

        <div class="weui-loadmore">
            <i class="weui-loading"></i>
            <span class="weui-loadmore__tips">正在加载</span>
        </div>
        <script src="/Public/jquery-weui-build/dist/lib/jquery-2.1.4.js"></script>
        <script src="/Public/jquery-weui-build/dist/lib/fastclick.js"></script>
        <script>
                $(function () {
                    //查看详情
                    $('.checkmore').click(function(){
                        var id = $(this).prev('input').val();
                        if(id){
                            var url ='<?php echo U("Purchase/orderinfo","",true,true);?>';                                
                            location = url+"?id="+id;
                        }                    
                    });                    
                    
                    var leng = $(".alllist").children('.container').length;
                    console.log(leng)
                    if (leng <= 6) {
                        $(".weui-loadmore").hide();
                    } else {
                        $(".weui-loadmore").show();
                    }


                    $(".havepay").addClass('checktit');
                    var loading = false;  //状态标记
                    var url = '<?php echo U("Purchase/overorders","",true,true);?>';
                    url = url + '?page='+$('#page').val();                    
                    $(document.body).infinite().on("infinite", function () {
                        if (loading)
                            return;
                        loading = true;
                        setTimeout(function () {    
                            var url = '<?php echo U("Purchase/overorders","",true,true);?>';
                            url = url + '?page='+$('#page').val();                            
                            $.ajax({
                                type: "get",
                                url: url,
                                dataType: "json",
                                success: function (res) {
                                    //每次加载完成，需要更新一下  page
                                    if(res.length == 0){
                                        loading = false;
                                        $('.weui-loadmore').hide();
                                    }else{
                                        var page = $('#page').val();
                                        $('#page').val(parseInt(page)+1);
                                        //如果起始没有订单，则清空元字符
                                        if($('.container')[0]){
                                            
                                        }else{
                                            $('.alllist').remove();
                                        }
                                        $.each(res,function(index,content){
 $(".alllist").append("<div class='container'> <img src='/Public/Image/112img/icondd.png' width='12%;' /> <p class='money'>"+content.package_name+"<span class='pay'><b>订购成功</b> </span> </p>  <p class='time'>下单时间： <span class='paytime'>"+content.ordertime+"</span></p><p class='total'>总价：<span>"+content.amount+"元</span></p><input type='hidden' value='"+content.id+"'/><button class='checkmore'>查看详情</button></div>");                                                                                        
                                        });
                                    }
                                },
                                error: function () {

                                }
                            })
                            loading = false;
                        }, 1000);   //模拟延迟
                    });
                });

        </script>
        <script src="/Public/jquery-weui-build/dist/js/jquery-weui.js"></script>
        <script src="/Public/Js/112js.js"></script>
    </body>

</html>