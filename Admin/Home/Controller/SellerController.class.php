<?php
// 后台登陆界面
namespace Home\Controller;
use Think\Controller;
class SellerController extends Controller {
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
    public function sellerlogin(){
    	session(null);
    	$this->display("seller");
    }
    /*
     * 登陆验证
     * 2016.12.16
     */
    public function AuthSeller(){
    	$data = $this->_RELEASEPOST;
    	$uphe = $data['phe'];//用户手机号
    	$pwd  = $data['pwd'];//用户密码
    	$login= $data['login_type'];//用户登陆模式
    	if($login !=1){
    	    $this->ajaxReturn(returnJs("非法登陆", "error"),"EVAL");
    	}
    	$model = D("Admin");
    	$result= $model->CheckSeller($uphe,$pwd);
    	if($result ==1){
    	    $this->ajaxReturn(returnJs("成功","success","window.location.href='./admin.php?c=Admin&logintype=mobile'"),"EVAL");
    	}
    	$this->ajaxReturn($result,"EVAL");
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
}