<?php
/**
 * 支付方式
 *2017.02.11
 * 2017.04.11修改项目根目录地址数据库获取
 */
namespace Home\Controller;
use Think\Controller\RestController;
class PayController extends RestController {
	private $_AdminUrl;
	/**
	 * 获取支付方式信息
	 * 2017.02.21
	 */
	public function GetPay(){
	    $isseller  = I("get.isseller");
	    $where = "flag=0";
	    if(!empty($isseller)){
	        $where = "";
	    }
		$this->_AdminUrl=D("Common")->getUrl();
		$data 	= D("Common")->GetPayInfo($where);
		if(empty($data)){
			$return['code'] = "204";
			$return['message'] = "服务超时";
		}else{
			$return['code'] = "200";
			$return['message'] = "获取成功";
			$return['info'] 	= join_carouse_url($data, $this->_AdminUrl, "pimg");
		}	
		$this->response($return);
	}
	/**
	 * 微信回调
	 *2017.05.25
	 */
	public function WxNotiFy(){
		$xml = $GLOBALS["HTTP_RAW_POST_DATA"];
		$msg = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$resultcode = $msg['result_code'];
		$returncode = $msg['return_code'];
		if($resultcode == "SUCCESS" && $returncode=="SUCCESS"){
			$outorderno = $msg['out_trade_no'];
			$wxpay   = D("Order");
			$result 	= $wxpay->Handle_Order_Logic($outorderno);
			if($result){
				$su = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
				echo $su;
			}
		}else{
		    echo "失败";
		}
	}
	/**
	 * 微信回调【再次付款】
	 *2017.05.25
	 */
	public function WxNotiFy2(){
	    $xml = $GLOBALS["HTTP_RAW_POST_DATA"];
	    $msg = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	    $resultcode = $msg['result_code'];
	    $returncode = $msg['return_code'];
	    if($resultcode == "SUCCESS" && $returncode=="SUCCESS"){
	        $outorderno = $msg['out_trade_no'];
	        $wxpay   = D("Order");
	        $result 	= $wxpay->Handle_Order_Logic2($outorderno);
	        if($result){
	            $su = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
	            echo $su;
	        }
	    }
	}
	/**
	 * 支付宝回调
	 * 2017.05.25
	 */
	public function AlipayNotify(){
		
		//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
		$alipay_config['partner'] = '2017062107538131';
		//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
		$alipay_config['private_key'] = 'MIICWwIBAAKBgQCmDxZeRKGLfbm5X4gP3PyGHMbSqvXo2tECX3zZB+pVFpPTZsT6gINpBnilWVbheLfavCd03ibY+sgaoD3vmBLCGPDMHRArvRDjaDAC8gZ5z+u7m8KRf/PSwbPIly6IjALcfhUQXI22snU5TyehkATDVt6PVW/s5rEiboIBJG1MhQIDAQABAoGAD1UHSK7nh3yVQFNcubI6sptHlD3qtJQ3hhOQ8Gz1quOJxJQRjdt9xp2tV/72sZy2xQBoWDcGuTP2c9blwRB2csVIr696mVQaZMUMLViYeOD9EGxSVTYdIRZuVkYbFWoxoERsEYHgZrrcn+TNtJTS+AZfyGpUA345AUz22OxNGWECQQDU+PliJdWDXLZyjuLSVj2s5H5t3e1ASRj5O4C9ZFBSKlLNJIMqtJP/f8d4WX4+Zu3+hr2fIj7O42r/elodjv4ZAkEAx5u41MuWdcqlY5ZyjCvg3GuS61q9h4g8qExrtVkyCZJfjSI5FzLX2MQq1GkifhmsKnKxbVkRdla6GbC3Wd+3TQJAEhw98Lew6WPH7AP0i9hjlEE18xfNJgtRQVhrAS/3mJUax3EUalxCvZZbGwPZNWtBT8SbeV3LI4K5RZJc+WiAeQJAH5ZZ+nq5Nay63Xaaa+oprFftqQb0BTWe+7cUOF+EFpWuP0RSKoSmOaxZZsG4bqIbZ6A8yaQVqldxHc2kRjmr5QJAanph6/uOOKnwUye7mjbox7emf9pJAg3pWV1A35m+ofyFUwhgR++1psMRdWHnVsGNz+aah1jFnCix3EyrTmhfQw==';
		//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
		$alipay_config['alipay_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB';
		//异步通知接口
		$alipay_config['service']= 'mobile.securitypay.pay';
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('RSA');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'/cacert.pem';
		
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		import("Vendor.Alipay.lib.alipay_notify");
		$alipayNotify = new \AlipayNotify($alipay_config);
		if($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
		{
			if($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])){//
				// 使用支付宝公钥验签
				//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
				//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
				//商户订单号
				$out_trade_no = $_POST['out_trade_no'];
				//支付宝交易号
				$trade_no = $_POST['trade_no'];
				//交易状态
				$trade_status = $_POST['trade_status'];
				// var_dump($_POST);
				if($_POST['trade_status'] == 'TRADE_FINISHED') {
					//判断该笔订单是否在商户网站中已经做过处理
					//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
					//如果有做过处理，不执行商户的业务程序
					//注意：
					//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
					//请务必判断请求时的out_trade_no、total_fee、seller_id与通知时获取的out_trade_no、total_fee、seller_id为一致的
				}else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
					$model   = D("Order");
					$result 	= $model->Handle_Order_Logic($out_trade_no);
				}
				//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
				echo "success";		//请不要修改或删除
			}else{//验证签名失败
				// var_dump($_POST);
				echo "sign fail";
			}
		}else{//验证是否来自支付宝的通知失败
			echo "response fail";
		}
	}
	/**
	 * 支付宝回调【再次付款】
	 * 2017.05.25
	 */
	public function AlipayNotify2(){
	    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
	    //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
	    $alipay_config['partner'] = ' 2088621476546764';
	    //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
	    $alipay_config['private_key'] = '';
	    //支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
	    $alipay_config['alipay_public_key'] = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
	    //异步通知接口
	    $alipay_config['service']= 'mobile.securitypay.pay';
	    //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
	    //签名方式 不需修改
	    $alipay_config['sign_type']    = strtoupper('RSA');
	    //字符编码格式 目前支持 gbk 或 utf-8
	    $alipay_config['input_charset']= strtolower('utf-8');
	    //ca证书路径地址，用于curl中ssl校验
	    //请保证cacert.pem文件在当前文件夹目录中
	    $alipay_config['cacert']    = getcwd().'/cacert.pem';
	    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
	    $alipay_config['transport']    = 'http';
	    import("Vendor.Alipay.lib.alipay_notify");
	    $alipayNotify = new \AlipayNotify($alipay_config);
	    if($alipayNotify->getResponse($_POST['notify_id']))//判断成功之后使用getResponse方法判断是否是支付宝发来的异步通知。
	    {
	        if($alipayNotify->getSignVeryfy($_POST, $_POST['sign'])){//
	            // 使用支付宝公钥验签
	            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	            //商户订单号
	            $out_trade_no = $_POST['out_trade_no'];
	            //支付宝交易号
	            $trade_no = $_POST['trade_no'];
	            //交易状态
	            $trade_status = $_POST['trade_status'];
	            // var_dump($_POST);
	            if($_POST['trade_status'] == 'TRADE_FINISHED') {
	                //判断该笔订单是否在商户网站中已经做过处理
	                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
	                //如果有做过处理，不执行商户的业务程序
	                //注意：
	                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
	                //请务必判断请求时的out_trade_no、total_fee、seller_id与通知时获取的out_trade_no、total_fee、seller_id为一致的
	            }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
	                $model   = D("Order");
	                $result 	= $model->Handle_Order_Logic2($out_trade_no);
	            }
	            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	            echo "success";		//请不要修改或删除
	        }else{//验证签名失败
	            // var_dump($_POST);
	            echo "sign fail";
	        }
	    }else{//验证是否来自支付宝的通知失败
	        echo "response fail";
	    }
	}
	/**
	 * 测试回调
	 * 2017.04.14
	 */
	public function ceshi(){
		/* $orderno = "201704141413486897168114";
		$model   = D("Order");
		$result 	= $model->Handle_Order_Logic($orderno); */
	}
}