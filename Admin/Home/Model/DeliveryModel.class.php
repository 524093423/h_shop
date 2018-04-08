<?php
/**
 * 艺术签名--订单管理模型
 * Date: 2017.3.3
 * 
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class DeliveryModel extends Model {
	/*
    * 签名列表
    * 2016.12.20
    */
	public function GetDeliveryList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag,$type){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "odyid";
		$Coll="order_delivery.*";
		$sql = SqlStr($TableName="order_delivery",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="odyid",$OrderType="desc",$join,$joinid);
		$model = M("order_delivery");
		$count = $model
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$data['count'] = $count;
		$url = "admin.php?c=Delivery&a=DeliveryList";
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