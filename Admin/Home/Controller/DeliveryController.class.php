<?php
/**
 * 后台管理快递管理
 * 2017.02.06
 * 
 */
namespace Home\Controller;
use Think\Controller;
class DeliveryController extends CheckController {
	/*
	* 添加
	* 2017.2.28
	*/
	public function DeliveryAdd(){
		if(!empty($_POST)){		
            $data['describe'] = I('post.describe', 0);
            $data['price'] = I('post.price', 0);
	 		$return = D("order_delivery")->data($data)->add();
	 	echo $return;
		}else{
			$this->display('add');
		}		
	}
	/*
	* 修改
	* 2017.2.28
	*/
	public function DeliveryView(){
		if(!empty($_POST)){		
            $id = I('post.odyid', 0);
            $data['describe'] = I('post.describe', 0);
            $data['price'] = I('post.price', 0);
   	 		$return = D("order_delivery")->where(array('odyid'=>$id))->data($data)->save();
	 	echo $return;
		}else{
			$id = I('get.odyid',0);
			$info = D('order_delivery')->where(array('odyid'=>$id))->select();
			// var_dump($info);
	 		$this->assign("info",$info[0]);
			$this->display('view');
		}		
	}
	/**
	 * 列表
	 * 2017.03.18
	 */
	public function DeliveryList(){
		//定义每页显示条数
		$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no =($page -1) * 16;
	 	}
	 	$flag = $_GET['flag'];
	 	if($flag == -1){
	 		$where .=" and flag = 0";
	 	}else if($flag ==1){
	 		$where .=" and flag = 1";
	 	}
	 	$type = $_GET['type'];
	 	if(!empty($type)){
	 		$where  .=" and a_order.type =$type";
	 	}
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" ";
	 	}
	 	$return = D("Delivery")->GetDeliveryList($page,$where,$where,$ext,$flag,$type);
	 	// var_dump($return);
	 	$list = $return['list'];
	 	$page = $return['page'];
	 	$count = $return['count'];
	 	$this->assign("ext",$ext);
	 	$this->assign("zt",$flag);
	 	$this->assign("type",$type);
	 	$this->assign("count",$count);
	 	$this->assign("info",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
		$this->display('list');
	}
	/**
	 * 删除
	 * 2017.02.06
	 */
	public function DeliveryDel(){
		// return 1;
		if(!empty($_POST['odyid'])){
	 		$return = M("order_delivery")->where(array('odyid' =>$_POST['odyid']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
	/**
	 * 批量删除订单
	 * 2017.02.06
	 */
	public function DeliveryDelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("order_delivery");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("order_delivery")->where(array('odyid' =>$ids[$i]))->delete();
	 			if($return){
	 				echo $return;
	 			}else{
	 				// 事务回滚
	 				$m->rollback();
	 				$m->commit();
	 			}
			}
		}else{
			echo 0;
		}
	}
}