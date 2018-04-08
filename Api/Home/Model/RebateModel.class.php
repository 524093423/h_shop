<?php
/**
 * 提现模型
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class RebateModel extends Model {
	/**
	 * 获取用户返利记录
	 * @param unknown $userid
	 * @param unknown $page
	 */
	public function GetRebate($userid,$PageIndex=""){
		$PageSize = "10";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr .= "  AND nmt_tx_detail.userid1 =$userid ";
		$joinid = "ntdid";
		$join = " LEFT JOIN order_detail ON nmt_tx_detail.odid2 = order_detail.odid 	LEFT JOIN goods_order ON order_detail.goid2 = goods_order.goid  LEFT JOIN `user` on nmt_tx_detail.userid1 = `user`.user_id";
		$Coll="flprice,fftime,isdz,orderno,accountbalance as account";
		$sql = SqlStr($TableName="nmt_tx_detail",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="ntdid",$OrderType="desc",$join,$joinid);
		$model = M("nmt_tx_detail");
		$list  = $model->query($sql);
		if(is_bool($list)){
			$returnData['code'] = 204;
			$returnData['message'] = "找不到了";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			$returnData['info']    = $list;
		}
		return $returnData;
	}
	/**
	 * 获取用户提现记录
	 * @param unknown $userid
	 * @param string $PageIndex
	 * @return Ambigous <string, \Think\mixed>
	 */
	public function GetWithdraw($userid,$PageIndex=""){
		$PageSize = "10";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr .= "  AND nmt_user_tx.userid2 =$userid ";
		$joinid = "nutid";
		$join = "LEFT JOIN `user` on nmt_user_tx.userid2 = `user`.user_id";
		$Coll="price as tx,txtime,serialno as orderno,isstate,integral,accountbalance as account";
		$sql = SqlStr($TableName="nmt_user_tx",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="nutid",$OrderType="desc",$join,$joinid);
		$model = M("nmt_user_tx");
		$list  = $model->query($sql);
		if(is_bool($list)){
			$returnData['code'] = 204;
			$returnData['message'] = "找不到了";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			$returnData['info']    = $list;
		}
		return $returnData;
	}
	/**
	 * 获取订单返积分记录
	 * @param unknown $userid
	 * @param string $PageIndex
	 * @return Ambigous <string, \Think\mixed>
	 */
	public function GetIntl($userid,$PageIndex=""){
		$PageSize = "10";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr .= "  AND order_detail.userid = $userid AND reintl != 0 AND goods_order.ispay=2 ";
		$joinid = "odid";
		$join = "LEFT JOIN goods_order ON order_detail.goid2=goods_order.goid";
		$Coll="orderno,DATE_FORMAT(otime,'%Y-%m') AS otime,orderprice,reintl,gname";
		$sql = SqlStr($TableName="order_detail",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="odid",$OrderType="desc",$join,$joinid);
		$model = M("order_detail");
		$list  = $model->query($sql);
		if(is_bool($list)){
			$returnData['code'] = 204;
			$returnData['message'] = "找不到了";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			$returnData['info']    = $list;
		}
		return $returnData;
	}
	/**
	 * 用户提现
	 * 2017.03.21
	 * @param unknown $userid
	 * @param unknown $bankId
	 * @param unknown $price
	 * @param unknown $sjprice
	 */
	public function WithDraw($userid,$bankId,$price,$sjprice,$sxprice){
		$state 		= $this->CheckIsSatisfy($userid, $price);
		if(!$state){
			return array("code"=>"204","message"=>"用户余额不满足体现条件");
		}
		$istotal 	= $this->JSSjTotal($price, $sjprice);
		if(!$istotal){
			return array("code"=>"204","message"=>"提现金额有误");
		}
		$isBank  	= $this->CheckBankAndUser($userid, $bankId);
		if(empty($isBank)){
			return array("code"=>"204","message"=>"银行卡信息有误");
		}
		$state 		= $this->InsetTxRecord($userid, $price, $sjprice, $isBank,$sxprice,$bankId);
		if(!$state){
			$return = array("code"=>"204","message"=>"现在提现人物较多，请稍候重试");
		}else{
			$return = array("code"=>"200","message"=>"提现申请提交成功，提现金额24小时内到账");
		}
		return $return;
	}
	/**
	 * 检测用户是否满足提现条件
	 * @param unknown $userid
	 * @param unknown $price
	 * @return boolean
	 */
	public function CheckIsSatisfy($userid,$price){
		$userinfo 			= D("Register")->getUserBasicInfo($userid);
		//print_r($price);die;
		if(empty($userinfo)){
			return false;
		}
		$useraccount 	= $userinfo['account'];
		$account 			= (int)$useraccount;
		if($account < $price){
			return false;
		}
		$isstate 			= bccomp($useraccount, $price,"2");
		if($isstate >= 0){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 计算实际提现金额
	 * @param unknown $price
	 * @param unknown $sjprice
	 * @return boolean
	 */
	public function JSSjTotal($price,$sjprice){
		$bv 		= "0.15";
		$total 	=  $bv * $price;
		$total 	= sprintf("%.2f",substr(sprintf("%.3f", $total), 0, -2));
		$sjprice2= bcsub($price,$total,"2");
		$isstate 			= bccomp($sjprice, $sjprice2,"2");
		if($isstate <= 0){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 检测用户选择的银行卡是否与当前用户绑定
	 * @param unknown $userid
	 * @param unknown $bankId
	 * @return \Think\mixed
	 */
	public function CheckBankAndUser($userid,$bankId){
		$model 	= M("nmt_user_bank");
		$result 		= $model->where("bank_id=$bankId AND user_id=$userid")->find();
		return $result;
	}
	/**
	 * 插入提现记录并更新用户余额
	 * @param unknown $userid
	 * @param unknown $price
	 * @param unknown $sjprice
	 * @param unknown $bankinfo
	 * @param unknown $sxprice
	 * @param unknown $bankId
	 * @return boolean
	 */
	public function InsetTxRecord($userid,$price,$sjprice,$bankinfo,$sxprice,$bankId){
		$data['serialno'] 	= CreateOrderNo();
		$data['userid2'] 	= $userid;
		$data['price']			= $price;
		$data['txtime'] 		= date("Y-m-d H:i:s");
		$data['isstate'] 		= 1;
		$data['skzh'] 		= $bankinfo['bank_card'];
		$data['sjprice'] 		= $sjprice;
		$data['sxprice'] 		= $sxprice;
		$data['isauth'] 		= 1;
		$data['bankid'] 		= $bankId;
		$data['card_user'] 	= $bankinfo['card_holder'];
		$data['bankname']= $bankinfo['bank_name'];
		$model 	 			= M("nmt_user_tx");
		$result 					= $model->add($data);
		if(!empty($result)){
			$modelu 	= M("user");
			$result1 		= $modelu->execute("UPDATE `user` set accountbalance = accountbalance - $price where user_id=$userid");
			if(!empty($result1)){
				return true;
			}else{
				$result2 	= $model->where("nutid=$result")->delete();
				return false;
			}
		}
	}
}