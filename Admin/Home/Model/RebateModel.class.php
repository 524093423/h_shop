<?php
/**
 * 农牧通后台用户返利模型
 * 冯晓磊
 * 2017.03.10
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class RebateModel extends Model {
	/**
	 * 获取个人返利情况
	 * @param 用户id $userid
	 * @param string $PageIndex 页码
	 * @param string $WhereStr  搜索条件
	 * @return \Think\mixed
	 */
	public function GetRebate($userid,$PageIndex="",$WhereStr=""){
		$PageSize = "4";
		$WhereStr .= "  AND isretotal =1 ";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "odid";
		$join 	= "LEFT JOIN `user` ON order_detail.userid= `user`.user_id";
		$Coll="gname,rebatetotal AS flzje,remaintotal AS flsyje,sjtime,reintl AS jfsl,isfl,odid";
		$sql = SqlStr2($TableName="order_detail",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="odid",$OrderType="desc",$join,$joinid);
		$model = M("order_detail");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		if($PageIndex == 1){
	 		$no = 1;
	 	}else{
	 		$no =($PageIndex -1) * $PageSize +1;
	 	}
		$data['list']  = $this->JoinHtml($model->query($sql),$no);
		$url = "admin.php?c=Single&a=GetUserRebate";
		$url .="&userid=".$userid;
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 将返利情况加入html
	 * @param unknown $data
	 */
	public function JoinHtml($data,$no,$state=""){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				$total 	= $this->GetSellerSaleTotal($data[$i]['bid']);
				if($total > (int)$data[$i]['flzje']){
					$style 	= "";
					$title 	= "";
				}else{
					$title 	= "<font style=\"color:red\">[&nbsp;预警&nbsp;]</font>";
					$style="border:#ff0000 solid 1px;border-bottom:#ff0000 solid 1px;";
				}
				if($data[$i]['isfl'] == 1){
					$text = "返利已暂停";
				}else{
					$text 	= "正常返利中";
				}
				$html .= "<tr>";
				$html .= "<td align=\"center\" style=\"$style\">".$no."".$title."</td>";
				if($state ==1){
					$html .= "<td align=\"center\">".$data[$i]['user_name']."</td>";
				}
				$html .= "<td align=\"center\">".$data[$i]['sjtime']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['gname']."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['flzje']."</font></td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['flsyje']."</font></td>";
				$html .= "<td align=\"center\">".$data[$i]['jfsl']."(积分)</td>";
				$html .= "<td align=\"center\"><span class=fl".$data[$i]['odid'].">".$text."</span></td>";
				if($data[$i]['isfl'] == 0){
					$y 	= 0;
					$sg = 1;
				}else{
					$y 	= 1;
					$sg = 0;
				}
				if($state ==1){
					$html .= "<td align=\"center\"><a href=\"javascript:void(0)\" onclick=\"look(".$data[$i]['odid'].")\">查看</a>&nbsp;|&nbsp;<a class='auth' myuser=".$data[$i]['userid']." rel=".$data[$i]['odid']."  ystate=".$y." sgstate=".$sg." href='javascript:void(0)'>审核</a></td>";
				}
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return array();;
	}
	/**
	 * 获取返利总金额
	 * 返利剩余总金额
	 * 返利总积分
	 * @param unknown $userid
	 * @return Ambigous <string, number>
	 */
	public function GetRabateTotal($userid){
		$model 	= M("order_detail");
		$result 		= $model->field("rebatetotal AS flzje,remaintotal AS flsyje,reintl AS jfsl")->where("ispay=2 And isretotal =1 And userid=$userid")->select();
		$rabate['jf'] 		= CountNumber($result, "jfsl");
		$rabate['flzje'] 	= CountTotal($result, "flzje");
		$rabate['flsyje'] 	= CountTotal($result, "flsyje");
		return $rabate;
	}
	/**
	 * 获取返利列表
	 * @param 
	 * @param string $PageIndex 页码
	 * @param string $WhereStr  搜索条件
	 * @return \Think\mixed
	 */
	public function GetRebateList($PageIndex="",$date1="",$date2="",$search=""){
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr .= "  AND isretotal =1 ";
		$WhereStr .= createTime($date1, $date2,"sjtime");
		if(!empty($search)){
			$WhereStr 	.= " AND INSTR(`user`.user_name,'".$search."') OR INSTR(order_detail.gname,'".$search."')";
		}
		$show = "5";
		$joinid = "odid";
		$join 	= "LEFT JOIN `user` ON order_detail.userid= `user`.user_id LEFT JOIN goods_order ON order_detail.goid2 = goods_order.goid";
		$Coll="gname,rebatetotal AS flzje,remaintotal AS flsyje,sjtime,reintl AS jfsl,isfl,user_name,odid,order_detail.userid,bid";
		$sql = SqlStr2($TableName="order_detail",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="odid",$OrderType="desc",$join,$joinid);
		$model = M("order_detail");
		$count = $model->join($join)->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		if($PageIndex == 1){
			$no = 1;
		}else{
			$no =($PageIndex -1) * $PageSize +1;
		}
		$data['list']  = $this->JoinHtml($model->query($sql),$no,1);
		$url = "admin.php?c=Rebate&a=GetRebateList";
		$url .="&date1=$date1&date2=$date2&search=$search";
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 返利详情列表
	 * @param unknown $page
	 * @param unknown $date1
	 * @param unknown $date2
	 * @param unknown $odid
	 */
	public function GetRebateDetail_m($PageIndex,$date1,$date2,$odid){
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr .= "  AND odid2 =$odid";
		$WhereStr .= createTime($date1, $date2,"fftime");
		$show = "5";
		$joinid = "ntdid";
		$Coll="ntdid,userid1,flprice,fftime,isff,isdz";
		$sql = SqlStr2($TableName="nmt_tx_detail",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="ntdid",$OrderType="desc",$join,$joinid);
		$model = M("nmt_tx_detail");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		if($PageIndex == 1){
			$no = 1;
		}else{
			$no =($PageIndex -1) * $PageSize +1;
		}
		$data['list']  = $this->JoinHtml_detail($model->query($sql),$no);
		$url = "admin.php?c=Rebate&a=GetRebateDetail";
		$url .="&date1=$date1&date2=$date2&odid=$odid";
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 将返利详情加入html
	 * @param unknown $data
	 */
	public function JoinHtml_detail($data,$no){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				if($data[$i]['isff'] == 2){
					$text = "返利已暂停";
					$y 		= 2;
					$sg 	= 1;
				}else{
					$text 	= "正常返利中";
					$y 		= 1;
					$sg 	= 2;
				}
				if($data[$i]['isdz'] == 2){
					$text1= "到账";
				}else{
					$text1 	= "未到账";
				}
				$html .= "<tr>";
				$html .= "<td align=\"center\">".$no."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['flprice']."</font></td>";
				$html .= "<td align=\"center\">".$data[$i]['fftime']."</td>";
				$html .= "<td align=\"center\">".$text1."</font></td>";
				$html .= "<td align=\"center\"><span class=detail".$data[$i]['ntdid'].">".$text."</td>";
				if($data[$i]['isdz'] ==1){
					$html .= "<td align=\"center\"><a class='auth' myuser=".$data[$i]['userid1']." rel=".$data[$i]['ntdid']."  ystate=".$y." sgstate=".$sg." href='javascript:void(0)'>审核</a></td>";
				}else{
					$html .= "<td align=\"center\">---</td>";
				}
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return $html;
	}
	/**
	 * 获取商家销售额 的 15%
	 * @param unknown $bid
	 * @return \Think\mixed
	 */
	public function GetSellerSaleTotal($bid){
		$model 		= M("goods_order");
		$sql 				= "select SUM(actulpay) as sr,SUM(freprice) as yf from goods_order where bid=$bid and ispay=2";
		$result 			= $model->query($sql);
		if(is_bool($result)){
			$sr 		= $result[0]['sr'];//收入
			$yf 		= $result[0]['yf'];//运费
			$total 	= $sr - $yf;
			$total 	= sprintf("%.2f",$total * 0.15);
		}else{
			$total 	= 0;
		}
		return $total;
	}
}