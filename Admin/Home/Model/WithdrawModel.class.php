<?php
/**
 * 后台用户提现模型
 * 20170523
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class WithdrawModel extends Model {
	/**
	 * 提现列表
	 * @param unknown $PageIndex
	 * @param string $WhereStr
	 * @param string $WhereStr2
	 * @param unknown $ext
	 * @param unknown $flag
	 * @param unknown $type
	 * @return \Think\mixed
	 */
	public function GetWithList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag,$status){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "h_withdraw.with_id";
		$join = "inner join h_user_bank on h_user_bank.bank_id = h_withdraw.bank_id inner join h_seller_detail on h_seller_detail.seller_id = h_withdraw.seller_id";
		$Coll="h_withdraw.*,h_user_bank.number,h_seller_detail.seller_name,h_seller_detail.seller_phone,h_seller_detail.company_name";
		$sql = SqlStr2($TableName="h_withdraw",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="h_withdraw.create_time",$OrderType="desc",$join,$joinid,"",$WhereStr);
		$model = M("h_withdraw");
		$count = $model
		->join("inner join h_user_bank on h_user_bank.bank_id = h_withdraw.bank_id")
		->join("inner join h_seller_detail on h_seller_detail.seller_id = h_withdraw.seller_id")
		->where("1=1".$WhereStr)->count();
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$data['count'] = $count;
		$url = "admin.php?c=Withdraw&a=DrawList&status=".$status;
		if(!empty($id)){
			$url .="&id=".$id;
		}
		if(!empty($ext)){
			$url .="&ext=".$ext;
		}
		if(!empty($flag)){
			$url .="&flag=".$flag;
		}
		if(!empty($status)){
			$url .="&status=".$status;
		}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
		/**
	 * 将提现情况加入html
	 * @param unknown $data
	 */
	public function JoinHtml($data,$no,$auth){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				$html .= "<tr>";
				$html .= "<td align=\"center\">".$no."</td>";
				$html .= "<td align=\"center\">".$data[$i]['serialno']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['user_name']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['card_user']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['skzh']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['bankname']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['txtime']."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['price']."</font></td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['sjprice']."</font></td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['sxprice']."</font></td>";
				$html .= "<td align=\"center\">".$data[$i]['state']."</td>";
				$html .= "<td align=\"center\"><span id=\"auth".$data[$i]['nutid']."\">".$data[$i]['auth']."</span></td>";
				if($data[$i]['isauth'] == 1 && !empty($auth)){
					$html .= "<td align=\"center\"><a class='auth' rel=".$data[$i]['nutid']."  myuser=".$data[$i]['userid']." href='javascript:void(0)' ><img src=\"./Public/admin/images/auth.png\" style=\"width:20px;height:20px;\" title=\"审核\"/></a></td>";
				}else{
					$html .= "<td align=\"center\"><a class='authYes'  href='javascript:void(0)' >无操作</a></td>";
				}
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return $data;
	}
	public function GetUserWithdrawTotal($userid){
		$model 	= M("nmt_user_tx");
		$result 		= $model->field("price,sjprice")->where("userid=$userid")->select();
		$data['price'] 	= CountTotal($result, 'price');
		$data['sjprice'] 	= CountTotal($result, "sjprice");
		return $data;
	}
}