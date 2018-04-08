<?php
/**
 *提现管理
 * 20170523
 */
namespace Home\Controller;
use Think\Controller;
class WithdrawController extends CheckController {
	/**
	 * [WithView 提现处理]
	 */
	public function WithView(){
		if(!empty($_POST)){
			$new_status = $_POST['status'];
			$old_status = $_POST['old_status'];
			if($new_status == $old_status){
				echo -1;exit;
			}
			$time = date('Y-m-d H:i:s');
			$result = M('h_withdraw')->where(array('with_id'=>$_POST['with_id']))->save(array('status'=>$_POST['status'],'withdraw_time'=>$time));
			if($_POST['status'] == 2){
				$userinfo = M('user')->field('user.user_id,user.account,user.freeze_account')->where(array('h_withdraw.with_id'=>$_POST['with_id']))
				->join('h_withdraw on h_withdraw.user_id = user.user_id')->find();
				$user_id = $userinfo['user_id'];
				$account = $userinfo['account']-$_POST['money'];
				$freeze_account = $userinfo['freeze_account']-$_POST['money'];
				M('user')->where(array('user_id'=>$user_id))->save(array('account'=>$account,'freeze_account'=>$freeze_account));
			}else if($_POST['status'] == 3){
				$userinfo = M('user')->field('user.user_id,user.account,user.freeze_account')->where(array('h_withdraw.with_id'=>$_POST['with_id']))
				->join('h_withdraw on h_withdraw.user_id = user.user_id')->find();
				$user_id = $userinfo['user_id'];
				$freeze_account = $userinfo['freeze_account']-$_POST['money'];
				M('user')->where(array('user_id'=>$user_id))->save(array('freeze_account'=>$freeze_account));
			}
			if($result){
				echo $result;
			}else{
				echo 0;
			}
		}else{
			$with_id = $_GET['with_id'];
			$info =  M("h_withdraw")->field('h_withdraw.*,h_user_bank.number,h_seller_detail.seller_name,h_seller_detail.seller_phone,h_seller_detail.company_name')->where("h_withdraw.with_id = $with_id")
				->join("inner join h_user_bank on h_user_bank.bank_id = h_withdraw.bank_id")
				->join("inner join h_seller_detail on h_seller_detail.seller_id = h_withdraw.seller_id")
				->find();
			$this->assign('info',$info);
			$this->display('with_view');
		}
	}
	/**
	 * [DrawList 商户提现列表]
	 */
	public function DrawList(){
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
	 	$status = $_GET['status'];
	 	if(!empty($status)){
	 		$where  .=" and h_withdraw.status =$status";
	 	}
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" and h_seller_detail.seller_name like \"%$ext%\" or h_seller_detail.company_name like \"%$ext%\"";
	 	}
	 	$return = D("Withdraw")->GetWithList($page,$where,$where,$ext,$flag,$status);
	 	// var_dump($return);
	 	$list = $return['list'];
	 	$page = $return['page'];
	 	$count = $return['count'];
	 	$this->assign("ext",$ext);
	 	$this->assign("zt",$flag);
	 	$this->assign("status",$status);
	 	$this->assign("count",$count);
	 	$this->assign("info",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
		$this->display("draw_list");
	}
	/**
	 * [WithDelAll 批量删除]
	 */
	public function WithDelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("h_withdraw");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("h_withdraw")->where(array('with_id' =>$ids[$i]))->delete();
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
	 * [WithDel 删除]
	 */
	public function WithDel(){
		if(!empty($_POST['with_id'])){
	 		$return = M("h_withdraw")->where(array('user_id' =>$_POST['with_id']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
}