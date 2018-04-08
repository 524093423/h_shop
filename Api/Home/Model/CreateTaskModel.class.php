<?php
/**
 * 创建任务以及下发任务接口模型
 * 2017.03.23
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class CreateTaskModel extends Model {
	/**
	 * 创建生成返利订单
	 * @return multitype:number string
	 */
	public function Task_Create(){
		$times 		= date("Y-m-d")." 00:00:00";
		$sql 			= "SELECT
									odid,
									rebatetotal as ze,
									remaintotal as sy,
									user_id AS uid
								FROM
									order_detail
								LEFT JOIN `user` ON order_detail.userid = `user`.user_id
								LEFT JOIN goods_order ON order_detail.goid2 = goods_order.goid
								WHERE
									(
										1 = 1
										AND isretotal = 1
										AND sjtime < '".$times."' 
										AND isfl = 0
									)";
		//echo $sql;die;
		$model = new \Think\Model();
		$result 		= $model->query($sql);
		//print_r($result);die;
		if(!empty($result)){
			$this->HandleRebateTotal($result);
			return array("code"=>200,"message"=>"执行成功");
		}else{
			return array("code"=>204,"message"=>"无返利信息");
		}
	}
	public function HandleRebateTotal($array){
		for ($i=0;$i<count($array);$i++){
			$ze 					= $array[$i]['ze'];
			$fl 					=  sprintf("%.2f",$ze * 0.0005);
			$sy1 					= $array[$i]['sy'];
			$sy 					= $sy1 - $fl;
			$array[$i]['fl'] 	= $fl;
			$array[$i]['sy'] 	= $sy;
		}
		//print_r($array);die;
		for ($i=0;$i<count($array);$i++){
			$this->UpdateDetail($array[$i]['sy'],$array[$i]['odid']);
		}
		$this->CreateDetail($array);
	}
	/**
	 * 处理订单详情表
	 * @param unknown $sy
	 * @param unknown $id
	 */
	public function UpdateDetail($sy,$id){
		$model = new \Think\Model();
		$result 		= $model->execute("UPDATE order_detail set remaintotal='".$sy."'  where odid=$id");
	}
	/**
	 * 生成返利详情
	 * @param unknown $array
	 */
	public function CreateDetail($array){
		$model = new \Think\Model();
		$sql 			= "insert into nmt_tx_detail(odid2,flprice,fftime,isdz,isff,userid1) Values";
		for($i=0;$i<count($array);$i++){
			$str .="('".$array[$i]['odid']."','".$array[$i]['fl']."','".date("Y-m-d H:i:s")."',1,1,'".$array[$i]['uid']."'),";
		}
		$newstr = substr($str,0,strlen($str)-1);
		$sql .=$newstr.";";
		$model->execute($sql);
	}
	/****************************************************任务的下发********************************************************/
	/**
	 * 下发任务操作
	 * @return multitype:number string
	 */
	public function Next_Task(){
		$sql = "SELECT flprice,userid1 as uid,ntdid FROM `nmt_tx_detail` WHERE isdz =1 AND isff =1;";
		$model = new \Think\Model();
		$result 		= $model->query($sql);
		if(!empty($result)){
			for ($i=0;$i<count($result);$i++){
				$this->updateUserAccount($result[$i]['flprice'],$result[$i]['uid']);
				$this->updateDetailFl($result[$i]['ntdid']);
			}
			return array("code"=>200,"message"=>"任务下发成功");
		}else{
			return array("code"=>204,"message"=>"无下发任务");
		}
	}
	/**
	 * 更新用户余额
	 * @param unknown $price
	 * @param unknown $uid
	 */
	public function updateUserAccount($price,$uid){
		$sql = "UPDATE `user` set accountbalance = accountbalance + $price where user_id=$uid";
		$model = new \Think\Model();
		$result 		= $model->execute($sql);
	}
	/**
	 * 更新任务信息
	 * @param unknown $ntdid
	 */
	public function updateDetailFl($ntdid){
		$sql = "UPDATE `nmt_tx_detail` set isdz = 2 where ntdid=$ntdid";
		$model = new \Think\Model();
		$result 		= $model->execute($sql);
	}
}