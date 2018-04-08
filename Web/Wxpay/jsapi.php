<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "./lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';
//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
//①、获取用户openid
$openId = $_GET['openid'];
$body   = $_GET['body'];
$attach = $_GET['attach'];
$order_no   = $_GET['out_trade_no'];
$total_fee  = $_GET['total_fee'];
//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody($body);
$input->SetAttach($attach);
$input->SetOut_trade_no($order_no);
$input->SetTotal_fee($total_fee*100);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("HeiGuKeJi");
$input->SetNotify_url("http://www.heigushop.com/index.php/Pay/WxNotiFy");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$tools = new JsApiPay();
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<title>微信支付</title>
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
	<meta name="format-detection" content="telephone=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link rel="stylesheet" href="../css/public.css"/>
	<link rel="stylesheet" href="../css/global.css"/>
	<style>
		body{background-color: #fff;}
		/*微信支付页面*/
		.wx-p1{color: #000;font-size:50px;text-align: center;padding-top: 50px;}
		.wx-p2{color: #000;font-size: 60px; text-align: center;font-weight: bold;margin-top: 20px;}
		.wx-p3{color: #666;font-size:0.375rem;line-height: 1.1rem;border-bottom: 1px solid #999;
			border-top: 1px solid #999;margin-top: 30px;}
		.wx-p3 b{float: left;margin-left: 40px;}
		.wx-p3 span{float: right;margin-right: 40px;}
		.wx-p4{color: #fff;font-size: 0.5625rem;line-height: 1.3rem;background-color: #1aad19;
			text-align: center;border-radius: 10px;margin-top: 40px;}
	</style>
</head>
<body>
<div class="box">

	<p class="wx-p1">嘿谷商城</p>
	<p class="wx-p2">¥ <b><?php echo $_GET['total_fee'];?></b></p>
	<p class="wx-p3 clearfix">
		<b>收款方</b>
		<span>嘿谷商城</span>
	</p>
	<p class="wx-p4 wrap" onclick="callpay()">立即支付</p>

</div>
<!--content end-->

</body>
<script src="../js/jquery1.9.1.js"></script>
<script src="../js/common.js"></script>
<script type="text/javascript">
	/*  $(function(){ //判断 是 安卓 还是IOS 手机
	 var u = navigator.userAgent;
	 var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
	 var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
	 if(isAndroid){
	 return;
	 }
	 if(isiOS){
	 $('.top').css({'height':'1.765625rem'})
	 $('.content').css({'margin-top':'1.765625rem'})
	 $('.topWrap').css({'margin-top':'0.328125rem'})
	 }
	 })*/
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				<?php echo $jsApiParameters; ?>,
				function(res){
					WeixinJSBridge.log(res.err_msg);
					//alert(res.err_code+res.err_desc+res.err_msg);
					if(res.err_msg=="ok"){
						window.location.href="http://www.heigushop.com/Web/html/myOrder.html?state=2";
					}else{
						window.location.href="http://www.heigushop.com/Web/html/myOrder.html?state=1";
					}
				}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
			if( document.addEventListener ){
				document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			}else if (document.attachEvent){
				document.attachEvent('WeixinJSBridgeReady', jsApiCall);
				document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			}
		}else{
			jsApiCall();
		}
	}
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
				'editAddress',
				<?php echo $editAddress; ?>,
				function(res){
					var value1 = res.proviceFirstStageName;
					var value2 = res.addressCitySecondStageName;
					var value3 = res.addressCountiesThirdStageName;
					var value4 = res.addressDetailInfo;
					var tel = res.telNumber;

					//alert(value1 + value2 + value3 + value4 + ":" + tel);
				}
		);
	}

	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
			if( document.addEventListener ){
				document.addEventListener('WeixinJSBridgeReady', editAddress, false);
			}else if (document.attachEvent){
				document.attachEvent('WeixinJSBridgeReady', editAddress);
				document.attachEvent('onWeixinJSBridgeReady', editAddress);
			}
		}else{
			editAddress();
		}
	};
</script>
</html>