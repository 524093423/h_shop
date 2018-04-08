<?php
/**
 * 盈亏管理
 */
namespace Home\Controller;
use Think\Controller;
class ProfitLossController extends CheckController {
	/**
	 * 日盈亏报表
	 * 2017.03.13
	 */
	public function DayList(){
		$time 	= date("Y-m-d");
		$this->assign("st",$time);
		$this->assign("title","日统计");
		$this->assign("state",1);
		$this->display("day_list");
	}
	/**
	 * 日 月 年盈亏json
	 * 2017.03.13
	 */
	public function getDayList(){
		$userid 	= $_GET['userid'];
		$bid 			= $_GET['bid']?$_GET['bid']:session("nmt_bid");
		$date 		= $_GET['date']?$_GET['date']:date("Y-m-d");
		$state 		= $_GET['state'];
		$pageIndex 	= $_GET['page'];
		$adminid 		= session("nmt_adminid");
		$adminpinyin= session("nmt_pinyin");
		$data 		= D("ProfitLoss")->GetDayPage($userid,$bid,$date,$pageIndex,$pageSize=0,$state,$adminid,$adminpinyin);
		echo json_encode($data);
	}
	/**
	 * 日 月 年盈亏统计
	 * 2017.03.13
	 */
	public function GetDayStatistics(){
		$userid 	= $_GET['userid'];
		$bid 			= $_GET['bid']?$_GET['bid']:session("nmt_bid");
		$date 		= $_GET['date']?$_GET['date']:date("Y-m-d");
		$state 		= $_GET['state'];
		$data 		= D("ProfitLoss")->GetDayStatisticsHtml($userid,$bid,$date,$state);
		echo json_encode($data);
	}
	/**
	 * 月盈亏报表
	 * 2017.03.13
	 */
	public function MonthList(){
		$time 	= date("Y-m-d");
		$this->assign("st",$time);
		$this->assign("title","月统计");
		$this->assign("state",2);
		$this->display("day_list");
	}
	/**
	 * 年盈亏报表
	 * 2017.03.13
	 */
	public function YearList(){
		$time 	= date("Y-m-d");
		$this->assign("st",$time);
		$this->assign("title","年统计");
		$this->assign("state",3);
		$this->display("day_list");
	}
}