<?php
/**
 * 商家管理
 * 2017.03.21
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class BankModel extends Model {
	/**
	 * [GetApplySellerList 申请商家列表]
	 * @param [type] $page  [description]
	 * @param [type] $where [description]
	 * @param [type] $where [description]
	 * @param [type] $ext   [description]
	 * @param [type] $flag  [description]
	 * @param [type] $type  [description]
	 */
	public function GetApplySellerList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$status){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "seller_id";
		$join = "join `user` on h_seller_detail.user_id = `user`.user_id";
		$Coll="h_seller_detail.*,`user`.user_name,`user`.user_id,user_phone";
		$sql = SqlStr2($TableName="h_seller_detail",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="h_seller_detail.seller_id",$OrderType="desc",$join,$joinid,"",$WhereStr);
		$model = M("h_seller_detail");
		$count = $model
		->join("inner join user on h_seller_detail.user_id = user.user_id")
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$list	= $model->query($sql);
		foreach($list as $key=>&$value){
			$value['shopid']	=$this->getShopid($value['seller_id']);
		}
		$data['list']  =$list;
		$data['count'] = $count;
		$url = "admin.php?c=Bank&a=ApplySeller&status=$status";
		if(!empty($ext)){
			$url .="&ext=".$ext;
		}
		if(!empty($flag)){
			$url .="&flag=".$flag;
		}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 获取shopid
	 */
	public function getShopid($id){
		$model	= M("business_info");
		$result	= $model->field("b_id as shopid")->where("seller_id={$id}")->find();
		$shopid	= $result['shopid']?$result['shopid']:0;
		return $shopid;
	}
	/**
	 * 用户银行卡管理
	 * @param unknown $PageIndex
	 * @param string $WhereStr
	 * @param string $WhereStr2
	 * @param unknown $ext
	 * @param unknown $flag
	 * @param unknown $type
	 * @return \Think\mixed
	 */
	public function GetBankList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$type){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "bank_id";
		$join = "inner join `user` on h_user_bank.user_id = `user`.user_id inner join `h_seller_detail` on h_seller_detail.user_id = `h_user_bank`.user_id";
		$Coll="h_user_bank.*,`user`.user_name,`user`.user_id,user_phone,h_seller_detail.seller_name,h_seller_detail.seller_phone,h_seller_detail.company_name";
		$sql = SqlStr2($TableName="h_user_bank",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="h_user_bank.update_time",$OrderType="desc",$join,$joinid,"",$WhereStr);
		$model = M("h_user_bank");
		$count = $model
		->join("inner join user on h_user_bank.user_id = user.user_id")
		->join("inner join h_seller_detail on h_seller_detail.user_id = h_user_bank.user_id")
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$data['count'] = $count;
		$url = "admin.php?c=Bank&a=BankList";
		if(!empty($id)){
			$url .="&id=".$id;
		}
		if(!empty($ext)){
			$url .="&ext=".$ext;
		}
		if(!empty($flag)){
			$url .="&flag=".$flag;
		}
		if(!empty($type)){
			$url .="&type=".$type;
		}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
}