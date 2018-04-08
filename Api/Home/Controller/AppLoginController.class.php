<?php
/**
 * 用户登陆
 * 2017.02.04
 */
namespace Home\Controller;
use Think\Controller\RestController;
class AppLoginController extends RestController {
	private $_APIURl ="";
	/**
	 * [user_seller_detail 用户进入店铺-获取店铺详情]
	 * @return [type] [description]
	 */
	public function user_seller_detail(){
		$b_id = trim($_GET['b_id']);
		$token = trim($_GET['token']);//可传可不传
		$b_info = M('business_info')->where(array('b_id'=>$b_id))->find();
		$this->_AdminUrl=D("Common")->getUrl();
		if($b_info){
			$info['b_id'] = $b_info['b_id'];
			$info['seller_id'] = $b_info['seller_id'];
			$info['name'] = $b_info['name'];
			$info['bdesc'] = $b_info['bdesc'];
			$info['shoplogo'] = $this->_AdminUrl.$b_info['blogo'];
			$info['shopback'] = $this->_AdminUrl.$b_info['bakimg'];
			$info['follow'] = $b_info['follow'];
			// 根据seller_id 查找商户的token
			$seller_token = M('h_seller_detail')->field('user.token,user.user_id')->where(array('h_seller_detail.seller_id'=>$b_info['seller_id']))->join('user on h_seller_detail.user_id = user.user_id')->find();
			$info['seller_token'] = $seller_token['token'];
			if($token){
				$is_follow = M('h_user_follow_seller')->field('user.user_id')->where(array('user.token'=>$token,'h_user_follow_seller.seller_id'=>$b_info['seller_id']))->join('user on user.user_id = h_user_follow_seller.user_id')->find();
				if($is_follow){
					$info['is_follow'] = 1;
				}else{
					$info['is_follow'] = 0;
				}
			}else{
				$info['is_follow'] = 0;
			}
			
			$data['code'] = 200;
			$data['message'] ="获取成功";
			$data['info'] = $info;
			$this->response($data,I("get.callback"));
			return false;
		}else{
			$data['code'] = 204;
			$data['message'] ="获取失败";
			$this->response($data,I("get.callback"));
			return false;
		}
	}
   /**
    *用户登陆
    *@phe 手机号
    *@pwd 用户密码
    *@login_type 登录方式（1手机号码登录，2 微信登录 3 qq登录）
    *@third_num 第三方账号
    *@third_name 第三方昵称
    */
	public function ULogin(){
		$login_type = $_POST['login_type'];
		if($login_type == 1){
			$phe = $_POST['phe'];
			$pwd = $_POST['pwd'];
			$data = D("Register")->Login($phe,$pwd,$login_type);
		}elseif($login_type == 2 || $login_type == 3){
			// 第三方登录
			$third_num = $_POST['third_num'];
			$third_name = $_POST['third_name'];
			$pho = trim($_POST['pho']);
			if(!$third_num || !$third_name || !$pho){
				$data['code'] = 204;
				$data['message'] ="登录数据不全，请检查";
				$this->response($data);
				return false;
			}
			$data = D("Register")->ThirdLogin($third_num,$third_name,$login_type,$pho);
		}else{
			$data['code'] = 204;
			$data['message'] ="登录类型错误";
			$this->response($data);
			return false;
		}
		$this->response($data);
	}
	/**
	 * 忘记密码
	 * @phe 手机号BindUserPhe
	 * @code 验证码
	 * @pwd 第一次输入密码
	 * @ppwd 第二次输入密码
	 */
	public function FindUPwd(){
		$phe = trim($_POST['phe']);
		$code= trim($_POST['code']);
		$pwd = trim($_POST['pwd']);
		$p_pwd= trim($_POST['p_pwd']);
		if(!$phe || !$pwd || !$p_pwd){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$userinfo = M('user')->where(array('user_phone'=>$phe,'register_type'=>1))->find();
		if($userinfo){
			$salt = $userinfo['salt'];
			$data = D("Register")->F_UserPwd($phe,$code,$pwd,$p_pwd,$salt);
		}else{
			$data['code'] = 204;
			$data['message'] ="未注册，请直接注册";
			$this->response($data);
			return false;
		}

		$this->response($data);
	}
	/**
	 * 用户修改/绑定手机号码
	 * @token_name  用户唯一标识
	 * @phe 手机号码
	 * @code 验证码
	 */
	public function UpUserPhe(){
		$token = trim($_POST['token']);
		$code = trim($_POST['code']);
		$phe = trim($_POST['phe']);
		if(!$token || !$phe){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$data = D('Register')->M_UpUserPhe($token,$code,$phe);
		$this->response($data);
	}
	/**
	 * 登录修改密码
	 * @token  用户唯一标识token
	 * @cpwd  当前密码
	 * @pwd   新密码
	 * @p_pwd 确认新密码
	 * 20170513
	 */
	public function UpUserPwd(){
		$token = trim($_POST['token']);
		$code = trim($_POST['code']);
		$cpwd = trim($_POST['cpwd']);
		$pwd = trim($_POST['pwd']);
		$phe = trim($_POST['phe']);
		$p_phe = trim($_POST['p_phe']);
		$p_pwd = trim($_POST['p_pwd']);
		if(!$token || !$cpwd || !$pwd || !$p_pwd){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		if($cpwd == $pwd){
			$data['code'] = 204;
			$data['message'] ="新密码与当前密码相同，请重新填写";
			$this->response($data);
			return false;
		}
		if($pwd != $p_pwd){
			$data['code'] = 204;
			$data['message'] ="两次新密码不一致，请重新填写";
			$this->response($data);
			return false;
		}
		$data    = D("Register")->M_UpUserPwd($token,$cpwd,$pwd,$phe,$p_pwd);
		$this->response($data);
	}
	/**
	 * 修改用户支付密码
	 * @userid  用户唯一标识id
	 * @phe  用户手机号
	 * @code   验证码
	 * @paypwd 第一次输入的支付密码
	 * @paypwds  新支付密码
	 * 2017.02.04
	 */
	public function UpUserPayPwd(){
	    $token  = I("post.token");
		$userid = D("Register")->GetUserIdFromToken($token);
		$phe    = $_POST['phe'];
		$code   = $_POST['code'];
		$paypwd = $_POST['paypwd'];
		$paypwds= $_POST['paypwds'];
		$data = D("Register")->UpUserPayPwd($phe,$userid,$code,$paypwd,$paypwds);
		$this->response($data);
	}
	/**
	 * 头像上传接口
	 * @token 用户唯一标识token
	 * @pho   用户base64头像数据流
	 * 日期：2017.02.04
	 */
	public function UploadUserPhoto(){
		$token = trim($_POST['token']);
		$pho = trim($_POST['pho']);
		if(!$token || !$pho){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$file_data = base64_decode($_POST['pho']);//;
		//图片存放路径
		$savePath   = "/Uploads/head/";
		$photosavename  = $token.rand(10,1000)."head.jpg";
		$file="./".$savePath.$photosavename;
		$dataPath   = $savePath.$photosavename;
		$m=fopen($file,"w");//当参数为"w"时是将内容覆盖写入文件，而当参数为"a"时是将内容追加写入。
		fwrite($m,$file_data);
		fclose($m);
		//将图片存储至数据库
		$returnData = D("Register")->UploadHead($token,$dataPath);
		$this->response($returnData);
	}
	/**
	 * 修改用户昵称
	 * @token 用户唯一标识token
	 * @nickname 用户昵称
	 * 2017.02.04
	 */
	public function UpdateUserNick(){
		$token = trim($_POST['token']);
		$nickname = trim($_POST['user_name']);
		if(!$token || !$nickname){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$data = D("Register")->UpdateUserNickName($token,$nickname);
		$this->response($data);
	}
	/**
	 * 个人资料
	 * @token 用户唯一标识token
	 * 20170513
	 */
	public function GetUserInfo(){
		$token= trim($_GET['token']);
		if(!$token){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data,I("get.callback"));
			return false;
		}
		$data    = D("Register")->getUserBasicInfo($token);
		if($data){
			if($data['login_type'] == 1){
				$this->_AdminUrl=D("Common")->getUrl();
				$data['user_photo'] = $this->_AdminUrl.$data['user_photo'];
			}
			if($data['pay_password']){
				$data['is_paypwd']= 1;
			}else{
				$data['is_paypwd']= 2;
			}
			// 根据user_id获取用户的商铺状态，商家状态
			$store = D("Register")->getSellerStore($data['user_id']);
			$data['seller_id'] = $store['seller_id'];
			$data['seller_status'] = $store['seller_status'];
			$data['store_status'] = $store['store_status'];
			unset($data['user_password']);
			unset($data['pay_password']);
			$returndata['code'] =200;
			$returndata['message'] = "用户信息获取成功";
			$returndata['info']  = $data;
		}else{
			$returndata['code'] =204;
			$returndata['message'] = "用户信息不存在";
		}
		$this->response($returndata,I("get.callback"));
	}
	/**
	 * 用户注销
	 */
	public function logout(){
		//$_SESSION = array(); //清除SESSION值.
		if(isset($_COOKIE[session_name()])){  //判断客户端的cookie文件是否存在,存在的话将其设置为过期.
			setcookie(session_name(),'',time()-1,'/');
		}
		//session_destroy();  //清除服务器的sesion文件
		$data = array(
				'code' => 200,
				'message' => '退出成功'
		);
		$this->response($data,I("get.callback"));
	}
}