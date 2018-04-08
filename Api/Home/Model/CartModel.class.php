<?php
/**
 * 购物车模型
 * 2017年10月7日
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class CartModel extends Model {
	private $_AdminUrl;
	/**
	 * 加入购物车
	 * @array 数据数组
	 * 2017.02.13
	 */
	public function InsertCart($array){
	    $check = $this->CheckGoodsIsDie($array);
	    if($check !=1){
	       return  reTurnJSONArray("204",$check);
	    }
		$model 	= M("goods_cart");
		$seleSql 	= "select gctid,specid from goods_cart where userid=".$array['userid']." AND godsid=".$array['godsid']." ";
		$insql   = "insert into goods_cart(`userid`,`godsid`,`gnum`,`adtime`,`specid`) value(".$array['userid'].",".$array['godsid'].",".$array['gnum'].",'".$array['adtime']."',{$array['specid']}) ";
		$result1 	= $model->query($seleSql);
		if(!empty($result1)){
			$gctid	= 0;
			for($i=0;$i<count($result1);$i++){
				if($result1['specid']==$array['specid']){
					$gctid	= $result1['gctid'];
					break;
				}
			}
			$upsql = "UPDATE goods_cart set  `gnum`=".$array['gnum']." + gnum,`specid`={$array['specid']},`adtime`='".$array['adtime']."'  where gctid={$gctid}";
			if($gctid==0){
				$result 		= $model->execute($insql);
			}else{
				$result 		= $model->execute($upsql);
			}
		}else{
			$result 		= $model->execute($insql);
		}
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "加入购物车失败";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "加入购物车成功";
		}
		return $returnData;
	}
	/**
	 * 检测商品和用户是否存在
	 * @param unknown $array
	 */
	public function CheckGoodsIsDie($array){
	   $userid     = $array['userid'];
	   $gsid       = $array['specid'];
	   $gnum       = intval($array['gnum']);
	   $goodsInfo  = M("goods_spec")->field("gdsid as gid,gstock as gnum")->where("gsid=$gsid")->find();
	   if(!empty($goodsInfo)){
	       $dataNum    = intval($goodsInfo['gnum']);
	       if($gnum > $dataNum){
	           return "库存不足";
	       }else{
	           $userinfo   = M("user")->field("user_id")->where("user_id=$userid")->find();
	           if(!empty($userinfo)){
	              return 1; 
	           }else{
	              return "用户不存在，请先注册";
	           }
	       }
	   }else{
	       return "商品不存在";
	   }
	}
	/**
	 * 编辑购物车信息
	 * @array 数据数组
	 * @gctid  购物车Id
	 * 2017.02.13
	 */
	public function AlterCarts($array){
		$model 	= M("goods_cart");
		//Rewrite_log("cartsdfasdfs",json_encode($array));
		foreach ($array as $key=>&$val) {
		    if(gettype($val) == "object"){
		        foreach ($val as $k=>$v){
		            $data['gnum'] 	= $v->gnum;
		            $cid 					= $v->gctid;
		            $result[]		= $model->where("gctid = $cid")->save($data);
		        }  
		    }else{
		        foreach ($val as $k=>$v){
					if(gettype($v) == "object"){
						$data['gnum'] 	= $v->gnum;
						$cid 					= $v->gctid;
					}else{
						$data['gnum'] 	= $v['gnum'];
						$cid 					= $v['gctid'];
					}
		            $result[]		= $model->where("gctid = $cid")->save($data);
		        }
		    }
			
		}
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "购物车信息更新失败";
			return $returnData;
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "购物车信息更新成功";
			//$returnData['info'] 		= array("gctid"=>$cid,"gnum"=>$array[$i]['gnum'],"spid"=>$array[$i]['spid']);
		}
		
		return $returnData;
	}
	/**
	 * 获取购物车列表
	 * @userid 
	 * @page 
	 * 2017.05.18
	 */
	public function GetCartList($userid,$page,$num="",$idstr=""){
	    $this->_AdminUrl =D("Common")->getUrl();
		$OrderKey = "goods_cart.adtime";
		$OrderType = "desc";
		$PageSize = $num?$num:10;
		$PageIndex = $page?$page:1;
		if(!empty($userid)){
			$WhereStr = " and goods_cart.userid=$userid";
		}
		if(!empty($idstr)){
		    $WhereStr = " and goods_cart.gctid in ($idstr)";
		}
		$join = " Inner join goods_cart on goods.gid=goods_cart.godsid INNER JOIN goods_classify ON goods.gclid=goods_classify.gcid LEFT JOIN business_info ON goods.bid=business_info.b_id";
		$joinid = "gctid";
		$Coll="specid,gctid,gid,'' as spectitle,gid as goodsid,gname,intl_num as jfnum,REPLACE(`blogo`,'/Uploads','".$this->_AdminUrl."/Uploads') AS shopimg,REPLACE(`gphoto`,'/Uploads','".$this->_AdminUrl."/Uploads') AS listimg,goods.intl_num as jfnum,goods.gprice AS price,useintl as intl,isintpay,bid,goods_cart.gnum";
		$sql = SqlStr($TableName="goods",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		//echo $sql;die;
		$goodsModel = M("goods");
		$result=$goodsModel->query($sql);
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "找不到了";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			if(!empty($result)){
				//$result = $this->AdCarousel(join_carouse_url($result,$this->_AdminUrl,"gpo"));
				$result = $this->AdSpec($result);
				$result = $this->HandleCart($result);
			}
			$returnData['info'] = $result?$result:array();
		}
		return $returnData;
	}
	/**
	 * 将购物车信息商品同属一个商家的归于一类
	 * @array  数据数组
	 * 2017.02.13
	 */
	public function HandleCart($array){
		//print_r($array);
		foreach($array as $k=>$v) {
			//$array2[$v['bid']]    = 
			$array2[$v["bid"]]['seller'] = D("Seller")->GetSellerName($v['bid']);
			$array2[$v["bid"]]['sellerid'] = $v['bid'];
			$array2[$v["bid"]]['shopimg']= $v['shopimg'];
			$array2[$v["bid"]][] = $v;
		}
		$array2 = array_merge($array2);
		foreach ($array2 as $key=>&$val){
			foreach ($val as $k=>&$v){
				if(is_array($v)){
					$new[] = $v;
					unset($val[$k]);
				}
			}
		}
		foreach ($array2 as $key1=>&$val1){
			foreach ($new as $key2=>&$val2){
				if($val1['sellerid'] ==$val2['bid']){
					$val1['cart'][] = $val2;
				}
			}
		}
		return $array2;
	}
	/**
	 * 获取商品的轮播图
	 * @array  待处理数组
	 * 2017.02.08
	 */
	public function AdCarousel($array){
		$carousel = M("goods_photo");
		for($i=0;$i<count($array);$i++){
			$gid = $array[$i]['gid'];
			$array[$i]['gc'] = join_carouse_url($carousel->field("img_path as img")->where("gid=$gid")->select(),$this->_AdminUrl,"img");
		}
		return $array;
	}
	/**
	 * 获取商品的库存规格信息
	 * @array  待处理数组
	 * 2017.02.10
	 */
	public  function  AdSpec($array){
		$goodspec = M("goods_spec");
		for($i=0;$i<count($array);$i++){
			$specid = $array[$i]['specid'];
			$specinfo = $goodspec->field("gsid as specid,gsdesc as spectitle,gstock as specnum,gprice as specprice,jfnum")->where("gsid=$specid")->find();
			if(!empty($specinfo)){
				$array[$i]['jfnum']	= $specinfo['jfnum'];
				$array[$i]['spectitle']	= $specinfo['spectitle'];
				$array[$i]['price']			= $specinfo['specprice'];
			}
		}
		$array 	= $this->BindingCustom($array);
		return $array;
	}
	/**
	 * 新增  2017.04.21
	 * 给商品绑定客服信息
	 * 2017.04.21
	 */
	public function BindingCustom($result){
		for ($i=0;$i<count($result);$i++){
			$result[$i]['mobile'] 	= $this->getGoodsCustom($result[$i]['bid']);
		}
		return $result;
	}
	/**
	 * 首先获取该商家是否存在客服帐号
	 * 日如果没有则从商家信息中获取
	 * 2017.04.21
	 */
	public function getGoodsCustom($bid){
		$model 	= M("nmt_customer");
		$result 		= $model->field("number as mobile")->where("bid2=$bid")->find();
		if(!empty($result)){
			return $result['mobile'];
		}else{
			$model = M("admin");
			$result 	= $model->field("adminphe as mobile")->where("bid=$bid")->find();
			return $result['mobile'];
		}
	}
	/**
	 * 删除用户购物车
	 * @gctid 购物车Id
	 * @userid 用户Id
	 * 2017.05.18
	 */
	public function DeleteCart($gctid="",$userid=""){
	    if(empty($gctid) || empty($userid)){
	        $returnData['code'] = 204;
	        $returnData['message'] = "删除失败";
	        return $returnData;
	    }
		$model 	= M("goods_cart");
		$result = $model->where("gctid in ($gctid) AND userid=$userid")->delete();
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "删除失败";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "删除成功";
			
		}
		return $returnData;
	}
}