项目说明：
本项目为《中国联通eSIM智能设备》公众号的生产版本
测试项目版本为 ：wechatunicom


eSIM存费送费生产环境相关：
    121.40.91.111  服务器   部署路径：D:\WeChatBMS\wxunicom    数据库：wxunicom
        相关依赖：
            1.启动phpstudy      （用于启动nginx web服务器、mysql数据库服务）
            2.启动tomcat        （依赖的java项目，用于接口的加解密、摘要和签名的生成，生产版本部署在：D:\soft\java\tomcat7056\webapps\huawei    测试版本部署在：D:\soft\java\tomcat7056\webapps\Testhuawei ）
            3.启动相关windows定时任务  （用于定时上传支付中心、一卡充对账文件到中转对账主机、定时发送异常交易提醒）
            4.启动华为vpn       （用于连接中转对账主机）
            5.启动中转对账主机   （用于接收对账文件，并上传或者接收支付中心、一卡充对账文件，私网ip：192.168.146.74，公网ip：112.65.239.127）


eSIM存费送费测试环境相关：
    116.62.224.107  服务器   部署路径：E:\www\wechatunicom    数据库：wechatunicom