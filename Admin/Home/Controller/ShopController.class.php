<?php
/**
 * 后台管理
 * 店铺管理
 * 2017.03.06
 */
namespace Home\Controller;
use Think\Controller;
class ShopController extends CheckController {
	/**
	 * 新增店铺
	 * 2017.03.06
	 */
	public function NewShop(){
		$this->display("new_shop");
	}
	/**
	 * 店铺列表
	 * 2017.03.06
	 */
	public function ShopList(){
		$PageIndex 	= $_GET['page']?$_GET['page']:1;
		$PageSize 	= 15;
		$date1 		= $_GET['date1'];
		$date2 		= $_GET['date2'];
		$search 	= $_GET['search'];
		$sale 		= $_GET['sales'];
		if($PageIndex ==1){
			$pageno =1;
		}else{
			$pageno = ($PageSize * $PageIndex) -1;
		}
		$st 		= $date1?$date1:lastmonth_firstday(0,false);
		$et 		= $date2?$date2:month_lastday(0,false);
		$date1 	= $st;
		$date2 	= $et;
		$adminid 			= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$data 	= D("Shop")->ShopList_m($PageIndex,$date1,$date2,$sales,$search,$PageSize,$adminid,$adminpinyin);
		$list 		= $data['list'];
		$page 	= $data['page'];
		$this->assign("st",$st);
		$this->assign("et",$et);
		$this->assign("date1",$date1);
		$this->assign("date2",$date2);
		$this->assign("search",$search);
		$this->assign("sales",$sale);
		$this->assign("no",$pageno);
		$this->assign("shop",$list);
		$this->assign("page",$page);
		$this->display("shop_list");
	}
	/**
	 * 录入店铺信息
	 * 2017.03.06
	 */
	public function AdShop_ajax(){
		//print_r($_POST);die;
		$shop['name'] 		= $_POST['shoptitle'];
		$shop['bdesc'] 		= $_POST['content'];
		$shop['address'] 	= $_POST['shopadd'];
		$img 					= $_POST['logo'];
		$img = str_replace("./Uploads", "/Uploads", $img);
		$shop['blogo'] 		= $img;
		$shop['phone'] 		= $_POST['shoptel'];
		$shop['search'] 		= $_POST['search'];
		$shop['posttime'] 	= date("Y-m-d");
		$admin['admin_name'] 			= $_POST['shopadmin'];
		$admin['admin_password'] 	= hash("sha256",$_POST['shoppwd']);
		$admin['usepeople'] 				= $_POST['shoppeople'];
		$admin['adminphe'] 				= $_POST['shoptel'];
		$admin['adminpid'] 				= session("nmt_adminid");
		$shopid 		= $_POST['shopid'];
		$shoptype 	= $_POST['type'];
		$model 		= D("Shop");
		$imgArray 	= $_POST['shopimgarray'];
		//去除数组中重复数据
		$imgArray 	= array_unique($imgArray);
		$imgArray 	= array_merge($imgArray);
		if($shoptype ==1){//新增
			$shopAd 		= $model->AdShop($shop,$imgArray,$admin);//录入商户信息
			if(!$shopAd){echo returnJs("录入失败","error");exit();}
			$msg = "店铺注册成功";
			$location = "history.go(0)";
		}else{//更新
			$adminInfo['usepeople'] = $_POST['shoppeople'];
			$adminInfo['adminphe'] = $_POST['shoptel'];
			$shopUp 		= $model->RenewShop($shopid,$shop,$imgArray,$adminInfo);
			if(!$shopUp){echo returnJs("更新失败","error");exit();}
			$msg = "店铺信息更新成功";
			$location = "";
		}
		echo returnJs($msg,"",$location);
	}
	/**
	 * 检测店铺名称是否重复
	 * 2017.03.06
	 */
	public function CheckShopName(){
		$shoptitle 	= $_POST['shopname'];
		$shopid 		= $_POST['shopid'];
		if(!empty($shoptitle)){$where = "name='".$shoptitle."'";}
		if(!empty($shopid)){ $where .=" and b_id !=$shopid";}
		$result 			= D("Shop")->FindShop($bid,$field,$join,$where,1);
		//var_dump($result);
		if(!$result){
			echo 1;
		}else{
			echo 2;
		}
	}
	/**
	 * 店铺详情
	 * 2017.03.07
	 */
	public function ShopDetail(){
		$adminId = session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$id 			= $_GET['id']?$_GET['id']:session("nmt_bid");
		if(empty($id)){
			echo "<script>alert('无店铺信息');</script>";
			exit();
		}
		if($adminpinyin =="partner"){
			$state 		= D("Common")->CheckAdminHandleBusiness($id,$adminId);
			if(!$state){$this->error("无操作权限");}
		}elseif ($adminpinyin =="seller"){
			$id 	= session("nmt_bid");
		}
		$alb 			= array(array("text"=>"第一张"),array("text"=>"第二张"),array("text"=>"第三张"),array("text"=>"第四张"),array("text"=>"第五张"));
		$model 	= D("Shop");
		$carouselInfo 	= $model->FindShopCarousel($id);//店铺轮播图信息
		$basic 		= $model->FindShop($id,"","","",2);//店铺基本信息
		for ($i=0;$i<count($alb);$i++){
			if(!empty($carouselInfo[$i]['img'])){
				$alb[$i]['img'] = ".".$carouselInfo[$i]['img'];
			}
		}
		$this->assign("alb",$alb);
		$this->assign("basic",$basic);
		$this->display("shop_detail");
	}
	/**
	 * 获取所有店铺信息（json）
	 * 2017.03.15
	 */
	public function GetShopJson(){
		$adminId 		= 1;//session("nmt_adminid");
		$pinyin 		= session("nmt_pinyin");
		$bid 				= 1;//session("");
		$data 		= D("Shop")->getShopInfo($adminId,$pinyin,$bid);
		echo json_encode($data);
	}	
}