var month = ''+new Date().getMonth();
if(month.length == 1){
    month = '0'+month;
}
var year = new Date().getFullYear();
var cycleId = year+''+month; 

var localhostPath='http://'+location.host+'/index.php'; 

var Ajax={
    get: function(url, fn) {
        var obj = new XMLHttpRequest();  
        obj.open('GET', url, true);
        obj.onreadystatechange = function() {
            if (obj.readyState == 4 && obj.status == 200 || obj.status == 304) { // readyState == 4说明请求已完成
                fn.call(this, obj.responseText);  
            }
        };
        obj.send();
    },
    post: function (url, data, fn) {         
        var obj = new XMLHttpRequest();
        obj.open("POST", url, true);
        obj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");  
        obj.onreadystatechange = function() {
            if (obj.readyState == 4 && (obj.status == 200 || obj.status == 304)) {  // 304未修改
                fn.call(this, obj.responseText);
            }
        };
        obj.send(data);
    }
}

Ajax.get(localhostPath+"/home/userInfo/getexpense/type/esim/cycleId/"+cycleId,function(res){
        postMessage(res);
});

