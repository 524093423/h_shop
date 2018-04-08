<?php
/**
 * 订单模型
 *2017.06.03
 */
namespace Home\Model;
use Think\Model;
use Org\Util\ArrayList;
header("Content-Type: text/html; charset=UTF-8");
class OrderModel extends Model {
	private $_AdminUrl;
	private $_IntlRatio=0.01;//积分比例
	private $_carjfnum	= 0;
	private $states	= 0;//是否在使用积分的情况下返回积分
	public function __construct($name, $tablePrefix, $connection)
	{
		parent::__construct($name, $tablePrefix, $connection);
		$model	= M("publicsetting");
		$result	= $model->field("`content`")->where("`set`='payuseintl'")->find();
		$this->states=$result['content'];
	}

	/**
	 * 订单结算第一步
	 * @array  包含结算信息的数组
	 * 2017.02.17
	 */
	public function FirstStep($OrderArray,$userid,$payid,$addid,$isuseintl,$pay,$paypwd){
		//print_r($OrderArray);die;
		/*if($payid==3){
            $status   = $this->CheckUserPwd($userid,$paypwd);
            Rewrite_log("./zhifumima.txt",$userid."-".$paypwd);
            if(!$status){return array("code"=>"204","message"=>"支付密码输入错误或未设置支付密码");}
        }*/
		$state 	= $this->CheckUserIsXd($userid);
		if(!$state){return array("code"=>"204","message"=>"你没有购买商品的资格,请联系管理员");}
		if(empty($OrderArray)){return array("code"=>"204","message"=>"购买的商品不在了");}
		//设置积分配置参数
		$this->SetIntlRatio();
		if(!empty($OrderArray['order'])){
			//立即购买
			$data 	= $this->BuyNow($OrderArray,$userid,$payid,$addid,$isuseintl,$pay);
		}else{
			//购物车结算
			$data 	= $this->ShoppingCart($OrderArray,$userid,$payid,$addid,$isuseintl,$pay);
		}
		return $data;
	}
	/**
	 * 检测用户是否可以下单
	 * @param unknown $userid
	 * @return boolean
	 */
	public function CheckUserIsXd($userid){
		$userinfo 	= D("Register")->getUserBasicInfo2($userid);
		if($userinfo['state'] == 1){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 检测用户余额支付支付密码是否正确
	 * @param unknown $userid
	 * @param unknown $pwd
	 */
	public function CheckUserPwd($userid,$pwd){
		$pwd	= trim($pwd);
		$userinfo = M("user")->field("pay_password,salt")->where("user_id=$userid")->find();
		$pwd1     = $userinfo['pay_password'];
		$salt     = $userinfo['salt'];
		if(md5($pwd.$salt) == $pwd1){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 检测用户余额是否充足
	 * @param unknown $userid
	 * @param unknown $total
	 */
	public function CheckUserBalance($userid,$total){
		$userinfo = M("user")->field("account")->where("user_id=$userid")->find();
		if(empty($userinfo)){
			return false;
		}
		$ua    = $userinfo['account'] * 100;
		$totab = $total * 100;
		if($totab <=0){
			return false;
		}
		if($ua >= $totab){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 订单结算第二步【立即购买】
	 * 2017.02.18
	 */
	public function BuyNow($OrderArray,$userid,$payid,$addid,$isuseintl,$pay){
		$goodsid 		= $OrderArray['order']['goodsid'];
		$msg 			= $OrderArray['order']['msg'];
		$freightId 	= 0;//$OrderArray['order']['way']
		$useintl 		= $OrderArray['order']['jf'];
		$gspecid 		= $OrderArray['order']['spid'];//去掉
		$gnum 			= $OrderArray['order']['gnum'];
//		Rewrite_log("order_xiaowei",$goodsid."\r\n");
//		Rewrite_log("order_xiaowei",$msg."\r\n");
//		Rewrite_log("order_xiaowei",$freightId."\r\n");
//		Rewrite_log("order_xiaowei",$useintl."\r\n");
//		Rewrite_log("order_xiaowei",$gspecid."\r\n");
//		Rewrite_log("order_xiaowei",$gnum."\r\n");
		//echo  $freightId;die;
		$data 	= $this->InsertOrderData($userid,$goodsid,$gspecid,$gnum,$useintl,$isuseintl,$freightId,$addid,$msg,$payid,$pay);
		return $data;
	}
	/**
	 * 将购物车中商品信息查询出来
	 */
	/**
	 *订单表插入数据【立即购买】
	 *@userid 用户id
	 *@goodsid 商品id
	 *@gspecid  商品规格id
	 *@gnum   商品数量
	 *@useintl 使用积分数量
	 *@isuserintl 是否使用积分  0否1是
	 *@freightid  配送方式id
	 *@addid     收货地址id
	 *@msg      买家留言
	 *@payid  支付方式id
	 *2017.02.20
	 *更新积分以及返现金
	 *2017.04.26
	 */
	public function InsertOrderData($userid,$goodsid,$gspecid,$gnum,$useintl,$isuseintl,$freightId="",$addid,$msg,$payid,$pay){
		if(!D("Common")->CheckGoodsIsSelf($userid,$goodsid)){
			return reTurnJSONArray("204","禁止恶意刷单");
		}
		$payname 		= $this->GetPayChinese($payid);
		if(empty($payname)){return array("code"=>"204","message"=>"所选支付方式不存在");}
		$orderData['payid'] 	= $payid;
		$orderData['paym'] 	= $payname;
		$orderData['orderno'] 	= $this->CreateOrderNo();//订单号
		$orderData['out_aplipay_no']  	= $this->CreateOrderNo();//发送给支付宝的订单
		//检测用户积分是否满足支付条件
		$checkUserJf	=	$this->CheckUserIntl($userid,$useintl);
		if(!$checkUserJf){return array("code"=>"204","message"=>"您积分不足");}
		//获取配送费
		//$freight 			= $this->GetDeliver("","odyid=$freightId");
		//if(!is_array($freight)){return array("code"=>"204","message"=>"商品暂不配送");}
		//$freprice 			= $freight[0]['price'];//所选配送方式所需的价格
		$freprice     = 0;
		$goodsinfo    = $this->GetGoodsInfoFromGid($goodsid);
		if(empty($goodsinfo)){return array("code"=>"204","message"=>"商品不存在");}
		//print_r($goodsinfo);die;
		//相关价格数组
		$priceArray 		= $this->SingleGoodsTotal($userid,$goodsid,$gspecid,$gnum,$isuseintl,$useintl);
		if(!is_array($priceArray)){return array("code"=>"204","message"=>"产品价格有误,请联系商家");}
		$orderData['bid'] 				= $goodsinfo['bid'];
		$orderData['msg'] 			= $msg?$msg:"无备注信息";
		$orderData['freid'] 			= $freightId;
		$orderData['freprice'] 		= $freprice;
		$orderData['goodstotal'] 	= $priceArray['gt'];
		$orderData['orderprice'] 	= $priceArray['gt'] + $freprice;
		$orderData['actulpay'] 		= $priceArray['gsjt'] + $freprice;
		//检测用户是否满足余额支付
		if($payid==3){
			if(!$this->CheckUserBalance($userid,$orderData['actulpay'])){return array("code"=>"204","message"=>"账户余额不足");}
		}
		$orderData['intlnum'] 		= is_null($priceArray['jfnum'])?0:$priceArray['jfnum'];
		$orderData['intlprice'] 		= $priceArray['jf'];
		$orderData['tctotal']			= 0;//$priceArray['tctotal'];
		$orderData['yjtotal'] 			= 0;//$priceArray['yjtotal'];
		//收货地址
		$addressInfo 	= $this->GetAddressInfo($addid);
		if(empty($addressInfo)){return array("code"=>"204","message"=>"收货地址不存在,请重新填写收货地址");}
		$orderData['rpeople'] 		= $addressInfo['consignee_name'];
		$orderData['rphe'] 			= $addressInfo['consignee_phe'];
		$orderData['raddress'] 		= $addressInfo['remarks'];
		$orderData['userid'] 			= $userid;
		//是否使用积分
		/*if($priceArray['jf'] !=0 && $isuseintl ==1){
			$orderData['isintl'] = 1;
		}else{
			$orderData['isintl'] = 0;
		}*/
		$orderData['otime'] = date("Y-m-d H:i:s");
		//print_r($orderData);die;
		$model 	= M("goods_order");
		$model->startTrans();
		//print_r($orderData);die;
		$result		= $model->add($orderData);//订单id
		//echo $model->getLastSql();die;
// 		//插入订单明细信息
 		$specinfo 		= $this->GetSpecInfo($gspecid);
 		if(empty($specinfo)){return array("code"=>"204","message"=>"规格不存在");}
		//gsid,gsdesc,gstock,gprice,jfnum,ccprice
		$orderDetail['goid2'] 		= $result;
		$orderDetail['godsid'] 	= $goodsid;
		$orderDetail['specid'] 	= $gspecid;//产品规格
		$orderDetail['gname'] 	= $goodsinfo['gname'];
		$orderDetail['gspec'] 	= $specinfo['gsdesc'];//规格名称"无规格";
		$orderDetail['gnum'] 	=	$gnum;
		$orderDetail['userid'] 	= $userid;
		$orderDetail['gprices'] 	= $specinfo['gprice'];
		$godsIsUseJf 				= $goodsinfo['isintpay'];
		if($this->states==2){
			$reintl = 0;
		}else{
			$reintl = intval($orderData['orderprice']);
		}
		$orderDetail['reintl'] 				= $reintl;
		$orderDetail['isretotal']			= 0;
		$orderDetail['rebatetotal'] 	= 0;
		$orderDetail['remaintotal'] 	= 0;
		$orderDetail['sjtime'] 			= date("Y-m-d H:i:s");
		$orderDetail['trade_type']		= $pay;
		//print_r($orderDetail);die;
		$model1 	= M("order_detail");
		$result1 = $model1->add($orderDetail);
		$out_trade_no 		= $orderData['out_aplipay_no'];
		$total 					= $orderData['actulpay'];
		$body 					= "嘿谷商城";
		$subject 				= $orderDetail['gname'];
		if($result && $result1){
			$model->commit();
			$returnPay 		= array("code"=>"200","message"=>"下单成功,开始支付");
			$jfnums		= $priceArray['jfnum'];
			if(!empty($jfnums)){
				$this->DeductionIntl($userid,$jfnums);//下单如果使用积分扣除用户积分
			}
			if($pay == "app"){
				$returnPay['info'] 		= D("Pay")->SelectPay($payid,$out_trade_no,$total,$body,$subject="","",$userid);
			}else{
				$returnPay['info']      = D("Pay")->WebWxpay($body,$body,$out_trade_no,$total,"");
			}
			if($payid==3){
				$returnPay['message']	= "支付成功";
				unset($returnPay['info']);
			}
		}else{
			$model->rollback();
			$returnPay 		= array("code"=>"204","message"=>"下单失败");
		}
		return $returnPay;
	}

	/**
	 * @param $userid
	 * @param $useintl
	 * @return bool
	 * 检测用户是否可以使用积分支付
	 */
	public function CheckUserIntl($userid,$useintl){
		$userinfo 	= D("Register")->getUserBasicInfo2($userid);
		$intldata	= $userinfo['integral'];
		if($intldata < $useintl){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 订单结算第二步【购物车结算】
	 * @orderArray  需要结算的订单数据
	 * 更新 2017.05.22
	 */
	public function ShoppingCart($orderArray1,$userid,$payid,$addid,$isuseintl,$pay=""){
		//print_r($orderArray);die;
		$model=M("goods_order");
		$model2=M("order_detail");
		if(empty($orderArray1)){return array("code"=>"204","message"=>"请选择需要结算的商品");}
		//生成本次唯一订单号
		$out_aplipay_no 	= $this->CreateOrderNo();
		$orderArray 		 	= $orderArray1['cart'];
		for($i=0;$i<count($orderArray);$i++){
			//遍历处理单个商家的商品
			$result[$i] 	= $this->ShoppingCartA($orderArray[$i],$userid,$payid,$addid,$isuseintl,$out_aplipay_no,$pay);
			if(empty($result[$i])){
				$model->rollback();
				$model2->rollback();
				return array("code"=>"204","message"=>"结算失败");
			}
		}
		$total 		= 0;
		for ($i=0;$i<count($result);$i++){
			$total = $total + $result[$i];//本次购物车结算订单总价
		}
		//echo $total;die;
		//检测用户是否满足余额支付
		if($payid==3){
			if(!$this->CheckUserBalance($userid,$total)){return array("code"=>"204","message"=>"账户余额不足,请去我的订单中选择其他支付方式");}
		}
		$returnPay 		= array("code"=>"200","message"=>"下单成功,开始支付");
		if($payid==3){
			$returnPay['message']	= "支付成功";
		}
		$model->commit();
		$model2->commit();
		//通知支付宝或者微信 调起支付操作
		$body 						= "嘿谷商城";
		$subject 					= "嘿谷商城";
		$this->DumpCart($orderArray);//购物车结算成功清除用户购物车信息
		if($this->_carjfnum > 0){
			$jfnums		= $this->_carjfnum;
			if(!empty($jfnums)){
				$this->DeductionIntl($userid,$jfnums);//下单如果使用积分扣除用户积分
			}
		}
		if($pay!="app"){
			$returnPay['info']      = D("Pay")->WebWxpay($body,$body,$out_aplipay_no,$total,"");
		}else{
			$returnPay['info'] 		= D("Pay")->SelectPay($payid,$out_aplipay_no,$total,$body,$subject="","",$userid);
		}
		$this->_carjfnum= 0;
		return $returnPay;
	}
	/**
	 * 订单结算第三步【购物车计算】
	 * @array 需要处理的数组
	 * 更新 2017.05.22
	 */
	public function ShoppingCartA($array,$userid,$payid,$addid,$isuseintl,$out_aplipay_no,$pay=""){
		//print_r($array);die;
		if(empty($array)){return false;}
		//遍历处理购车中商品
		$bid 		= $array['bid'];//商家id
		$way 	= 0;//$array['way'];//配送方式id
		$msg 	=$array['mag'];//买家留言
		$cart 	= $array['cart'];//购物车数组
		$payname 		= $this->GetPayChinese($payid);
		if(empty($payname)){return false;}
		$data['freid'] 	= $way;
		$data['msg'] 		= $msg?$msg:"无";
		$data['payid'] 	= $payid;
		$data['paym'] 	= $payname;
		$data['bid'] 						= $bid;//商家id
		$data['orderno'] 				= $this->CreateOrderNo();//订单号
		$data['out_aplipay_no']  	= $out_aplipay_no;//发送给支付宝的订单
		//收货地址
		$addressInfo 	= $this->GetAddressInfo($addid);
		//print_r($addressInfo);die;
		if(empty($addressInfo)){return false;}
		$data['rpeople'] 		= $addressInfo['consignee_name'];
		$data['rphe'] 			= $addressInfo['consignee_phe'];
		$data['raddress'] 		= $addressInfo['remarks'];
		$data['userid'] 			= $userid;
		$goid= $this->CutInOrder($data);
		//print_r($data);die;
		if(empty($goid)){return false;}
		for($i=0;$i<count($cart);$i++){
			$result[$i] 	= $this->ShoppingCartB($cart[$i]['id'],$userid,$isuseintl,$goid,$cart[$i]['jf'],$way,$pay);
			if(!is_array($result[$i])){return false;}
		}
		//print_r($result);die;
		$total 		= 0;
		$freprice	= "";
		$goodstotal	= "";
		$actulpay	= "";
		$intlnum	= "";
		$intlprice	= "";
		$tctotal	= "";
		$yjtotal	= "";
		for ($i=0;$i<count($result);$i++){
			$freprice 		+= $result[$i]['freprice'];
			$goodstotal 	+= $result[$i]['gt'];
			$actulpay 		+= $result[$i]['gsjt'];
			$intlnum 		+= $result[$i]['jfnum'];
			$intlprice 		+= $result[$i]['jf'];
			$tctotal 		+= $result[$i]['tctotal'];
			$yjtotal			+= $result[$i]['yjtotal'];
		}
		$orderData['freprice'] 		= $freprice;
		$orderData['goodstotal'] 	= $goodstotal;
		$orderData['orderprice'] 	= $goodstotal + $freprice;
		$orderData['actulpay'] 		= $actulpay + $freprice;
		$orderData['intlnum'] 		= is_null($intlnum)?0:$intlnum;
		$orderData['intlprice'] 		= $intlprice;
		$orderData['tctotal'] 			= $tctotal;
		$orderData['yjtotal'] 			= $yjtotal;
		$total 								= $actulpay + $freprice;
		//是否使用积分
		if($intlnum['jf'] !=0 && $isuseintl ==1){
			$orderData['isintl'] = 1;
		}else{
			$orderData['isintl'] = 0;
		}
		//$a  = list();
		$orderData['otime'] = date("Y-m-d H:i:s");
		//print_r($orderData);die;
		$model 	= M("goods_order");
		$model->startTrans();
		$return2		= $model->where("goid=$goid")->save($orderData);//订单id
		if(is_bool($return2)){
			$model->rollback();
			return false;
		}else{
			$model->commit();
			return $total;
		}
		//return $total;
	}
	/**
	 * 订单结算第四步
	 * @bid 商家id
	 * @way  配送方式id
	 * @cartid  需要处理的数组
	 * 更新商品返利
	 * 2017.04.26
	 */
	public function ShoppingCartB($cartid,$userid1,$isuseintl,$goid,$jfnum,$freightId,$pay=""){
		//echo $cartid;die;
		if(empty($cartid)){return false;}
		$result 	= $this->GetCartGoodsInfo($cartid);//获取购物车中存储的商品信息
		//print_r($result);die;
		if(empty($result)){return false;}
		if(empty($jfnum)){$isuse=0;}else{$isuse=1;}
		$userid 	= $userid1;
		$goodsid 	= $result['godsid'];
		$gspecid 	= $result['specid'];//$result['spid'];购物车规格信息
		$gnum 		= $result['gnum'];
		//获取配送费
		//$freight 			= $this->GetDeliver("","odyid=$freightId");
		$freprice 			= 0;//$freight[0]['price'];//所选配送方式所需的价格
		//相关价格数组
		$priceArray 		= $this->SingleGoodsTotal($userid,$goodsid,$gspecid,$gnum,$isuse,$jfnum);
		//print_r($priceArray);die;
		$priceArray['freprice'] 		= $freprice;
		if(!is_array($priceArray)){return array("code"=>"204","message"=>"产品价格有误,请联系商家");}
		$specinfo 		= $this->GetSpecInfo($gspecid);
		if(empty($specinfo)){return array("code"=>"204","message"=>"规格不存在");}
		//插入订单明细信息
		$orderDetail['goid2'] 		= $goid;
		$orderDetail['godsid'] 	= $goodsid;
		$orderDetail['specid'] 	= $gspecid;//$gspecid;
		$orderDetail['gname'] 	= $result['gname'];
		$orderDetail['gspec'] 	= $specinfo['gsdesc'];//"无规格";
		$orderDetail['gnum'] 	=	$gnum;
		$orderDetail['userid'] 	= $userid;
		$orderDetail['gprices'] 	= $specinfo['gprice'];
		$godsIsUseJf 				= $result['isintpay'];
		$orderDetail['trade_type']	= $pay;
		//默认积分数量是0
		/*if($isuseintl ==1 && $godsIsUseJf ==1  &&  $jfnum !=0){
			$reintl 			= 0;
			$isretotal 		= 0;
			$rebatetotal 	= 0;
			$remaintotal = 0;
		}else{
		    $reintl   =0;
		    $isretotal =0;
		    $rebatetotal=0;
		    $remaintotal =0;
		}*/
		if($this->states==2){
			$reintl = 0;
		}else{
			$reintl = intval($priceArray["gsjt"]);
		}
		$orderDetail['reintl'] 				= $reintl;
		$orderDetail['isretotal']			= 0;
		$orderDetail['rebatetotal'] 	= 0;
		$orderDetail['remaintotal'] 	= 0;
		$orderDetail['sjtime'] 			= date("Y-m-d H:i:s");
		//print_r($orderDetail);die;
		$model1 	= M("order_detail");
		$model1->startTrans();
		$result1 = $model1->add($orderDetail);
		if($result1){
			$model1->commit();
			//$total = $orderData['actulpay'];
			$jfnums	= $this->_carjfnum + intval($priceArray['jfnum']);
			$this->_carjfnum	= $jfnums;
			return $priceArray;
		}else{
			$model1->rollback();
			return false;
		}
	}
	/**
	 * 购物车结算（清空用户已经结算的购物车商品信息）
	 * @param unknown $id
	 */
	public function DumpCart($orderarray){
		$cart = array();
		$cartid = "";
		if(!empty($orderarray)){
			$model 	= M("goods_cart");
			for ($i=0;$i<count($orderarray);$i++){
				$cart = $orderarray[$i]['cart'];
				for ($j=0;$j<count($cart);$j++){
					$cartid = $cart[$j]['id'];
					$model->where("gctid=$cartid")->delete();
				}
			}
		}
	}
	/**
	 * 插入订单表数据
	 * @orderarray  需要插入数据库的订单数组
	 * 2017.02.22
	 */
	public function CutInOrder($orderarray){
		$mode 		= M("goods_order");
		$result 		= $mode->add($orderarray);
		//echo $mode->getLastSql();die;
		return returnArrayBool($result);
	}
	/************************************************代付款订单再次支付**********************************************/
	/**
	 * 代付款订单重新支付
	 * @param unknown $userid
	 * @param unknown $orderid
	 * @param unknown $payid
	 * 2017.05.25
	 */
	public function PayAgin($userid,$orderid,$payid,$trade_type){
		$save['paym']  = $this->GetPayChinese($payid);
		$save['payid'] = $payid;
		$save['otime'] = date("Y-m-d H:i:s");
		$save['out_aplipay_no']    = $this->CreateOrderNo();
		$where         = "goid =$orderid AND userid=$userid AND ispay=1";
		$field         = "orderno,actulpay";
		$orderInfo     = $this->GetOrderInfo("",$field,$where,"");
		if(empty($orderInfo)){return reTurnJSONArray("204", "订单不存在");}
		$model = M("goods_order");
		$result= $model->where("goid=$orderid")->save($save);
		if($payid==1){
			$url   = "http://www.heigushop.com/index.php/Pay/AlipayNotify";
		}elseif($payid==2){
			$url   = "http://www.heigushop.com/index.php/Pay/WxNotify";
		}elseif ($payid==3){
			//检测用户是否满足余额支付
			if(!$this->CheckUserBalance($userid,$orderInfo[0]['actulpay'])){return array("code"=>"204","message"=>"账户余额不足");}
			$url   = "";
		}
		if(!empty($trade_type)){
			$returnPay  = D("Pay")->WebWxpay("嘿谷商城","嘿谷商城订单付款",$save['out_aplipay_no'],$orderInfo[0]['actulpay'],"");
		}else{
			$returnPay  = D("Pay")->SelectPay($payid,$save['out_aplipay_no'],$orderInfo[0]['actulpay'],"嘿谷商城",$subject="嘿谷商城订单付款",$url,$userid);
		}
		if(!empty($returnPay)){
			$returnData		= array("code"=>"200","message"=>"开始支付","info"=>$returnPay);
		}else{
			$returnData 		= array("code"=>"204","message"=>"系统错误,请稍候重试");
		}
		return $returnData;
	}
	/**
	 * 再次付款回调
	 * @param unknown $orderno
	 * @return boolean
	 * 2017.05.25
	 */
	public function Handle_Order_Logic2($orderno,$state=""){
		$where = "goods_order.orderno='".$orderno."'";
		$field = "goid,bid,godsid,gnum,reintl,gname,odid,goods_order.userid,goodstotal,freprice";
		$orderInfo 	= $this->GetOrderInfo($orderno,$field,$where);
		if(empty($orderInfo)){return false;}
		for($i=0;$i<count($orderInfo);$i++){
			$this->UpdateOrderIsPayFromId($orderInfo[$i]['goid']);//修改订单中子订单的支付状态
			//$this->UpdateBusineAndGoodsInfo($orderInfo[$i]['bid'],$orderInfo[$i]['godsid'],$orderInfo[$i]['gnum'],0);//修改涉及到的商家和商品的库存以及销量
			//$this-> Join_Defray($orderInfo[$i]['odid'],$orderInfo[$i]['reintl'],0);//加入本次送积分和返利记录
			//$this->JfToUser($orderInfo[$i]['reintl'], $orderInfo[$i]['userid']);//支付成功 将积分到用户账户
		}
		$orderInfo2 	= $this->GetOrderInfo($orderno,"","","");
		//余额支付直接执行回调
		if(!empty($state)){
			$this->UpdateUserAccount($orderInfo2[0]['actulpay'],$orderInfo2[0]['userid']);
		}
		//加入收入统计表
		for($i=0;$i<count($orderInfo2);$i++){
			$this->Join_Income($orderInfo2[$i]['goid'], $orderInfo2[$i]['goodstotal'], $orderInfo2[$i]['freprice']);
		}
		$userid    = D("Common")->getUserIdFromBid($orderInfo[0]['bid']);
		D("Common")->PushSystem("用户下单","用户在您的商店购买了商品,请及时发货",2,$userid);//用户下单通知商家站内信
		$userphone	= D("Common")->GetSellerPhoneFromBusinessId($orderInfo[0]['bid']);
		if(!empty($userid)){
			D("Function")->jpushonly($userid,"用户在您的商店购买了商品,请及时发货",array("isseller"=>1,"state"=>0));//极光推送
		}
		if(!empty($userphone)){
			$content	= "【嘿谷商城】提示您：用户在您商店购买了商品,请及时发货";
			D("Function")->sendPhoneCodeFromOrder($userphone,$content);
		}
		return true;
	}
	/************************************************商品结算回调**************************************************/
	/**
	 * 订单支付成功需要处理的业务逻辑
	 * @orderno  订单号
	 * 2017.05.25
	 * 更新 去除返利现金 和规格
	 */
	public function Handle_Order_Logic($orderno,$state="",$total="",$userid=""){
		$orderInfo 	= $this->GetOrderInfo($orderno,"goid,bid,godsid,gname,gnum,reintl,odid,goods_order.userid,goodstotal,freprice,specid");
		if(empty($orderInfo)){return false;}
		for($i=0;$i<count($orderInfo);$i++){
			$this->UpdateOrderIsPayFromId($orderInfo[$i]['goid']);//修改订单中子订单的支付状态
			$this->updateGoodsNum($orderInfo[$i]['gnum'],$orderInfo[$i]['godsid'],"-",$orderInfo[$i]['specid']);//修改商品库存
		}
		$orderInfo2 	= $this->GetOrderInfo($orderno,"","","");
		//加入收入统计表
		for($i=0;$i<count($orderInfo2);$i++){
			$this->Join_Income($orderInfo2[$i]['goid'], $orderInfo2[$i]['goodstotal'], $orderInfo2[$i]['freprice']);
		}
		//余额支付直接执行回调
		if(!empty($state)){
			$this->UpdateUserAccount($total,$userid);
		}
		$model		= M("goods_order");
		$orderInfo3	= $model->field("bid,intlnum,userid")->where("out_aplipay_no=$orderno")->select();
		$intlnum	= 0;
		if(!empty($orderInfo3)){
			for($j=0;$j<count($orderInfo3);$j++){
				//扣除积分
				if($orderInfo3[$j]['intlnum']!=0){
					$intlnum	+= $orderInfo3[$j]['intlnum'];
				}
				//生成站内信和短信
				$userid    = D("Common")->getUserIdFromBid($orderInfo3[$j]['bid']);
				D("Common")->PushSystem("用户下单","用户在您的商店购买了商品,请及时发货",2,$userid);//用户下单通知商家站内信
				$userphone	= D("Common")->GetSellerPhoneFromBusinessId($orderInfo3[$j]['bid']);
				if(!empty($userid)){
					D("Function")->jpushonly($userid,"用户在您的商店购买了商品,请及时发货",array("isseller"=>1,"state"=>0));//极光推送
				}
				if(!empty($userphone)){
					$content	= "【嘿谷商城】提示您：用户在您商店购买了商品,请及时发货";
					D("Function")->sendPhoneCodeFromOrder($userphone,$content);
				}
			}
		}
		//执行扣除积分操作
		//$this->DeductionIntl($orderInfo3[0]['userid'],$intlnum);
		return true;
		//开始将本次数据加入统计
	}
	/**
	 * 余额支付成功更改用户余额
	 * @param unknown $total
	 * @param unknown $userid
	 */
	public function UpdateUserAccount($total,$userid){
		$userInfo  = M("user")->field("account")->where("user_id=$userid")->find();
		$account   = $userInfo['account'];
		$newaccount= (($account*100)-($total*100))/100;
		M("user")->execute("update user set account=$newaccount where user_id=$userid");
	}
	/**
	 * 购买成功之后将积分数据导入用户数据中
	 * @param unknown $reintl
	 * @param unknown $userid
	 * @return Ambigous <boolean, unknown>
	 */
	public function JfToUser($reintl,$userid,$state=""){
		if($state==3){
			return true;
		}
		if($reintl==0){
			return true;
		}
		if($this->states==2){
			return true;
		}
		$sql ="update user  set `integral`=`integral`+{$reintl} where user_id={$userid}";
		$model 	= new \Think\Model();
		$result 		= $model->execute($sql);
		return true;
	}
	/**
	 * 加入收入统计表
	 * @orderid  订单表id
	 * @goodstotal  实付商品总价
	 * @fretotal  运费
	 * 2017.02.22
	 */
	public function Join_Income($orderid,$goodstotal,$fretotal){
		$sql ="insert into income_record(`orderid`,`goodstotal`,`fretotal`,`time`) values($orderid,'".$goodstotal."','".$fretotal."','".date("Y-m-d")."')";
		$model 	= new \Think\Model();
		$result 		= $model->execute($sql);
		return returnArrayBool($result);
	}
	/**
	 * 加入支出统计表
	 * @orderid  订单表明细id
	 * @intlnum  送的积分数量
	 * @rebate   返现金的总数量
	 * 2017.02.22
	 */
	public function  Join_Defray($orderid,$intlnum,$rebate){
		$sql ="insert into defray_record(`orderid`,`intlnum`,`rebate`,`time`) values($orderid,$intlnum,'".$rebate."','".date("Y-m-d")."')";
		$model 	= new \Think\Model();
		$result 		= $model->execute($sql);
		return returnArrayBool($result);
	}
	/**
	 * 根据订单号返回后边处理逻辑问题的参数值
	 * @orderno  订单号
	 * @field     需要显示的字段
	 * @where  查询条件
	 * 2017.02.21
	 */
	public function GetOrderInfo($orderno="",$field="",$where="",$join="left join order_detail on goods_order.goid = order_detail.goid2"){
		$field 		= $field?$field:"";
		$where 	= $where?$where:"out_aplipay_no='$orderno'";
		$model 	= M("goods_order");
		$result 		= $model->field($field)->join($join)->where($where)->select();
		//echo $model->getLastSql();die;
		return returnArrayBool($result);
	}
	/**
	 * 根据订单id修改订单状态
	 * @orderId  订单id
	 * 2017.02.21
	 */
	public function UpdateOrderIsPayFromId($orderId){
		$model 	= new \Think\Model();
		$model ->startTrans();
		$re1 			= $model->execute("update goods_order set ispay=2 where goid=$orderId");
		$re2 			= $model->execute("update order_detail set ispay=2 where goid2=$orderId");
		if($re1 && $re2){
			$model->commit();
		}else{
			$model->rollback();
		}
	}
	/**
	 * 修改商家销量  商品销量 以及商品的库存 以及插入一条销售记录
	 * @bid 商家id
	 * @gnum  商品数量
	 * @gid  商品Id
	 * @gspecid  商品规格id
	 * 2017.05.25
	 */
	public  function UpdateBusineAndGoodsInfo($bid,$gid,$gnum,$gspecid=""){
		$model 	= new \Think\Model();
		$model->startTrans();
		//$re1 			= $model->execute("update business_info set `sale`=`sale`+$gnum where b_id=$bid");//修改商销量
		$re2 			= $model->execute("update goods set `sales`=`sales`+$gnum where gid=$gid");//修改商品销量信息
		$re4 			= $model->execute("insert into sale_record(`bid`,`gid`,`srtime`,`number`) values($bid,$gid,'".date("Y-m-d")."',$gnum)");
		if($re2  && $re4){
			$model->commit();
		}else{
			$model->rollback();
		}

	}

	/**
	 * @param $gnum
	 * @param $gid
	 * 修改商品库存
	 */
	public function updateGoodsNum($gnum,$gid,$str,$specid=""){
		$model 	= new \Think\Model();
		$re3 			= $model->execute("update goods set `goods_number` = `goods_number` $str  $gnum where gid=$gid");//修改商品的库存信息
		$res4			= $model->execute("update goods_spec set `gstock`=`gstock` $str $gnum WHERE gsid={$specid}}");
	}
	/*********************************************结算回调********************************************************/
	/**
	 * 计算单个商品的付款总金额
	 * @userid   用户id
	 * @goodsid  商品id
	 * @specid     规格id
	 * @gnum      商品数量
	 * @useintl    是否使用积分
	 * @intlnum   积分数量
	 * @freightId  发货方式id
	 * 返回参数：总价  实际付款金额 积分抵消总额  使用积分数量
	 * 2017.02.18
	 * 更新默认可以使用积分代付
	 * 更新 2017.05.20
	 */
	public function SingleGoodsTotal($userid,$goodsid,$specid,$gnum,$useintl,$intlnum){
		//获取商品信息
		$goodsinfo 	= $this->GetGoodsInfoFromGid($goodsid);
		//print_r($goodsinfo);die;
		if(empty($goodsinfo)){return false;}
		$isintlpay 		= $goodsinfo['isintpay'];//是否可以使用积分支付  0否 1是
		$useintlbl 		= $goodsinfo['useintl'];//可使用积分比例
		$jftotal 			= 0;//积分抵消的价格
		$tcbl 			= $goodsinfo['tc'];//提成比例
		//获取商品规格信息
		$specInfo 		= $this->GetSpecInfo($specid);
		if(empty($specInfo)){return false;}
		$dataprice      = $specInfo['gprice'];//$goodsinfo['gprice'];对应规格价格
		$goodsIsUsejfNum 	= $specInfo['jfnum'] * $gnum;//$goodsinfo['intl_num'];//floor($dataprice*$useintlbl);//不以单个  有多少使用多少
		if($intlnum > $goodsIsUsejfNum){return false;}
		$goodsprice 	= $dataprice;//商品单价
		$useIntlPay     = $goodsIsUsejfNum;
		if($useIntlPay < $intlnum){return false;}
		$gstock 		= $specInfo['gstock'];//$goodsinfo['gnum'];//商品库存
		/*if($useintl!=1){*/
			$jftotal=$this->returnIntlToPrice($intlnum);//计算实际抵消的价格
		/*}else{
			$intlnum	= 0;
			$jftotal=0;
		}*/
		//检查商品库存 以及购买商品的数量是否正确
		if($gnum >$gstock || $gstock <=0 || $gnum <=0){return false;}
		//检查运费
		$goodsTotal =math_mul($goodsprice,$gnum,2);//总金额
		$goodsSjTotal =  math_sub($goodsTotal,$jftotal,2);//实际付款金额
		$intlnum        = $intlnum?$intlnum:0;
		//不允许假订单的出现
		if($goodsTotal==0 || $goodsTotal=="0.00" || $goodsTotal <0){
			return false;
		}
		$total =array('gt'=>$goodsTotal,"gsjt"=>$goodsSjTotal,"jf"=>$jftotal,"jfnum"=>$intlnum);
		return $total;
	}
	/**
	 * 获取支付方式的中文信息
	 * @id  传入支付方式id
	 * 2017.02.20
	 */
	public function GetPayChinese($id){
		switch ($id){
			case 1:
				$pname = "支付宝";
				break;
			case 2:
				$pname = "微信支付";
				break;
			case 3:
				$pname = "余额支付";
				break;
			default:
				return false;
		}
		return $pname;
	}
	/**
	 * 通过userid返回用户信息
	 * @userid 用户id信息
	 * 2017.02.18
	 */
	public function GetUserInfo($userid,$field="",$where=""){
		$field 	= $field?$field:"";
		$where = $where?$where:"user_id=$userid";
		$model = 	M("user");
		$result 	= $model->field($field)->where($where)->find();
		return returnArrayBool($result);
	}
	/**
	 * 判断用户的剩余积分是否供此次消费
	 * @userIntlNum 使用积分数量
	 * @userid           用户id
	 * 2017.02.20
	 */
	public function IsConsume($useIntlNum,$userid){
		$userinfo 	= $this->GetUserInfo($userid);
		if(empty($userinfo)){return false;}
		$userjf 		= $userinfo['integral'];
		if((int)$userjf < (int)$useIntlNum){return false;}else{return true;}
	}
	/**
	 *支付成功扣除用户积分
	 */
	public function DeductionIntl($userid,$intl){
		$userinfo 	= $this->GetUserInfo($userid);
		if(empty($userinfo)){return false;}
		$userjf 		= $userinfo['integral'];
		$save['integral']	= math_sub($userjf,$intl,2);
		$userid			= $userinfo['user_id'];
		M("user")->where("user_id=$userid")->save($save);
	}
	/**
	 * 根据积分数量返回积分抵消的金额
	 * @intl   积分
	 * 2017.02.18
	 */
	public function returnIntlToPrice($intl){
		if(empty($intl) || empty($this->_IntlRatio)){return 0;}
		$jfprice 	=math_mul($intl,$this->_IntlRatio,2);
		return $jfprice;
	}
	/**
	 * 设置积分配置参数
	 * 2017.02.18
	 */
	public function SetIntlRatio(){
		if($this->_IntlRatio ==0){$this->_IntlRatio=0;return true;}
		if(!empty($this->_IntlRatio)){return true;}
		$model = M("publicsetting");
		$result 		= $model->field("content")->where("`set`='intl'")->find();
		if(is_bool($result)){
			$this->_IntlRatio =0;
		}else{
			if(!empty($result)){
				$this->_IntlRatio =$result['content'];
			}else{
				$this->_IntlRatio =0;
			}
		}
	}
	/**
	 * 根据购物车记录id返回结算所需要的数据
	 * @cartid 购物车记录id
	 * @field 需要查询的数据
	 * @where  查询条件
	 * 2017.02.17
	 */
	public function GetCartGoodsInfo($cartid="",$field="",$where="",$method=""){
		$field 	= $field?$field:"godsid,userid,gname,bid,intl,isintpay,useintl,goods_cart.gnum,givintl,goods.`gprice`,specid";
		$where = $where?$where:"gctid=$cartid";
		$method = $method?$method:0;
		$model = M("goods_cart");
		if(empty($method)){
			$result 	= $model->join("goods on goods.gid=goods_cart.godsid")->field($field)->where($where)->find();
		}else{
			$result 	= $model->join("goods on goods.gid=goods_cart.godsid")->field($field)->where($where)->select();
		}
		return returnArrayBool($result);
	}
	/**
	 * 根据商品id返回商品的数据
	 * @gid   商品id
	 * 2017.02.18
	 */
	public function GetGoodsInfoFromGid($gid,$field="",$where=""){
		$field 	= $field?$field:"gid,gname,bid,intl,gprice,isintpay,useintl,givintl,goods_number as gnum,intl_num";
		$where = $where?$where:"gid=$gid";
		$model = M("goods");
		$result 		= $model->field($field)->where($where)->find();
		return returnArrayBool($result);
	}
	/**
	 * 根据收货地址id 返回详细信息
	 * @adid 收货地址id
	 * 2017.02.17
	 */
	public function GetAddressInfo($adid,$field="",$where=""){
		$field 	= $field?$field:"*";
		$where = $where?$where:"address_id=$adid";
		$model = M("user_addressinfo");
		$result 	= $model->field($field)->where($where)->find();
		return returnArrayBool($result);
	}
	/**
	 * 根据规格id返回对应的价格和库存
	 * @specid  规格id
	 * @field    需要查询的字段
	 * @where  查询条件
	 * 2017.02.18
	 */
	Public  function  GetSpecInfo($specid,$field="",$where=""){
		$field 	= $field?$field:"gsid,gsdesc,gstock,gprice,jfnum,ccprice";
		$model = M("goods_spec");
		$where 	= $where?$where:"gsid=$specid";
		$result 		= $model->field($field)->where($where)->find();
		return returnArrayBool($result);
	}
	/**
	 * 生成订单号
	 *2017.05.25
	 */
	public function CreateOrderNo(){
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
	 * 我的订单[用户]
	 * 2017.03.30
	 * @param unknown $userid
	 * @param unknown $PageIndex
	 * @param $orderstate订单状态 0全部 1代付款 2代发货3待收获4待评价
	 * @return Ambigous <string, multitype:, unknown>
	 * 更新 2017.05.22
	 */
	public function MyOrderList($userid,$PageIndex,$orderstate="",$callback=""){
		$PageSize = "50";
		$orderstate   = $orderstate?$orderstate:0;
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr	="";
		$WhereStr .= "  AND goods_order.userid = $userid ";
		//goods_order.userid=$userid AND goods_order.ispay=2 AND order_detail.isfh=2 AND issh=1 AND goods_order.isdel=0 and order_detail.isth!=3
		$Where1    = "";
		if(!empty($callback)){
			$Where =" AND goods_order.payid=2";
		}
		if($orderstate==1){
			$WhereStr .=" AND goods_order.ispay=1 ";
		}elseif($orderstate==2){
			$WhereStr .=" AND goods_order.ispay=2 AND order_detail.isfh=1 AND order_detail.isth !=3".$Where1;
		}elseif($orderstate==3){
			$WhereStr .=" AND goods_order.ispay=2 AND order_detail.isfh=2 AND order_detail.issh=1 AND order_detail.isth !=3".$Where1;
		}elseif ($orderstate==4){
			$WhereStr .=" AND goods_order.ispay=2 AND order_detail.isfh=2 AND order_detail.issh=2  AND goods_order.israte=1 AND order_detail.isth !=3".$Where1;
		}

		$WhereStr .=" AND isdel =0 AND  Not ISNULL(odid)";
		$this->_AdminUrl = D("Common")->getUrl();
		$joinid = "goid";
		$join = "LEFT JOIN order_detail ON goods_order.goid =order_detail.goid2 LEFT JOIN goods ON order_detail.godsid=goods.gid LEFT JOIN business_info ON goods.bid=business_info.b_id";
		$Coll="refund_reason as th_reason,isth as th,seller_id,order_detail.odid,goods.gname,goods_order.israte as rate,issh as sh,order_detail.isfh as fh,goods_order.ispay as pay,order_detail.gnum,goods_order.otime as ordertime,goods_order.bid,order_detail.godsid,REPLACE(goods.gphoto,'/Uploads','".$this->_AdminUrl."/Uploads') as gphoto,goods_order.orderprice,order_detail.gprices,order_detail.goid2,REPLACE(`blogo`,'/Uploads','".$this->_AdminUrl."/Uploads') AS shopimg,gspec as spectitle";
		$sql = SqlStr2($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid);
		//echo $sql;die;
		$model = M("goods_order");
		$list  = $model->query($sql);
		//print_r($list);die;
		$textArray    = array("全部订单","代付款订单","待发货订单","待收货订单","待评价订单");
		if(is_bool($list)){
			$returnData['code'] = 204;
			$returnData['message'] = $textArray[$orderstate]."找不到了";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = $textArray[$orderstate]."获取成功";
			if($orderstate==3){
				$returnData['info']    = $this->HandleMyorder_IsDeliver($list,1);
			}else{
				$returnData['info']    = $this->HandleMyorder($list,1);
			}

		}
		return $returnData;
	}
	/**
	 * 我的订单[商家]
	 * @param unknown $userid
	 * @param unknown $PageIndex
	 * @param $orderstate订单状态 0待处理 1已完成
	 * @return Ambigous <string, multitype:, unknown>
	 * 更新 2017.05.23
	 */
	public function ShopOrderList($userid,$PageIndex,$orderstate="",$sellerid){
		$PageSize = "10";
		$orderstate   = $orderstate?$orderstate:0;
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr	="";
		$WhereStr .= "  AND business_info.seller_id = $sellerid AND goods_order.goodstotal !='0.00'";
		$WhereStr2 = " AND business_info.seller_id = $sellerid";
		if($orderstate==0){
			$WhereStr .=" AND goods_order.ispay=2 AND order_detail.isfh=1 AND order_detail.isth=1";//待处理发货
			$WhereStr2 .=" AND goods_order.ispay=2  AND order_detail.isth=2";//待处理退货
		}elseif ($orderstate==1){
			$WhereStr .=" AND ((goods_order.ispay=2 AND order_detail.isfh=2 AND order_detail.issh=1) or (goods_order.ispay=2 AND order_detail.isfh=2 AND order_detail.issh=2) OR (goods_order.ispay=2 AND order_detail.issh=1 AND order_detail.isth=3))";
		}
		$WhereStr .=" AND isdel =0 AND  Not ISNULL(odid)";
		$WhereStr2.=" AND isdel =0 AND  Not ISNULL(odid)";
		$this->_AdminUrl =D("Common")->getUrl();
		$joinid = "goid";
		$join = "Inner JOIN order_detail ON goods_order.goid =order_detail.goid2 LEFT JOIN goods ON order_detail.godsid=goods.gid LEFT JOIN business_info ON goods.bid=business_info.b_id";
		$Coll="order_detail.odid,business_info.seller_id,refund_reason as th_reason,isth as th,orderno as order_no,rpeople as shr,rphe as shrphe,raddress as shrdz,msg as shrbz,goods.gname,goods_order.israte as rate,order_detail.issh as sh,isfh as fh,goods_order.ispay as pay,order_detail.gnum,goods_order.otime as ordertime,goods_order.bid,order_detail.godsid,REPLACE(goods.gphoto,'/Uploads','".$this->_AdminUrl."/Uploads') as gphoto,goods_order.orderprice,order_detail.gprices,order_detail.goid2,REPLACE(`blogo`,'/Uploads','".$this->_AdminUrl."/Uploads') AS shopimg,gspec as spectitle";
		$sql2 = SqlStr2($TableName="goods_order",$Coll,$WhereStr2,$WhereStr2,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid,"",$WhereStr2);
		$sql = SqlStr2($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid,"",$WhereStr);
		/* echo $sql;
        echo $sql2;die; */
		$model = M("goods_order");
		$list1  = $model->query($sql);
		$info   = array();
		$textArray    = array("待处理订单","已完成订单");
		if($orderstate==1){
			if(!is_bool($list1)){
				$returnData['code'] = 200;
				$returnData['message'] = $textArray[$orderstate]."获取成功";
				$info1   = $this->HandleMyorder2($list1,2);
				if(!empty($info1)){
					$info	= $info1;
				}
				$returnData['info']    = $info;
			}else{
				$returnData['code'] = 204;
				$returnData['message'] = $textArray[$orderstate]."";
			}
			return $returnData;
		}
		$list2  = $model->query($sql2);
		if(is_bool($list1) && is_bool($list2)){
			$returnData['code'] = 204;
			$returnData['message'] = $textArray[$orderstate]."";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = $textArray[$orderstate]."获取成功";
			$info1   = $this->HandleMyorder2($list1,2);
			if(!is_bool($list2) && !empty($list2)){
				$info2 = $this->HandleMyorder2($list2, 2);
			}
			if(!empty($info1)){
				if(!empty($info2)){
					$info  = array_merge($info1,$info2);
				}else{
					$info  = $info1;
				}
			}else{
				if(!empty($info2)){
					$info  = $info2;
				}
			}
			$returnData['info']    = $info;
		}
		return $returnData;
	}
	/**
	 * 将购物车信息商品同属一个商家的归于一类
	 * @array  数据数组
	 * 2017.02.13
	 */
	public function HandleMyorder2($array,$state){
		if(empty($array)){return array();}
		foreach($array as $k=>$v) {
			$array3[$v["odid"]]['sellerid'] = $v['seller_id'];
			$array3[$v['odid']]['seller'] = D("Seller")->GetSellerName($v['bid']);
			$array3[$v['odid']]['shopimg'] = $v['shopimg'];
			$array3[$v['odid']]['goid']  = $v['goid2'];
			$array3[$v['odid']]["total"] = bcmul($v['gprices'],$v['gnum'],2);
			$array3[$v['odid']]["ordertime"] = $v['ordertime'];
			$array3[$v['odid']]["rate"] = $v['rate'];
			//$array3[$v['odid']]["total"] = sprintf("%.2f",$array3[$v['odid']]["total"]);
			$array3[$v['odid']]['number']   += $v['gnum'];
			if($state ==1){
				if($v['pay'] == 2 && $v['fh']==2 && $v['sh'] ==1 && $v['rate']==1){
					$array3[$v['odid']]['orderstate']   = 3;
				}elseif ($v['pay'] ==2 && $v['fh']==1){
					$array3[$v['odid']]['orderstate']   = 2;
				}elseif ($v['pay']==1){
					$array3[$v['odid']]['orderstate']   = 1;
				}elseif ($v['pay']==2  && $v['fh']==2 && $v['sh']==2){
					$array3[$v['odid']]['orderstate']   = 4;
				}
				unset($v['th_reason']);
			}
			// 		    $array3[$v['goid2']]['pay'] = $v['pay'];
			// 		    $array3[$v['goid2']]['fh'] = $v['fh'];
			// 		    $array3[$v['goid2']]['sh'] = $v['sh'];
			// 		    $array3[$v['goid2']]['rate'] = $v['rate'];
			//$array3[$v['goid2']]['th'] = $v['th']?$v['th']:"";
			if($state ==2){
				$array3[$v['odid']]['order_no'] = $v['order_no']?$v['order_no']:"";
				$array3[$v['odid']]['shr'] = $v['shr']?$v['shr']:"";
				$array3[$v['odid']]['shrphe'] = $v['shrphe']?$v['shrphe']:"";
				$array3[$v['odid']]['shrdz'] = $v['shrdz']?$v['shrdz']:"";
				$array3[$v['odid']]['shrbz'] = $v['shrbz']?$v['shrbz']:"";
				$array3[$v['odid']]['odid'] = $v['odid'];
				//$array3[$v]
				if (($v['pay'] ==2 && $v['fh']==1 && $v['th']==1)){
					$array3[$v['odid']]['orderstate']   = 5;//发货
				}elseif (($v['pay'] ==2  && $v['sh']==1 && $v['th']==2)){
					$array3[$v['odid']]['orderstate']   = 6;//退货
				}elseif (($v['pay'] ==2 && $v['fh']==2 && $v['sh']==2) || ($v['pay'] ==2 && $v['fh']==2 && $v['sh']==1 && $v['th']==3)){
					$array3[$v['odid']]['orderstate']   = 7;//已经完成
				}
				unset($v['order_no']);
				unset($v['shr']);
				unset($v['shrphe']);
				unset($v['shrdz']);
				unset($v['shrbz']);
			}
			//unset($v['th']);
			unset($v['shopimg']);
			unset($v['bid']);
			unset($v['pay']);
			unset($v['fh']);
			unset($v['sh']);
			unset($v['rate']);
			unset($v['ordertime']);
			unset($v['orderprice']);
			$array3[$v['odid']]['order'][] = $v;
		}
		return  array_merge($array3);
	}
	/**
	 * 将购物车信息商品同属一个商家的归于一类
	 * @array  数据数组
	 * 2017.02.13
	 */
	public function HandleMyorder($array,$state){
		if(empty($array)){return array();}
		foreach($array as $k=>$v) {
			$array3[$v["goid2"]]['sellerid'] = $v['seller_id'];
			$array3[$v['goid2']]['seller'] = D("Seller")->GetSellerName($v['bid']);
			$array3[$v['goid2']]['bid'] = $v['bid'];
			$array3[$v['goid2']]['shopimg'] = $v['shopimg'];
			$array3[$v['goid2']]['goid']  = $v['goid2'];
			$array3[$v['goid2']]["total"] = $v['orderprice'];
			$array3[$v['goid2']]["ordertime"] = $v['ordertime'];
			$array3[$v['goid2']]["rate"] = $v['rate'];
			$array3[$v['goid2']]["total"] = sprintf("%.2f",$array3[$v['goid2']]["total"]);
			$array3[$v['goid2']]['number']   += $v['gnum'];
			$array3[$v['goid2']]['th']   = $v['th'];
			if($state ==1){
				if($v['pay'] == 2 && $v['fh']==2 && $v['sh'] ==1 && $v['rate']==1){
					$array3[$v['goid2']]['orderstate']   = 3;
				}elseif ($v['pay'] ==2 && $v['fh']==1){
					$array3[$v['goid2']]['orderstate']   = 2;
				}elseif ($v['pay']==1){
					$array3[$v['goid2']]['orderstate']   = 1;
				}elseif ($v['pay']==2  && $v['fh']==2 && $v['sh']==2){
					$array3[$v['goid2']]['orderstate']   = 4;
				}
				unset($v['th_reason']);
			}
// 		    $array3[$v['goid2']]['pay'] = $v['pay'];
// 		    $array3[$v['goid2']]['fh'] = $v['fh'];
// 		    $array3[$v['goid2']]['sh'] = $v['sh'];
// 		    $array3[$v['goid2']]['rate'] = $v['rate'];
			//$array3[$v['goid2']]['th'] = $v['th']?$v['th']:"";
			if($state ==2){
				$array3[$v['goid2']]['order_no'] = $v['order_no']?$v['order_no']:"";
				$array3[$v['goid2']]['shr'] = $v['shr']?$v['shr']:"";
				$array3[$v['goid2']]['shrphe'] = $v['shrphe']?$v['shrphe']:"";
				$array3[$v['goid2']]['shrdz'] = $v['shrdz']?$v['shrdz']:"";
				$array3[$v['goid2']]['shrbz'] = $v['shrbz']?$v['shrbz']:"";
				$array3[$v['goid2']]['th_reason'] = $v['th_reason']?$v['th_reason']:"";
				$array3[$v['odid']]['odid'] = $v['odid'];
				//$array3[$v]
				if (($v['pay'] ==2 && $v['fh']==1)){
					$array3[$v['goid2']]['orderstate']   = 5;//发货
				}elseif (($v['pay'] ==2 && $v['fh']==2 && $v['sh']==1 && $v['th']==2)){
					$array3[$v['goid2']]['orderstate']   = 6;//退货
				}elseif (($v['pay'] ==2 && $v['fh']==2 && $v['sh']==2) || ($v['pay'] ==2 && $v['fh']==2 && $v['sh']==1 && $v['th']==3)){
					$array3[$v['goid2']]['orderstate']   = 7;//已经完成
				}
				unset($v['order_no']);
				unset($v['shr']);
				unset($v['shrphe']);
				unset($v['shrdz']);
				unset($v['shrbz']);
			}
			//unset($v['th']);
			unset($v['shopimg']);
			unset($v['bid']);
			unset($v['pay']);
			unset($v['fh']);
			unset($v['sh']);
			unset($v['rate']);
			unset($v['ordertime']);
			unset($v['orderprice']);
			$array3[$v['goid2']]['order'][] = $v;
		}
		return  array_merge($array3);
	}

	/**
	 * 代发货订单
	 * @param $array
	 * @param $state
	 * @return array
	 * 2017年7月11日
	 */
	public function HandleMyorder_IsDeliver($array,$state){
		if(empty($array)){return array();}
		foreach($array as $k=>$v) {
			$array3[$v["odid"]]['sellerid'] = $v['seller_id'];
			$array3[$v['odid']]['seller'] = D("Seller")->GetSellerName($v['bid']);
			$array3[$v['odid']]['bid'] = $v['bid'];
			$array3[$v['odid']]['shopimg'] = $v['shopimg'];
			$array3[$v['odid']]['goid']  = $v['goid2'];
			$array3[$v['odid']]["total"] = bcmul($v['gprices'],$v['gnum'],2);
			$array3[$v['odid']]["ordertime"] = $v['ordertime'];
			$array3[$v['odid']]["rate"] = $v['rate'];
			//$array3[$v['odid']]["total"] = sprintf("%.2f",$array3[$v['odid']]["total"]);
			$array3[$v['odid']]['number']   = $v['gnum'];
			$array3[$v['odid']]['th']   = $v['th'];
			if($state ==1){
				if($v['pay'] == 2 && $v['fh']==2 && $v['sh'] ==1 && $v['rate']==1){
					$array3[$v['odid']]['orderstate']   = 3;
				}elseif ($v['pay'] ==2 && $v['fh']==1){
					$array3[$v['odid']]['orderstate']   = 2;
				}elseif ($v['pay']==1){
					$array3[$v['odid']]['orderstate']   = 1;
				}elseif ($v['pay']==2  && $v['fh']==2 && $v['sh']==2){
					$array3[$v['goid2']]['orderstate']   = 4;
				}
				unset($v['th_reason']);
			}
// 		    $array3[$v['goid2']]['pay'] = $v['pay'];
// 		    $array3[$v['goid2']]['fh'] = $v['fh'];
// 		    $array3[$v['goid2']]['sh'] = $v['sh'];
// 		    $array3[$v['goid2']]['rate'] = $v['rate'];
			//$array3[$v['goid2']]['th'] = $v['th']?$v['th']:"";
			if($state ==2){
				$array3[$v['odid']]['order_no'] = $v['order_no']?$v['order_no']:"";
				$array3[$v['odid']]['shr'] = $v['shr']?$v['shr']:"";
				$array3[$v['odid']]['shrphe'] = $v['shrphe']?$v['shrphe']:"";
				$array3[$v['odid']]['shrdz'] = $v['shrdz']?$v['shrdz']:"";
				$array3[$v['odid']]['shrbz'] = $v['shrbz']?$v['shrbz']:"";
				$array3[$v['odid']]['th_reason'] = $v['th_reason']?$v['th_reason']:"";
				//$array3[$v]
				if (($v['pay'] ==2 && $v['fh']==1)){
					$array3[$v['odid']]['orderstate']   = 5;//发货
				}elseif (($v['pay'] ==2 && $v['fh']==2 && $v['sh']==1 && $v['th']==2)){
					$array3[$v['odid']]['orderstate']   = 6;//退货
				}elseif (($v['pay'] ==2 && $v['fh']==2 && $v['sh']==2) || ($v['pay'] ==2 && $v['fh']==2 && $v['sh']==1 && $v['th']==3)){
					$array3[$v['odid']]['orderstate']   = 7;//已经完成
				}
				unset($v['order_no']);
				unset($v['shr']);
				unset($v['shrphe']);
				unset($v['shrdz']);
				unset($v['shrbz']);
			}
			//unset($v['th']);
			unset($v['shopimg']);
			unset($v['bid']);
			unset($v['pay']);
			unset($v['fh']);
			unset($v['sh']);
			unset($v['rate']);
			unset($v['ordertime']);
			unset($v['orderprice']);
			$array3[$v['odid']]['order'][] = $v;
		}
		return  array_merge($array3);
	}
	/**
	 * 退货申请
	 * @param unknown $userid
	 * @param unknown $orderid
	 * @param unknown $reason
	 * 2017.05.24
	 */
	public function RefundRequest($userid,$orderid,$reason){
		$orderInfo 	= $this->GetOrderInfo("","bid,gname,goods_order.payid,actulpay,orderno,refund_total,out_aplipay_no,goid,godsid,gprices,gnum,reintl,odid,goods_order.userid","order_detail.odid=$orderid AND order_detail.userid=$userid AND  goods_order.ispay=2");
		if(empty($orderInfo)){return reTurnJSONArray("204", "退货订单不存在");}
		//print_r($orderInfo);die;
		$goid  = $orderInfo[0]['goid'];
		$sfk   = $orderInfo[0]['actulpay'];//实付款
		$thgoodsprice  = $orderInfo[0]['gprices'];//退货商品单价
		$thgoodsgnum   = $orderInfo[0]['gnum'];//购买商品数量
		$tkje          = math_mul($thgoodsprice, $thgoodsgnum);//本次退款总金额
		$order         = $orderInfo[0]['orderno'];
		if($thgoodsgnum==1){
			$tkje      = $thgoodsprice;
		}
		$yjtkje        = $orderInfo[0]['refund_total']?$orderInfo[0]['refund_total']:0;//已经退款金额
		$ddsyje        = math_sub($sfk, $yjtkje);//订单剩余金额
		$payid         = $orderInfo[0]['payid'];
		$alipayOrderno = $orderInfo[0]['out_aplipay_no'];
		$userid        = $orderInfo[0]['userid'];
		if(math_comp($ddsyje,0) ==-1){
			return reTurnJSONArray("204", "退货订单已经结束");
		}
		if(math_comp($tkje,$ddsyje)==1 || math_comp($tkje,$ddsyje)==0){
			$bcsjtk    = $ddsyje;//实际退款金额
			$bcddsyje  = 0;//本次退款之后剩余金额
		}else{
			$bcsjtk    = $tkje;
			$bcddsyje  = math_sub($ddsyje,$tkje);
		}
		$save['refund_reason']    = $reason;
		$save['apply_time']      = date("Y-m-d H:i:s");
		$save['isth']             = 2;
		$save['refund_totals']     = $bcsjtk;
		$result    = M("order_detail")->where("userid=$userid AND odid=$orderid")->save($save);
		if(is_bool($result)){
			return reTurnJSONArray("204", "退货申请提交失败");
		}else{
			$userid    = D("Common")->getUserIdFromBid($orderInfo[0]['bid']);
			D("Common")->PushSystem("用户提交退货申请","用户在您的商店购买了商品：[ ".$orderInfo[0]['gname']." ] 提交了退货申请，请查看",2,$userid);//用户退货通知商家站内信
			$userphone	= D("Common")->GetSellerPhoneFromBusinessId($orderInfo[0]['bid']);
			if(!empty($userid)){
				D("Function")->jpushonly($userid,"用户在您的商店购买了商品：[ ".$orderInfo[0]['gname']." ] 提交了退货申请，请查看",array("isseller"=>1,"state"=>0));//极光推送
			}
			if(!empty($userphone)){
				$content	= "【嘿谷商城】提示您：用户在您商店购买的[ ".$orderInfo[0]['gname']." ] 提交了退货申请，请查看";
				D("Function")->sendPhoneCodeFromOrder($userphone,$content);
			}
			return reTurnJSONArray("200", "退货申请提交成功");
		}
	}
	/****************************订单确认收货*********************************/
	/**
	 * 订单确认收货
	 * @param unknown $userid
	 * @param unknown $orderid
	 * 2017.05.25
	 */
	public function ConfirmReceipt($userid,$orderid){
		//$result    = M("goods_order")->where("userid=$userid AND goid=$orderid AND issh=1 AND isfh=2 AND ispay=2")->save(array("issh"=>2));
		$result      = $this->ConfirmReceipt2($userid,$orderid);
		if(!$result){
			return reTurnJSONArray("204", "商家未授理该商品");
		}else{
			return reTurnJSONArray("200", $result);
		}
	}
	/**
	 * 订单确认收货 第二步
	 * @param unknown $userid
	 * @param unknown $orderid
	 */
	public function ConfirmReceipt2($userid,$orderid){
		$orderInfo 	= $this->GetOrderInfo("","orderprice,goid,bid,godsid,gnum,reintl,odid,goods_order.userid,goodstotal,actulpay,refund_total,issh,freprice,isth","goods_order.userid=$userid AND odid=$orderid  AND isfh=2 AND goods_order.ispay=2");
		if(empty($orderInfo)){return false;}
		if($orderInfo[0]['issh']==2){return true;}
		for($i=0;$i<count($orderInfo);$i++){
			if($orderInfo[$i]['isth'] !=3){
				$this->Join_Defray($orderInfo[$i]['odid'],$orderInfo[$i]['reintl'],0);//加入本次送积分和返利记录
				$this->JfToUser($orderInfo[$i]['reintl'], $orderInfo[$i]['userid'],$orderInfo[$i]['isth']);//支付成功 将积分到用户账户2017.06.22【确认收货】GG
				$this->UpdateBusineAndGoodsInfo($orderInfo[$i]['bid'],$orderInfo[$i]['godsid'],$orderInfo[$i]['gnum'],0);//修改涉及到的商家和商品的库存以及销量
			}
		}
		$result    = M("order_detail")->where("userid=$userid AND odid=$orderid AND issh=1 AND isfh=2 AND ispay=2")->save(array("issh"=>2,"isth"=>1));//修改1
		$calltext	= "";
		$states		= $this->judgeOrderIsAllSh($orderInfo[0]['goid']);//判断订单是否全部全部收货
		if($states){
			$this->MoneyIntoAccount($orderInfo[0]['orderprice'],$orderInfo[0]['refund_total'],$orderInfo[0]['bid']);//订单确认收货
			$this->returnIntlNum($orderInfo[0]['refund_total'],$orderInfo[0]['orderprice'],$orderInfo[0]['userid'],$orderInfo[0]['goid']);//返回积分
			$a	= bcsub($orderInfo[0]['orderprice'],$orderInfo[0]['refund_total'],0);
			if($a > 0){
				$calltext	= "您本次购买,平台赠送您{$a}积分";
			}
		}
		//站内信以及推送
		$userid    = D("Common")->getUserIdFromBid($orderInfo[0]['bid']);
		D("Common")->PushSystem("用户确认收货","用户已经签收了在您店铺购买的商品",2,$userid);//用户退货通知商家站内信
		$userphone	= D("Common")->GetSellerPhoneFromBusinessId($orderInfo[0]['bid']);
		if(!empty($userid)){
			D("Function")->jpushonly($userid,"用户已经签收了在您店铺购买的商品",array("isseller"=>1,"state"=>1));//极光推送
		}
		if(!empty($userphone)){
			$content	= "【嘿谷商城】用户已经签收了在您店铺购买的商品";
			D("Function")->sendPhoneCodeFromOrder($userphone,$content);
		}
		if(empty($calltext)){
			$calltext	="订单确认收货成功";
		}
		return $calltext;
	}

	/**
	 * @param $refund_total
	 * @param $orderTotal
	 * @param $userid
	 * 返利积分
	 */
	public function returnIntlNum($refund_total,$orderTotal,$userid,$orderid){
		$refundIntl	= floor($refund_total);
		$orderIntl	= floor($orderTotal);
		$actualIntl	= bcsub($orderIntl,$refund_total,0);
		$model	= new Model();
		$result1	= $model->execute("update `user` set `integral`=`integral` + {$actualIntl} WHERE user_id={$userid}");
		$result2	= $model->execute("update `good_order` set `flags`=1 WHERE orderid={$orderid}");
	}
	/**
	 * 判断订单是否全部全部收货
	 * @param $goid
	 * @return bool
	 */
	public function judgeOrderIsAllSh($goid){
		$model	= M("order_detail");
		$result	= $model->field("issh,isth")->where("goid2=$goid")->select();
		if(!empty($result)){
			for($i=0;$i<count($result);$i++){
				if($result[$i]['issh']==1){
					return false;
				}
			}
			return true;
		}
	}
	/**
	 * 打款到用户余额
	 * @param unknown $actulpay
	 * @param unknown $refund_total
	 * @param unknown $bid
	 */
	public function MoneyIntoAccount($actulpay,$refund_total,$bid){
		$userid    = D("Common")->getUserIdFromBid($bid);
		if(empty($userid)){return false;}
		$intoTotal = math_sub($actulpay, $refund_total,"2");
		$usermodel = M("user");
		$data      = $usermodel->field("account")->where("user_id=$userid")->find();
		$account    = $data['account'];
		$save['account']    = math_add($intoTotal, $account);
		$usermodel->where("user_id=$userid")->save($save);
	}
	/****************************订单确认收货*********************************/
	/**
	 * 商家处理订单（$handlestate为1发货处理2同意退货处理）
	 * @param unknown $userid
	 * @param unknown $sellerid
	 * @param unknown $orderid
	 * @param unknown $handleState
	 * 2017.05.25
	 */
	public function ShopHandleOrder($userid,$sellerid,$orderid,$handleState){
		//Rewrite_log("./business.txt","userid+".$userid."||sellerid+".$sellerid."||orderid+".$orderid."||handleState+".$handleState);
		$bid = $this->getShopIdFromUserIdASellerId($sellerid,$userid);
		if(empty($bid)){
			return reTurnJSONArray("204", "用户未绑定商家");
		}
		if($handleState==1){
			$reTurnData    = $this->DeliverGoods($bid,$orderid);
		}elseif($handleState==2){
			$reTurnData    = $this->ReturnGoods($bid,$orderid);
		}else{
			$reTurnData    = reTurnJSONArray("204", "无未处理订单");
		}
		return $reTurnData;
	}
	/**
	 * 订单发货处理
	 * @param unknown $bid
	 * @param unknown $orderid
	 * 2017.05.25
	 */
	public function DeliverGoods($bid,$orderid){
		$orderInfo = M("goods_order")->field("goods_order.userid,order_detail.gname,goods_order.ispay,order_detail.isfh,order_detail.issh,goods_order.israte")->join("order_detail ON goods_order.goid=order_detail.goid2")->where("order_detail.odid=$orderid AND order_detail.isth=1")->find();
		if(empty($orderInfo)){
			return reTurnJSONArray("204", "订单中存在未授理商品..无法操作");
		}
		//$state	= $this->judgeOrderStateUser($orderInfo['ispay'],$orderInfo['isfh'],$orderInfo['issh'],$orderInfo['israte']);//获取订单的类型
		$result    = M("order_detail")->where("odid=$orderid AND ispay=2")->save(array("isfh"=>2,"delivertime"=>date("Y-m-d H:i:s"),"autoshtime"=>date('Y-m-d H:i:s', strtotime('+7 days'))));
		if(!is_bool($result)){
			D("Common")->PushSystem("订单已发货","您购买的商品：[ ".$orderInfo['gname']." ]已经在路上了",2,$orderInfo['userid']);
			$userphone	= D("Common")->GetUserPhoneFromUserid($orderInfo['userid']);
			if(!empty($orderInfo['userid'])){
				D("Function")->jpushonly($orderInfo['userid'],"【嘿谷商城】提示您：您购买的商品：[ ".$orderInfo['gname']." ]已经在路上了",array("isseller"=>0,"state"=>3));//极光推送
			}
			if(!empty($userphone)){
				$content	= "【嘿谷商城】提示您：您购买的商品：[ ".$orderInfo['gname']." ]已经在路上了";

				D("Function")->sendPhoneCodeFromOrder($userphone,$content);
			}
			return reTurnJSONArray("200", "发货成功");
		}else{
			return reTurnJSONArray("204", "订单发货失败");
		}
	}
	/*****************订单退货*******************/
	/**
	 * 退货订单处理
	 * @param unknown $bid
	 * @param unknown $orderid
	 */
	public function ReturnGoods($bid,$orderid){
		return $this->ReturnGoods1($bid,$orderid);
	}
	/**
	 * 退款第二步
	 * @param unknown $bid
	 * @param unknown $orderid
	 */
	public function ReturnGoods1($bid,$orderid){
		$orderInfo 	= $this->GetOrderInfo("","goods_order.ispay,order_detail.isfh,order_detail.issh,goods_order.israte,trade_type,goods_order.payid,actulpay,orderno,refund_total,out_aplipay_no,goid,godsid,gprices,gnum,reintl,odid,goods_order.userid,specid","order_detail.odid=$orderid And goods_order.bid=$bid AND  goods_order.ispay=2");
		if(empty($orderInfo)){return reTurnJSONArray("204", "退货订单不存在");}
		$state	= $this->judgeOrderStateUser($orderInfo[0]['ispay'],$orderInfo[0]['isfh'],$orderInfo[0]['issh'],$orderInfo[0]['israte']);//获取订单的类型
		$goid  = $orderInfo[0]['goid'];
		$sfk   = $orderInfo[0]['actulpay'];//实付款
		$thgoodsprice  = $orderInfo[0]['gprices'];//退货商品单价
		$thgoodsgnum   = $orderInfo[0]['gnum'];//购买商品数量
		$tkje          = math_mul($thgoodsprice, $thgoodsgnum);//本次退款总金额
		$order         = $orderInfo[0]['orderno'];
		$WxpayType		= $orderInfo[0]['trade_type'];
		if($thgoodsgnum==1){
			$tkje      = $thgoodsprice;
		}
		$yjtkje        = $orderInfo[0]['refund_total']?$orderInfo[0]['refund_total']:0;//已经退款金额
		$ddsyje        = math_sub($sfk, $yjtkje);//订单剩余金额
		$payid         = $orderInfo[0]['payid'];
		$alipayOrderno = $orderInfo[0]['out_aplipay_no'];
		$userid        = $orderInfo[0]['userid'];
		$specid			= $orderInfo[0]['specid'];//规格id
		if(math_comp($ddsyje,0) ==-1){
			return reTurnJSONArray("204", "退货订单已经结束");
		}
		if(math_comp($tkje,$ddsyje)==1 || math_comp($tkje,$ddsyje)==0){
			$bcsjtk    = $ddsyje;//实际退款金额
			$bcddsyje  = 0;//本次退款之后剩余金额
		}else{
			$bcsjtk    = $tkje;
			$bcddsyje  = math_sub($ddsyje,$tkje);
		}
		//
		if($payid==1){
			$result    = D("Pay")->AlipayRefund($bcsjtk,$alipayOrderno);//支付宝退款
		}elseif ($payid==2){
			$result    = D("Pay")->WxpayRefund($bcsjtk,$alipayOrderno,$sfk,$WxpayType);//微信退款
		}elseif ($payid==3){
			$result    = D("Pay")->balanceRefund($userid,$bcsjtk);//余额支付退款
		}else{
			return reTurnJSONArray("204", "第三方验证失败");
		}
		if($result){
			$savedata  = math_add($yjtkje, $bcsjtk);
			$this->updateGoodsNum($thgoodsgnum,$goid,"+",$specid);//退货之后收回库存
			M("goods_order")->where("goid=$goid")->save(array("refund_total"=>$savedata));//更新退款
			M("order_detail")->where("odid=$orderid")->save(array("isth"=>3,"refund_time"=>date("Y-m-d H:i:s")));
			D("Common")->PushSystem("订单已退款","您的订单号为：[ ".$order." ]的订单退款，商户已经授理",2,$userid);//站内信
			if(!empty($userid)){
				D("Function")->jpushonly($userid,"您的订单号为：[ ".$order." ]的订单退款，商户已经授理",array("isseller"=>0,"state"=>0));//极光推送
			}
			$userphone	= D("Common")->GetUserPhoneFromUserid($userid);
			if(!empty($userphone)){
				$content	= "【嘿谷商城】提示您：您订单号为[".$order."]的退货申请，商户已受理。";
				D("Function")->sendPhoneCodeFromOrder($userphone,$content);
			}
			return reTurnJSONArray("200", "退货操作成功");
		}else{
			return reTurnJSONArray("204", "账户余额不足，请联系平台客服。");
		}
	}

	/**
	 * @param $pay
	 * @param $fh
	 * @param $sh
	 * @param $rate
	 * @return int
	 * 判断订单的状态（用户）
	 */
	public function judgeOrderStateUser($pay,$fh,$sh,$rate){
		if($pay == 2 && $fh==2 && $sh ==1 && $rate==1){
			return  3;
		}elseif ($pay ==2 && $fh==1){
			return 2;
		}elseif ($pay==1){
			return 1;
		}elseif ($pay==2  && $fh==2 && $sh==2){
			return 4;
		}
	}

	/**
	 * @param $pay
	 * @param $fh
	 * @param $th
	 * @param $sh
	 * @return int
	 * 判断订单的状态（商铺）
	 */
	public function judgeOrderStateShop($pay,$fh,$th,$sh){
		if (($pay ==2 && $fh==1 && $th==1)){
			return 5;//发货
		}elseif (($pay ==2  && $sh==1 && $th==2)){
			return 6;//退货
		}elseif (($pay ==2 && $fh==2 && $sh==2) || ($pay ==2 && $fh==2 && $sh==1 && $th==3)){
			return 7;//已经完成
		}
	}
	/*****************订单退货*******************/
	/**
	 *通过用户id和sellerid获取商家id
	 * @param unknown $sellerid
	 * @param unknown $userid
	 * 2017.05.25
	 */
	public function getShopIdFromUserIdASellerId($sellerid,$userid){
		$sql   = "SELECT
            	    business_info.b_id
            	    FROM
            	    `h_seller_detail`
            	    LEFT JOIN business_info ON h_seller_detail.seller_id = business_info.seller_id
            	    WHERE
            	    business_info.seller_id={$sellerid} AND h_seller_detail.user_id={$userid} limit 1";
		$model = M("business_info");
		$result    = $model->query($sql);
		return $result[0]['b_id'];
	}
	/**
	 * 删除我的订单
	 * 2017.05.25
	 */
	public function DelMyOrder($userid,$goid){
		$model 	= M("goods_order");
		$return 	= $model->join("order_detail ON goods_order.goid=order_detail.goid2","INNER")->where("goods_order.userid=$userid and goid=$goid and order_detail.isth=1")->select();
		if(is_bool($return)){
			return array("code"=>204,"message"=>"订单中存在未授理商品..无法操作");
		}else{
			$intlnum	=0;
			for($j=0;$j<count($return);$j++){
				//扣除积分
				if($return[$j]['intlnum']!=0){
					$intlnum	+= $return[$j]['intlnum'];
				}
			}
			if($return[0]['ispay']==1){
				if($intlnum!=0){
					$this->JfToUser($intlnum,$return[0]['userid'],"");
				}
				$model->where("goid=$goid")->delete();
			}else{
				$model->where("goid=$goid")->save(array("isdel"=>1));
			}
			//$this->DeductionIntl($orderInfo3[0]['userid'],$intlnum);
			return array("code"=>200,"message"=>"订单删除成功");
		}
	}
	/**
	 * 获取各种类型订单的数量【用户】
	 * @param unknown $userid
	 */
	public function getOrderNum($userid,$state=""){
		$where = "";
//	        if(!empty($state)){
//	            $where = " AND goods_order.payid=2 ";
//	        }
		$data['dfk']= $this->executeSql("goid","goods_order.userid=$userid AND goods_order.ispay=1 AND goods_order.isdel=0");
		$data['dfh']=$this->executeSql("goid","goods_order.userid=$userid AND goods_order.ispay=2 AND order_detail.isfh=1 AND goods_order.isdel=0 and order_detail.isth!=3".$where);
		$data['dsh']=$this->executeSql("goid","goods_order.userid=$userid AND goods_order.ispay=2 AND order_detail.isfh=2 AND issh=1 AND goods_order.isdel=0 and order_detail.isth!=3".$where);
		$data['dpj']=$this->executeSql("goid","goods_order.userid=$userid AND goods_order.ispay=2 AND goods_order.israte=1 AND order_detail.isfh=2 AND issh=2 AND goods_order.isdel=0".$where);
		$returnData['code']    = "200";
		$returnData['message'] = "获取成功";
		$returnData['info']    = $data;
		return $returnData;
	}
	/**
	 * 执行sql语句
	 * 2017.06.02
	 * @param string $sql
	 */
	public function executeSql($field="goid",$where=""){
		$model = M("goods_order");
		$count = $model->field($field)->join("order_detail ON goods_order.goid=order_detail.goid2")->where($where)->count();
		if(!empty($count)){
			return $count;
		}else{
			return '0';
		}
	}
	/**************后台退货API*********************/
	/**
	 * @param $odid
	 * @param $adminpinyin
	 * @param $groupId
	 * @return array
	 * 管理员处理订单退款
	 */
	public function RefundOrder($odid,$adminpinyin,$groupId){
		if($adminpinyin=="manager"){
			$data	= $this->OperatrionRefundOrder($odid);
		}else{
			$state	= $this->JudgeAdminIsRefund($groupId);
			if($state){
				$data	= 1;
				$data	= $this->OperatrionRefundOrder($odid);
			}else{
				$data	= array("code"=>204,"message"=>"您没有操作权限");
			}
		}
		return $data;
	}
	/**
	 * @param $odid
	 * @return int
	 * 根据odid返回对应的商家bid
	 */
	public function getOrderBidFromOdid($odid){
		$model	= M("goods_order");
		$result	= $model
				->field("bid")
				->join("order_detail ON goods_order.goid=order_detail.goid2")
				->where("order_detail.odid=$odid And order_detail.isth=2 And goods_order.ispay=2")
				->find();
		if(!empty($result)){
			return $result['bid'];
		}
		return 0;
	}

	/**
	 * @param $groupid
	 * @return bool
	 * 判断其他管理员是否有权限进行退货操作
	 */
	public function JudgeAdminIsRefund($groupid){
		$model	= M("nmt_admin_group");
		$result	= $model->field("columnidstr")->where("id=$groupid")->find();
		if(!empty($result)){
			$idstr	= $result['columnidstr'];
			$state	= strpos($idstr,"54");
			if(is_bool($state)){
				return false;
			}
			return true;
		}
		return false;
	}
	/**
	 * @param $odid
	 * @return array
	 * 退货
	 */
	public function OperatrionRefundOrder($odid){
		$bid	= $this->getOrderBidFromOdid($odid);
		if(empty($bid)){
			return array("code"=>204,"message"=>"订单无法处理");
		}
		$data	= $this->ReturnGoods1($bid,$odid);
		return $data;
	}
}