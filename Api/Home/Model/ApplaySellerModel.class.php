<?php
/**
 * 商户审核相关模型类
 *2017.5.12
 */
namespace Home\Model;
use Think\Model;
header("Content-code: text/html; charset=UTF-8");
class ApplaySellerModel extends Model {
	private $_APIURl;
	/**
	 * [seller_store 提交商家店铺信息]
	 * @param  [type] $seller_id      [description]
	 * @param  [type] $shop_name      [description]
	 * @param  [type] $shop_desc      [description]
	 * @param  [type] $shop_logo_path [description]
	 * @param  [type] $shop_back      [description]
	 * @return [type]                 [description]
	 */
	public function seller_store($seller_id,$shop_name,$shop_desc,$shop_logo_path,$shop_back_path,$user_id){
		$new_add = array(
			'seller_id'=>$seller_id,
			'name'=>$shop_name,
			'bdesc'=>$shop_desc,
			'blogo'=>$shop_logo_path,
			'bakimg'=>$shop_back_path,
			'create_time' => date("Y-m-d H:i:s"),
			);
		$result = M('business_info')->add($new_add);
		if($result){
            // 站内信 新增消息
            $title = '商户申请';
            $message = '店铺信息提交成功！';
            $type = 2;//type 1系统消息 2站内信
            $h_message = D('Message')->add($user_id,$title,$message,$type);
            $returnData['code'] = 200;
            $returnData['message'] ="店铺信息提交成功";
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="店铺信息提交失败";
            return $returnData;
        }
	}
    /**
     * [findPayPwd 忘记支付密码]
     * @param [type] $user_id   [description]
     * @param [type] $pay_pwd [description]
     */
    public function findPayPwd($user_id,$phe,$code,$pay_pwd,$p_pay_pwd,$salt){
        $vmodel = D("Verification");
        if($pay_pwd != $p_pay_pwd){
            $data['code'] = 204;
            $data['message'] ="两次密码输入不一致";
            return $data;
        }
        $vcode = $this->verifyAppCode($phe,$code);
        if($vcode==-1){
         $data['code'] = 204;
         $data['message'] ="验证码输入有误";
         return $data;
        }else if($vcode ==2){
         $data['code'] = 204;
         $data['message'] ="验证码已经失效，请重新发送";
         return $data;
        }
        // 把原来的密码删除
        $res= M('user')->where(array('user_id'=>$user_id))->save(array('pay_password'=>null));
        $result = M('user')->where(array('user_id'=>$user_id))->save(array('pay_password'=>md5($pay_pwd.$salt)));
        $ls = M("register_emporary");
        $ls->where("is_phone='$phe'")->delete();
        if($result){
            $returnData['code'] = 200;
            $returnData['message'] ="支付密码修改成功";
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="支付密码修改失败";
            return $returnData;
        }
    }
    /**
     * 验证输入的手机验证码是否正确(是否超出验证码时间限制目前是10分钟 后台管理可操作)
     * @phe 手机号
     * @code 验证码
     * 返回值：-1 验证码输入有误 1 验证码输入正确 2验证码已经失效
     * 日期：2017.02.04
     */
    public function verifyAppCode($user_phone,$code){
        $ls = M("register_emporary");
        $data   =$ls->where("is_phone='$user_phone' and code='$code'")->select();
        $send_time  = strtotime($data[0]['send_time']);
        $curr_time  = strtotime(date("Y-m-d H:i:s"));
        $codetime   = $this->GetCodeTime();
        $codetime   = $codetime * 60;
        if(empty($data)){
            return -1;
        }else{
            $coud_time  = $curr_time-$send_time;
            if($coud_time > $codetime){
                return 2;
            }
            return 1;
        }
    }
    /**
     * 获取验证码失效时间
     * 日期：2017.02.04
     */
    public function GetCodeTime(){
        $model  = M("publicsetting");
        $data   = $model->where("`set`='codetime'")->find();
        if(!empty($data)){
            return $data['content'];
        }else{
            return 30;
        }
    }
	 /**
     * [setPayPwd 设置/修改密码]
     * @param [type] $token   [description]
     * @param [type] $pay_pwd [description]
     */
    public function setPayPwd($token,$pay_pwd,$salt,$user_id){
        $result = M('user')->where(array('token'=>$token))->save(array('pay_password'=>md5($pay_pwd.$salt)));
        if($result){
            // 站内信 新增消息
            $title = '密码修改';
            $message = '支付密码修改成功';
            $type = 2;//type 1系统消息 2站内信
            $h_message = D('Message')->add($user_id,$title,$message,$type);
            $returnData['code'] = 200;
            $returnData['message'] ="支付密码修改成功";
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="支付密码修改失败";
            return $returnData;
        }
    }
     /**
      * 申请成为商家
     * @user_id 
     * @seller_name  商家姓名
     * @seller_idcar 商家身份证信息
     * @seller_phone 商家手机号码
     * @province 省份
     * @city 城市
     * @area 区/县
     * @remarks 详细地址
     */
    public function M_Apply_Seller($user_id,$seller_name,$seller_phone,$province,$city,$remarks,$company_name,$payment_no,$dataPath){
        $h_seller = M('h_seller_detail');
        $sellerinfo = $h_seller->where(array('user_id'=>$user_id))->find();
        $vmodel = D("Verification");
        $vphe = $vmodel::isMobile($seller_phone);
        if(!$vphe  || strlen($seller_phone)>11){
            $returnData['code'] = 204;
            $returnData['message'] ="手机号格式错误,请重新填写";
            return $returnData;
        }
        $new_add = array(
            'user_id' => $user_id,
            'seller_name' => $seller_name,
            'seller_phone' => $seller_phone,
            'province' => $province,
            'city' => $city,
            'remarks' => $remarks,
            'company_name' => $company_name,
            // 'payment_no' => $payment_no,
            'picture' => $dataPath,
            'status' => 1,
            'create_time' => date("Y-m-d H:i:s"),
            );
        $new_pay = array(
            'user_id' => $user_id,
            'number' =>  $payment_no,
            'type' =>  1,
            'create_time' => date("Y-m-d H:i:s"),
            );
        if(!$sellerinfo){
            $result = $h_seller->add($new_add);
            $res = M('h_user_bank')->add($new_pay);
            if($result){
                $returnData['code'] = 200;
                $returnData['message'] ="商家申请提交成功，请等待审核结果";
                return $returnData;
            }else{
                $returnData['code'] = 204;
                $returnData['message'] ="商家申请提交失败";
                return $returnData;
            }
        }else{
            if($sellerinfo['status'] == 1){
                $returnData['code'] = 204;
                $returnData['message'] ="商家申请正在审核，请勿反复提交";
                return $returnData;
            }else if($sellerinfo['status'] == 2){
                $returnData['code'] = 204;
                $returnData['message'] ="商家申请已经批准，请勿再次申请";
                return $returnData;
            }
        }
    }
    /**
     * 绑定银行卡//支付宝
     * @user_id
     * @hold_name 持卡卡姓名
     * @hold_idcar 持卡人身份证
     * @bank_num  银行卡号
     * @bank_deposit 开户行名称
     * @phe  预留手机号码
     */
    public function M_Seller_Bind_Bank($user_id,$hold_name,$hold_idcar,$bank_num,$bank_deposit,$phe,$code){
        // 判断是否是商家，是商家才批准填写银行卡
        $h_seller = M('h_seller_detail');
        $sellerinfo = $h_seller->where(array('user_id'=>$user_id))->find();
        if($sellerinfo['status'] == 1){
            $returnData['code'] = 204;
            $returnData['message'] ="商家申请尚在审核,无法绑定商家银行卡,请等待审核结果";
            return $returnData;
        }if($sellerinfo['status'] != 2){
            $returnData['code'] = 204;
            $returnData['message'] ="不是商家，不必申请银行卡绑定";
            return $returnData;
        }
        $vmodel = D("Verification");
        $vphe = $vmodel::isMobile($phe);
        if(!$vphe  || strlen($phe)>11){
            $returnData['code'] = 204;
            $returnData['message'] ="手机号格式错误,请重新填写";
            return $returnData;
        }
        $vidcard     = $vmodel::isIDcard($hold_idcar);
        if(!$vidcard){
            $returnData['code'] = 204;
            $returnData['message'] ="身份证格式错误,请重新填写";
            return $returnData;
        }
        // $vcode = $this->verifyAppCode($phe,$code);
        // if($vcode==-1){
        //  $data['code'] = 204;
        //  $data['message'] ="验证码输入有误";
        //  return $data;
        // }else if($vcode ==2){
        //  $data['code'] = 204;
        //  $data['message'] ="验证码已经失效，请重新发送";
        //  return $data;
        // }
        $vbanknum = $vmodel::check_cardno($bank_num);
        // $vbanknum  返回结果 1 银行卡 2信用卡 false 是卡号错误
        // var_dump($vbanknum);
        if(!$vbanknum){
            $returnData['code'] = 204;
            $returnData['message'] ="银行卡号码错误,请重新填写";
            return $returnData;
        }else if($vbanknum == 2){
            $returnData['code'] = 204;
            $returnData['message'] ="该卡未信用卡,请重新填写银行卡";
            return $returnData;
        }
        $new_add = array(
            'user_id'=>$user_id,
            'card_holder'=>$hold_name,
            'identity_card'=>$hold_idcar,
            'bank_card'=>$bank_num,
            'bank_name'=>$bank_deposit,
            'hold_phone'=>$phe,
            'create_time'=>date("Y-m-d H:i:s"),
            );
        $result = M('h_user_bank')->add($new_add);
        if($result){
            $returnData['code'] = 204;
            $returnData['message'] ="银行卡添加成功";
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="银行卡添加失败";
            return $returnData;
        }
    }
    /**
     * [M_Seller_Bank_List 商家银行卡列表]
     * @param [type] $user_id [description]
     */
    public function M_Seller_Bank_List($user_id){
        // 判断是否是商家
        $h_seller = M('h_seller_detail');
        $sellerinfo = $h_seller->where(array('user_id'=>$user_id))->find();
        if($sellerinfo['status'] == 1){
            $returnData['code'] = 204;
            $returnData['message'] ="商家申请尚在审核,无法查看银行卡,请等待审核结果";
            return $returnData;
        }if($sellerinfo['status'] != 2){
            $returnData['code'] = 204;
            $returnData['message'] ="不是商家，不能查看银行卡";
            return $returnData;
        }
        $result = M('h_user_bank')->field('bank_id,card_holder,identity_card,bank_name,bank_card,hold_phone')->where(array('user_id'=>$user_id))->select();
        if($result){
            $returnData['code'] = 200;
            $returnData['message'] ="查看银行卡";
            $returnData['info'] = $result;
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="未绑定银行卡";
            return $returnData;
        }
    }
      /**
     * [Seller_Bank_Del 商家删除银行卡]
     * @param [type] $user_id [description]
     * @param [type] $bank_id [description]
     */
    public function M_Seller_Bank_Del($user_id,$bank_id){
        $result = M('h_user_bank')->where(array('user_id'=>$user_id,'bank_id'=>$bank_id))->delete();
        if($result){
            $returnData['code'] = 200;
            $returnData['message'] ="银行卡删除成功";
            return $returnData;
        }else{
            $returnData['code'] = 204;
            $returnData['message'] ="银行卡删除失败";
            return $returnData;
        }
    }
}