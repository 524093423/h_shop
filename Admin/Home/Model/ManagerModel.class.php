<?php
/**
 * 管理员模型
 *2017.03.08
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class ManagerModel extends Model {
	/**
	 * 管理员列表
	 * @param 页码 $PageIndex
	 * @param 分类id $groupid
	 * @param 搜索文本 $search
	 * @param 没有显示条数 $PageSize
	 */
	public function ManagerList($PageIndex,$groupid,$search,$PageSize,$adminid=""){
		$OrderKey 	.= "admin.admin_id asc";
		if(!empty($search)){$WhereStr .=" and  INSTR(admin.usepeople ,'".$search."')  ";}
		if(!empty($groupid)){$WhereStr .=" and nmt_admin_group.id=$groupid";}
		$PageSize = $PageSize?$PageSize:15;
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$join = "INNER JOIN nmt_user_group ON admin.admin_id=nmt_user_group.adminid INNER JOIN nmt_admin_group ON nmt_user_group.groupid = nmt_admin_group.id";
		$joinid = "admin_id";
		$Coll="usepeople AS fzr,admin_name AS adminname,adminphe,groupname,pinyin,admin_id";
		$sql = SqlStr2($TableName="admin",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		$model = M("admin");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Manager&a=ManagerList";
		if(!empty($search)){$url.="&search=".$search;}
		if(!empty($groupid)){$url .="&groupid=$groupid";}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 更新管理员信息
	 * @param 管理员数组 $admin
	 * @param 管理员id  $adminId
	 */
	public function RenewManager($admin,$adminId){
		$model =	M("admin");
		$info['usepeople'] 	= $admin['usepeople'];
		$info['adminphe'] 		= $admin['adminphe'];
		$result 						= $model->where("admin_id=$adminId")->save($info);
		return true;
	}
	/**
	 * 新增管理员
	 * @param unknown $user_group
	 * @param unknown $admin
	 */
	public function NewManager($user_group,$admin){
		$model 	= M("admin");
		$adminInfo 	= $model->add($admin);
		if(!empty($adminInfo)){
			$group 	= M("nmt_user_group");
			$user_group['adminid']		= $adminInfo;
			$groupInfo 	= $group->add($user_group);
			if(!empty($groupInfo)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public function FindAdminInfo($id){
		$model = M("Admin");
		$where = " where admin.admin_id=$id";
		$sql = "SELECT
				usepeople AS fzr,
				admin_name AS adminname,
				nmt_user_group.groupid,
				adminphe,
				groupname,
				pinyin,
				admin_id
			FROM
				admin
			INNER JOIN nmt_user_group ON admin.admin_id = nmt_user_group.adminid
			INNER JOIN nmt_admin_group ON nmt_user_group.groupid = nmt_admin_group.id $where ORDER BY admin.admin_id asc";
		$result 	= $model->query($sql);
		return $result;
	}
}