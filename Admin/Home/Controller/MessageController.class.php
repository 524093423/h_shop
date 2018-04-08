<?php
/*
 * 后台管理消息管理
 */
namespace Home\Controller;
use Think\Controller;
class MessageController extends CheckController {
 	 private $_RELEASEPOST;
	 public function _initialize(){
	 	
	 }
	 /*
	  * 系统消息
	  * 2016.12.23
	  */
	 public function MessageSystem(){
	 	$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no = ($page -1) * 15 +1;
	 	}
	 	$data = D("Message")->GetAppSystemMessage($page);
	 	$list = $data['list'];
	 	$page = $data['page'];
	 	$this->assign("list",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
	 	$this->display("message_system");
	 }
	 /*
	  * 用户消息
	  * 2016.12.23
	  */
	 public function MessageUser(){
	 	$page = $_GET['page']?$_GET['page']:1;
	 	if($page == 1){
	 		$no = 1;
	 	}else{
	 		$no = ($page -1) * 15 +1;
	 	}
	 	$data = D("Message")->GetAppUserMessage($page);
	 	$list = $data['list'];
	 	$page = $data['page'];
	 	$this->assign("list",$list);
	 	$this->assign("page",$page);
	 	$this->assign("no",$no);
	 	$this->display("message_user");
	 }
	 /*
	  * 保存客服回复的消息
	  * 2016.12.23
	  */
	 public function SubAskInfo(){
	 	$id=$_POST['id'];
	 	$content = $_POST['info'];
	 	$model = M("askinfo");
	 	$data['answer'] = $content;
	 	$data['isback'] = 1;
	 	$data['anstime']= time();
	 	$data['anusernick'] = "客服";
	 	$return = $model->where("a_id=$id")->save($data);
	 	if(!empty($return)){
	 		echo 1;
	 	}else{
	 		echo -1;
	 	}
	 }
	 /*
	  * 新增系统消息
	  * 2016.12.23
	  */
	 public function SubSystem(){
	 	$conten = $_POST['info'];
	 	$userid = $_POST['userid'];
	 	$data['Content'] = $conten;
	 	$data['Userid']  = $userid?$userid:0;
	 	$data['Addtime'] = date("Y/m/d H:i:s");
	 	$model = M("notice");
	 	$return = $model->data($data)->filter('strip_tags')->add();
	 	if(!empty($return)){
	 		echo 1;
	 	}else{
	 		echo -1;
	 	}
	 }
	 /*
	  * 获取用户提问的信息数量
	  * 2016.12.29
	  */
	 public function getUserQuestion(){
	 	$m = M("askinfo");
	 	$count = $m->where("isback =0")->count();
	 	echo $count;
	 }
	/**
	 *意见反馈
	 */
	public function MessageFeedBack(){
		$page = $_GET['page']?$_GET['page']:1;
		if($page == 1){
			$no = 1;
		}else{
			$no = ($page -1) * 15 +1;
		}
		$data = D("Message")->GetAppFeedBack($page);
		$list = $data['list'];
		$page = $data['page'];
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->assign("no",$no);
		$this->display("feedback_list");
	}
	/**
	 * 反馈信息的删除
	 */
	public function FeedBackDelAll(){
		$idstr	= I("post.idstr");
		if(strpos($idstr,",")){
			$idstr	= substr($idstr,0,(strlen($idstr)-1));
		}
		$data	= D("Message")->feedBackDel($idstr);
		echo $data;
	}

	/**
	 * 反馈消息回复操作
	 */
	public function ReplyFeedBack(){
		$userid	= I("post.userid");
		$mid	= I("post.mid");
		$content	= I("post.content");
		$data	= D("Message")->subReplyInfo($userid,$mid,$content);
		exit(json_encode($data));
	}
}