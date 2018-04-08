<?php
/*
 * 后台管理系统消息管理
 */
namespace Home\Controller;
use Think\Controller;
class SysmessController extends CheckController {
	/**
	 * 添加消息
	 */
	public function Add(){
		if(empty($_POST)){
            $act    = $_GET['act']?$_GET['act']:1;
            $this->assign("act",$act);
			$this->display('add');
		}else{
			$add['title'] = $_POST['title'];
			$add['message'] = $_POST['message'];
			$add['type'] =3;
			$add['create_time'] = date('Y-m-d H:i:s');
			if($_POST['act']==2)
            {
                D("Common")->jpushAll($add['message'],6);
            }
			$res = M('h_message')->add($add);
			if($res){
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	/**
	 * 删除
	 * 20170522
	 */
	public function Del(){
		// return 1;
		if(!empty($_POST['m_id'])){
	 		$return = M("h_message")->where(array('m_id' =>$_POST['m_id']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
	/**
	 * 批量删除
	 * 20170522
	 */
	public function DelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("h_message");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("h_message")->where(array('m_id' =>$ids[$i]))->delete();
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
		
 	/**
	 * [Mess_List 消息列表]
	 * 20170516
	 */
	public function Mess_List(){
		//定义每页显示条数
		$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no =($page -1) * 16;
	 	}
	 	$type = $_GET['type'];
	 	if(!empty($type)){
	 		$where  .=" and type = $type";
	 	}
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" and user.user_name like \"%$ext%\" or h_user_bank.card_holder like \"%$ext%\"";
	 	}
	 	$return = D("Sysmess")->GetList($page,$where,$where,$ext,$type);
	 	$list = $return['list'];
	 	$page = $return['page'];
	 	$count = $return['count'];
	 	$this->assign("ext",$ext);
	 	$this->assign("count",$count);
	 	$this->assign("info",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
		$this->display('list');
	}
}