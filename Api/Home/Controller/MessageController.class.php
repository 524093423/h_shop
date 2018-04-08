<?php
/**
 * 消息控制器
 * 2017.06.15
 */
namespace Home\Controller;
use Think\Controller\RestController;
class MessageController extends RestController {
	public $user_id;
	public function __construct(){
		parent::__construct();
		$token = $_REQUEST['token'];
		$user = M('user')->where(array('token'=>$token))->field('user_id,state')->find();
		if(!$token || !$user['user_id']){
			$data['code'] = 204;
			$data['message'] ="请先登录";
			$this->response($data);
			return false;
		}
		if ($user['state'] == 1){
			$data['code'] = 204;
			$data['message'] ="对不起，此账号已被查封";
			$this->response($data);
			return false;
		}
		$this->user_id = $user['user_id'];
	}
	/**
	 * [messasge_un 消息最新读取条数和最新一条]
	 * @return [type] [description]
	 */
	public function unread(){
		$model	= M('h_message');
		$rr = M('h_message')->query("SELECT `m_id`,'user_id',`title`,`message`,`create_time` FROM `h_message` WHERE ( `type` = 3 ) order by m_id desc");
			foreach ($rr as $key => $v) {
				$ids = explode(',', $v['user_id']);
				if(!in_array($this->user_id,$ids)){
					// 没有读过的
					array_push($m_ids,$v['m_id']);
				}
			}
			
		$list= $model->field("m_id,type,message")->where("(user_id = ".$this->user_id." and is_read = 2 and type != 3) or (type = 3 and user_id Not like '%".$this->user_id."%')")->order("m_id desc")->select();//分开后的两个数组第一个就为最新消息
		$message1	= "没有未读系统消息";
		$message2	= "没有未读站内信消息";
		$listxt	= array();//系统消息
		$listznx= array();//站内信
		$m=0;
		$n=0;
		$xtunread	= 0;
		$znxunread	= 0;
		if(!empty($list)){
			for($i=0;$i<count($list);$i++){
				if($list[$i]['type']==1 ||  $list[$i]['type'] == 3){
					$listxt[$m]['m_id']	= $list[$i]['m_id'];
					$listxt[$m]['message']	= $list[$i]['message'];
					$m++;
				}elseif($list[$i]['type']==2){
					$listznx[$n]['m_id']	= $list[$i]['m_id'];
					$listznx[$n]['message']	= $list[$i]['message'];
					$n++;
				}
			}
			$xtunread	= count($listxt);//系统消息数量
			$znxunread	= count($listznx);//站内信消息数量
			if(!empty($listxt)){
				$message1	= $listxt[0]['message'];
			}
			if(!empty($listznx)){
				$message2	= $listznx[0]['message'];
			}
		}
		$list1['new_message'] = $message1;
		$list1['un_read'] = $xtunread;
		$list2['un_read'] = $znxunread;
		$list2['new_message'] =$message2;
		$data['code'] = 200;
        $data['message'] ="获取成功";
        $data['info']['list1'] =$list1;
        $data['info']['list2'] = $list2;
        $this->response($data,I("get.callback"));
	}
	
	/*
	 * 获取该用户是否存在未读的消息
	 */
	public function IsNewMessage(){
		$count	= M("h_message")->where("(user_id = ".$this->user_id." and is_read = 2 and type != 3) or (type = 3 and user_id Not like '%".$this->user_id."%') ")->count();
		if(!empty($count)){$isnew	= 1;}else{$isnew=2;}
		$data['code'] = 200;
		$data['message'] ="获取成功";
		$online=$this->Online();
		$data['info']['isnew'] =$isnew;
		$data['info']['online']=$online;
		$this->response($data,I("get.callback"));
	}
	/**
	 * [message_list 消息列表]
	 * @return [type] [description]
	 */
	public function message_list(){
		//type 1系统消息 2站内信
		$type = $_GET['type'];
		if($type == 1){
			// 系统所有人消息
			$rr = M('h_message')->query("SELECT `m_id`,`user_id`,`title`,`message`,`create_time` FROM `h_message` WHERE ( `type` = 3 ) order by m_id desc");

			foreach ($rr as $key => $v) {
				$ids = explode(',', $v['user_id']);
				if(empty($v['user_id'])){
					$uu = $this->user_id;
				}else{
					$uu = $v['user_id'].','.$this->user_id;
				}
				if(is_array($ids)){
					if(!in_array($this->user_id,$ids)){
						$rr2 = M('h_message')->query("UPDATE `h_message` SET `user_id` = '".$uu."' WHERE ( `m_id` = {$v['m_id']} ) AND ( `type` = 3 )");
					}
				}else if($this->user_id!=$ids){
						$rr2 = M('h_message')->query("UPDATE `h_message` SET `user_id` = '".$uu."' WHERE ( `m_id` = {$v['m_id']} ) AND ( `type` = 3 )");
				}
			}
			
			$result = M('h_message')->query("SELECT `m_id`,`title`,`message`,`create_time` FROM `h_message` WHERE ( `user_id` = $this->user_id ) AND ( `type` = 1 ) order by m_id desc");

			// 将$rr压入$result
			if(!empty($result)){
				$result	= array_merge($result,$rr);
			}else{
				$result	= $rr;
			}
			$result	= $this->array_sort($result,"m_id");
			$data['code'] = 200;
            $data['message'] ="获取成功";
			if($result){
				M('h_message')->query("UPDATE `h_message` SET `is_read`=1 WHERE ( `user_id` = $this->user_id ) AND ( `type` = 1 )");
				$data['info'] = $result;
			}else{
				$data['info'] = array();
			}
			$this->response($data,I("get.callback"));
		}else if($type == 2){
			$result = M('h_message')->query("SELECT `m_id`,`title`,`message`,`create_time` FROM `h_message` WHERE ( `user_id` = $this->user_id ) AND ( `type` = 2 ) order by m_id desc");
			$data['code'] = 200;
            $data['message'] ="获取成功";
			if($result){
				M('h_message')->query("UPDATE `h_message` SET `is_read`=1 WHERE ( `user_id` = $this->user_id ) AND ( `type` = 2 )");
				$data['info'] = $result;
			}else{
				$data['info'] = array();
			}
			$this->response($data,I("get.callback"));
		}else{
			$data['code'] = 204;
            $data['message'] ="获取失败，注意参数";
            $this->response($data,I("get.callback"));
			return false;
		}

	}
	/**
	 * [message_del 删除消息]
	 * @return [type] [description]
	 */
	public function message_del(){
		$m_id = $_GET['m_id'];
		$res = M('h_message')->where(array('user_id'=>$this->user_id,'m_id'=>$m_id))->delete();
		if($res){
			$data['code'] = 200;
            $data['message'] ="删除成功";
		}else{
			$data['code'] = 204;
            $data['message'] ="删除失败";
		}
        $this->response($data);
		return false;
	}
	/**
	 * 用户反馈
	 */
	public function feedBack(){
		$userid	= $this->user_id;
		if(empty($userid)){
			$this->response(array("code"=>204,"message"=>"用户未登录"));
		}
		$data['userid']	= $userid;
		$data['message']	= I("post.message");//反馈的消息
		$data['time']		= date("Y-m-d H:i:s");//反馈提交的时间
		$result				= D("Message")->saveBackInfo($data);
		$this->response($result);
	}
	public function  array_sort($array, $key){
		if(is_array($array)){
			$key_array = null;
			$new_array = null;
			for( $i = 0; $i < count( $array ); $i++ ){
				$key_array[$array[$i][$key]] = $i;
			}
			krsort($key_array);
			$j = 0;
			foreach($key_array as $k => $v){
				$new_array[$j] = $array[$v];
				$j++;
			}
			unset($key_array);
			return $new_array;
		}else{
			return $array;
		}
	}
}