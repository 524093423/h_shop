<?php
/**
 * 嘿谷商城后台分类模型
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class ClassifyModel extends Model {
   /**
    * 获取全部分类
    * 2017.03.01
    */
	public function ClassifyList($field="",$where="",$order=""){
		$field 		= $field?$field:"gcid as id,gcname as name,gcsort,ctime,level,gimg as img,ppid as parentid";
		$where 	=$where?$where:"state = 0";
		$order 		=$order?$order:"gcsort asc";
		$model 	= M("goods_classify");
		$data 		= $model->field($field)->where($where)->order($order)->select();
		return returnArrayBool($data);
	}
	/**
	 * 构造页面显示
	 * @param unknown $cate
	 * @param number $pid
	 * @param number $level
	 * @param string $html
	 * @return Ambigous <multitype:, multitype:unknown >
	 * 2017.03.01
	 */
	public function sortOut($sid=""){
		$cate 	= $this->ClassifyList();
		$tree 	= D("Unlimited");
		$tree->tree($cate);
		$html ='<select name="tree" id="SelectClass" class="select">';
		$html .='<option value="" selected>请选择</option>';
		$str = "<option value=\$id \$selected>\$spacer\$name</option>"; 
		$html .= $tree->get_tree(0,$str,$sid).'</select>'; 
		return $html;
		//return $info;
	}
	/**
	 * 新增分类
	 * @param 分类名称 $classname
	 * @param 父类id $parentid
	 * @param 图片地址 $img
	 */
	public function addClassify($classname,$parentid,$img,$state,$id="",$adminid){
		$data['gimg'] 		= $img;
		$data['gcname'] 	= $classname;
		$model 		= M("goods_classify");
		$data['ctime'] 		= date("Y-m-d");
		$data['adminid'] 				= $adminid;
		if(!empty($parentid) && $id != $parentid){
			$ginfo 	= $this->ClassifyList(""," gcid=$parentid and state=0");
			if(!$ginfo){return false;}
			$plevel 		= $ginfo[0]['level'];
			$data['level'] 	= $plevel+1;
			$data['pid'] 		= $parentid;
			$data['ppid']		= $parentid;
			//echo $state;die;
			if($state ==2){
				$result 		= $model->where("gcid=$id")->save($data);
			}else{
				$result 		= $model->add($data);
			}
			return returnArrayBool($result);
		}else{
			$data['level'] 	= 1;
			$data['ppid'] 	= 0;
			if($state ==2){
				$result 		= $model->where("gcid=$id")->save($data);
			}else{
				$result 		= $model->add($data);
			}
			if(!$result){
				return false;
			}else{
				if($state ==2){
					$gcid = $id;
				}else{
					$gcid = $result;
				}
				$save['pid'] 	= $gcid;
				$result1 		= $model->where("gcid=$gcid")->save($save);
				if(!$result1){
					return false;
				}else{
					return true;
				}
			}
		}
	}
	/**
	 * 检查对应分类的轮播图是否超额
	 * @param 分类Id $gid 如果分类id为空则返回首页轮播图数量
	 */
	public function CheckClassImgCount($gid=""){
		$model 	= M("carousel_photo");
		$where 	= "";
		if(!empty($gid)){
			$where .=" gclid = $gid ";
		}else{
			$where .="ISNULL(gclid)";
		}
		$number 	= $model->where($where)->count();
		return $number;
	}
	public function typeSortS($strval,$strid){
		$strvalArray	= explode("-",$strval);
		$stridArray	= explode("-",$strid);
		if(!empty($strvalArray) || !empty($stridArray)){
			$model 	= M("goods_classify");
			for($i=0;$i<count($stridArray);$i++){
				$result	= $model->where("gcid={$stridArray[$i]}")->save(["gcsort"=>$strvalArray[$i]]);
			}
		}
		return reTurnJSONArray("200","保存成功");
	}
}