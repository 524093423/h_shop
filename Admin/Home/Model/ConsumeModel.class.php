<?php
/**
 * 后台用户消费模型
 * 20170619
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class ConsumeModel extends Model {
	/**
	 * 获取个人消费情况
	 * @param 用户id $userid
	 * @param string $PageIndex 页码
	 * @param string $WhereStr  搜索条件
	 * @return \Think\mixed
	 */
	public function GetUserConsume($userid,$PageIndex="",$WhereStr=""){
		$PageSize = "4";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "goid";
		$Coll="actulpay as price,otime,paym";
		$sql = SqlStr($TableName="goods_order",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goid",$OrderType="desc",$join,$joinid);
		$model = M("goods_order");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		if($PageIndex == 1){
	 		$no = 1;
	 	}else{
	 		$no =($PageIndex -1) * $PageSize +1;
	 	}
		$data['list']  = $this->JoinHtml($model->query($sql),$no);
		$url = "admin.php?c=Single&a=GetUserConsumeInfo";
		$url .="&userid=".$userid;
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 将消费情况加入html
	 * @param unknown $data
	 */
	public function JoinHtml($data,$no){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				$html .= "<tr>";
				$html .= "<td align=\"center\">".$no."</td>";
				$html .= "<td align=\"center\">".$data[$i]['otime']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['paym']."</td>";
				$html .= "<td align=\"center\">￥<font style=\"color:red\">".$data[$i]['price']."</font></td>";
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return array();
	}
	/**
	 * 获取html
	 * 2017.03.10
	 */
	public function getHtml(){
		$html = "";
		$html .= "<tr>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\"></td>";
		$html .= "<td align=\"center\"></td>";
	    $html .= "<td align=\"center\">￥<font style=\"color:red\"></font></td>";
		$html .="</tr>";
	}
	/**
	 * 获取用户消费总金额
	 * @param unknown $userid
	 * @return Ambigous <string, number>
	 */
	public function getConsumeTotal($userid){
		$model 	= M("goods_order");
		$result 		= $model->field("actulpay,goodstotal,freprice")->where("userid=$userid and ispay=2")->select();
		$total['actulpay'] 	= CountTotal($result,'actulpay');
		$total['goodstotal'] 	= CountTotal($result, "goodstotal");
		$total['freprice'] 		= CountTotal($result, 'freprice');
		return $total;
	}
}