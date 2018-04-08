<?php
/**
 *农牧通 评论管理
 * 2017.03.17
 */
namespace Home\Controller;
use Think\Controller;
class RateController extends CheckController {
	public function RateList(){
		$st 		= lastmonth_firstday(0,false);
		$ed 		= month_lastday(0,false);
		$this->assign("st",$st);
		$this->assign("ed",$ed);
		$this->display("rate_list");
	}
 	 /**
 	  * 获取用户评论情况
 	  * 2017.03.17
 	  */
 	 public function GetUserRate(){
 	 	$search 	= $_GET['search'];
 	 	$page 		= $_GET['page']?$_GET['page']:1;
 	 	$date1 		= $_GET['st']?$_GET['st']:lastmonth_firstday(0,false);
 	 	$date2 		= $_GET['ed']?$_GET['ed']:month_lastday(0,false);
 	 	$bid 			= session("nmt_bid")?session("nmt_bid"):"";
 	 	$data 		= D("Rate")->GetRate($page,$date1,$date2,$search,$bid);
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