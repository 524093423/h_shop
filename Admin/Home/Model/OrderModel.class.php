<?php
/**
 * 嘿谷商城后台订单模型
 * 2017.03.14
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class OrderModel extends Model {
	/**
	 * 获取订单列表(全部，代付款，代发货，已完成)
	 * @param 用户id $userid
	 * @param 商家id $bid
	 * @param 时间 $date
	 * @param 页码 $pageIndex
	 * @param  $pageSize
	 * @return unknown
	 */
	public function GetOrderPage($userid,$bid,$date1,$date2,$PageIndex,$PageSize,$where="",$fhstyle="",$ispay="",$isfh="",$orderno="",$payt=""){
		$PageIndex 		= $PageIndex?$PageIndex:"1";
		$PageSize 		= $PageSize?$PageSize:"25";
		if(!empty($date1) && !empty($date2)){
		    $WhereStr .=createTime($date1,$date2,"goods_order.otime");
		}
		if($PageIndex == 1){
			$no = 1;
		}else{
			$no =($PageIndex -1) * $PageSize +1;
		}
		if(!empty($userid)){
			$WhereStr .=" AND goods_order.userid=$userid";
		}
		if(!empty($bid)){
			$WhereStr .=" AND goods_order.bid=$bid";
		}
		if(!empty($fhstyle)){
			$WhereStr .=" AND goods_order.freid=$fhstyle";
		}
		if(!empty($isfh)){
			$WhereStr .=" AND order_detail.isfh=$isfh AND goods_order.ispay=2 And isth=1";
		}
		if(!empty($ispay)){
			$WhereStr .=" AND goods_order.ispay=$ispay";
		}
		if(!empty($orderno)){
			$WhereStr .=" AND (goods_order.orderno=$orderno or out_aplipay_no=$orderno)";
		}
		if(!empty($payt)){
			$WhereStr .=" AND goods_order.payid = $payt ";
		}
		$show = "5";
		$joinid = "goid";
		$join 		= "	LEFT JOIN  `user` ON goods_order.userid=`user`.user_id LEFT JOIN order_detail ON goods_order.goid=order_detail.goid2";
		if(!empty($where)){
			$WhereStr .= $where;
			$join 		.="	LEFT JOIN business_info ON goods_order.bid=business_info.b_id";
		}
		$WhereStr 	.=  " AND goods_order.isdel=0 ";
		$Coll="goid,
					orderprice,
					goods_order.otime,
					user_name,
					orderno,
					freid,
					goods_order.ispay,
					goods_order.paym,goods_order.userid,isfh,out_aplipay_no";
		$sql = SqlStr2($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid);
		$model = M("goods_order");
		$count = $model->join($join)->where("1=1".$WhereStr)->count();
		//echo $sql;die;
		//$data = new ArrayList();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $this->JoinHtml_Order($model->query($sql),$no);
		$url = "admin.php?c=Order&a=GetOrderList";
		$url .="&userid=$userid";
		$url .="&bid=$bid";
		$url .="&date=$date";
		$url .="&state=$state";
		$url .="&fhstyle=$fhstyle";
		$url .="&ispay=$ispay";
		$url .="&isfh=$isfh";
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 获取退货订单列表
	 * @param 用户id $userid
	 * @param 商家id $bid
	 * @param 时间 $date
	 * @param 页码 $pageIndex
	 * @param  $pageSize
	 * @return unknown
	 */
	public function GetRefundOrder($userid,$bid,$date1,$date2,$PageIndex,$PageSize,$where="",$fhstyle="",$ispay="",$isfh="",$orderno="",$payt="",$adminpinyin=""){
	    $PageIndex 		= $PageIndex?$PageIndex:"1";
	    $PageSize 		= $PageSize?$PageSize:"25";
	    if(!empty($date1) && !empty($date2)){
	        $WhereStr .=createTime($date1,$date2,"goods_order.otime");
	    }
	    if($PageIndex == 1){
	        $no = 1;
	    }else{
	        $no =($PageIndex -1) * $PageSize +1;
	    }
	    if(!empty($userid)){
	        $WhereStr .=" AND goods_order.userid=$userid";
	    }
	    if(!empty($bid)){
	        $WhereStr .=" AND goods_order.bid=$bid";
	    }
	    if(!empty($fhstyle)){
	        $WhereStr .=" AND goods_order.freid=$fhstyle";
	    }
	    if(!empty($isfh)){
	        $WhereStr .=" AND order_detail.isfh=$isfh AND goods_order.ispay=2";
	    }
	    if(!empty($ispay)){
	        $WhereStr .=" AND goods_order.ispay=$ispay";
	    }
	    if(!empty($orderno)){
	        $WhereStr .=" AND (goods_order.orderno=$orderno or out_aplipay_no=$orderno)";
	    }
	    if(!empty($payt)){
	        $WhereStr .=" AND goods_order.payid = $payt ";
	    }
	    $show = "5";
	    $joinid = "goid";
	    $join 		= " INNER JOIN	order_detail ON goods_order.goid = order_detail.goid2 INNER JOIN `user` ON goods_order.userid = `user`.user_id";
	    if(!empty($where)){
	        $WhereStr .= $where;
	        $join 		.="	INNER JOIN business_info ON goods_order.bid=business_info.b_id";
	    }
	    $WhereStr 	.=  " AND goods_order.isdel=0 AND (order_detail.isth = 2 or order_detail.isth=3)";
	    $Coll="goid,
	    out_aplipay_no,
            	orderprice,
            	goods_order.otime,
            	user_name,
            	orderno,
            	freid,
	        gname,
	        apply_time,
	        odid,
            	goods_order.ispay,
            	goods_order.paym,
            	goods_order.userid,
            	isfh,
	        refund_totals,
	        refund_time,
            	CASE  WHEN isth=2 THEN '待审核' WHEN isth=3 THEN '审核通过【退货成功】'  END AS isth,refund_reason,order_detail.gprices,order_detail.gnum";
	    $sql = SqlStr2($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="isth",$OrderType="desc",$join,$joinid,""," AND (order_detail.isth = 2 or order_detail.isth=3)");
	    $model = M("goods_order");
	    $count = $model->join($join)->where("1=1".$WhereStr)->count();
	    //echo $model->getLastSql();die;
	    //$data = new ArrayList();
	    $pagenum = ceil($count/$PageSize);
	    $data['list']  = $this->JoinHtml_Order_Refund($model->query($sql),$no,1,$adminpinyin);
	    $url = "admin.php?c=Order&a=GetRefundOrder";
	    
	    $url .="&userid=$userid";
	    $url .="&bid=$bid";
	    $url .="&date=$date";
	    $url .="&state=$state";
	    $url .="&fhstyle=$fhstyle";
	    $url .="&ispay=$ispay";
	    $url .="&isfh=$isfh";
	    $url .="&page=";
	    $data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
	    return $data;
	}
	/**
	 * 订单分页加入html[退货订单]
	 * @param unknown $data
	 */
	public function JoinHtml_Order_Refund($data,$no,$status="",$adminpinyin=""){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			$refundtime = "";
			$operation	= "";
			for ($i=0;$i<count($data);$i++){
				$html .= "<tr>";
				$html .= "<td align=\"center\">".$no."</td>";
				$html .= "<td align=\"center\">".$data[$i]['orderno']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['out_aplipay_no']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['user_name']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['gname']." * ".$data[$i]['gnum']."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".math_mul($data[$i]['gprices'],$data[$i]['gnum'])."</font>元</td>";
				$html .= "<td align=\"center\">".$data[$i]['apply_time']."</td>";
				$html .="<td align=\"center\"><a href='javascript:void(0)' onclick=\"looks('".$data[$i]['refund_reason']."')\"> ".$data[$i]['refund_reason']."</a></td>";
				$html .= "<td align=\"center\">".$data[$i]['isth']."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['refund_totals']."</font>元</td>";
				if($data[$i]['refund_time'] ==0){
				    $refundtime ="未授理";
					if($adminpinyin!="seller"){
						$operation	= "<a href='javascript:void(0)' id='".$data[$i]['odid']."' pinyin='".session('nmt_pinyin')."' group='".session('nmt_groupid')."' class='oper'>同意退货</a>";
					}
				}else{
				    $refundtime = $data[$i]['refund_time'];
				}
				$html .= "<td align=\"center\">".$refundtime."&nbsp;&nbsp;&nbsp;&nbsp;".$operation."</td>";
				$operation	= "";
				$no++;
			}
			return $html;
		}
		return $data;
	}
	/**
	 * 订单分页加入html
	 * @param unknown $data
	 */
	public function JoinHtml_Order($data,$no,$status=""){
	    if(!empty($data)){
	        $html = "";
	        $delivery 		= D("Common")->GetDelivery();
	        $no 	= $no?$no:1;
	        for ($i=0;$i<count($data);$i++){
	            for ($j=0;$j<count($delivery);$j++){
	                if($data[$i]['freid'] == $delivery[$j]['id']){
	                    $frename 	= $delivery[$j]['dtitle'];
	                }else{
	                    $frename    = "包邮";
	                }
	            }
	            if($data[$i]['ispay'] == 1){
	                $ispaytext 	= "未支付";
	            }else{
	                $ispaytext 	= "支付成功";
	            }
	            if($data[$i]['isfh'] ==1){
	                $isfh 		= "未发货";
	            }else{
	                $isfh 		= "发货成功";
	            }
	            $html .= "<tr>";
	            $html .= "<td align=\"center\">".$no."</td>";
	            $html .= "<td align=\"center\">".$data[$i]['orderno']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['out_aplipay_no']."</td>";
	            $html .= "<td align=\"center\"><a class=\"look\"  rel=".$data[$i]['goid']." href=\"javascript:void(0)\" >".$data[$i]['user_name']."</a></td>";
	            $html .= "<td align=\"center\">[".$data[$i]['otime']."]</td>";
	            $html .= "<td align=\"center\">".$frename."</td>";
	            $html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['orderprice']."</font>元</td>";
	            $html .= "<td align=\"center\">".$ispaytext."</td>";
	            $html .= "<td align=\"center\">".$data[$i]['paym']."</td>";
	            $html .= "<td align=\"center\">".$isfh."</td>";
	            $html .= "<td align=\"center\">
						<a class=\"look\"  rel=".$data[$i]['goid']." href=\"javascript:void(0)\" ><img  src='Public/admin/images/look.png' title=\"查看订单详情\" alt=\"查看订单详情\"/></a>
						<a class=\"del\"  rel=".$data[$i]['goid']." href=\"javascript:void(0)\" ><img  src='Public/admin/images/delete.gif' title=\"删除\" alt=\"删除\"/></a>
						</td>";
	            $html .="</tr>";
	            $no++;
	        }
	        return $html;
	    }
	    return $data;
	}
	/**
	 * 获取订单文本
	 * @param unknown $goid
	 */
	public function GetOrderDetail($goid){
		$data 				= $this->GetOrderInfo($goid);
		$freid 				= $data[0]['freid'];
		$delivery 			= D("Common")->GetDelivery();
		for ($j=0;$j<count($delivery);$j++){
			if($freid == $delivery[$j]['id']){
				$frename 	= $delivery[$j]['dtitle'];
			}
		}
		$list['freid'] 		= $freid;
		$list['fretext'] 	= $frename;
		$sjrecordHtml 	= $this->JoinHtml_pay($data);
		$orderno 			= $data[0]['orderno'];
		$text 				= $this->JoinHtml_detail($data);
		$bid 					= $data[0]['bid'];
		$binfo 				= D("Shop")->FindShop($bid,"`name`","","",2);
		$ispay 				= $data[0]['ispay'];
		$isfh 				= $data[0]['isfh'];
		$list['ispay'] 		= $ispay;
		$list['isfh'] 		= $isfh;
		if($ispay ==1){
			$title1 	= "未支付";
			$title2 	= "无付款信息";
		}else{
			if($isfh == 1){
				$title1 	= "支付成功";
				$title2 ="代发货";
			}else{
				$title1 	= "支付成功";
				$title2 = "发货成功";
			}
		}
		$list['sj'] 		= $sjrecordHtml;
		$list['title1'] 	= $title1;
		$list['title2'] 	= $title2;
		$list['bname']= $binfo['name'];
		$list['peo'] 		= $data[0]['rpeople'];
		$list['phe'] 		= $data[0]['rphe'];
		$list['address'] 	= $data[0]['raddress'];
		$list['orderno'] 	= $orderno;
		$list['text'] 		= $text;
		return $list;
	}
	/**
	 * 查看某订单的详情（数据）
	 * @param unknown $goid
	 */
	public function GetOrderInfo($goid){
		$model 	= M("goods_order");
		$sql 			= "SELECT 
							goods_order.ispay,goods_order.payid,isfh,gphoto,goods_order.bid,rpeople,rphe,raddress,
							out_aplipay_no,orderno,order_detail.gname,order_detail.gspec,order_detail.gnum,order_detail.gprices,goods_order.msg,
							goods_order.actulpay,goods_order.orderprice,goods_order.freprice,goods_order.otime,goods_order.freid
							FROM `goods_order`
							LEFT JOIN order_detail ON goods_order.goid = order_detail.goid2
							LEFT JOIN goods ON order_detail.godsid = goods.gid
							WHERE
								goid =$goid";
		$result 		= $model->query($sql);
		return $result;
	}
	/**
	 * 加入订单详情html
	 * @param unknown $result
	 * @return string
	 */
	public function JoinHtml_detail($result){
		$no = 1;
		for ($i=0;$i<count($result);$i++){
			$c 		= count($result);
			$gnum +=$result[$i]['gnum'];
			$total 	+=($result[$i]['gprices'] * $result[$i]['gnum']);
			$msg 		= $result[$i]['msg'];
			$orderprice 	= $result[$i]['orderprice'];
			$actulpay 		= $result[$i]['actulpay'];
			$freprice 		= $result[$i]['freprice'];
			$html .= "<tr style=\"height:84px\">";
			$html .= "<td align=\"center\">".$no."</td>";
			$html .= "<td align=\"center\">".$result[$i]['gname']."</td>";
			$html .= "<td align=\"center\"><img src=.".$result[$i]['gphoto']." style=\"width:216px;height:76px;\"></td>";
			$html .= "<td align=\"center\">".$result[$i]['gspec']."</td>";
			$html .= "<td align=\"center\">".$result[$i]['gnum']."(件)</td>";
			$html .= "<td align=\"center\">".$result[$i]['gprices']."</td>";
			$html .= "<td align=\"center\">".$result[$i]['gprices'] * $result[$i]['gnum']."</td>";
			if($i+1 == ceil($c/2)){
				$html .= "<td align=\"center\" rowspan=".$c.">".$msg."</td>";
			}
			$html .="</tr>";
			$no++;
		}
		$html .= "<tr style=\"height:84px\">";
		$html .= "<td align=\"center\">合计</td>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\">".$gnum."(件)</td>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\">".$total."</td>";
		$html .= "<td align=\"center\"></td>";
		$html .="</tr>";
		$html .= "<tr style=\"height:84px\">";
		$html .= "<td align=\"right\" colspan='8'><font style='font-size:10px'>运费：$freprice</font>&nbsp;&nbsp;订单总金额：".$orderprice."&nbsp;&nbsp;<font style='color:red;font-size:20px;'>应付金额 : $actulpay</font>&nbsp;&nbsp;</td>";
		$html .="</tr>";
		return $html;
	}
	/**
	 * 获取订单收款记录
	 * @param 数组 $result
	 * @return Array is NULL|string
	 */
	public function JoinHtml_pay($result){
		if(empty($result)){ return  $result;}
		$html ="";
		$i = 0;
		//for($i=0;$i<count($result);$i++){
			$payinfo 	= $this->GetPayInfo($result[$i]['payid']);
			$html .= "<tr style=\"height:68px\">";
			$html .= "<td align=\"center\">".$result[$i]['out_aplipay_no']."</td>";
			$html .= "<td align=\"center\">".$result[$i]['otime']."</td>";
			$html .= "<td align=\"center\">".$result[$i]['actulpay']."</td>";
			$html .= "<td align=\"center\">".$payinfo['payname']."</td>";
			$html .= "<td align=\"center\">".$payinfo['seller']."</td>";
			$html .="</tr>";
		//}
		return $html;
	}
	/**
	 * 根据支付id返回支付名称和收款帐号
	 * @param unknown $payid
	 * @param string $field
	 * @param string $where
	 * @return \Think\mixed
	 */
	public function GetPayInfo($payid,$field="",$where=""){
		$model 	= M("pay_config");
		$field 		= $field?$field:"pay_name,seller";
		$where 	= $where?$where:"p_id=$payid";
		$result 		= $model->field($field)->where($where)->find();
		$info['payname'] 		= $result['pay_name'];
		$info['seller'] 			= $result['seller'];
		return $info;
	}
	/**
	 * 订单数据的导出
	 * @param unknown $userid
	 * @param unknown $bid
	 * @param unknown $date1
	 * @param unknown $date2
	 * @param unknown $PageIndex
	 * @param unknown $PageSize
	 * @param string $where
	 * @param string $fhstyle
	 * @param string $ispay
	 * @param string $isfh
	 * @param string $orderno
	 */
	public function OrderExport($userid,$bid,$date1,$date2,$where="",$fhstyle="",$ispay="",$isfh="",$orderno="",$all="",$payt="",$dostyle=""){
		$WhereStr 	= " 1=1 ";
		$WhereStr .=createTime($date1,$date2,"goods_order.otime");
		if(!empty($userid)){
			$WhereStr .=" AND goods_order.userid=$userid";
		}
		if(!empty($orderno)){
			$WhereStr .=" AND (goods_order.orderno=$orderno or out_aplipay_no=$orderno)";
		}
		$join 		= "	LEFT JOIN  `user` ON goods_order.userid=`user`.user_id ";
		if(!empty($where)){
			$WhereStr .= $where;
			$join 		.="	LEFT JOIN business_info ON goods_order.bid=business_info.b_id ";
		}
		if(!empty($fhstyle)){
			$WhereStr .=" AND goods_order.freid=$fhstyle ";
		}
		if(!empty($payt)){
			$WhereStr .=" AND goods_order.payid = $payt ";
		}
		if(!empty($all)){
			$WhereStr 	= " 1=1 ";
		}
		if(!empty($bid)){
			$WhereStr .=" AND goods_order.bid=$bid";
		}
		if(!empty($isfh)){
			$WhereStr .=" AND order_detail.isfh=$isfh AND goods_order.ispay=2 And isth=1";
		}
		if(!empty($ispay)){
			$WhereStr .=" AND goods_order.ispay=$ispay ";
		}
		$join .=" 	LEFT JOIN order_delivery ON goods_order.freid = order_delivery.odyid
						LEFT JOIN order_detail ON goods_order.goid = order_detail.goid2
						LEFT JOIN business_info ON goods_order.bid = business_info.b_id ";
		$TableName="goods_order";
		$Coll="	orderno,
					business_info.`name`,
					order_detail.gname,
					order_detail.gspec,
					order_detail.gprices,
					order_detail.gnum,
					goods_order.otime,
					goods_order.paym,
					CASE
				WHEN goods_order.ispay = 1 THEN
					'未支付'
				WHEN goods_order.ispay = 2 THEN
					'已支付'
				END AS paytext,
				 CASE
				WHEN isfh = 1 THEN
					'未发货'
				WHEN isfh = 2 THEN
					'已发货'
				END AS fh,
				 order_delivery.`describe`,
				 goods_order.rpeople,
				 goods_order.raddress,
				 goods_order.rphe,
				 goods_order.msg,
				 goods_order.freprice,
				 goods_order.goodstotal,
				 goods_order.intlprice,
				 orderprice,
				 goods_order.actulpay";
		if($isfh ==1){
			$Coll = "orderno,
					business_info.`name`,
					order_detail.gname,
					order_detail.gspec,
					order_detail.gprices,
					order_detail.gnum,
					goods_order.otime,
					order_delivery.`describe`,
					 goods_order.rpeople,
					 goods_order.raddress,
					 goods_order.rphe,
					 goods_order.msg";
		}
		$sql = "Select  {$Coll} FROM {$TableName}  {$join} WHERE {$WhereStr}";
		$model 	= M("$TableName");
		$result 		= $model->query($sql);
		if(!empty($result)){
			$fileName 	= "【嘿谷商城】订单-".date("Y-m-d-i-s");
			$headlist 		= array("订单号","商家名称","商品名称","规格","商品单价","数量","下单时间","支付方式","是否支付","是否发货","发货方式","收货人","收货地址","联系电话","留言","运费","商品总价","积分抵现金","订单总价","实付款");
			if($isfh ==1){
				$headlist 		= array("订单号","商家名称","商品名称","规格","商品单价","数量","下单时间","发货方式","收货人","收货地址","联系电话","留言");
				$fileName     = "【嘿谷商城】代发货订单-".date("Y-m-d-i-s");
			}
			if($dostyle ==1){
				$returnData = csv_export($result,$headlist,$fileName);
			}else if($dostyle ==2){
				$returnData = excel_import($headlist,$result,$fileName);
			}else{
				return false;
			}
			//return array("code"=>200,"message"=>"数据处理中");
		}else{
			echo  "<script>alert('没有要处理数据');history.go(-1)</script>";
			//return array("code"=>204,"message"=>"无待处理数据....");
		}
	}
}