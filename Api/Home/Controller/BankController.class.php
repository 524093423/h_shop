<?php
/**
 * 商户申请，商家支付宝
 *20170518
 * 
 */
namespace Home\Controller;
use Think\Controller\RestController;
class BankController extends RestController {
	
	/**
	 * 添加银行卡
	 * @user_id 用户id
	 * @card_holder 持卡人姓名
	 * @identity_card 身份证号码
	 * @bank_name 开户行名称
	 * @bank_card 开户行卡号
	 * @bank_address 开户行地址
	 */
	public function BankAdd(){
		$user_id  = $_POST['user_id'];
		$card_holder  = $_POST['card_holder'];
		$identity_card  = $_POST['identity_card'];
		$bank_name  = $_POST['bank_name'];
		$bank_card  = $_POST['bank_card'];
		$bank_address  = $_POST['bank_address'];
		if(!$user_id || !$card_holder || !$identity_card || !$bank_name || !$bank_card || !$bank_address){
			$data['code'] = 204;
			$data['message'] ="请完善您的填写信息";
			$this->response($data);
			return false;
		}else {
			$data = D('Bank')->getAddBank($user_id,$card_holder,$identity_card,$bank_name,$bank_card,$bank_address);
		}
		$this->response($data);
	}
	/**
	 *银行卡列表
	 * @user_id 用户id
	 * @card_holder 持卡人姓名
	 * @identity_card 身份证号码
	 * @bank_name 开户行名称
	 * @bank_card 开户行卡号
	 * @bank_address 开户行地址
	 */
	public function BankList(){
		$user_id  = $_GET['user_id'];
		if(!$user_id){
			$data['code'] = 204;
			$data['message'] ="您没有绑定银行卡,请添加";
			$this->response($data);
			return false;
		}else {
			$data = D('Bank')->getListBank($user_id);
		}
		$this->response($data);
	}
	/**
	 *用户银行卡详情
	 * @user_id 用户id
	 * @bank_id 银行卡列表id
	 */
	public function BankDetail(){
		$user_id  = $_GET['user_id'];
		$bank_id  = $_GET['bank_id'];
		if(!$user_id || !$bank_id){
			$data['code'] = 204;
			$data['message'] ="您没有绑定银行卡,请添加";
			$this->response($data);
			return false;
		}else {
			$data = D('Bank')->getBankDetail($user_id,$bank_id);
		}
		$this->response($data);
	}
	/**
	 *用户银行卡详情
	 * @user_id 用户id
	 * @bank_id 银行卡列表id
	 */
	public function BankUpdate(){
		$user_id  = $_POST['user_id'];
		$bank_id  = $_POST['bank_id'];
		$card_holder  = $_POST['card_holder'];
		$identity_card  = $_POST['identity_card'];
		$bank_name  = $_POST['bank_name'];
		$bank_card  = $_POST['bank_card'];
		$bank_address  = $_POST['bank_address'];
		if(!$user_id || !$bank_id || !$card_holder || !$identity_card || !$bank_name || !$bank_card || !$bank_address){
			$data['code'] = 204;
			$data['message'] ="请完善您的填写信息";
			$this->response($data);
			return false;
		}else {
			$data = D('Bank')->getBankUpdate($user_id,$bank_id,$card_holder,$identity_card,$bank_name,$bank_card,$bank_address);
		}
		$this->response($data);
	}
	/**
	 *用户删除银行卡
	 * @user_id 用户id
	 * @bank_id 银行卡列表id
	 */
	public function BankDelete(){
		$user_id  = $_GET['user_id'];
		$bank_id  = $_GET['bank_id'];
		if(!$user_id || !$bank_id){
			$data['code'] = 204;
			$data['message'] ="您没有绑定银行卡,请添加";
			$this->response($data);
			return false;
		}else {
			$data = D('Bank')->getBankDelete($user_id,$bank_id);
		}
		$this->response($data);
	}
}