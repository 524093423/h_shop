<?php 
/**
 * 用户管理控制器
 * 20170513
 */
namespace Home\Controller;
use Think\Controller\RestController;
Class UserController extends RestController {
	public $user_id;
	public function __construct(){
		parent::__construct();
		$token = $_REQUEST['token'];
		$user = M('user')->where(array('token'=>$token))->field('user_id,state')->find();
		if(!$token || !$user['user_id']){
			$data['code'] = 204;
			$data['message'] ="请先登录";
			$this->response($data);
			return false;
		}
		if ($user['state'] == 1){
			$data['code'] = 204;
			$data['message'] ="对不起，此账号已被查封";
			$this->response($data);
			return false;
		}

		$this->user_id = $user['user_id'];
	}
	/**
	 * [my_withdraw_list 商户--提现记录]
	 * @return [type] [description]
	 */
	public function my_withdraw_list(){
		$user_id = $this->user_id;
		$seller_id = trim($_GET['seller_id']);
		if(!$seller_id){
			$data['code'] = 204;
			$data['message'] ="数据不全，请检查";
			$this->response($data);
			return false;
		}
		$info= M('h_withdraw')->field('with_id,seller_id,money,type,status,create_time')->where(array('user_id'=>$user_id,'seller_id'=>$seller_id))->order("create_time desc")->select();
		if($info){
			$data['code'] = 200;
			$data['message'] ="提现记录获取成功";
			$data['info'] = $info;
			$this->response($data);
			return false;
		}else{
			$data['code'] = 200;
			$data['message'] ="未发起过提现,没有提现记录";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [withdraw_deposit 商家发起提现]
	 * @return [type] [description]
	 */
	public function withdraw_deposit(){
		$user_id = $this->user_id;
		$add['user_id'] = $money = $user_id;
		$add['money'] = $money = trim($_POST['money']);
		$add['seller_id'] = $seller_id = trim($_POST['seller_id']);
		$add['type'] = $type = trim($_POST['type']);
		$add['bank_id'] = $bank_id = trim($_POST['bank_id']);
		$add['remarks'] = $remarks = trim($_POST['remarks']);
		if(!$money || !$seller_id ||!$type || !$bank_id){
			$data['code'] = 204;
			$data['message'] ="数据不全，请检查";
			$this->response($data);
			return false;
		}
		$userinfo = M('user')->where(array('user_id'=>$user_id))->find();
		// 账户余额,冻结余额
		$account = $userinfo['account']-$userinfo['freeze_account'];
		if($money>$account){
			$data['code'] = 204;
			$data['message'] ="提现金额不能大于当前余额";
			$this->response($data);
			return false;
		}
		$add['create_time'] = date('Y-m-d H:i:s');
		$add['status'] = 1;
		$update['freeze_account'] = $userinfo['freeze_account']+$money;
		$res1 = M('h_withdraw')->add($add);
		if($res1){
			$res2 = M('user')->where(array('user_id'=>$user_id))->save($update);
			$data['code'] = 200;
			$data['message'] ="提现订单提交成功";
			$this->response($data);
			return false;
		}else{
			$data['code'] = 204;
			$data['message'] ="提现订单提交失败";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [up_withdraw_number 修改商户提现账号]
	 * @return [type] [description]
	 */
	public function up_withdraw_number(){
		$user_id = $this->user_id;
		$type = trim($_POST['type']);
		$number = trim($_POST['number']);
		if(!$type || !$number){
			$data['code'] = 204;
			$data['message'] ="数据不全，请检查";
			$this->response($data);
			return false;
		}
		if($type == 1){
			$alipay = M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type))->find();
			if($alipay['number'] == $number){
				$data['code'] = 204;
				$data['message'] ="支付宝账户和原账户一致，请检查";
				$this->response($data);
				return false;
			}
			$res =  M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type))->save(array('number'=>$number,'status'=>1,'create_time'=>date('Y-m-d H:i:s')));
//			$add_bank =  M('h_user_bank')->add(array('user_id'=>$user_id,'type'=>$type,'number'=>$number,'status'=>1,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s')));
			if($res){
				$data['code'] = 200;
				$data['message'] ="修改成功";
				$this->response($data);
				return false;
			}else{
				$data['code'] = 204;
				$data['message'] ="修改失败";
				$this->response($data);
				return false;
			}
		}else if($type == 2){
			$weixin = M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type))->find();
			if($weixin){
				if($weixin['number'] == $number){
					$data['code'] = 204;
					$data['message'] ="微信账户和原账户一致，请检查";
					$this->response($data);
					return false;
				}
				$path = 'seller';
				$other_logo = 'weixin_withdraw'.rand(1000,9999);
				$picture_path = D('Common')->po_img($number,$path,$user_id,$other_logo);
				$res =  M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type))->save(array('number'=>$picture_path,'status'=>1,'create_time'=>date('Y-m-d H:i:s')));
				// $add_bank =  M('h_user_bank')->add(array('user_id'=>$user_id,'type'=>$type,'number'=>$picture_path,'status'=>1,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s')));
				if($res){
					unlink($weixin['number']);
					$data['code'] = 200;
					$data['message'] ="修改成功";
					$this->response($data);
					return false;
				}else{
					$data['code'] = 204;
					$data['message'] ="修改失败";
					$this->response($data);
					return false;
				}
			}else{
				$path = 'seller';
				$other_logo = 'weixin_withdraw'.rand(1000,9999);
				$picture_path = D('Common')->po_img($number,$path,$user_id,$other_logo);
				$result =  M('h_user_bank')->add(array('user_id'=>$user_id,'type'=>$type,'number'=>$picture_path,'status'=>1,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s')));
				if($result){
					$data['code'] = 200;
					$data['message'] ="新增成功";
					$this->response($data);
					return false;
				}else{
					$data['code'] = 204;
					$data['message'] ="新增失败";
					$this->response($data);
					return false;
				}
			}
			$this->response($data);
			return false;
		}else{
			$data['code'] = 204;
			$data['message'] ="数据错误，请检查";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [withdraw_number 获取提现账户]
	 * @return [type] [description]
	 */
	public function withdraw_number(){
		$user_id = $this->user_id;
		$type = $_GET['type'];
		if($type == 1){
			$alipay = M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type,'status'=>1))->find();
			$data['code'] = 200;
			$data['message'] ="获取成功";
			if($alipay){
				$data['info']['bank_id'] = $alipay['bank_id'];
				$data['info']['number'] = $alipay['number'];
			}else{
				$data['info']['bank_id'] = "";
				$data['info']['number'] = "";
			}
			$this->response($data);
			return false;			
		}else if($type == 2){
			$weixin = M('h_user_bank')->where(array('user_id'=>$user_id,'type'=>$type,'status'=>1))->find();
			$this->_AdminUrl=D("Common")->getUrl();
			$data['code'] = 200;
			$data['message'] ="获取成功";
			if($weixin){
				$data['info']['bank_id'] = $weixin['bank_id'];
				$data['info']['number'] = $this->_AdminUrl.$weixin['number'];
			}else{
				$data['info']['bank_id'] = "";
				$data['info']['number'] = "";
			}
			$this->response($data);
			return false;
		}else{
			$data['code'] = 204;
			$data['message'] ="数据错误，请检查";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [my_wallet 商家--我的钱包]
	 * @return [type] [description]
	 */
	public function my_wallet(){
		$user_id = $this->user_id;
		$seller_id = $_GET['seller_id'];
		$userinfo = M('user')->where(array('user_id'=>$user_id))->find();
		if($userinfo && $seller_id){
			if($userinfo['is_seller'] != 1){
				$data['code'] = 204;
				$data['message'] ="您还不是商家，请检查";
				$this->response($data);
				return false;
			}else{	
				$data['code'] = 200;
				$data['message'] ="获取成功";
				// 我的余额=余额-冻结余额
				$data['info']['account'] = $userinfo['account']-$userinfo['freeze_account'];
				$this->response($data);
				return false;
			}
		}else{
			$data['code'] = 204;
			$data['message'] ="数据错误，请检查";
			$this->response($data);
			return false;
		}
	}

	/**
	 * [my_follow_store 我的关注-商家列表]
	 * @return [type] [description]
	 */
	public function my_follow_store(){ 
		$user_id = $this->user_id;
		$AdminUrl =D("Common")->getUrl();
		$follow_info = M('h_user_follow_seller')
		     ->field("business_info.seller_id,business_info.b_id as shopid,`name` as shop_name,bdesc as shop_desc,REPLACE(`blogo`,'/Uploads','".$AdminUrl."/Uploads') as shop_logo,follow")
		    ->join('business_info on business_info.seller_id = h_user_follow_seller.seller_id')
		    ->where(array('h_user_follow_seller.user_id'=>$user_id))
		    ->select();
		if(!empty($follow_info)){
			$data['code'] = 200;
			$data['message'] ="我的关注获取成功";
			$data['info'] =$follow_info;
			$this->response($data,I("callback"));
		}else{
			$data['code'] = 200;
			$data['message'] ="您还未关注商家,赶快去关注……";
			$this->response($data,I("callback"));
		}
	}
	/**
	 * [follow_store 关注/取关商家]
	 * @return [type] [description]
	 */
	public function follow_store(){
		$user_id = $this->user_id;
		$seller_id = trim($_GET['seller_id']);
		$follow = trim($_GET['follow']);//1关注，2取消关注
		if($follow == 1){
			if($seller_id){
				$se = M('h_user_follow_seller')->where(array('user_id'=>$user_id,'seller_id'=>$seller_id))->find();
				if($se){
					$data['code'] = 204;
					$data['message'] ="已经关注，请勿再次关注";
					$this->response($data,I('get.callback'));
					return false;
				}
				// 更新商家关注量+1
				$seller_follow_up = M('business_info')->where(array('seller_id'=>$seller_id))->setinc('follow');
				// 新加用户的关注商家表
				$add = array(
					'user_id'=>$user_id,
					'seller_id'=>$seller_id,
					'create_time'=>date('Y-m-d H:i:s'),
					);
				$user_follow_up = M('h_user_follow_seller')->add($add);
				if($seller_follow_up && $user_follow_up){
					$data['code'] = 200;
					$data['message'] ="商家关注成功";
					$this->response($data,I('get.callback'));
					return false;
				}else{
					$data['code'] = 204;
					$data['message'] ="商家关注失败";
					$this->response($data,I('get.callback'));
					return false;
				}
			}else{
				$data['code'] = 204;
				$data['message'] ="数据有误，请检查";
				$this->response($data,I('get.callback'));
				return false;
			}
		}else if($follow == 2){
			if($seller_id){
				$se = M('h_user_follow_seller')->where(array('user_id'=>$user_id,'seller_id'=>$seller_id))->find();
				if(!$se){
					$data['code'] = 204;
					$data['message'] ="没有关注，无法取消关注";
					$this->response($data,I('get.callback'));
					return false;
				}
				// 更新商家关注量-1
				$seller_follow_del = M('business_info')->where(array('seller_id'=>$seller_id))->setDec('follow');
				// 用户的关注商家表删除此关注信息
				$user_follow_del = M('h_user_follow_seller')->where(array('user_id'=>$user_id,'seller_id'=>$seller_id))->delete();
				if($seller_follow_del && $user_follow_del){
					$data['code'] = 200;
					$data['message'] ="商家关注取消成功";
					$this->response($data,I('get.callback'));
					return false;
				}else{
					$data['code'] = 204;
					$data['message'] ="商家关注取消失败";
					$this->response($data,I('get.callback'));
					return false;
				}
			}else{
				$data['code'] = 204;
				$data['message'] ="数据有误，请检查";
				$this->response($data,I('get.callback'));
				return false;
			}
		}else{
			$data['code'] = 204;
			$data['message'] ="数据有误，请检查";
			$this->response($data,I('get.callback'));
			return false;
		}
	}
	/**
	 * [Seller_Detail 获取商家认证信息]
	 */
	public function get_Seller_Detail(){
		$user_id = $this->user_id;
		$seller_id = trim($_GET['seller_id']);
		$seller_info = M('h_seller_detail')
		            ->field('h_seller_detail.*,h_user_bank.number,h_user_bank.user_id,h_user_bank.type')
		            ->where(array('h_seller_detail.seller_id'=>$seller_id,'h_user_bank.type'=>1))
		            ->join('h_user_bank on h_user_bank.user_id = h_seller_detail.user_id')
		            ->find();
		if($seller_info){
			$this->_AdminUrl=D("Common")->getUrl();
			$info['seller_id']=$seller_info['seller_id'];
			$info['seller_name']=$seller_info['seller_name'];
			$info['seller_phone']=$seller_info['seller_phone'];
			$info['company_name']=$seller_info['company_name'];
			$info['payment_no']=$seller_info['number'];
			$info['province']=$seller_info['province'];
			$info['city']=$seller_info['city'];
			$info['remarks']=$seller_info['remarks'];
			$info['picture']=$this->_AdminUrl.$seller_info['picture'];
			$info['status']=$seller_info['status'];
			if($seller_info['status'] == 3){
				$info['nopass']=$seller_info['nopass'];
			}
			$data['code'] = 200;
			$data['message'] ="认证信息获取成功";
			$data['info'] = $info;
			$this->response($data);
			return false;
		}else{
			$data['code'] = 204;
			$data['message'] ="认证信息获取失败，请检查数据";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [up_seller_store 修改店铺信息]
	 * @return [type] [description]
	 */
	public function up_seller_store(){
		$user_id = $this->user_id;
		$token = $_REQUEST['token'];
		$b_id = trim($_POST['b_id']);//店铺id
		$business_info = M('business_info')->where(array('b_id'=>$b_id))->find();
		if($business_info){
			$shop_name = trim($_POST['shop_name']);
			$shop_desc = trim($_POST['shop_desc']);
			$shop_logo = trim($_POST['shop_logo']);
			$shop_back = trim($_POST['shop_back']);
			if(!$shop_name && !$shop_desc && !$shop_logo && !$shop_back){
				$data['code'] = 204;
				$data['message'] ="没有需要修改店铺信息，请检查";
				$this->response($data);
				return false;
			}
			if($shop_name){
				$update['name'] = $shop_name;
				// 商家修改店铺名称，修改环信昵称为店铺名
                D('Huanxin')->editNickname($token,$shop_name);
			}
			if($shop_desc){
				$update['bdesc'] = $shop_desc;
			}
			$path = 'seller_shop';
			$other_logo = 'shoplogo';
			$other_back = 'shopback';
			if($shop_logo){
					unlink($business_info['blogo']);
					$shop_logo_path = D('Common')->po_img($shop_logo,$path,$user_id,$other_logo);
					$update['blogo'] = $shop_logo_path;
			}
			if($shop_back){
					unlink($business_info['bakimg']);
					$shop_back_path = D('Common')->po_img($shop_back,$path,$user_id,$other_back);
					$update['bakimg'] = $shop_back_path;
			}
			$update['update_time'] = date('Y-m-d H:i:s');
			$bus_up = M('business_info')->where(array('b_id'=>$b_id))->save($update);
			if($bus_up){
				$data['code'] = 200;
				$data['message'] ="店铺信息更新成功";
				$this->response($data);
				return false;
			}else{
				$data['code'] = 204;
				$data['message'] ="店铺信息更新失败";
				$this->response($data);
				return false;
			}
		}else{
			$data['code'] = 204;
			$data['message'] ="没有店铺,请核对信息";
			$this->response($data);
			return false;
		}
	}
	/**
	 * [seller_list 商家进入自己店铺商家认证--店铺详情  效果图18.1上半部分和效果图2.1店铺上半部分]
	 * @return [type] [description]
	 */
	public function seller_list(){
		$user_id = $this->user_id;
		$res = M('h_seller_detail')->where(array('user_id'=>$user_id))->find();
		if($res['status'] == 2){
			$business_info = M('business_info')->where(array('seller_id'=>$res['seller_id']))->find();
			$this->_AdminUrl=D("Common")->getUrl();
			$is_follow = M('h_user_follow_seller')->where(array('user_id'=>$user_id,'seller_id'=>$res['seller_id']))->find();
			if($business_info){
				$info['b_id'] = $business_info['b_id'];
				$info['seller_id'] = $business_info['seller_id'];
				$info['name'] = $business_info['name'];
				$info['bdesc'] = $business_info['bdesc'];
				$info['follow'] = $business_info['follow'];
				$info['shoplogo'] = $this->_AdminUrl.$business_info['blogo'];
				$info['shopback'] = $this->_AdminUrl.$business_info['bakimg'];
				$info['pendorder']= D("Seller")->pendOrder($business_info['seller_id']);
				$info['status'] = 1;//状态为1 可以去请求 我的钱包，信息修改，订单处理，上传商品求，帮助接口
				$data['code'] = 200;
				$data['message'] ="获取成功";
				$data['info'] = $info;
			}else{
				$info['b_id'] = -1;
				$info['seller_id'] = $res['seller_id'];
				$info['name'] = '';
				$info['bdesc'] = '';
				$info['follow'] = 0;
				$info['shoplogo'] = "";//$this->_AdminUrl.'/Uploads/seller_shop/shoplogo.jpg';
				$info['shopback'] = "";//$this->_AdminUrl.'/Uploads/seller_shop/shopback.jpg';
				$info['status'] = 0;//状态为0  只能去请求 信息修改提交接口，帮助接口，其他无法请求
				$data['code'] = 200;
				$data['message'] ="获取成功";
				$data['info'] = $info;
			}
		}else{
			$data['code'] = 204;
			$data['message'] ="商家认证未通过,请核对信息";
			//$data['info']['status'] = 2;
			$this->response($data);
			return false;
		}

		$this->response($data);
	}
	/**
	 * [check_authentication_status 商家认证检测接口]
	 * @return [type] [description]
	 */
	public function check_authentication_status(){
		$user_id = $this->user_id;
		$res = M('h_seller_detail')->where(array('user_id'=>$user_id))->find();
		if($res){
			if($res['status'] == 1){
				$data['code'] = 200;
				$data['message'] ="商家认证，正在审核请耐心等待";
				$data['info']['seller_id'] = $res['seller_id'];
				$data['info']['status'] = 1;
				$this->response($data);
			}else if($res['status'] == 2){
				$data['code'] = 200;
				$data['message'] ="商家认证通过,前往认证列表";
				$data['info']['seller_id'] = $res['seller_id'];
				$data['info']['status'] = 2;
				$this->response($data);
			}else  if($res['status'] == 3){
				$data['code'] = 200;
				$data['message'] = $res['nopass']?$res['nopass']:"商家认证未通过,请修改认证信息";
				$data['info']['seller_id'] = $res['seller_id'];
				$data['info']['status'] = 3;
				$this->response($data);
			}
		}else{
			// 首次认证
			$data['code'] = 200;
			$data['message'] ="首次认证,去认证";
			$data['info']['status'] = 0;
			$this->response($data);
			return false;
		}
	}
	/**
	 * [seller_store 商家店铺信息首次提交
	 * @return [type] [description]
	 */
	public function seller_store(){
		$user_id = $this->user_id;
		$token = $_REQUEST['token'];
		$res = M('h_seller_detail')->where(array('user_id'=>$user_id))->find();
		if(!$res){
			$data['code'] = 204;
			$data['message'] ="本账户不是商家,请检查";
			$this->response($data);
			return false;
		}else if($res['status'] == 1){
			$data['code'] = 204;
			$data['message'] ="商家认证正在审核,请等待";
			$this->response($data);
			return false;
		}else if($res['status'] == 3){
			$data['code'] = 204;
			$data['message'] ="商家认证审核没有通过,请重新提交";
			$this->response($data);
			return false;
		}
		$seller_id = $res['seller_id'];
		// 判断是否已经有店铺
		$result = M('business_info')->where(array('seller_id'=>$seller_id))->find();
		if($result){
			$data['code'] = 204;
			$data['message'] ="商家店铺已有,请勿反复提交";
			$this->response($data);
			return false;
		}
		$shop_name = trim($_POST['shop_name']);
		$shop_desc = trim($_POST['shop_desc']);
		$shop_logo = trim($_POST['shop_logo']);
		$shop_back = trim($_POST['shop_back']);
		if(!$shop_name || !$shop_desc || !$shop_logo || !$shop_back){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请检查";
			$this->response($data);
			return false;
		}
		$path = 'seller_shop';
		$other_logo = 'shoplogo';
		$other_back = 'shopback';
		$shop_logo_path = D('Common')->po_img($shop_logo,$path,$user_id,$other_logo);
		$shop_back_path = D('Common')->po_img($shop_back,$path,$user_id,$other_back);
		$data = D('ApplaySeller')->seller_store($seller_id,$shop_name,$shop_desc,$shop_logo_path,$shop_back_path,$user_id);
		// 商家修改店铺名称，修改环信昵称为店铺名
        D('Huanxin')->editNickname($token,$shop_name);
		$this->response($data);
	}
	/**
	 * [forget_paypwd 忘记商户支付密码]
	 * @return [type] [description]
	 */
	public function find_paypwd(){
		$user_id = $this->user_id;
		// $seller_id = trim($_POST['seller_id']);
		$phe = trim($_POST['phe']);
		$code= trim($_POST['code']);
		$pay_pwd = trim($_POST['pay_pwd']);
		$p_pay_pwd= trim($_POST['p_pay_pwd']);
		if(!$phe || !$pay_pwd || !$p_pay_pwd || !$code){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$userinfo = M('user')->field('pay_password,salt,is_seller')->where(array('user_id' => $user_id))->find();
		$sellerinfo = M('h_seller_detail')->where(array('user_id' => $user_id,'status'=>2))->find();
		$salt = $userinfo['salt'];
		$is_seller = $userinfo['is_seller'];
		if($is_seller != 1 || !$sellerinfo){
			$data['code'] = 204;
			$data['message'] ="本账户不是商家,请检查";
			$this->response($data);
			return false;
		}
		if(strlen($pay_pwd) != 6){
			$data['code'] = 204;
			$data['message'] ="支付密码需要6位";
			$this->response($data);
			return false;
		}
		if($phe != $sellerinfo['seller_phone']){
			$data['code'] = 204;
			$data['message'] ="手机号码跟商家手机号码不一致，请检查";
			$this->response($data);
			return false;
		}
		$data = D('ApplaySeller')->findPayPwd($user_id,$phe,$code,$pay_pwd,$p_pay_pwd,$salt);
		$this->response($data);
	}
	/**
	 * [check_old_paypwd 核对商家原支付密码]
	 * @return [type] [description]
	 */
	public function check_old_paypwd(){
		$token = trim($_REQUEST['token']);
		$pay_pwd = trim($_REQUEST['old_pay_pwd']);
		$userinfo = M('user')->field('pay_password,salt,is_seller')->where(array('token' => $token))->find();
		$old_pay_pwd = $userinfo['pay_password'];
		$salt = $userinfo['salt'];
		$is_seller = $userinfo['is_seller'];
		if($is_seller != 1){
			$data['code'] = 204;
			$data['message'] ="本账户不是商家,请检查";
			$this->response($data);
			return false;
		}
		if(md5($pay_pwd.$salt) == $old_pay_pwd){
			$data['code'] = 200;
			$data['message'] ="原支付密码验证正确";
		}else{
			$data['code'] = 204;
			$data['message'] ="原支付密码输入不正确,请检查";
			$this->response($data);
			return false;
		}
		$this->response($data);
	}
	/**
	 * [setPayPwd 设置/修改支付密码]
	 */
	public function setPayPwd(){
		$token = trim($_GET['token']);
		$pay_pwd = trim($_GET['pay_pwd']);
		$userinfo = M('user')->field('user_id,pay_password,salt,is_seller')->where(array('token' => $token))->find();
		$old_pay_pwd = $userinfo['pay_password'];
		$salt = $userinfo['salt'];
		$is_seller = $userinfo['is_seller'];
		if($is_seller != 1){
			$data['code'] = 204;
			$data['message'] ="本账户不是商家,请检查";
			$this->response($data);
			return false;
		}
		if(strlen($pay_pwd) != 6){
			$data['code'] = 204;
			$data['message'] ="支付密码需要6位";
			$this->response($data);
			return false;
		}
		if($old_pay_pwd['pay_password']){
			if(md5($pay_pwd.$salt) == $old_pay_pwd){
				$data['code'] = 204;
				$data['message'] ="新支付密码不能与原支付密码一样";
				$this->response($data);
				return false;
			}
		}
		$data = D('ApplaySeller')->setPayPwd($token,$pay_pwd,$salt,$userinfo['user_id']);
		$this->response($data);
	}
	/**
	 * 申请成为商家
	 * @token 
	 * @seller_name  法人姓名
	 * @seller_phone 商家手机号码
	 * @province 省份
	 * @city 城市
	 * @remarks 详细地址
	 * @company_name 企业名称
	 * @business_licence 营业执照照片
	 * @payment_no 支付宝账号
	 */
	public function Apply_Seller(){
		$user_id = $this->user_id;
		$seller_name = trim($_POST['seller_name']);
		$seller_phone = trim($_POST['seller_phone']);
		$province = trim($_POST['province']);
		$city = trim($_POST['city']);
		$remarks = trim($_POST['remarks']);
		$company_name = trim($_POST['company_name']);
		$business_licence = trim($_POST['business_licence']);
		$payment_no = trim($_POST['payment_no']);
		// 审核不通过的,再次提交审核
		$seller_id = trim($_POST['seller_id']);
		$seller_info = M('h_seller_detail')
		            ->where(array('h_seller_detail.seller_id'=>$seller_id,'h_user_bank.type'=>1,'h_seller_detail.user_id'=>$user_id))
		            ->join('h_user_bank on h_user_bank.user_id = h_seller_detail.user_id')
		            ->find();
		if(!empty($seller_id) && $seller_id !="null"){
			if(!$seller_info){
				$data['code'] = 204;
				$data['message'] ="数据请求错误，请检查";
				$this->response($data);
				return false;
			}
			if(!$seller_name && !$seller_phone && !$province && !$city && !$remarks && !$company_name && !$business_licence && !$payment_no){
				$data['code'] = 204;
				$data['message'] ="没有需要修改认证信息，请检查";
				$this->response($data);
				return false;
			}
			if($seller_name){
// 				if($seller_name == $seller_info['seller_name']){
// 					$data['code'] = 204;
// 					$data['message'] ="法人名称与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['seller_name'] = $seller_name;
			}
			if($seller_phone){
// 				if($seller_phone == $seller_info['seller_phone']){
// 					$data['code'] = 204;
// 					$data['message'] ="电话号码与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['seller_phone'] = $seller_phone;
			}
			if($province){
// 				if($province == $seller_info['province']){
// 					$data['code'] = 204;
// 					$data['message'] ="省份与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['province'] = $province;
			}
			if($city){
// 				if($city == $seller_info['city']){
// 					$data['code'] = 204;
// 					$data['message'] ="城市与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['city'] = $city;
			}
			if($remarks){
// 				if($remarks == $seller_info['remarks']){
// 					$data['code'] = 204;
// 					$data['message'] ="详细信息与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['remarks'] = $remarks;
			}
			if($company_name){
// 				if($company_name == $seller_info['company_name']){
// 					$data['code'] = 204;
// 					$data['message'] ="公司名称与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update['company_name'] = $company_name;
			}
			if($payment_no){
// 				if($payment_no == $seller_info['number']){
// 					$data['code'] = 204;
// 					$data['message'] ="支付宝账号与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$update_bank['number'] = $payment_no;
			}
			if($business_licence){
// 				if($business_licence == $seller_info['picture']){
// 					$data['code'] = 204;
// 					$data['message'] ="店铺logo与原信息完全一致,请核对信息";
// 					$this->response($data);
// 					return false;
// 				}
				$path = 'seller';
				$other_logo = 'business_licence';
				unlink($seller_info['picture']);
				$picture_path = D('Common')->po_img($business_licence,$path,$user_id,$other_logo);
				$update['picture'] = $picture_path;
			}
			$update['status'] =1;//改为待审核状态
			$seller_up = M('h_seller_detail')->where(array('seller_id'=>$seller_id,'status'=>3))->save($update);
			if($payment_no){
				if($payment_no != $seller_info['number']){
					$user_bank_up = M('h_user_bank')->where(array('user_id'=>$user_id))->save($update_bank);
				}
			}
			if($seller_up || $user_bank_up){
				$data['code'] = 200;
				$data['message'] ="认证信息重新提交成功";
				$this->response($data);
				return false;
			}else{
				$data['code'] = 204;
				$data['message'] ="认证信息重新提交失败";
				$this->response($data);
				return false;
			}
		}else{
			// 第一次提交审核
			if(!$seller_name || !$seller_phone || !$province || !$city || !$remarks || !$company_name || !$business_licence || !$payment_no){
				$data['code'] = 204;
				$data['message'] ="数据不完善，请完善信息";
				$this->response($data);
				return false;
			}
			//是否提交过申请
			$sellerinfo = M('h_seller_detail')->where(array('user_id'=>$user_id))->find();
			if($sellerinfo){
				 if($sellerinfo['status'] == 1){
	                $returnData['code'] = 204;
	                $returnData['message'] ="商家申请正在审核，请勿反复提交";
	                $this->response($returnData);
					return false;
	            }else if($sellerinfo['status'] == 2){
	                $returnData['code'] = 204;
	                $returnData['message'] ="商家申请已经批准，请勿再次申请";
	                return $returnData;
	                $this->response($returnData);
					return false;
	            }
			}
			// 上传营业执照
			$path ='seller';
			$other = 'business_licence';
			$dataPath = D('Common')->po_img($business_licence,$path,$user_id,$other);

			$data = D('ApplaySeller')->M_Apply_Seller($user_id,$seller_name,$seller_phone,$province,$city,$remarks,$company_name,$payment_no,$dataPath);

		}
		$this->response($data);
	}
	/**
	 * 绑定银行卡
	 * @token
	 * @hold_name 持卡卡姓名
	 * @hold_idcar 持卡人身份证
	 * @bank_num  银行卡号
	 * @bank_deposit 开户行名称
	 * @phe  预留手机号码
	 * @code 验证码
	 */
	public function Seller_Bind_Bank(){
		$user_id = $this->user_id;
		$hold_name = trim($_POST['hold_name']);
		$hold_idcar = trim($_POST['hold_idcar']);
		$bank_num = trim($_POST['bank_num']);
		$bank_deposit = trim($_POST['bank_deposit']);
		$phe = trim($_POST['phe']);
		$code = trim($_POST['code']);
		if(!$hold_name || !$hold_idcar || !$bank_num || !$bank_deposit || !$phe || !$code){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$data = D('ApplaySeller')->M_Seller_Bind_Bank($user_id,$hold_name,$hold_idcar,$bank_num,$bank_deposit,$phe,$code);
		$this->response($data);
	}
	/**
	 * 商家银行卡列表
	 * token
	 */
	public function Seller_Bank_List(){
		$user_id = $this->user_id;
		$data = D('ApplaySeller')->M_Seller_Bank_List($user_id);
		$this->response($data);
	}
	/**
	 * 商家删除银行卡
	 * token
	 * bank_id 银行卡id
	 */
	public function Seller_Bank_Del(){
		$user_id = $this->user_id;
		$bank_id = trim($_GET['bank_id']);
		if(!$bank_id){
			$data['code'] = 204;
			$data['message'] ="数据不完善，请完善信息";
			$this->response($data);
			return false;
		}
		$data = D('ApplaySeller')->M_Seller_Bank_Del($user_id,$bank_id);
		$this->response($data);
	}
}