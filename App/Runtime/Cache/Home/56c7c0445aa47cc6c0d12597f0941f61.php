<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <script src="<?php echo ($public_url); ?>/layer-v3.0.3/layer/layer.js"></script>
    <link href="<?php echo ($public_url); ?>/CSS/css.css?v=2.4" rel="stylesheet" />
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
        <style>body .demo-class .layui-layer-btn{font-size: 0.24rem}</style>
<?php $p_title; $p_account; $p_submit; $p_url; switch($account_type){ case 'esim': if(empty($account)){ $p_title = '<title>出门问问手表账号绑定</title>'; $p_account = '<input type="number" class="inputlist number-unband " placeholder="请输入账号" name="mobile" id="account" maxLength="11" />'; $p_submit ='<INPUT class=band name=B1 type="button" id="bind" value="绑 定" style="position:relative;top:-0.5rem"> '; $p_url = U('home/index/accountExec/accountType/esim/opr/bind','',false,true); }else{ $p_title = '<title>出门问问手表账号解绑</title>'; $p_account = '<input class="inputlist number-unband " name="mobile" id="account" maxLength="11" readonly  value="'.$account.'"/>'; $p_submit ='<INPUT class=band name=B1 type="button" id="bind" value="解 绑" style="position:relative;top:-0.5rem"> '; $p_url = U('home/index/accountExec/accountType/esim/opr/unBind','',false,true); } break; case 'hw': if(empty($account)){ $p_title = '<title>华为手表账号绑定</title>'; $p_account = '<input type="number" class="inputlist number-unband " placeholder="请输入账号" name="mobile" id="account" maxLength="11" />'; $p_submit ='<INPUT class=band name=B1 type="button" id="bind" value="绑 定" style="position:relative;top:-0.5rem"> '; $p_url = U('home/index/accountExec/accountType/hw/opr/bind','',false,true); }else{ $p_title = '<title>华为手表账号解绑</title>'; $p_account = '<input class="inputlist number-unband" name="mobile" id="account" maxLength="11" readonly  value="'.$account.'"/>'; $p_submit ='<INPUT class=band name=B1 type="button" id="bind" value="解 绑" style="position:relative;top:-0.5rem"> '; $p_url = U('home/index/accountExec/accountType/hw/opr/unBind','',false,true); } break; } echo $p_title; ?>

</head>

<body>
    <div class="title">
        <img class="titpic" src="<?php echo ($headimgurl); ?>" />
<form name="mobileform">
    <?php echo $p_account; echo $p_submit; ?>
</form>
    </div>
    <script>
            
    $('#bind').click(function(){
         var btnval=$("#bind").val()    
        if(btnval=="解 绑"){           
          layer.confirm('是否确定解绑？', {    
                              skin: 'layui-layer-molv', //样式类名
                               skin: 'demo-class',
                               title:false,
                            btn: ['解绑','取消'] //按钮
                                 }, function(){
                                      var $bind = $(this);
            var account = $.trim($("#account").val());
       
       
            if(account == ''){
                 layer.msg('请先输入账号', function(){
                 });   
                 return;
            }else if(!(/^1[345678]\d{9}$/.test(account))){ 
                layer.msg('账号格式不正确', function(){
                 });
                 return;
            }     
            <?php echo 'var url = \''.$p_url.'\';'; ?>
            $.getJSON(url,{account:account},function(res){
                if(res.status != 1){
                    layer.msg(res.msg, function(){
                    });
                }else if(res.status == 1){
                    $bind.attr('disabled',true);                    
                    layer.msg(res.msg, {icon:1},function(){  
                        //location.href = "<?php echo U('home/userInfo/accountManage','',false,true);?>";
                    });
                    $("#account").val("");
                    $("#bind").attr("disabled","disabled");
                }
            })
                                 },function(){
                                     return;
                                 }
                            );
                }else{
            var $bind = $(this);
            var account = $.trim($("#account").val());
       
       
            if(account == ''){
                 layer.msg('请先输入账号', function(){
                 });   
                 return;
            }else if(!(/^1[345678]\d{9}$/.test(account))){ 
                layer.msg('账号格式不正确', function(){
                 });
                 return;
             }     
            <?php echo 'var url = \''.$p_url.'\';'; ?>
            $.getJSON(url,{account:account},function(res){
                if(res.status != 1){
                    layer.msg(res.msg, function(){
                    });
                }else if(res.status == 1){
                    $bind.attr('disabled',true);                    
                    layer.msg(res.msg, {icon:1},function(){  
                        //location.href = "<?php echo U('home/userInfo/accountManage','',false,true);?>";
                    });
                }
            })
        } 
     })     
    </script>
</body>

</html>