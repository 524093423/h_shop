<?php
/**
 * 银行卡模型
 * 2017.03.17
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class BankModel extends Model {
	/**
	 * 删除银行卡
	 * @param  [type] $user_id [description]
	 * @param  [type] $bank_id [description]
	 * @return [type]          [description]
	 */
	public function getBankDelete($user_id,$bank_id)
	{
		if(!$user_id || !$bank_id){
			$returnData['code'] = 204;
			$returnData['message'] ="您没有绑定银行卡,请添加";
			return $returnData;
		}
		$result = M("nmt_user_bank")->where(array('user_id'=>$user_id,'bank_id'=>$bank_id))->delete();
		// var_dump($result);
		if($result){
			$returnData['code'] = 200;
			$returnData['message'] ="删除成功";
			return $returnData;	
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="删除失败";
			return $returnData;	
		}
		return $returnData;
	}
	/**
	 * 银行卡详情信息
	 * @param  [type] $user_id [description]
	 * @param  [type] $bank_id [description]
	 * @return [type]          [description]
	 */
	public function getBankUpdate($user_id,$bank_id,$card_holder,$identity_card,$bank_name,$bank_card,$bank_address)
	{
		if(!$user_id || !$bank_id|| !$card_holder || !$identity_card || !$bank_name || !$bank_card || !$bank_address){
			$returnData['code'] = 204;
			$returnData['message'] ="请完善您的填写信息";
			return $returnData;
		}
		$check_identity_card = $this->isCreditNo($identity_card);
		if(!$check_identity_card){
			$returnData['code'] = 204;
			$returnData['message'] ="身份证号码格式不正确";
			return $returnData;	
		}
		$check_bank_card = $this->isBankCard($bank_card);
		if(!$check_bank_card){
			$returnData['code'] = 204;
			$returnData['message'] ="银行卡输入错误，请确认";
			return $returnData;	
		}
		$res = M("nmt_user_bank")->where(array('user_id'=>$user_id,'bank_card'=>$bank_card))->field('bank_id')->select();
		// var_dump($res);
		if($res[0]['bank_id'] == $bank_id){
			$data['card_holder'] = $card_holder;
			$data['identity_card'] = $identity_card;
			$data['bank_name'] = $bank_name;
			$data['bank_card'] = $bank_card;
			$data['bank_address'] = $bank_address;
			$data['update_time'] = date('YmdHis');
			$result = M("nmt_user_bank")->where(array('user_id'=>$user_id,'bank_id'=>$bank_id))->data($data)->save();
			if($result){
				$returnData['code'] = 200;
				$returnData['message'] ="银行卡修改成功";
				return $returnData;	
			}else{
				$returnData['code'] = 204;
				$returnData['message'] ="银行卡修改失败";
				return $returnData;	
			}
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="银行卡号已经添加绑定，请勿再次添加";
			return $returnData;	
		}
		
		return $returnData;
	}
	/**
	 * 银行卡详情信息
	 * @param  [type] $user_id [description]
	 * @param  [type] $bank_id [description]
	 * @return [type]          [description]
	 */
	public function getBankDetail($user_id,$bank_id)
	{
		if(!$user_id || !$bank_id){
			$returnData['code'] = 204;
			$returnData['message'] ="您没有绑定银行卡,请添加";
			return $returnData;
		}
		$field = array(
			'us.user_id',
			'ub.*',
			);
		$result = M("user")->alias('us')
				->field($field)
				->where(array('us.user_id'=>$user_id,'ub.bank_id'=>$bank_id))
				->join('nmt_user_bank as ub on us.user_id = ub.user_id')
				->find();
		if($result){
			$returnData['code'] = 200;
			$returnData['message'] ="查看银行卡成功";
			$returnData['info'] = $result;
			return $returnData;	
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="您没有绑定银行卡,请添加";
			return $returnData;	
		}
		return $returnData;
	}
	/**
	 * [getListBank description] 银行卡列表
	 * @param  [type] $user_id [description]
	 * @return [type]          [description]
	 */
	public function getListBank($user_id)
	{
		if(!$user_id){
			$returnData['code'] = 204;
			$returnData['message'] ="您没有绑定银行卡,请添加";
			return $returnData;
		}
		$field = array(
			'us.user_id',
			'ub.*',
			);
		$result = M("user")->alias('us')
				->field($field)
				->where(array('us.user_id'=>$user_id))
				->join('nmt_user_bank as ub on us.user_id = ub.user_id')
				->select();
		//var_dump($result);
		if($result){
			$returnData['code'] = 200;
			$returnData['message'] ="银行卡检索成功";
			$returnData['info'] = $result;
			return $returnData;	
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="您没有绑定银行卡,请添加";
			return $returnData;	
		}
		return $returnData;
	}
	/**
	 * [getAddBank description] 添加银行卡
	 * @param  [type] $user_id       [description]
	 * @param  [type] $card_holder   [description]
	 * @param  [type] $identity_card [description]
	 * @param  [type] $bank_name     [description]
	 * @param  [type] $bank_card     [description]
	 * @param  [type] $bank_address  [description]
	 * @return [type]                [description]
	 */
	public function getAddBank($user_id,$card_holder,$identity_card,$bank_name,$bank_card,$bank_address){
		if(!$user_id || !$card_holder || !$identity_card || !$bank_name || !$bank_card || !$bank_address){
			$returnData['code'] = 204;
			$returnData['message'] ="请完善您的填写信息";
			return $returnData;
		}
		$check_identity_card = $this->isCreditNo($identity_card);
		if(!$check_identity_card){
			$returnData['code'] = 204;
			$returnData['message'] ="身份证号码格式不正确";
			return $returnData;	
		}
		$check_bank_card = $this->isBankCard($bank_card);
		if(!$check_bank_card){
			$returnData['code'] = 204;
			$returnData['message'] ="银行卡输入错误，请确认";
			return $returnData;	
		}
		// 检测是不是已经添加过该银行卡
		$res = M("nmt_user_bank")->where(array('user_id'=>$user_id,'bank_card'=>$bank_card))->find();
		if($res){
			$returnData['code'] = 204;
			$returnData['message'] ="银行卡号已经添加绑定，请勿再次添加";
			return $returnData;	
		}
		$data['user_id'] = $user_id;
		$data['card_holder'] = $card_holder;
		$data['identity_card'] = $identity_card;
		$data['bank_name'] = $bank_name;
		$data['bank_card'] = $bank_card;
		$data['bank_address'] = $bank_address;
		$data['update_time'] = $data['create_time'] = date('YmdHis');
		$result = M("nmt_user_bank")->add($data);
		if($result){
			$returnData['code'] = 200;
			$returnData['message'] ="银行卡添加成功";
			return $returnData;	
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="银行卡添加失败";
			return $returnData;	
		}
		return $returnData;
	}
	/**
	 * 验证银行卡
	 * @param string $value
	 * @param string $match
	 * @return boolean
	 */
	public static function isBankCard($value,$match=' /^(\d{16}|\d{19})$/'){
		$v = trim($value);
		if(empty($v))
			return false;
		return preg_match($match,$v);
	}
	/**
	 * 验证身份证号
	 * @param $vStr
	 * @return bool
	 */
	public function isCreditNo($vStr)
	{
	    $vCity = array(
	        '11','12','13','14','15','21','22',
	        '23','31','32','33','34','35','36',
	        '37','41','42','43','44','45','46',
	        '50','51','52','53','54','61','62',
	        '63','64','65','71','81','82','91'
	    );	 
	    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
	    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
	    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
	    $vLength = strlen($vStr);
	    if ($vLength == 18)
	    {
	        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
	    } else {
	        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
	    }
	    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
	    if ($vLength == 18)
	    {
	        $vSum = 0; 
	        for ($i = 17 ; $i >= 0 ; $i--)
	        {
	            $vSubStr = substr($vStr, 17 - $i, 1);
	            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
	        }
	        if($vSum % 11 != 1) return false;
	    }
	    return true;
	}
}