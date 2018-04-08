<?php
/**
 * 分类模型
 * 2017.02.08
 */
namespace Home\Model;
use Think\Model;
class ClassifyModel extends Model {
	private $_AdminUrl;
	/**
	 * 获取商品列表页分类信息（无图）
	 * 2017.02.09
	 */
	Public  function GetClassify_M(){
		$model = M("goods_classify");
		$cate  = $this->FindClassify();
		//print_r($cate);
		$data = $this->GetLowerLevel($cate);
		return $data;
	}

	/**
	 * 获取首页分类
	 * 2017.02.11
	 */
	
	public function GetClassify_Home(){
		$field = "gcid,gcname,gimg";
		$where = " where level=1 and state=0";
		$cate  = $this->FindClassify($field,$where);
		$this->_AdminUrl =D("Common")->getUrl();
		$cate = join_carouse_url($cate,$this->_AdminUrl,"gimg");
		//$cate =$this->setClassifyCarousel($cate);//分类加入轮播图
		return $cate;
	}
	/**
	 * 处理分类数组加入分类轮播图
	 * @array 分类数组
	 * 2017.02.11
	 */
	public function setClassifyCarousel($array){
		$model = M("carousel_photo");
		if(empty($array)){
			return $array;
		}
		for($i=0;$i<count($array);$i++){
			$gid = $array[$i]['gcid'];
			$array[$i]['cl'] =  join_carouse_url($model->field("c_photo as cimg")->where("gclid=$gid")->select(),$this->_AdminUrl,"cimg");
		}
		return $array;
	}
	/**
	 * 查询商品分类表
	 * @field  需要显示的字段
	 * @where  查询条件
	 * 2017.02.11
	 */
	public function FindClassify($field="",$where=""){
		$field = $field?$field:"gcid,gcname,pid,level";
		$where = $where?$where:"";
		$sql = "select  ".$field."  from goods_classify  ".$where."  order by gcsort asc";
		$model = M("goods_classify");
		$cate  = $model->query($sql);
		return $cate;
	}
	/**
	 *通过pid和level查询下级分类信息
	 *@cate   需要分类的cate
	 *2017.02.09
	 */
	public function GetLowerLevel($cate){
		//print_r($cate);die;
		$tree1 = array();
		$tree2 = array();
		$tree3 = array();
		foreach ($cate as $key=>&$val){	
			if($val['level'] == 1){
				//var_dump($val);
				$tree1[] = $val;
			}else if($val['level'] ==2){
				$tree2[] = $val;
			}else if($val['level'] ==3){
				$tree3[] = $val;
			}
		}
		foreach ($tree2 as $k2=>&$v2){
			foreach ($tree3 as $k3=>&$v3){
				if($v2['gcid'] == $v3['pid']){
					$tree2[$k2]['lower'][] = $v3;
				}
			}
		}
		foreach ($tree1 as $k1=>&$v1){
			foreach ($tree2 as $k2=>&$v2){
				if($v1['gcid'] == $v2['pid']){
					$tree1[$k1]['lower'][] = $v2;
				}
			}
		}
		return $tree1;
	}
	
}