<?php
/**
 * 商家管理
 * 20170516
 * 
 */
namespace Home\Controller;
use Think\Controller;
class BankController extends CheckController {
	/**
	 * [SellerDetail 审核详情]
	 */
	public function SellerDetail(){
		if(empty($_POST)){
			$seller_id = trim($_GET['seller_id']);
			$user_id = trim($_GET['user_id']);
			$result = M('h_seller_detail')
					->field('user.user_id,user.user_name,user.user_phone,h_seller_detail.*')
					->where(array('seller_id'=>$seller_id))
					->join('user on h_seller_detail.user_id = user.user_id')
					->find();
			$this->assign('info',$result);
			// var_dump($result);
			$store = M('business_info')
				->field('name,bdesc,blogo,follow,bakimg,create_time')
				->where(array('seller_id'=>$seller_id))
				->find();
			$this->assign('store',$store);
			$this->display('detail');
		}else{
			$content	= "";//文本内容
			$user_id = trim($_POST['user_id']);
			$seller_id = trim($_POST['seller_id']);
			$status = trim($_POST['status']);
			$nopass = trim($_POST['nopass']);
			if($status == 3){
				$result = M('h_seller_detail')->where(array('seller_id'=>$seller_id))->save(array('status'=>$status,'nopass'=>$nopass));
			}else{
				$result = M('h_seller_detail')->where(array('seller_id'=>$seller_id))->save(array('status'=>$status));
			}
			
			if($status == 1){
				M('user')->where(array('user_id'=>$user_id))->save(array('is_seller'=>0));
			}else if($status == 2){
				$content	= "恭喜您，店铺申请已通过审核，赶快上传产品吧！";
				M('user')->where(array('user_id'=>$user_id))->save(array('is_seller'=> 1));
			}else if($status == 3){
				if(!empty($_POST['nopass'])){
					$content	= "您的商家资料审核未通过,未通过理由:".$_POST['nopass'];
				}else{
					$content	= "您的商家资料审核未通过,请核实后重新提交。";
				}
				M('user')->where(array('user_id'=>$user_id))->save(array('is_seller'=> 0));
			}
			if($result){
				$result1 = M('h_seller_detail')
						->field('user.user_phone')
						->where(array('seller_id'=>$seller_id))
						->join('user on h_seller_detail.user_id = user.user_id')
						->find();
				$mobile	= $result1["user_phone"];//用户手机号
				if(!empty($user_id)){
					D("Common")->PushSystem("商家审核",$content,1,$user_id);
				}
				if(!empty($mobile)){
					D("Common")->sendPhoneNotice($mobile,$content);
				}
				echo $result;
			}else{
				echo 0;
			}
			
		}
		
	}
	/**
	 * [ApplySellerDelAll 批量删除]
	 */
	public function ApplySellerDelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("h_seller_detail");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("h_seller_detail")->where(array('user_id' =>$ids[$i]))->delete();
	 			M('h_seller_detail')->where(array('user_id'=>$ids[$i]))->delete();
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
	 * [ApplySellerDel 申请商家删除]
	 */
	public function ApplySellerDel(){
		if(!empty($_POST['user_id'])){
	 		$return = M("h_seller_detail")->field("business_info.b_id")->join("business_info on h_seller_detail.seller_id = business_info.seller_id")->where(array('user_id' =>$_POST['user_id']))->find();
			if(!empty($return)){
				$bid	= $return["b_id"];
				$goodsModel	= M("goods");
				$goodsModel->where("bid=$bid")->save(array("state"=>1));
			}
			$return = M("h_seller_detail")->where(array('user_id' =>$_POST['user_id']))->delete();
	 		M('h_user_bank')->where(array('user_id'=>$_POST['user_id']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
	/**
	 * [Apply_Seller 申请商家列表]
	 * 20170516
	 */
	public function ApplySeller(){
		//定义每页显示条数
		$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no =($page -1) * 16;
	 	}
	 	$status = $_GET['status'];
		$where	= "";
	 	if(!empty($status)){
	 		if($status == 1 || $status ==3){
	 			$where  .=" and (h_seller_detail.status = 1 or h_seller_detail.status = 3)";
	 		}else{
	 			$where  .=" and h_seller_detail.status =$status";
	 		}
	 	
	 	}
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" and (user.user_name like \"%$ext%\" or h_seller_detail.seller_name like \"%$ext%\" or h_seller_detail.company_name like \"%$ext%\")";
	 	}
	 	$return = D("Bank")->GetApplySellerList($page,$where,$where,$ext,$status);
	 	//var_dump($return);
	 	$list = $return['list'];
	 	$page = $return['page'];
	 	$count = $return['count'];
		$this->assign("statuss",$status);
	 	$this->assign("ext",$ext);
	 	$this->assign("count",$count);
	 	$this->assign("info",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
		$this->display('apply');
	}
	/**
	 * 银行卡列表
	 * 20170516
	 */
	public function BankList(){
		//定义每页显示条数
		$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no =($page -1) * 16;
	 	}
	 	$where = " and user.is_seller = 1 and h_user_bank.status = 1";
	 	$type = $_GET['type'];
	 	if(!empty($type)){
	 		$where  .=" and a_order.type =$type";
	 	}
	 	$ext = $_GET['ext'];
	 	if(!empty($ext)){
	 		$where  .=" and user.user_name like \"%$ext%\" or h_seller_detail.seller_name like \"%$ext%\" or h_seller_detail.company_name like \"%$ext%\"";
	 	}
	 	$return = D("Bank")->GetBankList($page,$where,$where,$ext,$type);
	 	// var_dump($return);
	 	$list = $return['list'];
	 	$page = $return['page'];
	 	$count = $return['count'];
	 	$this->assign("ext",$ext);
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
	public function BankDel(){
		// return 1;
		if(!empty($_POST['bank_id'])){
	 		$return = M("h_user_bank")->where(array('bank_id' =>$_POST['bank_id']))->delete();
	 	echo $return;
		}else{
			echo 0;
		}
	}
	/**
	 * 批量删除
	 * 2017.02.06
	 */
	public function BankDelAll(){
		if(!empty($_POST['idstr'])){
			// 分割字符串
			$ids = explode(',',$_POST['idstr']);
			// var_dump($ids);
			// 开启事务
			$m = M("h_user_bank");
			$m->startTrans();
			for ($i=0; $i < count($ids); $i++) {
	 			$return = M("h_user_bank")->where(array('bank_id' =>$ids[$i]))->delete();
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