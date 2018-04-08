<?php
/**
 * 后台管理系统消息模型
 * 冯晓磊
 * Date: 2017-11-29
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class MessageModel extends Model {
  /*
   * 获取APP系统消息
   * 2016.12.23
   */
	public function GetAppSystemMessage($PageIndex,$WhereStr="",$WhereStr2=""){
		//ID as id,Userid AS userid,Addtime AS atime,Content AS ct,CASE WHEN Userid = 0 THEN '所有用户'  ELSE '某位用户' END AS state
		$PageSize = "1";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		//$join = "inner join city on release_task.city_id = city.id ";
		$joinid = "ID";
		$Coll="ID as id,Userid AS userid,Addtime AS atime,Content AS ct,CASE WHEN Userid = 0 THEN '所有用户'  ELSE '某位用户' END AS state";
		$sql = D("Page")->SqlStr($TableName="notice",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="ID",$OrderType="desc",$join,$joinid);
		$model = M("notice");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Message&a=MessageSystem";
		$url .="&page=";
		$data['page'] = D("Page")->PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/*
	 * 获取APP用户消息
	 * 2016.12.23
	 */
	public function GetAppUserMessage($PageIndex,$WhereStr="",$WhereStr2=""){
		//ID as id,Userid AS userid,Addtime AS atime,Content AS ct,CASE WHEN Userid = 0 THEN '所有用户'  ELSE '某位用户' END AS state
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		//$join = "inner join city on release_task.city_id = city.id ";
		$joinid = "a_id";
		$Coll="question,answer,date_format(FROM_UNIXTIME(quetime), '%Y-%m-%d %H:%i:%s') AS qt,CASE WHEN IFNULL(anstime,0) THEN date_format(FROM_UNIXTIME(anstime), '%Y-%m-%d %H:%i:%s') ELSE '--'  END AS ats ,anusernick,isback,CASE WHEN isback=0 Then '未回复' ELSE '已回复' END as txtinfo,'用户' as usernick,a_id AS id";
		$sql = D("Page")->SqlStr($TableName="askinfo",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="isback",$OrderType="asc",$join,$joinid);
		$model = M("askinfo");
		$count = $model->where("1=1".$WhereStr)->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Message&a=MessageUser";
		$url .="&page=";
		$data['page'] = D("Page")->PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}
	/*
	 * 删除系统消息记录
	 * 2016.12.23
	 */
	public function delSysInfo_m($id){
		$model = M("notice");
		$return= $model->where("ID =$id")->delete();
		return $return;
	}
/*
	 * 删除系统消息记录
	 * 2016.12.23
	 */
	public function delUserInfo_m($id){
		$model = M("askinfo");
		$return= $model->where("a_id =$id")->delete();
		return $return;
	}

	/**
	 * @param string $PageIndex
	 * @return mixed
	 * 获取意见反馈记录
	 */
	public function GetAppFeedBack($PageIndex=""){
		$PageSize = "15";
		$PageIndex = $PageIndex?$PageIndex:1;
		$show = "5";
		//$join = "inner join city on release_task.city_id = city.id ";
		$joinid = "mid";
		$Coll="mid,userid,message,replycontent,`time`,issolve,CASE WHEN issolve=0 Then '未处理' ELSE '已授理' END as solvetext,left(message,10) as mtitle";
		$sql = SqlStr2($TableName="feedback",$Coll,"","",$PageIndex,$PageSize,$OrderKey="issolve",$OrderType="desc","",$joinid);
		$model = M("feedback");
		$count = $model->where("1=1")->count();
		//echo $model->getLastSql();die;
		$pagenum = ceil($count/$PageSize);
		$data['list']  = $model->query($sql);
		$url = "admin.php?c=Message&a=MessageFeedBack";
		$url .="&page=";
		$data['page'] =PageText($url,$PageIndex,$pagenum,$show);
		return $data;
	}

	/**
	 * @param $id
	 * @return int
	 * 反馈信息的删除
	 */
	public function feedBackDel($id){
		$model	= M("feedback");
		$result	= $model->where("mid in($id)")->delete();
		if(is_bool($result)){
			return 0;
		}
		return 1;
	}

	/**
	 * @param $userid
	 * @param $mid
	 * @param $content
	 * @return array
	 * 更新反馈信息的回复内容和站内信通知用户
	 */
	public function subReplyInfo($userid,$mid,$content){
		$model	= M("feedback");
		$data['issolve']	= 1;
		$data['replycontent']	= $content;
		if(!is_bool($model->where("mid=$mid")->save($data))){
			$this->addMessage($userid,"反馈消息回复",$content,1);
			return array("code"=>"200","message"=>"回复成功");
		}
		return array("code"=>"204","message"=>"回复失败");
	}
	/**
	 * [add 添加消息]
	 * @param [type] $user_id [description]
	 * @param [type] $message [description]
	 */
	public function addMessage($user_id,$title,$message,$type){
		$add = array(
				'user_id'=>$user_id,
				'title'=>$title,
				'message'=>$message,
				'type'=>$type,
				'create_time'=>date("Y-m-d H:i:s"),
				'is_read'=>2,
		);
		M('h_message')->add($add);
	}
}