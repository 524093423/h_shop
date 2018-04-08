<?php
/**
 * 管理Banner图模型
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class BannerModel extends Model {
	/**
	 * 轮播图列表
	 * @param unknown $PageIndex
	 * @param unknown $WhereStr
	 * @param unknown $WhereStr2
	 * @param unknown $reno
	 * @param unknown $rflag
	 * @param unknown $rstate
	 * @param unknown $city
	 * @param unknown $pe
	 */
	public function GetBannerList($PageIndex,$WhereStr="",$WhereStr2="",$reno,$rflag,$rstate,$city,$pe){
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "cityid";
		$Coll="cityid,CASE WHEN ISNULL(fullName) THEN '全国' ELSE fullName END AS cityname";
		$join= "LEFT JOIN h_region ON carousel_photo.cityid=h_region.`code`";
		$group=" GROUP BY cityid";
		$sql = SqlStrImg($TableName="carousel_photo",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="c_id",$OrderType="asc",$join,$joinid,$group);
		$model = M("carousel_photo");
		$count = $model->join($join)->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Banner&a=BannerList";
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
		//echo $sql;
	}
	/**
	 * 删除app轮播图
	 * @param  $id
	 * @return number
	 */
	public function delCarousel_m($id){
		$model = M("carousel_photo");
		$return = $model->where("c_id=$id")->delete();
		if(!empty($return)){
			return 1;
		}else{
			return 0;
		}
	}
	/**
	 * 根据轮播图id返回详细信息
	 * @param unknown $id
	 * @return \Think\mixed
	 * 2017.04.24
	 */
	public function GetCarouselOnly($id){
		$model = M("carousel_photo");
		$result 	= $model->where("c_id=$id")->find();
		return $result;
	}
	public function getCityBanner($cityid,$title){
	    $model =M("carousel_photo");
	    $title.="轮播图";
	    $data  = $model->field("'".$title."' as alt,REPLACE ( c_photo, '/Uploads','./Uploads') as src,c_photo as thumb,c_id as pid")->where("cityid=$cityid")->order("sort asc")->select();
	    return $data;
	}
	/**
	 * 通过id判断或空判断图片是否超出数额
	 * @param  $id
	 * @param  $city
	 */
	/*
	 * public function checkIsCarouselNum($id="",$city=""){
		$model = M("carousel_photo");
		$where = "1=1  ";
		if(!empty($id)){
			$where .=" and c_id=$id";
			$data   = $model->where($where)->find();
			$city   = $data['cityid'];
			$count  = $model->where("cityid=$city")->count();
		}
		if($city == 0){
			$where .="cityid = $city";
			$count   = $model->where($where)->count();
		}
		return $count;
	}
	*/
}