<?php
/**
 * 农牧通后台管理系统配置模型
 * 2017.03.17
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class ConfigModel extends Model {
   /**
    * 获取配置信息
    * 2017.03.17
    */
	public function GetConfig($where){
		$where =$where?$where:"1=1";
		$model = M("publicsetting");
		$data  = $model->field("content")->where($where)->select();
		//echo $model->getLastSql();die;
		return $data;
	}
	/**
	 * 设置配置项内容
	 * 2017.03.17
	 */
	public function SetConfig($set,$data){
		if(!empty($data)){
			$model = M("publicsetting");
			$return = $model->where("`set`='$set'")->save($data);
			return $return;
		}else{
			return -1;
		}
	}
	/**
	 * 修改用户密码
	 * 2017.03.17
	 */
	 public function CheckUpdateUserInfo($oldpwd,$newpwd,$username){
	 	$model = M("admin");
	 	$type="";
	 	$message="";
	 	$state    = $model->where("admin_name='$username'")->find();
	 	if(!empty($state)){
	 		$oldpwd = hash("sha256", $oldpwd);
	 		if($oldpwd == $state['admin_password']){
	 			$data['admin_password'] = hash("sha256",$newpwd);
	 			$adminid  = $state['admin_id'];
	 			$savestate = $model->where("admin_id=$adminid")->save($data);
	 			if(!is_bool($savestate)){
	 				$type="200";
	 				$message = "修改密码成功，下次登陆生效";
	 			}else{
	 				$type = "204";
	 				$message = "服务器连接超时";
	 			}
	 		}else{
	 			$type="204";
	 			$message = "用户原密码输入错误";
	 		}
	 	}else{
	 		$type ="404";
	 		$message = "用户不存在";
	 	}
	 	$returnData['type'] = $type;
	 	$returnData['message'] = $message;
	 	return $returnData;
	 }
	 /**
	  * 获取所有支付方式
	  * 2017.03.17
	  */
	 public function GetPayList(){
	 	$model 	= M("pay_config");
	 	$result 		= $model->field("p_id as pid,pay_name as pname,img,seller")->select();
	 	if(!empty($result)){
	 		for ($i=0;$i<count($result);$i++){
	 			$result[$i]['total'] 		= D("Common")->GetPayAccount_m($result[$i]['pid']);
	 		}
	 		return $result;
	 	}else{
	 		return false;
	 	}
	 }
	 /**
	  * 客服管理列表
	  * 2017.04.17
	  */
	 public function CustomerList($PageIndex,$PageSize,$adminid,$adminpinyin,$bid){
	 	if($adminpinyin == "partner"){
	 		$WhereStr .=" AND  admin.adminpid=$adminid ";
	 	}elseif ($adminpinyin=="seller"){
	 		$WhereStr .="  AND admin.bid=$bid";
	 	}
	 	$OrderKey 	= "nmt_customer.adminid2";
	 	$OrderType  = "asc"; 
	 	$PageSize = $PageSize?$PageSize:15;
	 	$PageIndex = $PageIndex?$PageIndex:1;
	 	$show = "5";
	 	$join .= "	LEFT JOIN business_info ON nmt_customer.bid2 = business_info.b_id 	LEFT JOIN admin ON business_info.b_id = admin.bid ";
	 	$joinid = "id";
	 	$Coll=" id,cdesc,number,ctime,CASE WHEN ISNULL(`name`) THEN '畜牧通客服' ELSE `name` END AS shopname ";
	 	$sql = SqlStr2($TableName="nmt_customer",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
	 	//echo $sql;die;
	 	$model = M("nmt_customer");
	 	$count = $model->join($join)->where("1=1".$WhereStr)->count();
	 	//echo $model->getLastSql();die;
	 	$pagenum = ceil($count/$PageSize);
	 	$data['list']  = $model->query($sql);
	 	$url = "admin.php?c=Config&a=CustomerList";
	 	$url .="&page=";
	 	$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
	 	return $data;
	 }
}