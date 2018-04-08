<?php
/**
 * 农牧通后台用户评论模型
 * 冯晓磊
 * 2017.03.10
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class RateModel extends Model {
	/**
	 * 获取个人评论情况
	 * @param 用户id $userid
	 * @param string $PageIndex 页码
	 * @param string $WhereStr  搜索条件
	 * @return \Think\mixed
	 * 更新bid信息2017.03.21
	 * 新分页去掉使用新分页
	 */
	public function GetRate($PageIndex="",$date1,$date2,$search,$bid){
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$WhereStr   .= createTime($date1, $date2,"goods_rate.ratetime");
		if(!empty($search)){
			$WhereStr .="  AND ( INSTR(goods.gname,'".$search."') OR INSTR(`user`.user_name,'".$search."') )";
		}
		if(!empty($bid)){
			$WhereStr .=" AND goods.bid=$bid";
		}
		if($PageIndex == 1){
			$no = 1;
			$min = 0;
			$max = $no  * $PageSize;
		}else{
			$no =($PageIndex -1) * $PageSize +1;
			$min = $no;
			$max = $PageIndex  * $PageSize;
		}
		$show = "5";
		$joinid = "grid";
		$join = "LEFT JOIN goods ON goods_rate.ggid=goods.gid LEFT JOIN `user` ON goods_rate.userid=`user`.user_id";
		$Coll="rateinfo AS pltext,ratetime AS plsj,goods.gname,user_name,gname,grid";
		$sql = SqlStr2($TableName="goods_rate",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="grid",$OrderType="desc",$join,$joinid);//新版分页$min,$max
		$model = M("goods_rate");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $this->JoinHtml($model->query($sql),$no);
		$url = "admin.php?c=Rate&a=GetUserRate";
		$url .="&search=".$search;
		$url .="&st=".$date1;
		$url .="&ed=".$date2;
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 将评论情况加入html
	 * @param unknown $data
	 */
	public function JoinHtml($data,$no){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				$html .= "<tr>";
				$html .="<td  align=\"center\">  <input name=\"ck\"  type=\"checkbox\" class=\"cbk\"  value=".$data[$i]['grid']."/></td>";
				$html .= "<td align=\"center\">".$no."</td>";
				$html .= "<td align=\"center\">".$data[$i]['gname']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['user_name']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['plsj']."</td>";
				$html .= "<td align=\"center\" style=\"overflow:hidden\"><font style=\"color:red\"><a href=\"javascript:void(0)\" onclick=\"looks('".$data[$i]['pltext']."')\">".$data[$i]['pltext']."</font></td>";
				$html .="<td align=\"center\"><a class=\"del\" href=\"javascript:void(0)\"   rel=".$data[$i]['grid']." ><img  src=\"./Public/admin/images/delete.gif\"  title=\"删除\" alt=\"删除\"/></a></td>";
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return $data;
	}
}