<?php
/*
 * 后台管理所有删除数据
 * 2017.03.02
 * 
 */
namespace Home\Controller;
use Think\Controller;
class DelController extends CheckController {
	/**
	 * 删除所有数据
	 * 2017.03.02
	 */
 	 public function delData(){
 	 	$delid 		= $_POST['idstr'];
 	 	$adminId 	= session("nmt_adminid");
 	 	$bid 			= session("nmt_bid");
 	 	$adminpin= session("nmt_pinyin");
 	 	$table 		= $_POST['table'];
 	 	$result 		= D("Del")->delData($delid,$adminId,$bid,$table,$adminpin);
 	 	echo $result;
 	 }
	/**
	 * 删除角色管理
	 * 2017年7月12日
	 */
	public function delRole(){
		$adminpin= session("nmt_pinyin");
		$delid	= I("post.roleid");
		if($adminpin=="manager" && !empty($delid)){
			exit(json_encode(D("Del")->delRole($delid)));
		}else{
			exit(json_encode(array("code"=>204,"message"=>"您没有删除权限")));
		}
	}
}