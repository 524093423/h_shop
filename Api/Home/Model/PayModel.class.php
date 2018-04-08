<?php
/**
 * 支付模型
 * 2017年7月25日
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class PayModel extends Model {
	private $_partner;
	private $_seller;
	private $_privatekey;
	/**
	 * 支付方式的选择
	 * @payid 支付方式id
	 * @out_trade_no 商户订单号
	 * @total  订单总价
	 * 2017.02.23
	 */
	public function SelectPay($payid,$out_trade_no,$total,$body,$subject="",$url="",$userid=""){
		//设置参数
		if($payid !=3){
		    $this->SetPay($payid);
		}
		if($payid==1){
			$result 			= $this->Alipay($out_trade_no,$subject,$body,$total,$url);
		}elseif ($payid==2){
			$result 			= $this->WxPay($body, $total, $out_trade_no,$url);
		}elseif ($payid==3){
			$result             = D("Order")->Handle_Order_Logic($out_trade_no,1,$total,$userid);
		}
		return $result;
	}
	/**
	 * 设置支付方式需要的商户参数
	 * 2017.02.23
	 */
	public function SetPay($payid){
		if(empty($payid)){return false;}
		$payinfo 		= D("Common")->GetPayInfo("p_id=$payid","partnerid,seller,pay_key");
		if($payid ==1){
			$this->_partner=$payinfo[0]['partnerid'];
			$this->_seller=$payinfo[0]['seller'];
			$this->_privatekey=$payinfo[0]['pay_key'];
			//echo $this->_privatekey;die;
		}elseif ($payid ==2){
			$this->_partner=$payinfo[0]['partnerid']	;
			$this->_seller=$payinfo[0]['seller'];
			$this->_privatekey=$payinfo[0]['pay_key'];
		}elseif ($payid ==3){
			
		}
	}
	/**
	 * 支付宝支付
	 * @data  支付数据
	 * 2017.02.23
	 */
	public function Alipay($out_trade_no1,$subject,$body,$total_fee,$url=""){
	    //支付宝
	    //构造要请求的参数数组，无需改动
	    $notify_url   =$url?$url:"http://www.heigushop.com/index.php/Pay/AlipayNotify";
	    include_once './ThinkPHP/Library/Org/Net/Alipay2017/wappay/service/AopClient1.php';
	    include_once './ThinkPHP/Library/Org/Net/Alipay2017/wappay/service/AlipayTradeAppPayRequest.php';
	    $aop = new \AopClient1();
	    $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";//请求地址
	    $aop->appId = $this->_partner;//实际的appid
	    $aop->rsaPrivateKey =$this->_privatekey;//app商户私钥
	    $aop->format = "json";
	    $aop->charset = "UTF-8";
	    $aop->signType = "RSA";
	    $aop->alipayrsaPublicKey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";//公钥

	    //var_dump($aop);die;
	    //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
	    $request = new \AlipayTradeAppPayRequest();
	    $subject = '嘿谷商城订单';
	    //两个键名之间不要用空格（太坑人）
	    $bizcontent = "{\"body\":\"商品结算订单\","
	        ."\"subject\":\"$subject\","
	        ."\"out_trade_no\":\"$out_trade_no1\","
	        ."\"timeout_express\":\"30m\","
	            ."\"total_amount\":\"$total_fee\","
	            ."\"product_code\":\"QUICK_MSECURITY_PAY\""
	                ."}";
	    $request->setNotifyUrl($notify_url);
	    $request->setBizContent($bizcontent);
	    //print_r($request);die;
	    //这里和普通的接口调用不同，使用的是sdkExecute	
	    $response = $aop->sdkExecute($request);
	    //print_r($response);die;
	    return $response;
	}
	/**
	 *微信支付
	 *@pay 微信支付商户信息
	 *@body 商品描述
	 *@total  商品价格
	 *@out_trade_no 商户订单号
	 *2017.02.23
	 */
	public function WxPay($body,$total,$out_trade_no,$url){
		import("Vendor.Weixin.WechatAppPay");
		$url  = $url?$url:'http://www.heigushop.com/index.php/Pay/WxNotiFy';
		$options = array(
				'appid' 	=> 	$this->_partner,//填写微信分配的公众开放账号ID
				'mch_id'	=>	$this->_seller,//填写微信支付分配的商户号
				'notify_url'=>	$url,//填写微信支付结果回调地址
				'key'		=>	$this->_privatekey//填写  商户支付密钥Key。审核通过后，在微信发送的邮件中查看
		);
		//print_r($options);die;
		$wechatAppPay = new \WechatAppPay($options);
		$params['body'] = $body;//商品描述
		$params['out_trade_no'] = $out_trade_no;	//自定义的订单号
		$params['total_fee'] = $total*100;//$total;//订单金额 只能为整数 单位为分
		$params['trade_type'] = 'APP';//交易类型 JSAPI | NATIVE | APP | WAP
		$result = $wechatAppPay->unifiedOrder( $params );//微信下单
		//print_r($result);die;
		//if(){}
		$data = @$wechatAppPay->getAppPayParams( $result['prepay_id'] );//构造app传递的参数
		$m =	fopen("./ai.txt","w+");
		fwrite($m,json_encode($data));
		fclose($m);
		return $data;
	}
	/**
	 * 支付宝支付退款api操作
	 * 2017.06.17
	 */
	public function AlipayRefund($total,$orderno){
	    Vendor("AlipayAop.AopClient");
	    Vendor("AlipayAop.request.AlipayTradeRefundRequest");
	    $this->SetPay(1);
	    $aop = new \AopClient();
	    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
	    $aop->appId = $this->_partner;
	    $aop->rsaPrivateKey = $this->_privatekey;//'请填写开发者私钥去头去尾去回车，一行字符串';
	    $aop->alipayrsaPublicKey= "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";//'请填写支付宝公钥，一行字符串';
	    $aop->apiVersion = '1.0';
	    $aop->signType = 'RSA';
	    $aop->postCharset='UTF-8';
	    $aop->format='json';
	    //print_r($aop);die;
	    $request = new \AlipayTradeRefundRequest();
	    $request->setBizContent("{".
	        "\"out_trade_no\":\"$orderno\",".
	        "\"refund_amount\":$total,".
	        "\"refund_reason\":\"正常退款\",".
	        "\"out_request_no\":\"HZ01RF001\",".
	        "\"operator_id\":\"OP001\",".
	        "\"store_id\":\"NJ_S_001\",".
	        "\"terminal_id\":\"NJ_T_001\"".
	        "}");
	    $result = $aop->execute ( $request);
	    
	    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
	    $resultCode = $result->$responseNode->code;
	    if(!empty($resultCode)&&$resultCode == 10000){
	        return true;
	    } else {
	        return false;
	    }
	}
	/**
	 * 余额支付退款
	 * @param unknown $userid
	 * @param unknown $total
	 */
	public function balanceRefund($userid,$total){
	    $model = M("user");
	    $userinfo  = $model->field("account")->where("user_id=$userid")->find();
	    if(empty($userinfo)){
	        return false;
	    }
	    $account   = $userinfo['account'];
	    $account   = math_add($account, $total);
	    $result= $model->execute("update user set account=$account where user_id=$userid");
	    if(empty($result)){
	        return false;
	    }else{
	        return true;
	    }
	}
	/**
	 * 微信退款(app)
	 * @param unknown $total
	 * @param unknown $orderno
	 */
	public function WxpayRefund($total,$orderno,$sfk,$WxpayType){
		if($WxpayType=="jsapi"){
			return $this->WxpayRefundWeb($total,$orderno,$sfk);
		}
		//import("Vendor.Weixin.WxPay.Data");
		include_once './ThinkPHP/Library/Vendor/Weixin/WxPay.Api.php';
		include_once './ThinkPHP/Library/Vendor/Weixin/WxPay.Data.php';
		if(!$orderno) return false;
		$input = new \WxPayRefund();
		$input->SetOut_trade_no($orderno);         //自己的订单号
		//$input->SetTransaction_id($order['transaction_id']);     //微信官方生成的订单流水号，在支付成功中有返回
		$input->SetOut_refund_no($this->getrand_num());         //退款单号
		$input->SetTotal_fee($total*100);         //订单标价金额，单位为分
		$input->SetRefund_fee($sfk*100);            //退款总金额，订单总金额，单位为分，只能为整数
		$input->SetOp_user_id("1483514142");

		$result = \WxPayApi::refund($input); //退款操作
		// 这句file_put_contents是用来查看服务器返回的退款结果 测试完可以删除了
		//file_put_contents(APP_ROOT.'/Api/wxpay/logs/log3.txt',arrayToXml($result),FILE_APPEND);
		if(($result['return_code']=='SUCCESS') && ($result['result_code']=='SUCCESS')){
			//退款成功
			return true;
		}else if(($result['return_code']=='FAIL') || ($result['result_code']=='FAIL')){
			//退款失败
			//原因
			$reason = (empty($result['err_code_des'])?$result['return_msg']:$result['err_code_des']);
			Rewrite_log("./refundOrder",$reason."\r\n");
			return false;
		}else{
			//失败
			return false;
		}
	}
	/**
	 * 微信退款(web)
	 * @param unknown $total
	 * @param unknown $orderno
	 */
	public function WxpayRefundWeb($total,$orderno,$sfk){
		//import("Vendor.Weixin.WxPay.Data");
		include_once './ThinkPHP/Library/Vendor/WeixinWeb/WxPay.Api.php';
		include_once './ThinkPHP/Library/Vendor/WeixinWeb/WxPay.Data.php';
		if(!$orderno) return false;
		$input = new \WxPayRefund();
		$input->SetOut_trade_no($orderno);         //自己的订单号
		//$input->SetTransaction_id($order['transaction_id']);     //微信官方生成的订单流水号，在支付成功中有返回
		$input->SetOut_refund_no($this->getrand_num());         //退款单号
		$input->SetTotal_fee($total*100);         //订单标价金额，单位为分
		$input->SetRefund_fee($sfk*100);            //退款总金额，订单总金额，单位为分，只能为整数
		$input->SetOp_user_id("1483514142");

		$result = \WxPayApi::refund($input); //退款操作
		// 这句file_put_contents是用来查看服务器返回的退款结果 测试完可以删除了
		//file_put_contents(APP_ROOT.'/Api/wxpay/logs/log3.txt',arrayToXml($result),FILE_APPEND);
		if(($result['return_code']=='SUCCESS') && ($result['result_code']=='SUCCESS')){
			//退款成功
			return true;
		}else if(($result['return_code']=='FAIL') || ($result['result_code']=='FAIL')){
			//退款失败
			//原因
			$reason = (empty($result['err_code_des'])?$result['return_msg']:$result['err_code_des']);
			Rewrite_log("./refundOrder",$reason."\r\n");
			return false;
		}else{
			//失败
			return false;
		}
	}
	/**
	 * 生成订单号
	 *2017.05.25
	 */
	public function getrand_num(){
		@date_default_timezone_set("PRC");
		//订购日期
		$order_date = date('Y-m-d');
		//订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
		$order_id_main = date('YmdHis') . rand(10000000,99999999);
		//订单号码主体长度
		$order_id_len = strlen($order_id_main);
		$order_id_sum = 0;
		for($i=0; $i<$order_id_len; $i++){
			$order_id_sum += (int)(substr($order_id_main,$i,1));
		}
		//唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
		$order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
		return $order_id;
	}
	/**
	 * H5微信支付
	 * @param unknown $Body
	 * @param unknown $Attach
	 * @param unknown $Out_trade_no
	 * @param unknown $Total_fee
	 * @param unknown $Notify_url
	 */
	public function WebWxpay($Body,$Attach,$Out_trade_no,$Total_fee,$Notify_url){
	    $data['body']      = $Body;
	    $data['attach']    = $Attach;
	    $data['out_trade_no'] = $Out_trade_no;
	    $data['total_fee']     = $Total_fee;
	    $data['notify_url']    = $Notify_url;
	    return $data;
	}
}