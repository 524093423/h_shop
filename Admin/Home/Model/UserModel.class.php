<?php
/**
 * 用户模型
 * 20170513
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class UserModel extends Model {
   /**
    * 获取用户列表
    * @param 页码 $PageIndex
    * @param 搜索条件 $WhereStr
    * @param 搜索条件 $WhereStr2
    * @param 手机号或者用户昵称 $ext
    * @param 是否封号 $flag
    * @return \Think\mixed
    */
	public function GetUserList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag){
		$PageSize = "20";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "user_id";
		$Coll="user_id as uid,create_time as rtime,user_phone as phe,user_photo as uhead,user_name as nickname,state as isfh,integral as intl,account,register_type";
		$sql = SqlStr2($TableName="`user`",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="user_id",$OrderType="desc",$join,$joinid);
		$model = M("user");
		$count = $model->join($join)->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=User&a=UserList";
		if(!empty($id)){
			$url .="&id=".$id;
		}
		if(!empty($ext)){
			$url .="&ext=".$ext;
		}
		if(!empty($flag)){
			$url .="&flag=".$flag;
		}
		$url .="&page=";
		$data['page'] = PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 获取单个用户的信息
	 * @param 用户id $userid
	 * @param string $field 需要的数据
	 * @param string $where 查询条件
	 */
	public function getUserInfo($userid,$field="",$where=""){
		$model 	= M("user");
		$field 		= $field?$field:"user_name,user_phone,integral";
		$where 	= $where?$where:"user_id=$userid";
		$result 		= $model->field($field)->where($where)->find();
		return $result;
	}
	/**
	 * 获取用户使用总积分
	 * @param unknown $userid
	 * @return Ambigous <string, number>
	 */
	public function getIntegralTotal($userid){
		$model 	= M("goods_order");
		$result 		= $model->field("intlnum")->where("userid=$userid and ispay=2")->select();
		return  CountNumber($result,'intlnum');
	}
	/**
	 * 获取用户收货地址
	 * @param unknown $PageIndex
	 * @param unknown $userid
	 */
	public function GetUserAddress($PageIndex,$userid){
		$WhereStr 	= " AND user_id=$userid ";
		$PageSize = "5";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		$joinid = "user_id";
		$Coll="flag,consignee_address,consignee_phe,consignee_name,remarks";
		$sql = SqlStr2($TableName="`user_addressinfo`",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="flag",$OrderType="asc",$join,$joinid);
		$model = M("user_addressinfo");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		if($PageIndex == 1){
			$no = 1;
		}else{
			$no =($PageIndex -1) * $PageSize +1;
		}
		$data['list']  = $this->JoinHtml($model->query($sql),$no);
		$url = "admin.php?c=Single&a=GetUserAddress";
		if(!empty($userid)){
			$url .="&userid=".$userid;
		}
		$url .="&page=";
		$data['page'] = PageTextJS($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/**
	 * 将返利情况加入html
	 * @param unknown $data
	 */
	public function JoinHtml($data,$no){
		if(!empty($data)){
			$html = "";
			$no 	= $no?$no:1;
			for ($i=0;$i<count($data);$i++){
				if($data[$i]['flag'] ==2){
					$style 	= "";
					$title 	= "";
				}else{
					$title 	= "<font style=\"color:red\">[&nbsp;默认&nbsp;]</font>";
					$style="border:#ff0000 solid 1px;border-bottom:#ff0000 solid 1px;";
				}
				$html .= "<tr>";
				$html .= "<td align=\"center\" style=\"$style\">".$no.$title."</td>";
				$html .= "<td align=\"center\">".$data[$i]['consignee_name']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['consignee_phe']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['consignee_address']."</td>";
				$html .= "<td align=\"center\">".$data[$i]['remarks']."</td>";
				$html .="</tr>";
				$no++;
			}
			return $html;
		}
		return array();;
	}
}