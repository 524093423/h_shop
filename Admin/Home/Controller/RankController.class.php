<?php
//排名
namespace Home\Controller;
use Think\Controller;
class RankController extends Controller {
	/**
	 * 热门商品排名
	 * 分类商品排名
	 */
	public function HotGoodsRank(){
		$flag		= I("get.goodsorclass");
		$PageSize   = 18;
		$page 		= I('get.page')?I('get.page'):1;
		$gcid 		= I('get.gcid');
		$search 	= I('get.search');
		if($page ==1){
			$pageno =1;
		}else{
			$pageno = $PageSize*($page-1)+1;
		}
		$goodsInfo	= D("Rank")->getGoodsInfo($page,$gcid,"","",$search,$PageSize,$flag);
		$cate 	= D("Classify")->sortOut($gcid);
		$this->assign("goodsorclass",$flag);
		$this->assign("cate",$cate);
		$this->assign("search",$search);
		$this->assign("no",$pageno);
		$this->assign("goods",$goodsInfo['list']);
		$this->assign("page",$goodsInfo['page']);
		$this->display("goods_rank");
	}
	/**
	 * 商品选择窗口
	 */
	public function RankGoodsList(){
		$PageSize   = 18;
		$page 		= I('get.page')?I('get.page'):1;
		$gcid 		= I('get.gcid');
		$search 	= I('get.search');
		$sale 		= I('get.sales');
		$hot 		= I('get.hot');
		$pte 	    = I('get.pte');
		$flag		= I("get.goodsorclass");
		if($page ==1){
			$pageno =1;
		}else{
			$pageno = $PageSize*($page-1)+1;
		}
		$data 	= 	D("Rank")->goodsList($page,$gcid,"","",$sale,$hot,$pte,$search,$PageSize,"","","",$flag);
		$list 		= $data['list'];
		$page 	= $data['page'];
		$cate 	= D("Classify")->sortOut($gcid);
		$this->assign("goodsorclass",$flag);
		$this->assign("cate",$cate);
		$this->assign("search",$search);
		$this->assign("gcid",$gcid);
		$this->assign("sales",$sale);
		$this->assign("no",$pageno);
		$this->assign("goods",$list);
		$this->assign("page",$page);
		$this->display("select_goods");
	}
	/**
	 * 设置商品排名
	 */
	public function SetRank(){
		$gid	= I("post.gid");
		$type	= I("post.type");
		$endtime= I("post.endtime");
		$flag	= I("post.flag");
		$rankid	= I("post.rankid");
		$sort	= i("post.sort");
		$data	= D("Rank")->setRankData($gid,$type,$endtime,$flag,$rankid,$sort);
		echo json_encode($data);
	}
	/**
	 * 热门店铺
	 */
	public function HotShop(){
		$page = $_GET['page']?$_GET['page']:1;
		if($page == 1){
			$no = 1;
		}else{
			$no =($page -1) * 16;
		}
		$flag = $_GET['flag'];
		if($flag == -1){
			$where .=" and flag = 0";
		}else if($flag ==1){
			$where .=" and flag = 1";
		}
		$status = $_GET['status'];
		if(!empty($status)){
			if($status == 1 || $status ==3){
				$where  .=" and h_seller_detail.status = 1 or h_seller_detail.status = 3";
			}else{
				$where  .=" and h_seller_detail.status =$status";
			}

		}
		$ext = $_GET['ext'];
		if(!empty($ext)){
			$where  .=" and user.user_name like \"%$ext%\" or h_user_bank.card_holder like \"%$ext%\"";
		}
		$return = D("Rank")->GetRankSellerList($page,$where,$where,$ext,$flag,$type);
		// var_dump($return);
		$list = $return['list'];
		$page = $return['page'];
		$count = $return['count'];
		$this->assign("ext",$ext);
		$this->assign("zt",$flag);
		$this->assign("type",$type);
		$this->assign("count",$count);
		$this->assign("info",$list);
		$this->assign("page",$page);
		$this->assign("no",$no);
		$this->display("hot_shop");
	}
	/**
	 * 选择店铺
	 */
	public function RankShop(){
		//定义每页显示条数
		$page = $_GET['page']?$_GET['page']:1;
		if($page == 1){
			$no = 1;
		}else{
			$no =($page -1) * 16;
		}
		$flag = $_GET['flag'];
		if($flag == -1){
			$where .=" and flag = 0";
		}else if($flag ==1){
			$where .=" and flag = 1";
		}
		$status = $_GET['status'];
		if(!empty($status)){
			if($status == 1 || $status ==3){
				$where  .=" and h_seller_detail.status = 1 or h_seller_detail.status = 3";
			}else{
				$where  .=" and h_seller_detail.status =$status";
			}

		}
		$ext = $_GET['ext'];
		if(!empty($ext)){
			$where  .=" and user.user_name like \"%$ext%\" or h_user_bank.card_holder like \"%$ext%\"";
		}
		$return = D("Rank")->GetApplySellerList($page,$where,$where,$ext,$flag,$type);
		// var_dump($return);
		$list = $return['list'];
		$page = $return['page'];
		$count = $return['count'];
		$this->assign("ext",$ext);
		$this->assign("zt",$flag);
		$this->assign("type",$type);
		$this->assign("count",$count);
		$this->assign("info",$list);
		$this->assign("page",$page);
		$this->assign("no",$no);
		$this->display("rank_shop");
	}
	/**
	 * 设置店铺排名
	 */
	public function SetShopRank(){
		//print_r($_POST);die;
		$sellerid	= I("post.sellerid");
		$endtime= I("post.endtime");
		$sort	= I("post.sort");
		$rankid	= I("post.rankid");
		$flag	= I("post.flag");
		$data	= D("Rank")->setShopRankData($sellerid,$endtime,$sort,$rankid,$flag);
		echo json_encode($data);
	}
	/*****************************关闭和续时************************/
	/**
	 * 关闭商品排名
	 */
	public function CloseGoodsRank(){
		$goodsid	= I("post.gid");
		$type		= I("post.type");
		$rankid		= I("post.rankid");
		$data		= D("Rank")->CloseGoodsRank($goodsid,$type,$rankid);
		echo json_encode($data);
	}
	/**
	 * 关闭店铺排名
	 */
	public function CloseShopRank(){
		$rankid	= I("post.rankid");
		$sellerid=I("post.sellerid");
		$data	= D("Rank")->CloseShopRank($rankid,$sellerid);
		echo json_encode($data);
	}
}