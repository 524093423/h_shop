<?php
/**
 * 系统消息管理
 * 2017.03.21
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class SysmessModel extends Model {
	/**
	 * [GetApplySellerList 申请商家列表]
	 * @param [type] $page  [description]
	 * @param [type] $where [description]
	 * @param [type] $where [description]
	 * @param [type] $ext   [description]
	 * @param [type] $flag  [description]
	 * @param [type] $type  [description]
	 */
	public function GetList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$type){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "m_id";
		$join = "";
		$Coll="m_id,title,message,type,create_time";
		$sql = SqlStr2($TableName="h_message",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="create_time",$OrderType="desc",$join,$joinid,"",$WhereStr);
		$model = M("h_message");
		$count = $model
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$data['count'] = $count;
		$url = "admin.php?c=Sysmess&a=Mess_List&type=$type";
		if(!empty($ext)){
			$url .="&ext=".$ext;
		}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
}