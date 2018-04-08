<?php
/**
 *个人数据
 * 20170515
 */
namespace Home\Controller;
use Think\Controller;
class SingleController extends CheckController {
	/**
	 * 获取用户个人消费情况
	 * 2017.03.10
	 */
 	 public function GetUserConsumeInfo(){
 	 	$userid 	= $_GET['userid'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$where 	= "  AND userid=$userid AND ispay=2";
 	 	$data 		= D("Consume")->GetUserConsume($userid,$page,$where);
 	 	echo json_encode($data);
 	 }
 	 /**
 	  * 获取用户个人提现情况
 	  * 2017.03.10
 	  */
 	 public function GetWithraw(){
 	 	$userid  	= $_GET['userid'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$where 	= "  AND userid=$userid";
 	 	$data 		= D("Withdraw")->GetWithdraw($userid,$page,$where);
 	 	echo json_encode($data);
 	 }
 	 /**
 	  * 获取用户返利情况
 	  * 2017.03.10
 	  */
 	 public function GetUserRebate(){
 	 	$userid  	= $_GET['userid'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$where 	= " AND ispay =2 AND (isretotal =1 OR reintl !=0)  AND userid=$userid";
 	 	$data 		= D("Rebate")->GetRebate($userid,$page,$where);
 	 	echo json_encode($data);
 	 }
 	 /**
 	  * 获取用户收货地址
 	  * 2017.04.25
 	  */
 	 public function GetUserAddress(){
 	 	$userid 	= $_GET['userid'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$data 		= D("User")->GetUserAddress($page,$userid);
 	 	echo json_encode($data);
 	 }
 	 /**
 	  * 获取用户评论情况
 	  * 2017.03.10
 	  */
 	 public function GetUserRate(){
 	 	$userid  	= $_GET['userid'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$where 	= " AND userid=$userid";
 	 	$data 		= D("Rate")->GetRate($userid,$page,$where);
 	 	echo json_encode($data);
 	 	return false;
 	 	$str = "";
 	 	$m = fopen("./qianming.txt","w");
 	 	foreach ($_POST as $key=>&$val){
 	 		$str .=$key.":".$val."\r\n";
 	 	}
 	 	fwrite($m, $str);
 	 	fclose($m);
 	 }
}