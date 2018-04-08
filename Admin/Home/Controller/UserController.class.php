<?php
/**
 * 用户管理
 */
namespace Home\Controller;
use Think\Controller;
class UserController extends CheckController {
	 /**
	  * 用户列表
	  * 更新不同管理员看到的用户是不同的
	  * 20170513
	  */
	 public function UserList(){
	 	$page = $_GET['page']?$_GET['page']:1;
	 	$where .=" and isdel=0";
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no =($page -1) * 20;
	 	}
	 	$flag = $_GET['flag'];
	 	if($flag == -1){
	 		$where .=" and state = 0";
	 	}else if($flag ==1){
	 		$where .=" and state = 1";
	 	}
	 	$ext = $_GET['ext'];
	 	$adminid 		= session("nmt_adminid");
	 	$adminpinyin= session("nmt_pinyin");
	 	if($adminpinyin == "partner"){
	 		$where .= " and adminid =$adminid ";
	 	}
	 	if(!empty($ext)){
	 		$where  .=" and (INSTR(user_phone,'".$ext."') or INSTR(user_name,'".$ext."')  )";
	 	}
	 	$return = D("User")->GetUserList($page,$where,$where,$ext,$flag);
	 	$list = $return['list'];
	 	//print_r($list);
	 	$page = $return['page'];
	 	$this->assign("ext",$ext);
	 	$this->assign("zt",$flag);
	 	$this->assign("list",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
	 	$this->display("user_list");
	 }
	 /**
	  * 单个用户的所有消费情况
	  * 2017.03.09
	  */
	 public function SingleShopping(){
	 	$userid 	= $_GET['userid'];
	 	$userinfo 	= D("User")->getUserInfo($userid);
	 	$consume 	= D("Consume");
	 	$total 		= $consume->getConsumeTotal($userid);//获取总消费金额
	 	$withdraw 	= D("Withdraw");
	 	$data 		= $withdraw->GetUserWithdrawTotal($userid);//获取提现总金额和实际到账金额
	 	$intlnum 	= D("User")->getIntegralTotal($userid);//获取使用总积分量
	 	$rabate 	= D("Rebate")->GetRabateTotal($userid);//获取返利总金额 返利剩余总金额 返利总积分
	 	$price 		= $data['price'];
	 	$sjprice 	= $data['sjprice'];
	 	$this->assign("flzje",$rabate['flzje']);
	 	$this->assign("flsyje",$rabate['flsyje']);
	 	$this->assign("jf",$rabate['jf']);
	 	$this->assign("intlnum",$intlnum);
	 	$this->assign("price",$price);
	 	$this->assign("sjprice",$sjprice);
	 	$this->assign("total",$total['actulpay']);//实际付款
	 	$this->assign("goodsprice",$total['goodstotal']);//商品总价
	 	$this->assign("freprice",$total['freprice']);//订单总运费
	 	$this->assign("intl",$userinfo['integral']);
	 	$this->assign("uname",$userinfo['user_name']);
	 	$this->assign("uphe",$userinfo['user_phone']);
	 	$this->assign("userid",$userid);
	 	$this->display("user_shopping_list");
	 }
}