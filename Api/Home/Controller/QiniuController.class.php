<?php
namespace Home\Controller;
header("content-type:text/html;charset=utf-8");
use Think\Controller\RestController;
Vendor('Qiniusdk.autoload');  //七牛入口文件引入  
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
class QiniuController extends RestController {
    
    private $_ak     = 'eQdnP0zVKY95Hi1UxrQwSJcNLb8Zk6nUrJfuo3tx';
    private $_sk     = 'guR4yqku0chjsGB8qrcFgRZMCKctRLg3s6cSCX20';
    private $_bucket = 'sevpn'; 
    /* 
    获取上传凭证
    */
    public function getToken() {
        $auth = new Auth($this->_ak, $this->_sk);
		// 生成上传Token
        $token = $auth->uploadToken($this->_bucket);      
        $data['code']  = 200;
        $data['message'] = '获取token成功';
        $data['token'] = $token;
        $this->token = $token;
        $this->response($data);   
    }

}