<?php
/**
 * Created by PhpStorm.
 * User: xudong.ding
 * Date: 16/5/19
 * Time: 下午2:09
 */

//require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AopSdk.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AopClient1.php';
require dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'config.php';
require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'AlipayTradeRefundRequest.php';

class AlipayTradeService {

	//支付宝网关地址
	public $gateway_url = "https://openapi.alipay.com/gateway.do";

	//支付宝公钥
	public $alipay_public_key="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB";

	//商户私钥
	public $private_key="MIICWwIBAAKBgQCmDxZeRKGLfbm5X4gP3PyGHMbSqvXo2tECX3zZB+pVFpPTZsT6gINpBnilWVbheLfavCd03ibY+sgaoD3vmBLCGPDMHRArvRDjaDAC8gZ5z+u7m8KRf/PSwbPIly6IjALcfhUQXI22snU5TyehkATDVt6PVW/s5rEiboIBJG1MhQIDAQABAoGAD1UHSK7nh3yVQFNcubI6sptHlD3qtJQ3hhOQ8Gz1quOJxJQRjdt9xp2tV/72sZy2xQBoWDcGuTP2c9blwRB2csVIr696mVQaZMUMLViYeOD9EGxSVTYdIRZuVkYbFWoxoERsEYHgZrrcn+TNtJTS+AZfyGpUA345AUz22OxNGWECQQDU+PliJdWDXLZyjuLSVj2s5H5t3e1ASRj5O4C9ZFBSKlLNJIMqtJP/f8d4WX4+Zu3+hr2fIj7O42r/elodjv4ZAkEAx5u41MuWdcqlY5ZyjCvg3GuS61q9h4g8qExrtVkyCZJfjSI5FzLX2MQq1GkifhmsKnKxbVkRdla6GbC3Wd+3TQJAEhw98Lew6WPH7AP0i9hjlEE18xfNJgtRQVhrAS/3mJUax3EUalxCvZZbGwPZNWtBT8SbeV3LI4K5RZJc+WiAeQJAH5ZZ+nq5Nay63Xaaa+oprFftqQb0BTWe+7cUOF+EFpWuP0RSKoSmOaxZZsG4bqIbZ6A8yaQVqldxHc2kRjmr5QJAanph6/uOOKnwUye7mjbox7emf9pJAg3pWV1A35m+ofyFUwhgR++1psMRdWHnVsGNz+aah1jFnCix3EyrTmhfQw==";

	//应用id
	public $appid="2017062107538131";

	//编码格式
	public $charset = "UTF-8";

	public $token = NULL;
	
	//返回数据格式
	public $format = "json";


	function __construct($alipay_config){
		//dump($alipay_config);exit;
		$this->gateway_url = "https://openapi.alipay.com/gateway.do";//$alipay_config['gatewayUrl'];
		//echo $this->gateway_url;exit;
		$this->appid;
		$this->private_key;
		$this->alipay_public_key;
		$this->charset;

		if(empty($this->appid)||trim($this->appid)==""){
			throw new Exception("appid should not be NULL!");
		}
		if(empty($this->private_key)||trim($this->private_key)==""){
			throw new Exception("private_key should not be NULL!");
		}
		if(empty($this->alipay_public_key)||trim($this->alipay_public_key)==""){
			throw new Exception("alipay_public_key should not be NULL!");
		}
		if(empty($this->charset)||trim($this->charset)==""){
			throw new Exception("charset should not be NULL!");
		}
		if(empty($this->gateway_url)||trim($this->gateway_url)==""){
			throw new Exception("gateway_url should not be NULL!");
		}

	}
	function AlipayWapPayService($alipay_config) {
		$this->__construct($alipay_config);
	}

	/**
	 * alipay.trade.wap.pay
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @param $return_url 同步跳转地址，公网可访问
	 * @param $notify_url 异步通知地址，公网可以访问
	 * @return $response 支付宝返回的信息
 	*/
	function wapPay($builder,$return_url,$notify_url) {
	
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
	
		$request = new AlipayTradeWapPayRequest();
	
		$request->setNotifyUrl($notify_url);
		$request->setReturnUrl($return_url);
		$request->setBizContent ( $biz_content );
	
		// 首先调用支付api
		$response = $this->aopclientRequestExecute ($request,true);
		// $response = $response->alipay_trade_wap_pay_response;
		return $response;
	}

	 function aopclientRequestExecute($request,$ispage=false) {

		$aop = new AopClient1 ();
		$aop->gatewayUrl = $this->gateway_url;
		$aop->appId = $this->appid;
		$aop->rsaPrivateKey =  $this->private_key;
		$aop->alipayrsaPublicKey = $this->alipay_public_key;
		$aop->apiVersion ="1.0";
		$aop->postCharset = $this->charset;
		$aop->format= $this->format;
		// 开启页面信息输出
		$aop->debugInfo=true;
		if($ispage)
		{
			$result = $aop->pageExecute($request,"post");
			echo $result;
		}
		else 
		{
			$result = $aop->Execute($request);
		}
        
		//打开后，将url形式请求报文写入log文件
		$this->writeLog("response: ".var_export($result,true));
		return $result;
	}

	/**
	 * alipay.trade.query (统一收单线下交易查询)
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @return $response 支付宝返回的信息
 	*/
	function Query($builder){
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
		$request = new AlipayTradeQueryRequest();
		$request->setBizContent ( $biz_content );

		// 首先调用支付api
		$response = $this->aopclientRequestExecute ($request);
		$response = $response->alipay_trade_query_response;
		var_dump($response);
		return $response;
	}
	
	/**
	 * alipay.trade.refund (统一收单交易退款接口)
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @return $response 支付宝返回的信息
	 */
	function Refund($builder){
		
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
		$request = new \AlipayTradeRefundRequest();
		$request->setBizContent ( $biz_content );
	
		// 首先调用支付api
		
		$response = $this->aopclientRequestExecute ($request);
		$response = $response->alipay_trade_refund_response;
		
		return $response;
	}

	/**
	 * alipay.trade.close (统一收单交易关闭接口)
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @return $response 支付宝返回的信息
	 */
	function Close($builder){
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
		$request = new AlipayTradeCloseRequest();
		$request->setBizContent ( $biz_content );
	
		// 首先调用支付api
		$response = $this->aopclientRequestExecute ($request);
		$response = $response->alipay_trade_close_response;
		var_dump($response);
		return $response;
	}
	
	/**
	 * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @return $response 支付宝返回的信息
	 */
	function refundQuery($builder){
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
		$request = new AlipayTradeFastpayRefundQueryRequest();
		$request->setBizContent ( $biz_content );
	
		// 首先调用支付api
		$response = $this->aopclientRequestExecute ($request);
		$response = $response->alipay_trade_fastpay_refund_query_response;
		var_dump($response);
		return $response;
	}
	/**
	 * alipay.data.dataservice.bill.downloadurl.query (查询对账单下载地址)
	 * @param $builder 业务参数，使用buildmodel中的对象生成。
	 * @return $response 支付宝返回的信息
	 */
	function downloadurlQuery($builder){
		$biz_content=$builder->getBizContent();
		//打印业务参数
		$this->writeLog($biz_content);
		$request = new alipaydatadataservicebilldownloadurlqueryRequest();
		$request->setBizContent ( $biz_content );
	
		// 首先调用支付api
		$response = $this->aopclientRequestExecute ($request);
		$response = $response->alipay_data_dataservice_bill_downloadurl_query_response;
		var_dump($response);
		return $response;
	}

	/**
	 * 验签方法
	 * @param $arr 验签支付宝返回的信息，使用支付宝公钥。
	 * @return boolean
	 */
	function check($arr){
		$aop = new AopClient1();
		$aop->alipayrsaPublicKey = $this->alipay_public_key;
		$result = $aop->rsaCheckV1($arr, $this->alipay_public_key);
		return $result;
	}
	
	//请确保项目文件有可写权限，不然打印不了日志。
	function writeLog($text) {
		// $text=iconv("GBK", "UTF-8//IGNORE", $text);
		//$text = characet ( $text );
		file_put_contents ( dirname ( __FILE__ ).DIRECTORY_SEPARATOR."./../../log.txt", date ( "Y-m-d H:i:s" ) . "  " . $text . "\r\n", FILE_APPEND );
	}
	

	/** *利用google api生成二维码图片
	 * $content：二维码内容参数
	 * $size：生成二维码的尺寸，宽度和高度的值
	 * $lev：可选参数，纠错等级
	 * $margin：生成的二维码离边框的距离
	 */
	function create_erweima($content, $size = '200', $lev = 'L', $margin= '0') {
		$content = urlencode($content);
		$image = '<img src="http://chart.apis.google.com/chart?chs='.$size.'x'.$size.'&amp;cht=qr&chld='.$lev.'|'.$margin.'&amp;chl='.$content.'"  widht="'.$size.'" height="'.$size.'" />';
		return $image;
	}
}

?>