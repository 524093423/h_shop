<?php
/**
 * 后台管理商品分类管理
 * 2017.02.06
 * 
 */
namespace Home\Controller;
use Think\Controller;
class ClassifyController extends CheckController {
	/**
	 * 分类列表
	 * 2017.02.06
	 */
	public function ClassifyList(){
		$cateid 	= $_GET['classid'];
		$text 		= $_GET['text'];
		if(!empty($cateid)){
			$where = "(gcid = $cateid or pid=$cateid) and";
		}
		if(!empty($text)){
			$where .=" gcname like '%".$text."%' and";
		}
		$where .=" state=0";
		$cate1 	= D("Classify")->sortOut($cateid);
		$cate2 	= D("Classify")->ClassifyList("",$where);//getClassify();
		$data 	= unlimitedForLevel($cate2);
		$this->assign("cateList",$data);
		$this->assign("cate",$cate1);
		$this->display("classify");
	}
	/**
	 * 添加分类
	 * 2017.02.06
	 */
	public function AdClassify(){
		$id    	= $_GET['id'];
		$title 	= "添加分类";
		$state 	= 1;
		$model 	= D("Classify");
		$gcid 	= 0;
		if(!empty($id)){
			$info 	= $model->ClassifyList(""," gcid=$id  and state=0");
			$title 	= "编辑分类";
			$state 	= 2;
			$this->assign("info",$info);
			$gcid = $id;
			$parentid= $info[0]['parentid'];
		}
		$this->assign("idstr",$gcid);
		$this->assign("state",$state);
		$this->assign("title",$title);
		$cate 	= $model->sortOut($parentid);
		$this->assign("cate",$cate);
		$this->display("addclassify");
	}
	/**
	 * 添加分类
	 * 手机数据
	 * 2017.03.02
	 */
	public function AddClassify(){
		//print_r($_POST);die;
		$state 			= $_POST['isstate'];
		$id 				= $_POST['id'];
		$classname 	= $_POST['classname'];
		$parentid 		= $_POST['parintid'];
		$img 			= $_POST['imgurl'];
		$adminid 		= session("nmt_adminid");
		if(!empty($img)){
			$img = substr($img,1);
		}else{
			$img="";
		}
		$return 		= D("Classify")->addClassify($classname,$parentid,$img,$state,$id,$adminid);
		if($return){
			$data['text'] = "分类添加成功";
			$data['code']="success";
		}else{
			$data['text'] = "分类添加失败";
			$data['code']="error";
		}
		echo json_encode($data);
	}
	public function typeSort(){
		$strval	= $_POST['strval'];
		$strid	= $_POST['strid'];
		$data	= D("Classify")->typeSortS($strval,$strid);
		echo json_encode($data);
	}
	/**
	 * 分类
	 * 2017.03.01
	 */
	public function GetClass(){
		$cate 	= D("Classify")->ClassifyList();//getClassify();
		$data 	= unlimitedForLevel($cate);
		print_r($data);
	}
}