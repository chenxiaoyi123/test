<?php
namespace Util;
use Think\Log;

/**
 * SFtp上传下载文件
 *
 */
class Sftp
{
    
    // 初始配置为NULL
    private $config = NULL;
    // 连接为NULL
    private $conn = NULL;
    // 初始化
    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public function connect()
    {
        
        $this->conn = ssh2_connect($this->config['HOST'], $this->config['PORT']);
        if(empty($this->conn)){
            Log::record('sftp服务器连接失败');
            exit;
        }
        if( ssh2_auth_password($this->conn, $this->config['USERNAME'], $this->config['PASSWORD']))
        {
           
        }else{
            Log::record('无法再服务器进行身份验证');
            exit;
        }    
    }
    
    // 传输数据 传输层协议,获得数据
    public function downftp($remote, $local)
    {   
        $ressftp = ssh2_sftp($this->conn);
        return copy("ssh2.sftp://{$ressftp}".$remote, $local);
    }
    
    // 传输数据 传输层协议,写入ftp服务器数据
    public function upftp( $local,$remote, $file_mode = 0777)
    {   
        $ressftp = ssh2_sftp($this->conn);
        return  copy($local,"ssh2.sftp://{$ressftp}".$remote);  
        
    }
  
}
