<?php
/**
 * 盈亏统计
 * 2017.03.13
 */
namespace Home\Model;
use Think\Model;
use Org\Util\ArrayList;
header("Content-Type: text/html; charset=UTF-8");
class ProfitLossModel extends Model {
	/**
	 * 获取日盈利带数据分页
	 * @param 用户id $userid
	 * @param 商家id $bid
	 * @param 时间 $date
	 * @param 页码 $pageIndex
	 * @param  $pageSize
	 * @return unknown
	 */
    public function GetDayPage($userid,$bid,$date,$PageIndex,$PageSize,$state,$adminid,$adminpinyin){
   		$PageIndex 		= $PageIndex?$PageIndex:"1";
   		$PageSize 		= $PageSize?$PageSize:"20";
   		if($PageIndex == 1){
   			$no = 1;
   		}else{
   			$no =($PageIndex -1) * $PageSize +1;
   		}
   		if(!empty($userid)){
   			$WhereStr .=" AND goods_order.userid $userid";
   		}
   		if(!empty($bid)){
   			$WhereStr .=" AND goods_order.bid=$bid";
   		}
   		if($state ==1){
   			$WhereStr .="  AND date(otime) = date('".$date."') AND goods_order.ispay = 2";
   		}elseif ($state ==2){
   			$WhereStr .= "  AND month(`otime`) =  month('".$date."')  AND year(`otime`) = year('".$date."')  AND goods_order.ispay = 2";
   		}elseif ($state ==3){
   			$WhereStr .= "  AND year(`otime`) = year('".$date."')  AND goods_order.ispay = 2";
   		}
   		$show = "5";
   		$joinid = "goid";
   		$join 		.= " LEFT JOIN order_detail ON goods_order.goid = order_detail.goid2 INNER JOIN `user` ON goods_order.userid=`user`.user_id ";
   		$Coll="goid,bid,orderprice,goodstotal,freprice,actulpay,tctotal,order_detail.rebatetotal,	order_detail.reintl,	order_detail.remaintotal,yjtotal,	intlprice,goods_order.userid,goods_order.otime,user_name,orderno";
   		$sql = SqlStr3($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid);
   		$model = M("goods_order");
   		$count = $model->where("1=1".$WhereStr)->count();
   		//echo $model->getLastSql();die;
   		//$data = new ArrayList();
   		$pagenum = ceil($count/$PageSize);
   		$data['list']  = $this->JoinHtml_Day($model->query($sql),$no);
   		$url = "admin.php?c=ProfitLoss&a=getDayList";
   		$url .="&userid=$userid";
   		$url .="&bid=$bid";
   		$url .="&date=$date";
   		$url .="&state=$state";
   		$url .="&page=";
   		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
   		return $data;
   }
   /**
    * 日盈利分页加入html
    * @param unknown $data
    */
   public function JoinHtml_Day($data,$no){
   	if(!empty($data)){
   		$html = "";
   		$no 	= $no?$no:1;
   		for ($i=0;$i<count($data);$i++){
   			$html .= "<tr>";
   			$html .= "<td align=\"center\">".$no."</td>";
   			$html .= "<td align=\"center\">".$data[$i]['orderno']."</td>";
   			$html .= "<td align=\"center\">".$data[$i]['user_name']."</td>";
   			$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['goodstotal']."</font>元</td>";
   			$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['freprice']."</font>元</td>";
   			$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['intlprice']."</font>元</td>";
   			$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['orderprice']."</font>元</td>";
   			$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['actulpay']."</font>元</td>";
   			$html .= "<td align=\"center\">".$data[$i]['reintl']."</td>";
   			$html .= "<td align=\"center\">".$data[$i]['otime']."</td>";
   			$html .="</tr>";
   			$no++;
   		}
   		return $html;
   	}
   	return $data;
   }
   /**
    * 获取日统计
    * @param unknown $userid
    * @param unknown $bid
    * @param unknown $date
    */
   public function GetDayStatisticsHtml($userid,$bid,$date,$state){
   		$data 		= $this->GetDayStatisticsInfo($userid, $bid, $date,$state);
   		//print_r($data);die;
   		$tjArray 	= $this->CountStatistics($data);
   		$html['text'] 		= $this->JoinHtml_Day_Statistics($tjArray);
   		return $html;
   }
   /**
    * 获取不带分页数据
    * @param 用户id $userid
    * @param 商家id $bid
    * @param 时间 $date
    * @return Ambigous <boolean, unknown>
    */
   public function GetDayStatisticsInfo($userid,$bid,$date,$state){
   		$sql = "SELECT
					SUM(orderprice) AS ordertotal,
					sum(goodstotal) AS goodstotal,
					sum(freprice) AS fretotal,
					sum(actulpay) AS actulpay,
					sum(tctotal) AS tctotal,
					sum(yjtotal) AS yjtotal,
					sum(intlprice) AS jfprice
				FROM
					goods_order 
				WHERE
					 goods_order.ispay = 2
				";
   		$sql2 	= "SELECT SUM(rebatetotal) AS rebatetotal,SUM(reintl) AS intltotal,SUM(refund_totals) as refund FROM order_detail LEFT JOIN goods_order ON order_detail.goid2 = goods_order.goid  where  order_detail.ispay=2  ";
   		if(!empty($userid)){
   			$sql .=" AND userid=$userid";
   			$sql2 .=" AND userid=$userid";
   		}
   		if(!empty($bid)){
   			$sql .=" AND bid=$bid";
   			$sql2 .=" AND bid=$bid";
   		}
   		if($state ==1){
   			$sql .="  AND date(otime) = date('".$date."') ";
   			$sql2 .="  AND date(sjtime) = date('".$date."') ";
   		}elseif ($state ==2){
   			$sql .= "  AND month(`otime`) =  month('".$date."')  AND year(`otime`) = year('".$date."') ";
   			$sql2 .=" AND month(`sjtime`) =  month('".$date."')  AND year(`sjtime`) = year('".$date."')	";
   		}elseif ($state ==3){
   			$sql .= "  AND year(`otime`) = year('".$date."')";
   			$sql2 .=" AND year(`sjtime`) = year('".$date."')	";
   		}
   		$model 	= M("goods_order");
   		$result 		= $model->query($sql);
   		$result1 	= $model->query($sql2);
   		if(is_bool($result)){
   			if(is_bool($result1)){
   				$result 		= array();
   			}else{
   				$result 		= array_merge($result1);
   			}
   		}else{
   			if(is_bool($result1)){
   				$result 		= array_merge($result);
   			}else{
   				$result 		= array_merge($result,$result1);
   			}
   		}
   		return returnArrayBool($result);
   }
   /**
    * 计算统计数据（日 月 年）
    * @param unknown $data
    * @return multitype:number unknown |unknown
    */
   public function CountStatistics($data){
   		$info 	= array();
   		if(!empty($data)){
   			$intl 		= 0.01;
   			$ordertotal 		= $data[0]['ordertotal'];
   			$yjtotal 			= $data[0]['goodstotal'];//商品总价
   			$fretotal 			= $data[0]['fretotal'];
   			$fxj 					= $data[1]['rebatetotal'];
   			$fjf 					= $data[1]['intltotal'];
   			$fjfprice 			= $fjf*0.01;
   			$jfprice 			= $data[0]['jfprice'];
   			$actulpay 			= $data[0]['actulpay'];
   			$tctotal 			= $data[0]['tctotal'];
   			$info['ordertotal'] 		= $ordertotal?$ordertotal:0;
   			$info['yjtotal'] 			= $yjtotal?$yjtotal:0;
   			$info['fxj'] 				= $fxj?$fxj:0;
   			$info['fjf'] 					= $fjf?$fjf:0;
   			$info['jfprice'] 			= $jfprice?$jfprice:0;
   			$info['tctotal'] 			= $tctotal?$tctotal:0;
   			$info['fretotal'] 			= $fretotal?$fretotal:0;
   			$info['actulpay'] 		= $actulpay?$actulpay:0;
   			$fjfprice 					= $fjf*$intl;
   			$tk                         = $data[1]['refund']?$data[1]['refund']:0;
   			$info['pay'] 				=  math_add($jfprice, $fjfprice,2);
   			$info['pay']                = math_add($data[1]['refund'], $tk ,2);
   			$jd 					= math_sub($actulpay, $info['pay'],2);//$actulpay - $fretotal - $fxj - $fjfprice - $jfprice - $yjtotal;
   			$info['profit'] 			= $jd?$jd:0;
   			$info['tk']                 = $tk;
   			return $info;
   		}
   		return $data;
   }
   /**
    * 日盈利统计加入html
    * @param unknown $data
    */
   public function JoinHtml_Day_Statistics($data){
   	if(!empty($data)){
   		$html = "";
   		$html .= "<tr>";
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['ordertotal']."</font>元</td>";
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['yjtotal']."</font>元</td>";
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['fretotal']."</font>元</td>";
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">返嘿币(".$data['fjf'].")+嘿币抵现金(".$data['jfprice'].")+退款(".$data['tk'].")=支出(".$data['pay']."元)</font></td>";
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['profit']."</font>元</td>";
   		//$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['tctotal']."</font>元</td>";提成
   		$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data['actulpay']."</font>元</td>";
   		$html .="</tr>";
   		return $html;
   	}
   	return $data;
   }
}