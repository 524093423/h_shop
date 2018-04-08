<?php
/**
 * 农牧通删除模型
 * 冯晓磊
 * 2017.03.02
 */
namespace Home\Model;
use Think\Model;
use Think;
header("Content-Type: text/html; charset=UTF-8");
class DelModel extends Model {
	/**
	 * 删除数据
	 * @param 要删除数据的id $delid
	 * @param 进行操作的管理员id $adminId
	 * @param 进行操作的商户id $bid
	 * @param 删除数据主表 $table
	 */
  public function delData($delid,$adminId,$bid,$table){
  	$istable 	= $this->CheckTable($table);
  	if(!$istable){ return "非法操作，请求被拒....";}
  	if($table == "goods"){
  		$returnData 		= $this->delGoods($bid,$delid,$adminId);
  	}elseif ($table == "goods_classify"){
  		$returnData 		= $this->delGoodsClassify($adminId,$delid);
  	}elseif ($table == "carousel_photo"){
  		$returnData 		= $this->delBanner($delid,$adminId,$bid);
  	}elseif($table == "admin"){
  		$returnData 		= $this->delAdmin($delid,$adminId);
  	}elseif ($table == "user"){
  		$returnData 		= $this->delUser($delid,$adminId);
  	}elseif ($table == "goods_rate"){
  		$returnData 		= $this->Multi_Del($delid,$table,'grid');
  	}elseif($table == "nmt_customer"){
  		$returnData 		= $this->Multi_Del($delid,$table,"id");
  	}elseif ($table == "goods_order"){
  		$returnData 		= $this->DelGoodsorder($delid);
  	}
  	return $returnData;
  }
  /**
   * 删除订单
   * 2017.04.26
   */
  public function DelGoodsorder($deid){
  	$model 	= M("goods_order");
  	$result 		= $model->where("goid=$deid")->save(array("isdel"=>"1"));
  	if(!empty($result)){
  		return 1;
  	}else{
  		return "删除失败";
  	}
  }
  /*************************多功能直接删除开始***********************************/
  /**
   * 多功能删除
   * @param unknown $delid
   * @param unknown $table
   */
  public function Multi_Del($delid,$table,$str){
  	$model 	= M($table);
  	$result 		= $model->where("$str=$delid")->delete();
  	if(!empty($result)){
  		return 1;
  	}else{
  		return "删除失败";
  	}
  }
  /*************************多功能直接删除结束**********************************/
  /*************************删除app用户**************************************/
  public function delUser($delid,$adminId){
  		$state = $this->delUserInfo($delid);
  		if(!empty($state)){
  			return 1;
  		}else{
  			return "删除用户失败";
  		}
  }
  public function delUserInfo($delid){
  		$model 	= M("user");
  		$save['isdel'] 		= 1;
  		$result 		= $model->where("user_id=$delid")->save($save);
  		return returnArrayBool($result);
  }
  /*************************删除app用户结束*********************************/
  /*************************删除admin用户信息开始**************************/
  /**
   * 删除管理员信息
   * @param unknown $delid
   * @param unknown $adminId
   * @return string
   */
  public function delAdmin($delid,$adminId){
  		if($delid == $adminId){
  			return "不可对自己进行删除操作.......";
  		}
  		if($adminId ==1){
  			$state 		= $this->delAdminInfo($delid);
  			if(!empty($state)){
  				return 1;
  			}else{
  				return "删除管理员失败";
  			}
  		}
  		$state = $this->CheckAdminIsLevel($delid,$adminId);
  		if(!$state){return "您没有删除该管理员的权限,请联系超级管理员";}
  		$state 		= $this->delAdminInfo($delid);
  		if(!empty($state)){
  			return 1;
  		}else{
  			return "删除管理员失败";
  		}
  }
  /**
   * 检查用户是否有权限删除该管理员
   * @param unknown $delid
   * @param unknown $adminId
   * @return string|boolean
   */
  public function CheckAdminIsLevel($delid,$adminId){
  	$where = "adminpid = $adminId";
  	$result 	= $this->GetAdminTable("",$where);
  	if(empty($result)){return "无效数据";}
  	$flag = false;
  	for($i=0;$i<count($result);$i++){
  		if($result[$i]['id'] == $delid){
  			$flag = true;
  		}
  	}
  	if(!$flag){
  		return false;
  	}
  	return true;
  }
  /**
   * 获取管理员信息
   * @param string $field
   * @param string $where
   * @return Ambigous <boolean, unknown>
   */
  public function GetAdminTable($field="",$where=""){
  	$field 	= $field?$field:"admin_id as id";
  	$model = M("admin");
  	$result 	= $model->field($field)->where($where)->select();
  	return returnArrayBool($result);
  }
  /**
   * 删除管理员
   * @param unknown $adminid
   * @return Ambigous <boolean, unknown>
   */
  public function delAdminInfo($adminid){
  	$model = M("admin");
  	$result 	= $model->where("admin_id=$adminid")->delete();
  	return returnArrayBool($result);
  }
  /************************删除当前用户信息结束*****************************/
  /*************************删除banner轮播信息开始*************************/
  public function delBanner($delid,$adminId,$bid){
  		if($adminId != 1){
  			return "非法操作，请求被拒....";
  		}
  		$model 	= M("carousel_photo");
  		$num 		= $model->count();
  		if($num <2){
  			return "轮播图最少为一张......";
  		}
  		$result 		= $model->where("c_id=$delid")->delete();
  		if(returnArrayBool($result)){
  			return 1;
  		}else{
  			return "轮播删除失败";
  		}
  } 
  /*************************删除banner轮播信息结束*************************/
  /**************************删除商品分类开始***************************/
  /**
   * 删除分类
   * @param unknown $adminId
   * @param unknown $delid
   */
  public function delGoodsClassify($adminId,$delid,$adminpin){
  	if($adminpin !="manager"){
  		$isAuth 	= $this->CheckGoodsClassify($adminId, $delid);
  		if(!$isAuth){return "您没有该权限";}
  	}
  	$isParent 	= $this->CheckClassifyIsParent($delid);
  	if(!$isParent){return "分类下存在子级分类,无法删除";}
  	$isGoods 	= $this->CheckClassifyIsGoods($delid);
  	if(!$isGoods){return "分类下存在商品,请删除商品后再进行操作";}
  	$model 	=  M("goods_classify");
  	$save['state'] 	= 1;
  	$result 		= $model->where("gcid=$delid")->save($save);
  	if(returnArrayBool($result)){
  		return 1;
  	}else{
  		return "商品分类删除失败";
  	}
  }
  /**
   * 检测要删除的分类信息是否符合当前用户操作权限
   * @param 管理员id $adminId
   * @param 要删除的分类id $delid
   */
  public function CheckGoodsClassify($adminId,$delid){
  	return true;
  	$model 	= M("goods_classify");
 	$result 		= $model->field("gcid")->where("gcid=$delid and adminid=$adminId")->find();
 	if(!empty($result)){
 		return false;
 	}else{
 		return true;
 	}
  }
  /**
   * 检测该分类是否存在子分类
   * @param 要删除的分类 $delid
   */
 public function CheckClassifyIsParent($delid){
 	$model 	= M("goods_classify");
 	$result 		= $model->field("gcid")->where("pid=$delid and gcid !=$delid")->find();
 	if(!empty($result)){
 		return false;
 	}else{
 		return true;
 	}
 }
 /**
  * 检测下面是否存在商品
  * @param  要删除的分类 $delid
  */
 public function CheckClassifyIsGoods($delid){
 	$model 	= M("goods");
 	$result 		= $model->field("gid")->where("gclid=$delid and state=0")->find();
 	if(!empty($result)){
 		return false;
 	}else{
 		return true;
 	}
 }
  /**************************删除商品分类结束***************************/
  /**************************删除商品开始******************************/
  /**
   * 删除商品
   * @param 商家id $bid
   * @param 商品id $delid
   */
  public function delGoods($bid,$delid,$adminId){
    if(!is_bool(strpos($delid,","))){
        $delid  = substr($delid, 0,strlen($delid)-1);
    }
  	$isValid 		= $this->checkIdIsValid($delid,$bid);
  	$isAdmin 	= $this->CheckAdminAuth($adminId, $bid);
  	if(!$isValid){
  		if(!$isAdmin){
  			return "非法操作，请求被拒....";
  		}
  	}
    if(empty($bid)){
		$this->newSysMessage($delid);
	}
  	$model 	= M("goods");
  	$save['state'] 		= 1;
  	$result 	= $model->where("gid in ($delid)")->save($save);
  	if(returnArrayBool($result)){
  		return 1;
  	}else{
  		return "删除失败";
  	}
  }

	/**
	 * @param $gidstr
	 * @return bool
	 * 通知店铺用户
	 */
	private function newSysMessage($gidstr){
		if(empty($gidstr)){
			return true;
		}
		$sql	= "SELECT
						h_seller_detail.user_id,
						goods.gname
					FROM
						goods
					INNER JOIN business_info ON goods.bid = business_info.b_id
					INNER JOIN h_seller_detail ON business_info.seller_id = h_seller_detail.seller_id
					WHERE
					goods.gid IN ({$gidstr})";
		$model	= M("goods");
		$result	= $model->query($sql);
		if(!empty($result)){
			for($i=0;$i<count($result);$i++){
				if(!empty($result[$i]['user_id']) && !empty($result[$i]['gname'])){
					$message	= "商品：“".$result[$i]['gname']."”，内容不合法或被要求下架。";
					$userid		= $result[$i]['user_id'];
					D("Common")->PushSystem("商品下架",$message,1,$userid);
				}
			}
		}
	}
  /**
   * 检测商品id是否有效
   * @param 删除的商品 $id
   * @param 商家id $bid
   */
  public function checkIdIsValid($id,$bid){
  	 $model 	= M("goods");
  	 $result 	= $model->field("bid")->where("gid in ($id) and bid=$bid")->find();
  	 $isresult 	= returnArrayBool($result);
  	 if(!$isresult){return false;}else{return true;}
  }
	/*********************删除角色************************/
	public function delRole($id){
		$state	= $this->judgeRoleIsAdmin($id);
		if(!$state){
			return array("code"=>204,"message"=>"角色下存在管理员，请先删除管理员");
		}
		$model	= M("nmt_admin_group");
		$result	= $model->where("id=$id")->delete();
		if(!empty($result)){
			return array("code"=>200,"message"=>"角色删除成功");
		}else{
			return array("code"=>204,"message"=>"角色删除失败，请稍后重试");
		}
	}
	/**
	 * 判断该角色下是否存在管理员
	 * @param $id
	 * @return bool
	 */
	public function judgeRoleIsAdmin($id){
		$model	= M("admin");
		$result	= $model->field("admin_id")->where("admingroupid=$id")->find();
		if(!empty($result)){
			return false;
		}else{
			return true;
		}
	}
/************************删除商品结束***************************/
/*	|
 *	|
 *	|删除的公共检测函数
 *	|
 */
  /**
   * 根据管理员id获取信息
   * @param 管理员id $adminId
   */
  public function GetAdminInfo($adminId){
  	$model 	= M("admin");
  	$result 		= $model->field("bid,admin_id,level")->where("admin_id=$adminId")->find();
  	return returnArrayBool($result);
  }
  /**
   * 检查表名是否存在
   * @param unknown $table
   */
  public function CheckTable($table){
	$rs = M()->query("SHOW TABLES LIKE '".$table."'");
	if(!$rs){
		return false;
	}else{
		return true;
	}
  }
  /**
   * 检测是否为超级管理员操控
   * 以及是否为本商家自身操作
   * @param 管理员id $adminId
   * @param 商家id $bid
   */
  public function CheckAdminAuth($adminId,$bid){
  	$adminInfo 	= $this->GetAdminInfo($adminId);
  	if(!$adminInfo){return false;}
  	$level 		= $adminInfo['level'];
  	$bid2		= $adminInfo['bid'];
  	if($bid == $bid2){
  		return true;
  	}else{
  		if($level == 1){
  			return true;
  		}else{
  			return false;
  		}
  	}
  }
}