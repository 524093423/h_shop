<?php
/**
 * 后台商品模型
 * 2017.02.10
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class GoodsModel extends Model {
	private $_AdminUrl;
	/**
	 * 商品列表
	 * 2017.05.18
	 */
	public function goodsList($PageIndex,$gcid,$date1,$date2,$sales,$hot,$pte,$search,$PageSize,$adminid,$adminpinyin,$bid){
		$WhereStr =" and goods.state=0";
		if ($adminpinyin=="seller"){
			$WhereStr .="  AND goods.bid=".session("nmt_bid");
		}
		if(!empty($gcid)){$WhereStr .=" and goods_classify.gcid=$gcid  ";}
		if(!empty($date1) && !empty($date2)){
		    $WhereStr .=createTime($date1,$date2,"goods.rktime");
		}
		if(!empty($sales)){
			if($sales==1){
				$OrderType .="goods.sales asc";
			}else{
				$OrderType .="goods.sales desc";
			}
		}else{
			$OrderKey 	.= "goods.gid desc";
		}
		if(!empty($search)){$WhereStr .=" and (INSTR(goods.gname ,'".$search."') OR INSTR(business_info.`name`,'".$search."')) ";}
		$PageSize = $PageSize?$PageSize:15;
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$join .= "	LEFT JOIN business_info ON goods.bid=business_info.b_id 	LEFT JOIN goods_classify ON goods.gclid = goods_classify.gcid ";
		$joinid = "gid";
		$Coll="goods.gid,goods.gname AS gn,goods.gprice as ge,goods.gphoto AS gp,goods_classify.gcname AS gc,goods.sales AS gss,goods.pte AS gpe,goods.sel AS gsl,goods.rktime AS grk,business_info.`name` AS bn";
		$sql = SqlStr2($TableName="goods",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		$model = M("goods");
		$count = $model->join($join)->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Goods&a=GoodsList";
		if(!empty($gcid)){$url .="&gcid=".$gcid;}
		if(!empty($date1)){$url .="&date1=".$date1;}
		if(!empty($date2)){$url .="&date2=".$date2;}
		if(!empty($sales)){$url .="&sales=".$sales;}
		if(!empty($hot)){$url .="&hot=".$hot;}
		if(!empty($pte)){$url .="&pte=".$pte;}
		if(!empty($search)){$url.="&search=".$search;}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 获取单个商品的基本信息
	 * @param unknown $id
	 * @param string $field
	 * @param string $where
	 * @return Ambigous <boolean, unknown>
	 * 更新 2017.05.15
	 */
	public function GoodsInfo($id,$field="",$where=""){
		$OrderKey 	= "goods.gid";
		$OrderType 	= "desc";
		$WhereStr 	= $where?$where:"and goods.gid=$id";
		$join = "LEFT JOIN business_info ON goods.bid=business_info.b_id LEFT JOIN goods_classify ON goods.gclid = goods_classify.gcid ";
		$joinid = "gid";
		$Coll=$field?$field:"goods.bid,gprice,coverimg,goods.gdesc,goods.useintl as usei,goods.givintl as gii,
		    goods.gid,goods.gname AS gn,goods.gclid,goods_number,goods.gphoto AS gp,goods_classify.gcname AS gc,
		    goods.sales AS gss,goods.isintpay,goods.pte AS gpe,goods.sel AS gsl,goods.rktime AS grk,business_info.`name` AS bn,goods.weight as wt,goods.flashs as fs";
		$sql = SqlStr($TableName="goods",$Coll,$WhereStr,$WhereStr,1,1,$OrderKey,$OrderType,$join,$joinid);
		$model 	= M("goods");
		$result 		= $model->query($sql);
		return returnArrayBool($result);
	}
	/**
	 * 更新商品信息
	 * @param array $data
	 * @param array $spe
	 * @param 商品id $id
	 * @param 比率    $jfbl
	 * 更新日期  2017.05.15
	 */
	public function UpGoodsInfo($data,$id,$jfbl,$delimg,$uploadimg){
		$model 		= M("goods");
		$model->startTrans();
		$result1 		= $model->where("gid=$id")->save($data);
		$flag = true;
		//处理规格更新 -----开始
		//$result5 	= $carousel->execute("delete from goods_spec where gdsid=$id");
		//echo $carousel->getLastSql();die;
		//$result3  = $this->Update_s($spe,$id,$jfbl);
		//处理规格更新------结束
		$result2 = true;	
		if(!is_bool($result1)){
			$flag = true;
		}else{
			$flag = false;
			$model->rollback();
		}
		if($flag){
			$model->commit();
			$this->BindCarousel($id,$uploadimg,$delimg);//绑定和删除轮播
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 获取商品的图文描述 并显示视图
	 * @param unknown $gid
	 * @return \Think\mixed
	 */
	public function HtmlDesc($gid){
		$model 	= M("goods");
		$result 		= $model->field("gdesc")->where("gid=$gid")->select();
		return $result;
	}

	/**
	 * @param $result
	 * @param $uploadimg
	 * @param $delimg
	 */
	public function BindCarousel($result,$uploadimg,$delimg){
		if(empty($delimg) && empty($uploadimg)){
			return true;
		}
		//绑定商品轮播
		$goodsModel	= M("goods_photo");
		if(!empty($uploadimg)){
			$uploadimg	= substr($uploadimg,0,strlen($uploadimg)-1);
			$goodsModel	->where("gp_id in ($uploadimg)")->save(array("gid"=>$result));
		}
		if(!empty($delimg)){
			$delimg	= substr($delimg,0,strlen($delimg)-1);
			//删除取消的轮播
			$goodsModel->where("gp_id in ($delimg)")->delete();
		}
	}
	/**
	 * 获取商品的轮播图
	 * @array  待处理数组
	 */
	public function AdCarousel($goodsid){
		$carousel = M("goods_photo");
		$array = join_carouse_url($carousel->field("img_path as img,gp_id as imgid")->where("gid=$goodsid")->select(),$this->_AdminUrl,"img");
		if(empty($array)){
			return array();
		}
		return $array;
	}
}