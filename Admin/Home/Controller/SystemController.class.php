<?php
/**
 * 系统设置
 * 20170520
 * 
 */
namespace Home\Controller;
use Think\Controller;
class SystemController extends CheckController {
	/**
	 * [about_us 关于我们]
	 * @return [type] [description]
	 */
	public function About_us(){
		if(empty($_POST)){
			$sys_id = $_GET['sys_id'];
			$info = M('h_system_mes')->where(array('sys_id'=>$sys_id))->find();
			$this->assign('info',$info);
			$this->display('about_us');
		}else{
			$sys_id = $_POST['sys_id'];
			$update['title'] = $_POST['title'];
			$update['content'] = $_POST['content'];
			$res = M('h_system_mes')->where(array('sys_id'=>$sys_id))->save($update);
			if($res){
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	public function userProtocol(){
		if(empty($_POST)){
			$info = M('h_system_mes')->field('sys_id,content')->where(array('type'=>3))->find();
			$this->assign('info',$info);
			$this->display('userProtocol');
		}else{
			$sys_id = $_POST['sys_id'];
			$update['content'] = $_POST['content'];
			$res = M('h_system_mes')->where(array('sys_id'=>$sys_id))->save($update);
			if($res){
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	/**
	 * [Help_list 认证帮助列表]
	 */
	public function Help_list(){
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
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" and user.user_name like \"%$ext%\" or h_user_bank.card_holder like \"%$ext%\"";
	 	}
	 	$where .= " and type = 2";
	 	$return = D("System")->GetHelpList($page,$where,$where,$ext,$flag,$type);
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
		$this->display('help_list');
	}
	/**
	 * [Help_view 认证帮助]
	 * @return [type] [description]
	 */
	public function Help_view(){
		if(empty($_POST)){
			$sys_id = $_GET['sys_id'];
			$info = M('h_system_mes')->where(array('sys_id'=>$sys_id))->find();
			$this->assign('info',$info);
			$this->display('help_view');
		}else{
			$sys_id = $_POST['sys_id'];
			$update['title'] = $_POST['title'];
			$update['content'] = $_POST['content'];
			$res = M('h_system_mes')->where(array('sys_id'=>$sys_id))->save($update);
			if($res){
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	/**
	 * 添加认证帮助
	 */
	public function Help_Add(){
		if(empty($_POST)){
			$this->display('help_add');
		}else{
			$add['title'] = $_POST['title'];
			$add['content'] = $_POST['content'];
			$add['type'] =2;
			$res = M('h_system_mes')->add($add);
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
	public function HelpDel(){
		// return 1;
		if(!empty($_POST['sys_id'])){
	 		$return = M("h_system_mes")->where(array('sys_id' =>$_POST['sys_id']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
	/**
	 * 批量删除
	 * 20170522
	 */
	public function HelpDelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("h_system_mes");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("h_system_mes")->where(array('sys_id' =>$ids[$i]))->delete();
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