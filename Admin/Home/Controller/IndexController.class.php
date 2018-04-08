<?php
// 后台登陆界面
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
 	 private $_RELEASEPOST;
	 public function _initialize(){
	 	$this->_ApiReturn = D("ApiReturn");
	 	$data = $_POST;
	 	foreach($data as $key=>$val){
	 		$Getdata[$key] = I("post.$key","","strip_tags");
	 	}
	 	$this->_RELEASEPOST = $Getdata;
	 }
	/*
	 * 登陆界面
	 * 2016.12.16
	 */
    public function index(){
    	$this->display("admin");//Login
    }
    /*
     * 登陆验证
     * 2016.12.16
     */
    public function CheckLogin(){
    	$data = $this->_RELEASEPOST;
    	$imgcode = hash("sha256",strtolower($data['imgcode']));
    	if($imgcode == $_SESSION['authnum_session']){
    		$model = D("Admin");
    		$userinfo = $model->CheckUsername($data['username']);
    		if(!empty($userinfo)){
    			if($userinfo['pwd'] == hash("sha256",$data['userpwd'])){
    				session("nmt_admin",$userinfo['user']);
    				session("nmt_adminid",$userinfo['id']);
    				session("nmt_name",$userinfo['adname']);
    				session("nmt_groupname",$userinfo['groupname']);
    				session("nmt_pinyin",$userinfo['pinyin']);
    				session("nmt_groupid",$userinfo['groupid']);
    				session("nmt_bid",$userinfo['bid']);
    				session("logintype",1);
    				echo 1;
    			}else{
    				echo -2;
    			}
    		}else{
    			echo 0;
    		}
    	}else{
    		echo -1;
    	}
    }
    /*
     * 验证码生成
     * 日期：2016.12.16
     */
    public function Verify(){
    	//session_destroy();
    	import("Vendor.Code.Imgcode");
    	$Verify = new \ValidateCode();
    	$Verify->doimg();
    	$_SESSION['authnum_session'] = hash("sha256",$Verify->getCode());
    }
    /**
     * 商品详情
     * 2017.03.24
     */
    public function Godsdesc(){
    	$id 	= $_GET['gid'];
    	$data 	= D("Goods")->HtmlDesc($id);
    	$this->assign("list",$data);
    	$this->display("goods_info");
    }
    /**
     * 关于我们
     * 2017.03.24
     */
    public function About(){
    	$this->display("guanyu");
    }
    /**
     * 协议
     * 2017.03.24
     */
    public function Agreement(){
    	$this->display("xieyi");
    }

	/**
	 * 退出
	 */
	public function out(){
		session(null);
		echo "<script>if (top.location != self.location){top.location=self.location;}location.href='./admin.php?c=Index&a=index'</script>";
	}
}