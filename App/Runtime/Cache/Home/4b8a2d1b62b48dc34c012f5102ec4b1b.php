<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body onload="load()">
        <form action="<?php echo ($payUrl); ?>" method="post" id="form">
                <input type="hidden" name="serviceId" value="<?php echo ($serviceId); ?>" />
                <input type="hidden" name="data" value="<?php echo ($encodedata); ?>" />
        </form>        
        <script>
            function load(){
                document.getElementById('form').submit();
            }
        </script>
    </body>      
</html>