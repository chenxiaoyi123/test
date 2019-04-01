<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"> 
        <title>下载信息</title>
        <link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">  
        <script src="http://cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body>        
        <div class="panel panel-primary">
            <div class="panel-heading">
                下载信息
            </div>
            <div class="panel-body">   
                <script>
                    function GetDateStr(AddDayCount) {
                        var dd = new Date();
                        dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
                        var y = dd.getFullYear();
                        var m = dd.getMonth()+1;//获取当前月份的日期
                        var d = dd.getDate();
                        return y+"-"+m+"-"+d;
                    }
                    
                    function sub(flag){
                        $url = '?flag='+flag;                        
                        //alert($url);return;
                        location = $url;                            
                    }
                    
                    $(function(){
                        var $btn = $('button');
                        var t = GetDateStr(-1);
                        $btn.eq(0).text(t + '日手机号列表');
                        $btn.eq(1).text(t + '日存费送费订单');
                        $btn.eq(2).text('截止目前全部手机号列表');
                        $btn.eq(3).text('截止目前全部存费送费订单');
                    });
                </script>                
                <div>
                    <button type="button" class="btn btn-default" style='color:#337ab7;font-weight: bold;' onclick="sub('phoneRecord')"></button>
                    <button type="button" class="btn btn-default" style='color:#337ab7;font-weight: bold;' onclick="sub('payRecord')"></button>
                    <button type="button" class="btn btn-default" style='color:#337ab7;font-weight: bold;' onclick="sub('allPhoneRecord')"></button>
                    <button type="button" class="btn btn-default" style='color:#337ab7;font-weight: bold;' onclick="sub('allPayRecord')"></button>                                                     
                </div>        
            </div>
        </div>
    </body>
</html>