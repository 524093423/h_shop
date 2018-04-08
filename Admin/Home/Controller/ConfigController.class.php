<?php
/*
 * 后台管理系统配置
 */
namespace Home\Controller;
use Think\Controller;
class ConfigController extends CheckController {
	 /**
	  * 配置网站根目录
	  * 2017.03.17
	  */
	 public function WebRoot(){
		$data = D("Config")->GetConfig("`set` = 'adminUrl'");
		$this->assign("rooturl",$data);
	 	$this->display("config_web");
	 }
	 /**
	  * 修改配置信息
	  * 2017.03.17
	  */
	 public function UpdateConfig(){
	 	$set = $_POST['sets'];
	 	$contend = $_POST['contents'];
	 	$data['content'] = $contend;
	 	$return = D("Config")->SetConfig($set,$data);
	 	echo $return;
	 }
	 /**
	  * app客服列表
	  * 2017.04.17
	  */
	 public function CustomerList(){
	 	$page 	= $_GET['page'];
	 	$PageSize 	= "15";
	 	$adminid 		= session("nmt_adminid");
	 	$adminpinyin = session("nmt_pinyin");
	 	$bid 				= session("nmt_bid");
	 	$list 		= D("Config")->CustomerList($page,$PageSize,$adminid,$adminpinyin,$bid);
	 	$this->assign("list",$list['list']);
	 	$this->assign("page",$list['page']);
	 	$this->display("customer_list");
	 }

	/**
	 * 积分设置
	 */
	public function IntlSet(){
		$data = D("Config")->GetConfig("`set` = 'payuseintl'");
		$this->assign("intl",$data);
		$this->display("config_intl");
	}
	 /**
	  * app客服详情或者是添加新的客服
	  * 2017.04.17
	  */
	 public function Customer(){
	 	$id =$_GET['id'];
	 	$info 	= "";
	 	if(!empty($id)){
	 		$info =M("nmt_customer")->where("id=$id")->find();
	 	}
	 	$this->assign("list",$info);
	 	$this->display("customer");
	 }
	 /**
	  * app客服 数据更新或插入
	  * 2017.04.17
	  */
	 public function AjaxCustomer(){
	 	$model = M("nmt_customer");
 		$id 		= $_POST['id'];
 		$groupname 	= session("nmt_pinyin");
 		$str = "";
 		$location = "";
 		if(empty($id)){
 			$data['cdesc'] 	= $_POST['cdesc'];
 			$data['number']= $_POST['number'];
 			$data['ctime'] 	= date("Y-m-d H:i:s");
 			if($groupname =="seller"){
 				$data['bid2'] 		= $_POST['bid']?$_POST['bid']:session("nmt_bid");
 				$data['adminid2'] = $_POST['aid']?$_POST['aid']:session("nmt_adminid");
 			}else{
 				$data['bid2'] 		=$_POST['bid']?$_POST['bid']:0;
 				$data['adminid2'] = $_POST['aid']?$_POST['aid']:session("nmt_adminid");
 			}
 			$str = "客服信息新增";
 			$result = $model->add($data);
 			$location = "location.href='./admin.php?c=Config&a=CustomerList'";
 		}else{
 			$str = "客服信息更新";
 			$location = "";
 			$data['cdesc'] 	= $_POST['cdesc'];
 			$data['number']= $_POST['number'];
 			$data['ctime'] 	= date("Y-m-d H:i:s");
 			$result 	= $model->where("id=$id")->save($data);
 		}
 		if(!is_bool($result)){
 			echo returnJs($str."成功","",$location);
 		}else{
 			echo returnJs($str."失败");
 		}
	 }
	 /**
	  * 删除客服
	  * 2017.04.18
	  */
	 public function delCustomer(){
	 	$id=$_GET['id'];
	 	$model 	= M("nmt_customer");
	 	$result 		= $model 	->where("id=$id")->delete();
	 	
	 }
	 /*
	  * 参数配置
	  */
	 public function SetParam(){
	 	$data = D("Config")->GetAllConfig();
	 	$this->assign("info",$data);
	 	$this->display("config_param");
	 }
	 /*
	  * 票数与对应发布豆数量
	  * 2016.12.26
	  */
	 public function VoteNumAndBeans(){
	 	$model = M("ticket_unit");
	 	$data = $model->select();
	 	$this->assign("list",$data);
	 	$this->display("vote_num_beans");
	 }
	 /*
	  * 更新用户修改的票数与发布豆的比例信息
	  * 2016.12.26
	  */
	 public function UpdateTicketConfig(){
	 	$id = $_POST['id'];
	 	$data['rate'] = $_POST['content'];
	 	$return = M("ticket_unit")->where("id=$id")->save($data);
	 	echo $return;
	 }
	 /**
	  * 控制面板
	  * 修改密码
	  * 2017.03.17
	  */
	  public function EidtUserPwd(){
	  	$this->display("up_user_pwd");
	  }
	  /**
	   * 修改密码操作
	   *2017.03.17
	   */
	   public function EditPassWord(){
	   	 $oldpwd = $_POST['OldPassWord'];
	   	 $newpwd= $_POST['PassWord'];
	   	 $username= $_POST['UserName'];
	   	 $return   = D("Config")->CheckUpdateUserInfo($oldpwd,$newpwd,$username);
	   	 echo json_encode($return);
	   }
	   /**
	    * 配置手机App版本
	    * 2017.03.17
	    */
	   public function AppVersion(){
	   	$model = M("app_config");
	   	$data = $model->field("edition_no,editioninfo,edition_name,updatetime,downloadaddress")->find();
	   	$this->assign("info",$data);
	   	$this->display("app_version");
	   }
	   /**
	    * 修改版本配置
	    * 2017.03.17
	    */
	   public function UpdateAppConfig(){
	   	$data['edition_name'] = $_POST['ename'];
	   	$data['edition_no']      = $_POST['eno'];
	   	$data['editioninfo']      = $_POST['edesc'];
	   	$data['downloadaddress'] = $_POST['eurls'];
	   	$data['updatetime']      = date("Y-m-d");
	   	$model = M("app_config");
	   	$return = $model ->where("a_id = 1")->save($data);
	   	if(!empty($return)){
	   		echo 1;
	   	}else{
	   		echo 0;
	   	}
	   }
	   /**
	    * 支付方式列表
	    * 2017.03.17
	    */
	   public function PayList(){
	   		$data 	= D("Config")->GetPayList();
	   		$this->assign("list",$data);
	   		$this->display("pay_list");
	   }
}