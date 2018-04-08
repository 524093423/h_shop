<?php
/**
 * 农牧通后台管理店铺模型
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class ShopModel extends Model {
	/**
	 * 更新商户信息
	 * @param 商户id $shopid
	 * @param 商户更新数组 $shop
	 * @param 商户更新图片 $shopImg
	 * @param 商户更新管理员信息 $admin
	 */
	public function RenewShop($shopid,$shop,$shopImg,$admin){
		$model 		= M("business_info");
		$result 			= $model->where("b_id=$shopid")->save($shop);
		if(!is_bool($result)){
			$result1 	= $this->RenewShopCarousel($shopid, $shopImg);
			if(!is_bool($result1)){
				$result2 	= $this->RenewShopAdmin($shopid,$admin);
				if(!is_bool($result1)){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	/**
	 * 更新商户轮播
	 * @param 商户id $shopid
	 * @param 商户更新图片数组 $shopImg
	 */
	public function RenewShopCarousel($shopid,$shopImg){
		$model = M("business_carousel");
		$result 	= $model->field("img")->where("bid=$shopid")->select();
		if(!empty($result)){
			for ($i=0;$i<count($result);$i++){
				$imgurl 	= ".".$result[$i]['img'];
				//unlink($imgurl);
			}
		}
		$delimg 	= $model->where("bid=$shopid")->delete();
		$return =	$this->AdShopCarousel($shopid,$shopImg);
		return $return;
	}
	/**
	 * 更新商户管理员信息
	 * @param 商户id $shopid
	 * @param 管理员更新数据 $admin
	 */
	public function RenewShopAdmin($shopid,$admin){
		$model 			= M("admin");
		$result 			= $model->where("bid=$shopid")->save($admin);
		return returnArrayBool($result);
	}
	/**
	 * 录入商户信息
	 * @param 待录入商户数组 $shop
	 * @param 商户轮播图信息 $imgArray
	 * @param 商户管理员信息 $admin
	 * 2017.04.13 更新加入用户组关系信息
	 */
	public function AdShop($shop,$imgArray,$admin){
		$model 		= M("business_info");
		$model->startTrans();
		$result 			= $model->add($shop);
		if(!empty($result)){
			$carousel 		= $this->AdShopCarousel($result,$imgArray);//录入商户轮播图
			$ShopAdminId 			= $this->AdShopAdmin($result,$admin);//录入商户管理员信息
			//加入用户组
			$group 	= M("nmt_user_group");
			$user_group['adminid']		= $ShopAdminId;
			$user_group['groupid'] 		= 2;
			$groupInfo 	= $group->add($user_group);
			if(!empty($carousel) && !empty($ShopAdminId)){
				$model->commit();
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	/**
	 * 录入轮播图信息
	 * @param 商家id $bid
	 * @param 轮播图数组 $imgArray
	 */
	public function AdShopCarousel($bid,$imgArray){
		$model 		= M("business_carousel");
		$sql 				= "insert into business_carousel(`bid`,`sort`,`img`) Values";
		for($i=0;$i<count($imgArray);$i++){
			$img = $imgArray[$i];
			$img = str_replace("./Uploads", "/Uploads", $img);
			if(!empty($img)){
				$str .="($bid,'".$i."','".$img."'),";
			}
		}
		$newstr  	= substr($str,0,strlen($str)-1);
		$sql  		.=$newstr.";";
		$result 		= $model->execute($sql);
		return returnArrayBool($result);
	}
	/**
	 * 录入管理员信息
	 * @param 商户id $bid
	 * @param 待录入数据 $admin
	 */
	public function AdShopAdmin($bid,$admin){
		$model 			= M("admin");
		$admin['bid']		= $bid;
		$admin['level'] 	= 2;
		$result 			= $model->add($admin);
		return returnArrayBool($result);
	}
	/**
	 * 获取单个商户的信息
	 * @param  $bid
	 * @param  $field
	 * @param  $join
	 * @param  $where
	 * @param  $state
	 * @return Ambigous <boolean, unknown>
	 */
	public function FindShop($bid,$field,$join,$where,$state){
		$model 	= M("business_info");
		if($state ==1){
			$field 	= $field?$field:"b_id";
			$where = $where?$where:"business_info.b_id=$bid";
		}else{
			$field 	= $field?$field:"b_id as shopid,`name` as shopname,bdesc as shopdesc,blogo as shoplogo,phone as shopphe,address as shopadd,search as shopse,usepeople as shoppeople";
			//$join 	= $join?$join:"INNER JOIN admin ON business_info.b_id=admin.bid";
			$where = $where?$where:"b_id=$bid";
		}
		$result 		= $model->field($field)->join($join)->where($where)->find();
		return returnArrayBool($result);
	}
	/**
	 * 通过店铺id获取店铺轮播图信息
	 * @param 店铺id $bid
	 * @param 需要显示的字段 $field
	 * @param 查询条件 $where
	 */
	public function FindShopCarousel($bid,$field="",$where=""){
		$model 		= M("business_carousel");
		$field 			= $field?$field:"bcid,img";
		$where 		= $where?$where:"bid=$bid";
		$result 			= $model->field($field)->where($where)->select();
		return returnArrayBool($result);
	}
	/**
	 * 店铺列表
	 * @param 页码 $PageIndex
	 * @param 第一个日期 $date1
	 * @param 第二个日期 $date2
	 * @param 销量 $sales
	 * @param 搜索关键字或者店铺名称 $search
	 * @param 分页大小 $PageSize
	 * @return $data 数组
	 */
	public function ShopList_m($PageIndex,$date1,$date2,$sales,$search,$PageSize,$adminid,$adminpinyin){
		$WhereStr =" and business_info.online = 0";
		$OrderKey 	.= "business_info.b_id asc";
		if($adminpinyin == "partner"){
			$join = "  LEFT JOIN admin ON business_info.b_id=admin.bid 	";
			$WhereStr .="  AND admin.adminpid=$adminid ";
		}
		$WhereStr .=createTime($date1,$date2,"business_info.posttime");
		if(!empty($sales)){
			$OrderKey .=",business_info.sale";
			if($sales==1){
				$OrderType .="asc";
			}else{
				$OrderType .="desc";
			}
		}
		if(!empty($search)){$WhereStr .=" and (INSTR(business_info.`name` ,'".$search."') OR INSTR(business_info.search,'".$search."')) ";}
		$PageSize = $PageSize?$PageSize:15;
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$join = " INNER JOIN admin ON business_info.b_id=admin.bid ";
		$joinid = "b_id";
		$Coll="b_id AS shopid,`name` AS shopname,blogo AS shoplogo,sale AS shopsale,address AS shopadd,phone AS shopphe,posttime AS shoptime,usepeople AS shoppeople";
		$sql = SqlStr2($TableName="business_info",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		$model = M("business_info");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Shop&a=ShopList";
		if(!empty($date1)){$url .="&date1=".$date1;}
		if(!empty($date2)){$url .="&date2=".$date2;}
		if(!empty($sales)){$url .="&sales=".$sales;}
		if(!empty($search)){$url.="&search=".$search;}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 获取当前管理员可查看的店铺信息
	 * @param 管理员id $adminId
	 * @param 管理员拼音 $pinyin
	 * @param 商家id bid
	 */
	public function getShopInfo($adminId,$pinyin,$bid){
		$where = "";
		if($pinyin =="manager"){
			$where = "";
		}elseif ($pinyin =="partner"){
			$where = "adminids = $adminId";
		}elseif ($pinyin == "seller"){
			$where = "b_id = $bid";
		}else{
			return false;
		}
		$data 	= $this->getShop("",$where);
		return $data;
	}
	public function getShop($field="",$where="",$order=""){
		$field 		= $field?$field:"b_id as id,`name` as bname";
		$where 	= $where?$where:"";
		$order 		= $order?$order:"b_id desc";
		$model 	= M("business_info");
		$result 		= $model->field($field)->where($where)->order($order)->select();
		return returnArrayBool($result);
	}
	
}