<?php
/**
 * 后台管理商品管理
 * 2017.06.20
 * 
 */
namespace Home\Controller;
use Think\Controller;
class GoodsController extends CheckController {
	/**
	 * 商品列表
	 * 2017.06.20
	 */
	public function GoodsList(){
		//定义每页显示条数
		$adminid 	= session("nmt_adminid");
		$adminpinyin= session("nmt_pinyin");
		$bid 		= session("nmt_bid");
		$PageSize   = 18;
		$page 		= I('get.page')?I('get.page'):1;
		$gcid 		= I('get.gcid');
		$date1 		= I('get.date1');
		$date2 		= I('get.date2');
		$search 	= I('get.search');
		$sale 		= I('get.sales');
		$hot 		= I('get.hot');
		$pte 	    = I('get.pte');
		if($page ==1){
			$pageno =1;
		}else{
			$pageno = $PageSize*($page-1)+1;
		}
		$st 		= $date1?$date1:"";
		$et 		= $date2?$date2:"";
		$date1 	= $st;
		$date2 	= $et;
		$data 	= 	D("Goods")->goodsList($page,$gcid,$date1,$date2,$sale,$hot,$pte,$search,$PageSize,$adminid,$adminpinyin,$bid);
		$list 		= $data['list'];
		//print_r($list);
		$page 	= $data['page'];
		$cate 	= D("Classify")->sortOut($gcid);
		$this->assign("cate",$cate);
		$this->assign("st",$st);
		$this->assign("et",$et);
		$this->assign("date1",$date1);
		$this->assign("date2",$date2);
		$this->assign("search",$search);
		$this->assign("gcid",$gcid);
		$this->assign("hot",$hot);
		$this->assign("sales",$sale);
		$this->assign("pte",$pte);
		$this->assign("no",$pageno);
		$this->assign("goods",$list);
		$this->assign("page",$page);
		$this->display("goods_list");
	}
	/**
	 * 添加商品
	 * 2017.02.06
	 */
	public function AdGoods(){
		$cate 	= D("Classify")->sortOut();
		$this->assign("bid",'0');
		$this->assign("cate",$cate);
		$this->display("addgoods");
	}
	/**
	 * 商品数据录入
	 * 2017.05.18
	 */
	public function  AdGoods_ajax(){
	    //print_r($_POST);die;
		$isup         = I('post.isup');
		//$spe          = I('post.spe');
		$data['gname']= I('post.godstitle');
		$sessionId    = session("nmt_bid");//获取登陆的商家id信息
		if(!empty($sessionId)){
			$data['bid'] = $sessionId;//$sessionId;
		}else{
			$data['bid'] = I("post.bids");
		}
		$data['gphoto']   = substr($_POST['thumb'],1) ? substr($_POST['thumb'],1) : '';
		$data['flashs']   = substr($_POST['flash'],1) ? substr($_POST['flash'],1) : '';
		$data['gclid']    = I('post.godsclass');
		//$data['givintl']  = I('post.intlbl')?I('post.intlbl'):0;
		$uploadimg		   = I("post.uploadimg");//上传的商品id
		$delimg			   = I("post.delimg");//删除的商品轮播id
		$data['givintl']	= 0;
		$data['gdesc']    = I('post.content');
		$data['isintpay'] = 1;
		$data['pte']      = I('post.home');
		$data['sel']      = I('post.sel');
		$data['buserid']= session("seller_buserid");
		$data['goods_number']	= intval($_POST['gnum']);//商品库存
		//$data['useintl']  = I('post.intlpaybl')?I('post.intlpaybl'):0;
		$data['intl_num'] = floor(I('post.gprice'));
		/*if(!empty($data['isintpay'])){修改日期：2017年7月26日
		    $data['intl_num'] = floor(I('post.gprice') * 50);
		}else{
		    $data['intl_num'] = 0;
		}*/
		$data['gprice']   = I('post.gprice');
		$data['weight']   = I('post.weight');
		$data['rktime']   = date("Y-m-d");
		$data['coverimg'] = substr(I('post.cover'),1);
		$jfbl             = $data['useintl'];
		$model    = M("goods");
		//print_r($data);die;
		if($isup != "no"){
			//更新操作
			$return = D("Goods")->UpGoodsInfo($data,$isup,$jfbl,$delimg,$uploadimg);
			if($return){
				$this->ajaxReturn(returnJs("更新成功"),"EVAL");
			}else{
				$this->ajaxReturn(returnJs("更新失败"),"EVAL");
			}
		}
		if(empty($data['bid'])){
			$this->ajaxReturn(returnJs("您没有绑定商家，不可添加商品信息"),"EVAL");
		}
		$model->startTrans();
		$result = $model->add($data);
		if(!empty($result)){
			$flag = true;
			//$result3  = D("Goods")->AddSpec($spe,$result,$jfbl);
			if(is_bool($result)){
			    $model->rollback();
			    $flag = false;
			}else{
				D("Goods")->BindCarousel($result,$uploadimg,$delimg);
			    $model->commit();
			    $flag = true;
			}
		}else{
			$flag = false;
		}
		if($flag){
			$this->ajaxReturn(returnJs("商品添加成功","","history.go(0)"),"EVAL");
		}else{
			$this->ajaxReturn(returnJs("商品添加失败","","history.go(0)"),"EVAL");
		}
	}
	/**
	 * 上传图片接口
	 * 2017.02.06
	 */
	public function UploadImg(){
		//print_r($_POST);die;
		if(!empty($_FILES)){
			$path = $_POST['pah'];
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize = 3145728;// 设置附件上传大小
			$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->rootPath = './Uploads/'; // 设置附件上传根目录
			$upload->savePath="goods/".$path."/";// 设置附件上传（子）目录
			// 上传文件
			$info = $upload->upload();
			if(!$info){
				$src = "";
				$issuccess = 1;
			}else{
				$issuccess = 0;
				$filename = $info['filedata']['savename'];
				$src = "./Uploads/".$info['filedata']['savepath'].$filename;
				$info= "上传成功";
			}
		}else{
			$src = "";
			$issuccess = 1;
			$info="图片不存在";
		}
		$selectId = $_POST['pah'];
		if($path =="carousel"){//uploadSuccessCar
			echo "<script>window.parent.uploadSuccessCar(\"$info\",\"$src\",\"$issuccess\",\"$selectId\");</script>";
		}else{
			echo "<script>window.parent.uploadSuccess(\"$info\",\"$src\",\"$issuccess\",\"$selectId\");</script>";
		}
	}
	/**
	 * 获取分类列表
	 * 2017.02.27
	 */
	public function getClassList(){
		$data 	= D("Unlimited")->getList();
		$this->response($data);
	}
	/**
	 * 商品详情
	 * 2017.03.02
	 */
	public function GoodsDetail(){
		//print_r($_POST);
		$gid 	=  I('get.id');
		$model 	= D("Goods");
		$basic 	= $model->GoodsInfo($gid);
		//print_r($basic);
		$classid = $basic[0]['gclid'];
		$cate 	= D("Classify")->sortOut($classid);
		$img	= D("goods")->AdCarousel($gid);
		$alb 			= array(array("text"=>"第一张"),array("text"=>"第二张"),array("text"=>"第三张"),array("text"=>"第四张"),array("text"=>"第五张"));
		for ($i=0;$i<count($alb);$i++){
			if(!empty($img[$i]['img'])){
				$alb[$i]['img'] = ".".$img[$i]['img'];
				$alb[$i]['id']	 = $img[$i]['imgid'];
			}
		}
		//print_r($alb);
		$this->assign("bid",$basic[0]['bid']);
		$this->assign("basic",$basic);
		$this->assign("cate",$cate);
		$this->assign("alb",$alb);
		$this->display("goods_detail");
	}
}