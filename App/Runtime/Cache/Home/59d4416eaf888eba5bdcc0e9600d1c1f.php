<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"> 
        <title>用户列表</title>
        <link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">  
        <script src="http://cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>
    <body>        
        <div class="panel panel-primary">
            <div class="panel-heading">
                用户列表
            </div>
            <div class="panel-body">    
                <div>
                    <button type="button" class="btn btn-default disabled" style='color:#337ab7;font-weight: bold;'>总用户数：<span><?php echo ($res_fixed[0]['thenum']); ?></span></button>
                    <button type="button" class="btn btn-default disabled" style='color:#337ab7;font-weight: bold;'>绑定手机号：<span><?php echo ($res_fixed[1]['thenum']); ?></span></button>
                    <button type="button" class="btn btn-default disabled" style='color:#337ab7;font-weight: bold;'>绑定华为手表：<span><?php echo ($res_fixed[3]['thenum']); ?></span></button>
                    <button type="button" class="btn btn-default disabled" style='color:#337ab7;font-weight: bold;'>绑定出门问问：<span><?php echo ($res_fixed[2]['thenum']); ?></span></button>                                 
                    <span style='padding:5px;'></span>

                    <label class="checkbox-inline">
                        <input id='bindphone' type="checkbox" value="1"> 已绑定手机号
                    </label>
                    <label class="checkbox-inline">
                        <input id='bindhw' type="checkbox" value="2"> 已绑定华为手表
                    </label>
                    <label class="checkbox-inline">
                        <input id='bindask' type="checkbox" value="3"> 已绑定出门问问
                    </label>
                    <?php if(is_array($condition)): foreach($condition as $key=>$value): if($value == 1): ?><input type='hidden' value='bindphone'/>                            
                        <?php elseif($value == 2): ?>
                            <input type='hidden' value='bindhw'/>                            
                        <?php elseif($value == 3): ?>
                            <input type='hidden' value='bindask'/><?php endif; endforeach; endif; ?>
                    <span style='padding:5px;'></span>
                    <button type="button" class="btn btn-primary" onclick="sub()">&nbsp;查&nbsp;&nbsp;询&nbsp;</button><span style='padding:5px;'></span>
                    <button type="button" class="btn btn-primary" onclick="sub('export')">导出本次查询用户</button><span style='padding:5px;'></span>  
                    
                    <script>
                        function checkCondition(){
                            if($('[value=bindphone]').length >=1){
                                $('#bindphone')[0].checked = true;
                            }
                            if($('[value=bindhw]').length >=1){
                                $('#bindhw')[0].checked = true;
                            }
                            if($('[value=bindask]').length >=1){
                                $('#bindask')[0].checked = true;
                            }
                        }
                        checkCondition();
                        function sub(type){
                            $url = '?';
                            if(type == 'export'){
                                $url = '?type=export&';
                            }
                            $(':checkbox').each(function(i,dom){
                               if(dom.checked){
                                   $url += "bind[]="+dom.value+"&" ;                                       
                               } 
                            });
                            //alert($url);return;
                            location = $url;                            
                        }
                    </script>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default">本次查询到用户数：<span style='color:#337ab7;'><?php echo ($total); ?></span></button>
                    </div>
                </div>        
                <div class="table-responsive">     
                    <table class="table table-bordered" style='margin-top: 20px;'>
                        <!--<caption>边框表格布局</caption>-->
                        <thead>
                            <tr>
                                <th>序号</th>
                                <th>昵称</th>                                
                                <th>头像</th>                                
                                <th>用户标识</th>
                                <th>性别</th>
                                <th>省份</th>              
                                <th>城市</th>
                                <th>关注时间</th>
                                <th>手机号</th>
                                <th>华为手表号</th>
                                <th>出门问问号</th>
                            </tr>
                        </thead>
                        <tbody>                           
                            <?php if(is_array($list)): foreach($list as $key=>$value): ?><tr>
                                    <td><?php echo ($key+1); ?></td>
                                    <td><?php echo ($value['nickname']); ?></td>
                                    <td><img src='<?php echo ($value['headimgurl']); ?>' width='36'/></td>                                    
                                    <td><?php echo ($value['openid']); ?></td>
                                    <td><?php echo ($value['sex']); ?></td>
                                    <td><?php echo ($value['province']); ?></td>
                                    <td><?php echo ($value['city']); ?></td>
                                    <td><?php echo ($value['subscribe_time']); ?></td>
                                    <td><?php echo ($value['phone_num']); ?></td>       
                                    <td><?php echo ($value['hw']); ?></td>      
                                    <td><?php echo ($value['ask']); ?></td>      
                                </tr><?php endforeach; endif; ?>                                                        
                        </tbody>
                    </table>  
                </div>    
                <?php echo ($pager); ?>
            </div>
        </div>
    </body>
</html>