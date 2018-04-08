<?php
/**
 * 站内信系统消息模型
 *2017.6.10
 */
namespace Home\Model;
use Think\Model;
header("Content-code: text/html; charset=UTF-8");
class MessageModel extends Model {
	/**
	 * [add 添加消息]
	 * @param [type] $user_id [description]
	 * @param [type] $message [description]
	 */
	public function add($user_id,$title,$message,$type){
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

	/**
	 * @param $data
	 * @return array
	 * 保存用户反馈信息
	 */
	public function saveBackInfo($data){
		$model	= M("feedback");
		$result	= $model->data($data)->add();
		if(!is_bool($result)){
			return array("code"=>200,"message"=>"提交成功");
		}else{
			return array("code"=>204,"message"=>"连接超时");
		}
	}
}