<?php
/**
 * 系统管理
 * 2017.03.21
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class SystemModel extends Model {
	/**
	 * [GetApplySellerList 申请商家列表]
	 * @param [type] $page  [description]
	 * @param [type] $where [description]
	 * @param [type] $where [description]
	 * @param [type] $ext   [description]
	 * @param [type] $flag  [description]
	 * @param [type] $type  [description]
	 */
	public function GetHelpList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag,$type){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "h_system_mes.sys_id";
		$Coll="h_system_mes.*";
		$sql = SqlStr($TableName="h_system_mes",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="sys_id",$OrderType="desc",$join,$joinid);
		$model = M("h_system_mes");
		$count = $model
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$data['count'] = $count;
		$url = "admin.php?c=System&a=Help_list";
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