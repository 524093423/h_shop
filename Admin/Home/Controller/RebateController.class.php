<?php
/**
 * 后台返利管理
 */
namespace Home\Controller;
use Think\Controller;
class RebateController extends CheckController {
	/**
	 * 返利列表
	 */
	public function RebateList(){
		$st 		= lastmonth_firstday(0,false);
		$ed 		= month_lastday(0,false);
		$this->assign("st",$st);
		$this->assign("ed",$ed);
		$this->display("rebate_list");
	}
	/**
	 * 返利详情
	 * 2017-3-19
	 */
	public function RebateDetail(){
		$odid 	= $_GET['id'];
		$st 		= lastmonth_firstday(0,false);
		$ed 		= month_lastday(0,false);
		$this->assign("st",$st);
		$this->assign("ed",$ed);
		$this->assign("odid",$odid);
		$this->display("rebate_detail");
	}
	/**
	 * 返利详情文本
	 * 2017-3-19
	 */
	public function GetRebateDetail(){
		$odid 		= $_GET['odid'];
		$date1 		= $_GET['date1']?$_GET['date1']:lastmonth_firstday(0,false);
		$date2  	= $_GET['date2']?$_GET['date2']:month_lastday(0,false);
		$page 		= $_GET['page'];
		$html 		= D("Rebate")->GetRebateDetail_m($page,$date1,$date2,$odid);
		echo json_encode($html);
	}
	/**
	 * 返利列表文本
	 * 2017-3-19
	 */
	public function GetRebateList(){
		$date1 		= $_GET['date1']?$_GET['date1']:lastmonth_firstday(0,false);
		$date2  	= $_GET['date2']?$_GET['date2']:month_lastday(0,false);
		$search 	= $_GET['search'];
		$page 		= $_GET['page'];
		$html 		= D("Rebate")->GetRebateList($page,$date1,$date2,$search);
		echo json_encode($html);
	}
}