<?php 
/**
 * 用户收货地址模型
 * 20170515
 */
namespace Home\Model;
use Think\Model;
Class UserAddressModel extends Model {
	/**
	 *新增收货地址
	 *@user_id  用户id
	 *@consignee_phe 收货人电话
	 *@consignee_name 收货人姓名
	 *@consignee_address 收货人地址
	 *@remarks 详细地址
	 *@flag  是否默认
	 * 2017.02.11
	 */
	public function addUserAddress($user_id,$consignee_phe,$consignee_name,$consignee_address,$remarks,$flag){
		$data['user_id']    = $user_id;
		$data['consignee_phe']  = $consignee_phe;
		$data['consignee_name'] = $consignee_name;
		$data['consignee_address']  = $consignee_address;
		$data['remarks']            = $remarks;
		$vmodel = D("Verification");
    	$vphe = $vmodel::isMobile($consignee_phe);
    	if(!$vphe || strlen($consignee_phe) >11){
    		$returnData['code'] = 204;
			$returnData['message'] ="手机号格式错误";
			return $returnData;
    	}
		$userAddressInfo    = M("user_addressinfo");
		$state  = $userAddressInfo->add($data);
		if(!empty($state)){
			if($flag == 1){
				$type   = $this->wheatherSetAddress($user_id);
				$this->updateAddressFlag($state,$user_id);//更新默认地址
			}
			$savedata['flag'] = $flag;
			$userAddressInfo->where("address_id='$state'")->save($savedata);
			$returnData['code'] =200;
			$returnData['message']  = "新增收货地址成功";
			$returnData['info'] = array("address_id"=>$state,"consignee_phe"=>$consignee_phe,"consignee_name"=>$consignee_name,"consignee_address"=>$consignee_address,"remarks"=>$remarks,"flag"=>$flag);
		}else{
			$returnData['code'] =204;
			$returnData['message']  = "新增收货地址失败";
		}
		return $returnData;
	}
	/**
	 * 根据用户Id返回该用户是否设置默认地址
	 * @user_id 用户id 
	 * 2017.02.11
	 * 返回值：1 否 或者返回对应的地址表主键ID
	 */
	public function wheatherSetAddress($user_id){
		$user_addressinfo   = M("user_addressinfo");
		$state  = $user_addressinfo->field("address_id")->where("flag = 2 and user_id='$user_id'")->select();
		if(!empty($state)){
			$returnstate = $state[0]['address_id'];
		}else{
			$returnstate = 1;
		}
		return $returnstate;
	}
	/**
	 *更新用户的收货地址的默认状态值
	 *@address_id 收货地址id
	 *@userid  用户id
	 * 2017.02.11
	 */
	public function updateAddressFlag($address_id,$user_id){
		$useraddressinfo     = M("user_addressinfo");
		$data['flag']       = 2;
		$useraddressinfo->where("address_id !='$address_id' and user_id = '$user_id'")->save($data);
		//echo $useraddressinfo->getLastSql();die;
	}
	/**
	 * 删除用户收货地址
	 * @address_id  收货地址id
	 * 2017.02.11
	 */
	public function deleteAddress($address_id){
		$address_id = $address_id;
		$addressinfo = M("user_addressinfo");
		$ismr = $addressinfo->where("address_id ='$address_id'")->find();
		$userid = $this->getUserIdFromAddressId($address_id);
		$useraddress = $addressinfo->where("user_id=$userid and address_id != '$address_id'")->find();
		if($ismr['flag'] == 1){
			if(!empty($useraddress)){
				$id= $useraddress['address_id'];
				$datas['flag'] = 1;
				$addressinfo->where("address_id='$id'")->save($datas);
			}
		}
		$state = $addressinfo->where("address_id='$address_id'")->delete();
		if(is_bool($state)){
			$returnData['code'] =204;
			$returnData['message']  = "请求超时";
		}else{
			if(!empty($state)){
				$returnData['code'] =200;
				$returnData['message']  = "删除成功";
			}else{
				$returnData['code'] =203;
				$returnData['message']  = "删除失败";
			}
		}
		return $returnData;
	}
	/**
	 * 收货地址修改
	 *@address_id  收货地址id
	 *@consignee_phe 收货人电话
	 *@consignee_name 收货人姓名
	 *@consignee_address 收货人地址
	 *@remarks 详细地址
	 *@flag  是否默认
	 * 2017.02.11
	 */
	public function updateAddress($user_id,$address_id,$consignee_phe,$consignee_name,$consignee_address,$remarks,$flag){
		//将接收到的参数值写入数组中
		$data['consignee_phe'] = $consignee_phe;
		$data['consignee_name'] = $consignee_name;
		$data['consignee_address'] = $consignee_address;
		$data['remarks'] = $remarks;
		$userAddressInfo = M("user_addressinfo");
		// 查询修改的内容是否与之前一致
		$old = $userAddressInfo->where("address_id = '$address_id'")->find();
		if($old['consignee_phe'] == $consignee_phe && $old['consignee_name'] == $consignee_name && $old['consignee_address'] == $consignee_address && $old['remarks'] == $remarks && $old['flag'] == $flag){
			$returnData['code'] = 204;
			$returnData['message'] ="修改地址信息不能与当前地址一致，请重新填写";
			return $returnData;
		}
		$vmodel = D("Verification");
    	$vphe = $vmodel::isMobile($consignee_phe);
    	if(!$vphe || strlen($consignee_phe) >11){
    		$returnData['code'] = 204;
			$returnData['message'] ="手机号格式错误";
			return $returnData;
    	}
		$userAddressInfo ->startTrans();
		$state  = $userAddressInfo->where("address_id = '$address_id'")->save($data);
		//echo $flag;die;
		if($flag == 1){
			$this->updateAddressFlag($address_id,$user_id);//更新默认地址
			// 其他的为2
			$userAddressInfo->where("address_id != '$address_id' and user_id='$user_id'")->save(array('flag'=>2));
		}
		$savedata['flag'] = $flag;
		$state1 = $userAddressInfo->where("address_id='$address_id'")->save($savedata);
		if(is_bool($state) || is_bool($state1)){
			$returnData['code']=204;
			$returnData['message']="请求超时";
			$userAddressInfo->rollback();
		}else{
			$userAddressInfo->commit();
			$returnData['code'] =200;
			$returnData['message']  = "收货地址修改成功";
			$returnData['info'] = array("address_id"=>$address_id,"consignee_phe"=>$consignee_phe,"consignee_name"=>$consignee_name,"consignee_address"=>$consignee_address,"remarks"=>$remarks,"flag"=>$flag);
		}
		return $returnData;	
	}
	/**
	 * 根据收货地址ID获取用户ID
	 * @address_id 收货地址id
	 * 2017.02.11
	 */
	public function getUserIdFromAddressId($address_id){
		$userAddressInfo    = M("user_addressinfo");
		$data   = $userAddressInfo->where("address_id = '$address_id'")->find();
		return $data['user_id'];
	}
	/**
	 * 根据用户ID返回对应的收货地址列表
	 * @user_id 用户id
	 * 2017.02.11
	 */
	public function getAddressInfoFromUserId($user_id){
		$userAddressInfo = M("user_addressinfo");
		$user_id = $user_id;
		$data = $userAddressInfo->field('address_id,consignee_phe,consignee_name,consignee_address,remarks,flag')->where("user_id = '$user_id'")->order('flag asc')->select();
		if(is_bool($data)){
			$returnData['code'] =204;
			$returnData['message']  = "找不到了";
		}else{
			$returnData['code'] =200;
			$returnData['message']  = "获取成功";
			$returnData['info'] = $data?$data:array();
		}
		return $returnData;
	}
	/**
	 * 设置默认地址（列表页）
	 * @adid   收货地址Id
	 */
	public function SetDefault_m($adid){
		$model =  M("user_addressinfo");
		$result =$model->field("flag,user_id")->where("address_id = $adid")->find();
		if(!$result){
			$returnData['code'] = 204;
			$returnData['message'] ="地址信息错误，请核对";
			return $returnData;
		}
		$flag = $result['flag'];
		$userid = $result['user_id'];
		if($flag == 1){
			$returnData['code'] =200;
			$returnData['message']  = "已经是默认，请勿再选择";
		}else{
			$result2 = $model->field("address_id")->where("user_id=$userid and flag=1")->find();
			if(!empty($result2)){
				$result3 = $model->execute("update user_addressinfo set flag=1 where address_id=$adid");
				$result4 = $model->execute("update user_addressinfo set flag=2 where address_id !=$adid and user_id=$userid");
				if(is_bool($result3) && is_bool($result4)){
					$returnData['code'] =204;
					$returnData['message']  = "设置成功";
				}else{
					$returnData['code'] =200;
					$returnData['message']  = "设置成功";
				}
			}else{
				$result3 = $model->execute("update user_addressinfo set flag=1 where address_id=$adid");
				if(is_bool($result3)){
					$returnData['code'] =204;
					$returnData['message']  = "设置成功";
				}else{
					$returnData['code'] =200;
					$returnData['message']  = "设置成功";
				}
			}
		}
		return $returnData;
	}
}
?>