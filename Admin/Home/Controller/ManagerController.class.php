<?php
/**
 * 管理员管理控制器
 */
namespace Home\Controller;
use Think\Controller;
class ManagerController extends CheckController {
	/**
	 * 管理员列表
	 * 2017.03.08
	 */
	public function ManagerList(){
		$PageIndex 		= $_GET['page']?$_GET['page']:1;
		$groupid 			= $_GET['groupid'];
		$search 			= $_GET['search'];
		$PageSize = 15;
		if($PageIndex ==1){
			$pageno =1;
		}else{
			$pageno = ($PageSize * $PageIndex) -1;
		}
		$adminCate 		= D("Common")->GetAdminType();
		$data 				= D("Manager")->ManagerList($PageIndex,$groupid,$search,$PageSize);
		$list 					= $data['list'];
		$page 				= $data['page'];
		$this->assign("groupid",$groupid);
		$this->assign("search",$search);
		$this->assign("no",$pageno);
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->assign("cate",$adminCate);
		$this->display("manager_list");
	}
	/**
	 * 新增管理员
	 * 2017.03.08
	 */
	public function NewManager(){
		$this->assign("isnew",0);
		$adminCate 		= D("Common")->GetAdminType(1);
		$this->assign("cate",$adminCate);
		$this->display("new_manager");
	}
	/**
	 * 管理员数据更新或者新增
	 * 2017.03.09
	 */
	public function AdManager_ajax(){
		//print_r($_POST);die;
		$adminpinyin 					= session("nmt_pinyin");
		if($adminpinyin != "manager"){
			echo returnJs("你没有权限添加管理员","error",$location);
			exit();
		}
		$user_group['groupid'] 		= $_POST['groupid'];
		$admin['admin_name'] 		= $_POST['shopadmin'];
		$admin['admin_password']= hash("sha256",$_POST['password']);
		$admin['adminphe'] 			= $_POST['shoptel'];
		$admin['bid'] 					= 0;
		$admin['adminpid'] 			= session("nmt_adminid");
		$admin['usepeople'] 			= $_POST['shopfzr'];
		$isrenew 							= $_POST['isrenew'];
		if(!empty($isrenew)){
			$result 								= D("Manager")->RenewManager($admin,$isrenew);
		}else{
			$result 								= D("Manager")->NewManager($user_group,$admin);
		}
		if(!$result){
			if(!empty($isrenew)){
				$text = "管理员信息更新失败";
				$code= "error";
			}else{
				$text = "管理员信息新增失败";
				$code= "error";
			}
		}else{
			if(!empty($isrenew)){
				$text = "管理员信息更新成功";
				$code= "success";
			}else{
				$text = "管理员新增成功";
				$code= "success";
				$location = "history.go(0)";
			}
		}
		echo returnJs($text,$code,$location);
	}
	/**
	 * 管理员详情
	 * 2017.03.09
	 */
	public function ManagerDetail(){
		$id 		= $_GET['id'];
		$data 	= D("Manager")->FindAdminInfo($id);
		$this->assign("cate","");//$adminCate
		$this->assign("isnew",$id);
		$this->assign("data",$data);
		$this->display("new_manager");
	}

	/**
	 * 角色管理页面
	 */
	public function roleManage(){
		$roleList	= M("nmt_admin_group")->where("id!=3 AND id!=2")->select();
		$this->assign("rolelist",$roleList);
		$this->display("roleManage");
	}

	/**
	 * 新增角色数据（更新）
	 */
	public function AddRole(){
		$data['groupname']	= I("get.role");
		$groupid	= I("get.groupid");
		$state		= I("get.flag");
		$data['pinyin']	= "admin";
		$model	= M("nmt_admin_group");
		$where	= "groupname='".$data['groupname']."'";
		if($state==2){
			$where	.=" AND id=$groupid";
			$find	= $model->where("groupname='".$data['groupname']."' AND id != $groupid")->find();
			if(!empty($find)){
				exit(json_encode(array("code"=>204,"message"=>"角色名称重复，请重新输入")));
			}
		}
		$find	= $model->where($where)->find();
		if(!empty($find)){
			if($state==1){
				exit(json_encode(array("code"=>204,"message"=>"角色名称重复，请重新输入")));
			}else{
				exit(json_encode(array("code"=>204,"message"=>"角色名称未做修改，请重新输入")));
			}
		}
		if($state==1){
			$result	= $model->data($data)->add();
			$message	= "角色添加成功";
		}else{
			$result	= $model->data($data)->where("id=$groupid")->save($data);
			$message	= "角色更新成功";
		}
		if(is_bool($result)){
			$returnData	= array("code"=>204,"message"=>"系统繁忙，请稍后重试");
		}else{
			$returnData	= array("code"=>200,"message"=>$message);
		}
		exit(json_encode($returnData));
	}
	/**
	 * 栏目权限设置页面
	 */
	public function SetColumn(){
		$groupid	= I("get.groupid");
		$data	= D("Common")->getSetGroupColumn($groupid);
		//print_r($data);die;
		$this->assign("groupid",$groupid);
		$this->assign("columnlist",$data);
		$this->display("setColumn");
	}

	/**
	 * 角色权限设置
	 */
	public function SetRoleColumn(){
		$groupid	= I("post.groupid");
		$columnid	= I("post.columnid");
		if(!empty($groupid) && !empty($columnid)){
			$data	= D("Common")->SetRoleColumn($groupid,$columnid);
		}else{
			$data	= array("code"=>204,"message"=>"提交数据有误");
		}
		exit(json_encode($data));
	}
}