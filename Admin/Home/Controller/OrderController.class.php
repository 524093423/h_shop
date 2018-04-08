<?php
/**
 *订单管理
 * 2017.06.20
 */
namespace Home\Controller;
use Think\Controller;
class OrderController extends CheckController {
	/**
	 * 订单管理订单列表
	 * 2017.06.20
	 */
	public function OrderList(){
		$ispay 	= $_GET['ispay']?$_GET['ispay']:'0';
		$isfh 	= $_GET['isfh']?$_GET['isfh']:'0';
		$title 	= "";
		if(!empty($ispay)){
			$title = "未付款订单";
		}elseif (!empty($isfh)){
			$title = "代发货订单";
		}else{
			$title = "全部订单";
		}
		$st 		= "";//lastmonth_firstday(0,false);
		$ed 		= "";//month_lastday(0,false);
		$this->assign("title",$title);
		$this->assign("ispay",$ispay);
		$this->assign("isfh",$isfh);
		$this->assign("st",$st);
		$this->assign("ed",$ed);
		$this->display("order_list");
	}
	/**
	 * 获取订单信息json分页
	 * 2017.06.20
	 */
	public function GetOrderList(){
		$PageIndex 	= $_GET['page']?$_GET['page']:1;
		$fhstyle 		= $_GET['fhstyle'];//发货方式
		$ispay 			= $_GET['ispay'];//是否支付
		$isfh 			= $_GET['isfh'];//是否发货
		$date1 			= $_GET['date1']?$_GET['date1']:"";//选择的第一个日期
		$date2 			= $_GET['date2']?$_GET['date2']:"";//选择的第二个日期
		$bid1 				= $_GET['bid'];//选择的店铺id
		$payt 			= $_GET['payt'];//支付方式
		$sessionBid 	= session("nmt_bid");
		if(!empty($sessionBid)){
			$bid 			= session("nmt_bid");
		}
		$orderno 		= $_GET['orderno'];//用户订单号
		$pinyin 		=  session("nmt_pinyin");
		$adminId 		=  session("nmt_adminid");
		if ($pinyin == "seller"){
			$bid 	= $bid;
		}elseif($pinyin =="manager"){
		    $bid    = $bid1;
		}else{
			return false;
		}
		//$where  根据不同的用户组管理员设置不同的条件
		$data 	= D("Order")->GetOrderPage($userid=0,$bid,$date1,$date2,$PageIndex,$PageSize=0,"",$fhstyle,$ispay,$isfh,$orderno,$payt);
		$this->ajaxReturn($data,"JSON");
	}
	/**
	 * 获取退货订单json
	 * 2017.06.20
	 */
	public function GetRefundOrder(){
	    $PageIndex 	= $_GET['page']?$_GET['page']:1;
	    $fhstyle 		= $_GET['fhstyle'];//发货方式
	    $ispay 			= $_GET['ispay'];//是否支付
	    $isfh 			= $_GET['isfh'];//是否发货
	    $date1 			= $_GET['date1']?$_GET['date1']:"";//选择的第一个日期
	    $date2 			= $_GET['date2']?$_GET['date2']:"";//选择的第二个日期
	    $bid1 				= $_GET['bid'];//选择的店铺id
	    $payt 			= $_GET['payt'];//支付方式
	    $sessionBid 	= session("nmt_bid");
	    if(!empty($sessionBid)){
	        $bid 			= session("nmt_bid");
	    }
	    $orderno 		= $_GET['orderno'];//用户订单号
	    $pinyin 		=  session("nmt_pinyin");
	    $adminId 		=  session("nmt_adminid");
	    if ($pinyin =="partner"){
	        $where = "  AND (business_info.adminids = $adminId OR `user`.adminid=$adminId) ";
	    }elseif ($pinyin == "seller"){
	        $bid 	= $bid;
	    }elseif($pinyin =="manager"){
	        $bid    = $bid1;
	    }else{
	        return false;
	    }
		$adminpinyin 	= session("nmt_pinyin");
	    //$where  根据不同的用户组管理员设置不同的条件
	    $data 	= D("Order")->GetRefundOrder($userid=0,$bid,$date1,$date2,$PageIndex,$PageSize=0,$where,$fhstyle,$ispay,$isfh,$orderno,$payt,$adminpinyin);
	    echo json_encode($data);
	}
	/**
	 * 查看订单详细信息
	 * 2017.06.20
	 */
	public function OrderDetail(){
		$goid 		= $_GET['goid'];
		$list 			= D("Order")->GetOrderDetail($goid);
		$html 		= $list['text'];
		$order 		= $list['orderno'];
		$peo 		= $list['peo'];
		$phe 		= $list['phe'];
		$address 	= $list['address'];
		$bname 	= $list['bname'];
		$sjrecord 	= $list['sj'];
		$freid 		= $list['freid'];
		$frename 	= $list['fretext'];
		$this->assign("freid",$freid);
		$this->assign("frename",$frename);
		$this->assign("sj",$sjrecord);
		$this->assign("ispay",$list['ispay']);
		$this->assign("isfh",$list['isfh']);
		$this->assign("goid",$goid);
		$this->assign("tis1",$list['title1']);
		$this->assign("tis2",$list['title2']);
		$this->assign("bname",$bname);
		$this->assign("peo",$peo);
		$this->assign("phe",$phe);
		$this->assign("address",$address);
		$this->assign("orderno",$order);
		$this->assign("html",$html);
		$this->display("order_detail");
	}
	/**
	 * 订单数据导出csv
	 * 2017.06.20
	 */
	public function OrderExport(){
		$fhstyle 		= $_GET['fhstyle'];//发货方式
		$ispay 			= $_GET['ispay'];//是否支付
		$isfh 			= $_GET['isfh'];//是否发货
		$date1 			= $_GET['date1']?$_GET['date1']:lastmonth_firstday(0,false);//选择的第一个日期
		$date2 			= $_GET['date2']?$_GET['date2']:month_lastday(0,false);//选择的第二个日期
		$bid1 				= $_GET['bid'];//选择的店铺id
		$all 				= $_GET['all'];//导出全部时 该参数 不能为空
		$payt 			= $_GET['payt'];//支付方式
		$dostyle 		= $_GET['dostyle'];//导出方式
		$sessionBid 	= session("nmt_bid");
		if(!empty($sessionBid)){
			$bid 			= session("nmt_bid");
		}
		$orderno 		= $_GET['orderno'];//用户订单号
		$pinyin 		=  session("nmt_pinyin");
		$adminId 		=  session("nmt_adminid");
		if ($pinyin =="partner"){
			$where = "  AND (business_info.adminids = $adminId OR `user`.adminid=$adminId) ";
		}elseif ($pinyin == "seller"){
			$bid 	= $bid;
		}elseif($pinyin =="manager"){
		    $bid    = $bid1;
		}else{
			return false;
		}
		//$where  根据不同的用户组管理员设置不同的条件
		$data 	= D("Order")->OrderExport($userid=0,$bid,$date1,$date2,$where,$fhstyle,$ispay,$isfh,$orderno,$all,$payt,$dostyle);
		//echo ;
	}
	/**
	 * 退货订单
	 * 2017.06.20
	 */
	public function RefundOrder(){
	    $ispay 	= 2;
	    $isfh 	= $_GET['isfh']?$_GET['isfh']:'0';
	    $title 	= "";
	    if(!empty($ispay)){
	        $title = "未付款订单";
	    }elseif (!empty($isfh)){
	        $title = "代发货订单";
	    }else{
	        $title = "全部订单";
	    }
	    $st 		= "";//lastmonth_firstday(0,false);
	    $ed 		= "";//month_lastday(0,false);
	    $this->assign("title",$title);
	    $this->assign("ispay",$ispay);
	    $this->assign("isfh",$isfh);
	    $this->assign("st",$st);
	    $this->assign("ed",$ed);
	    $this->display("refund_order");
	}
}