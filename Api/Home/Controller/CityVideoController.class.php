<?php

/*
同城
2018年3月20日10:39:23
*/ 
namespace Home\Controller;
header("content-type:text/html;charset=utf-8");
use Think\Controller\RestController;

class CityVideoController extends RestController {
	private $_USERID;
    public function __construct(){
        $token  = $_REQUEST['token'];
		$this->_USERID  = D("Register")->GetUserIdFromToken($token);

    }

	/*
	自动获取同城视频
	*/
	public function AutoCityVideo() {

	    $userid = $this->_USERID;

	    if(empty($userid)){
	        $this->response(reTurnJSONArray("204","用户信息错误"));
	    }

		if (empty($_REQUEST['city']) || is_null($_REQUEST['city'])) {
			$ns      = trim($_REQUEST['ns'],'"');
			if (!$ns) {
				$data['code'] = 200;
				$data['message'] = '获取地址位置失败';
				$this->response($data);
			}
			$city    = $this->analysisCitys($ns)->city;
			
			if (empty($city) || is_null($city)) {
				$city  = $this->analysisCitys($ns)->province;
			} 			
		}else{
			$city  = $_REQUEST['city'];
		}

		$model = M('city_video');

		$count = $model->where("INSTR(city,'{$city}') AND sort_id = 1 AND type = 1")->count();

		$page        = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

		$maxResult   = 20;  //每一页最多数据

		$totalPage   = ceil($count / $maxResult); //总页数

		//(页数 - 1) * 每页数据
		$start  = ($page -1) * $maxResult;

		$sql  = "SELECT id,user_id,title,cover,click,video_path FROM city_video WHERE INSTR(city,'{$city}') AND sort_id = 1  AND type = 1 LIMIT $start,$maxResult";

		$res  = $model ->query($sql);
		

		if (!$res) {
			$this->response(reTurnJSONArray("204","暂时无此地同城信息"));
		}

		$arr  = [];

		foreach ($res as $k => $v) {

			$userinfo = M('user')->field('user_name,user_photo,ns')->where(array('user_id'=>$v['user_id']))->find();

			if($v['click'] >= 100) {
				$v['hot'] = 1;
			}else{
				$v['hot'] = 0;
			}

			unset($v['user_id']);

			$start = $_REQUEST['ns'];

			$userinfo['distance'] = sprintf("%.1f",$this->calNs($start,$userinfo['ns']));
				
			unset($userinfo['ns']);

			$arr[$k] = array_merge($v,$userinfo);			
		}
		$data['code']      = 200;
		
		$data['message']   = '获取同城视频成功';

		$data['totalPage'] = $totalPage;

		$data['address']   = $city;

		$data['data']      = $arr; 

		$this->response($data);	    
	}

	/*
	视频评论
	@param video_id 视频ID
	@param content  评论
	@pid   是否是子评论 0代表不是
	*/
	public function comment_up() {
		$user_id  = $this->_USERID;
		$video_id = $_REQUEST['video_id'];
		$pid      = $_REQUEST['pid'] ? $_REQUEST['pid'] : 0;

		if (!$video_id) {
		$data['code'] = 204;
		$data['message'] = '请求数据不存在';
		$this->response($data);			
		}
		//检测用户是否登陆
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		//检测评论
		$content = $_REQUEST['content'];
		if (empty($content)) {
			$data['code'] = 204;
			$data['message'] = '评论不能为空';
			$this->response($data);			
		}
		if ($this->length_number($content) < 5) {
			$data['code'] = 204;
			$data['message'] = '评论字数不能小于5';
			$this->response($data);					
		}
		if ($this->length_number($content) > 255) {
			$data['code'] = 204;
			$data['message'] = '评论字数不能大于255';
			$this->response($data);					
		}

		//先检测评论是不是子评论
		if($pid == 0) {
			//更新评论表
			$arr = [
				'cid' => $video_id,
				'user_id' => $user_id,
				'pid' => 0,
				'content' => $content,
				'create_time' => time(),
				'fabulous_num' => 0,
				'reply_num'  => 0
			];

			$update = M('city_video_comment')->add($arr);
			//评论 +1			
			$commup = M('city_video')->where(array('id'=>$video_id))->setinc('num_commen');

			if ($update && $commup) {
				$data['code']    = 200;
				$data['message'] = '评论成功';
				$this->response($data);					
			}else{
				$data['code'] = 204;
				$data['message'] = '评论失败';
				$this->response($data);						
			}

		}else{
			$arr = [
				'cid' => $video_id,
				'user_id' => $user_id,
				'pid' => $pid,
				'content' => $content,
				'create_time' => time(),
				'fabulous_num' => 0,
				'reply_num'  => 0
			];			
			//检测这次评论是不是回复
			$res = M('city_video_comment')->where(array('id'=>$pid))->find();

			if ($res['pid'] != 0) {
				$from_id  = $_REQUEST['from_id'];
				if (!$from_id) {
					$data['code'] = 204;
					$data['message'] = '参数不正确';
					$this->response($data);						
				}
				$arr = [
					'cid' => $video_id,
					'user_id' => $user_id,
					'pid'     => $res['pid'],
					'content' => $content,
					'create_time' => time(),
					'fabulous_num' => 0,
					'reply_num' =>0,
					'from_uid'   => $from_id
				];
			}
			
			//评论的子评论数+1
			$reply_up = M('city_video_comment')->where(array('id'=>$pid))->setinc('reply_num');
			//更新视频表 评论+1
			$update = M('city_video_comment')->add($arr);

			$commup = M('city_video')->where(array('id'=>$video_id))->setinc('num_commen');

			$data['reply_num'] = M('city_video_comment')->where(array('id'=>$pid))->field('reply_num')->find()['reply_num'];

			if ($update && $commup && $reply_up) {
				$data['code']    = 200;
				$data['message'] = '评论成功';
				$this->response($data);	

			}else{
				$data['code'] = 204;
				$data['message'] = '评论失败';
				$this->response($data);						
			}		

		}
	}

	/*
	评论删除
	@video_id  视频Id
	@comm_id   评论id
	*/
	public function comment_del() {
		$user_id = $this->_USERID;
		$video_id = $_REQUEST['cid'];
		$comm_id = $_REQUEST['comm_id'];
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);			
		}
		//检测用户是否登陆
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		//检测是否是子评论
		$res  = M('city_video_comment')->where(array('id'=>$comm_id))->find();

		if($res['user_id'] != $user_id) {
			$data['code'] = 204;
			$data['message'] = '非法请求';
			$this->response($data);					
		}

		//如果是直接删除 否则删除评论的子评论
		if($res['pid'] != 0)  {

			//删除评论
			$comm_del = M('city_video_comment')->where(array('id'=>$comm_id))->delete();
			//视频评论 -1 
			$delte  = M('city_video')->where(array('id'=>$video_id))->setDec('num_commen');
			//父评论的子评论数 -1

			$reply_del = M('city_video_comment')->where(array('id'=>$res['pid']))->setDec('reply_num');

			if ($comm_del && $delte && $reply_del) {
				$data['code'] = 200;
				$data['message'] = '删除成功';
				$this->response($data);	
			}else{
				$data['code'] = 204;
				$data['message'] = '删除失败';
				$this->response($data);					
			}
		}else{
			//查找评论下的子评论

			$result = M('city_video_comment')->field('id')->where(array('pid'=>$comm_id))->select();

			$number = count($result);

			//循环子评论删除数据
			for ($i=0; $i <$number ; $i++) { 
				M('city_video_comment')->where(array('id'=>$result[$i]['id']))->delete();
			}

			//删除评论
			$comm_del = M('city_video_comment')->where(array('id'=>$comm_id))->delete();			

			//评论-评论数
			$delte  = M('city_video')->where(array('id'=>$video_id))->setDec('num_commen',$number+1);
			
			if ($delte && $comm_del) {
				$data['code'] = 200;
				$data['message'] = '删除成功';
				$this->response($data);	
			}else{
				$data['code'] = 204;
				$data['message'] = '删除失败';
				$this->response($data);					
			}

		}

	}

	/*
	视频评论点赞
	@param comment_id  评论id
	*/
	public function fabulous_up() {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$comment_id = $_REQUEST['comment_id'];
		//判断评论id 是否传来
		if (!$comment_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		//判断是否已经点赞
		$res = M('city_video_comment_fabulous')->where(array('comment_id'=>$comment_id,'user_id'=>$user_id))->find();

		if ($res) {
			//删除点赞表数据
			$del = M('city_video_comment_fabulous')->where(array('comment_id'=>$comment_id,'user_id'=>$user_id))->delete();

			//评论点赞数 -1
			$fabulous_del    = M('city_video_comment')->where(array('id'=>$comment_id))->setDec('fabulous_num');		
			
			if($del && $fabulous_del) {
				$data['code'] = 200;
				$data['message'] = '取消点赞成功';
				$this->response($data);				
			}else{
				$data['code'] = 204;
				$data['message'] = '取消点赞失败';
				$this->response($data);				
			}
		}

		//更新点赞表
		$arr = [
			'comment_id' => $comment_id,
			'user_id'    => $user_id,
			'create_time'=> time()
		];

		$fabulous_update = M('city_video_comment_fabulous')->add($arr);
		//更新点赞数
		$fabulous_add    = M('city_video_comment')->where(array('id'=>$comment_id))->setinc('fabulous_num');

		if ($fabulous_update && $fabulous_add) {
			$data['code'] = 200;
			$data['message'] = '点赞成功';
			$this->response($data);				
		}else{
			$data['code'] = 204;
			$data['message'] = '点赞失败';
			$this->response($data);				
		}
		

	}


	/*
	视频点赞
	*/
	public function videoFabulous_up() {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断评论id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		//判断是否已经点赞
		$res = M('city_video_fabulous')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->find();

		if ($res) {
			//删除点赞表数据
			$del = M('city_video_fabulous')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->delete();

			//评论点赞数 -1
			$fabulous_del    = M('city_video')->where(array('id'=>$video_id))->setDec('num_fabulous');	

			if($del && $fabulous_del) {
				$data['code'] = 200;
				$data['message'] = '取消点赞成功';
				$this->response($data);				
			}else{
				$data['code'] = 204;
				$data['message'] = '取消点赞失败';
				$this->response($data);				
			}									
		}

		//更新点赞表
		$arr = [
			'video_id' => $video_id,
			'user_id'    => $user_id,
			'create_time'=> time()
		];

		$fabulous_update = M('city_video_fabulous')->add($arr);
		//更新点赞数
		$fabulous_add    = M('city_video')->where(array('id'=>$video_id))->setinc('num_fabulous');

		if ($fabulous_update && $fabulous_add) {
			$data['code'] = 200;
			$data['message'] = '点赞成功';
			$this->response($data);				
		}else{
			$data['code'] = 200;
			$data['message'] = '点赞失败';
			$this->response($data);				
		}
	}

	/*
	视频收藏
	@param $video_id 帖子id
	*/
	public function collection_up () {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断评论id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		//判断是否已经收藏
		$res = M('city_video_collection')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->find();

		if ($res) {
			$data['code'] = 204;
			$data['message'] = '已经收藏过了';
			$this->response($data);		
		}		

		//更新收藏表
		$arr = [
			'video_id' => $video_id,
			'user_id'    => $user_id,
			'create_time'=> time()
		];
		$collection_update = M('city_video_collection')->add($arr);
		//更新收藏数
		$cpllection_add    = M('city_video')->where(array('id'=>$video_id))->setinc('num_collection');
		if ($collection_update && $cpllection_add) {
			$data['code'] = 200;
			$data['message'] = '收藏成功';
			$this->response($data);				
		}else{
			$data['code'] = 204;
			$data['message'] = '收藏失败';
			$this->response($data);				
		}

	}

	/*
	视频取消收藏
	*/
	public function collection_del () {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断视频id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}
		//检测是否收藏
		$res = M('city_video_collection')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->find();

		if (!$res) {

			$data['code'] = 204;
			$data['message'] = '尚未收藏,无法取消收藏';
			$this->response($data);	

		}				

		//删除收藏表数据
		$del = M('city_video_collection')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->delete();

		//评论点赞数 -1
		$collection_del    = M('city_video')->where(array('id'=>$video_id))->setDec('num_collection');	
		
		if($del && $collection_del) {
			$data['code'] = 200;
			$data['message'] = '取消收藏成功';
			$this->response($data);				
		}else{
			$data['code'] = 204;
			$data['message'] = '取消收藏失败';
			$this->response($data);				
		}		

	}
	/* 
	视频分享
	@param  $video_id  视频id
	*/
	public function share_add() {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断评论id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		//判断是否已经分享
		$res = M('city_video_share')->where(array('video_id'=>$video_id,'user_id'=>$user_id))->find();

		if ($res) {
			$data['code'] = 204;
			$data['message'] = '已经分享过了';
			$this->response($data);		
		}		

		//更新收藏表
		$arr = [
			'video_id'   => $video_id,
			'user_id'    => $user_id,
			'create_time'=> time()
		];
		$share_update = M('city_video_share')->add($arr);
		//更新收藏数
		$share_add    = M('city_video')->where(array('id'=>$video_id))->setinc('num_share');
		if ($share_update && $share_add ) {
			$data['code'] = 200;
			$data['message'] = '分享成功';
			$this->response($data);				
		}else{
			$data['code'] = 204;
			$data['message'] = '分享失败';
			$this->response($data);				
		}		
	}
	/*
	获取视频评论
	@param video_id 视频id

	*/
	public function getVideoComment() {
		$video_id = $_REQUEST['video_id'];
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '获取数据失败';
			$this->response($data);					
		}
		//根据视频id先获取评论 不包括子评论
		$comment = M('city_video_comment')->where(array('cid'=>$video_id,'pid'=>0))->order('fabulous_num DESC')->select();
		if (!$comment) {
			$data['code'] = 204;
			$data['message'] = '暂无评论';
			$this->response($data);	
		}
		$arr = [];
		$arr['code'] = 200;
		$arr['message'] = '获取评论成功';
		foreach ($comment as $key => $value) {

			if (is_null($this->_USERID) || empty($this->_USERID)) {
				$value['is_fabulous'] = 0;	
			}else{
				$value['is_fabulous'] = $this->is_fabulous($this->_USERID,$value['id']) ? 1 : 0;
			}

			$userinfo = M('user')->where(array('user_id'=>$value['user_id']))->field('user_name,user_photo')->find();

			$value['create_time'] = mdate($value['create_time']);
			unset($value['pid']);
			unset($value['cid']);
			$arr['data'][$key] = array_merge($value,$userinfo);
		}
		$this->response($arr);
		
	}
	/*
	获取评论的回复
	@param comment_id 评论id
	*/
	public function getCommentReply() {
		$comment_id = $_REQUEST['comment_id'];
		if (!$comment_id) {
			$data['code'] = 204;
			$data['message'] = '获取数据失败';
			$this->response($data);				
		}
		//根据评论id 获取回复
		$reply = M('city_video_comment')->where(array('pid'=>$comment_id))->select();
		if (!$reply) {
			$data['code'] = 204;
			$data['message'] = '暂无回复';
			$this->response($data);					
		}
		$arr = [];
		foreach ($reply as $k => $v) {
			$v['is_fabulous'] =  $this->is_fabulous($this->_USERID,$v['id']) ? 1 : 0;
			$arr[$k] = $v;
		}
		//循环获取用户数据
		$data['code'] = 200;
		$data ['message'] = '获取回复成功';
		foreach ($arr as $key => $value) {
			$userinfo = M('user')->field('user_name,user_photo')->where(array('user_id'=>$value['user_id']))->find();
			if ($value['from_uid'] == 0) {
				unset($value['from_uid']);
			}else{
				$from_userinfo = M('user')->where(array('user_id'=>$value['from_uid']))->field('user_name as from_name,user_photo as from_photo')->find();
				$userinfo = array_merge($userinfo,$from_userinfo);
			}
			unset($value['cid']);
			$data['data'][$key] = array_merge($value,$userinfo);			
		}
		$this->response($data);
	}

	/*
	获取视频详情
	@param $video      视频id
	*/
	public function getVideoDetails() {
		//检测用户是否登陆
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断视频id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		$res = M('city_video')->where(array('id'=>$video_id))->find();

		if (!$res) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}

		$video_userid = $res['user_id'];

		$userinfo = M('user')->where(array('id'=>$video_userid))->field('user_name,user_photo')->find();

		$arr = array_merge($res,$userinfo);

		//视频点击量+1
		$rr = M('city_video')->where(array('id'=>$video_id))->setinc('click');

		// 0代表未点赞  1代表点赞
		$arr['is_fabulous'] = $this->is_videoFabulous($user_id,$video_id) ? 1 : 0 ;
		// 0代表未收藏  1代表收藏
		$arr['is_collection'] = $this->is_collection($user_id,$video_id) ? 1 : 0 ;

		// 0代表未关注  1代表关注

		$arr['is_follow']   = $this->is_follow($user_id,$video_userid) ? 1 : 0;
		
		$data['code'] = 200;
		$data['message'] = '获取视频详情成功';
		$data['data']  = $arr;

		$this->response($data);

	}

	/*
	领取红包
	@param $video_id 视频id
	*/
	public function receiveRed ()  {
		$user_id = $this->_USERID;
		if(empty($user_id)) {
			$data['code'] = 204;
			$data['message'] = '登陆后才能操作';
			$this->response($data);				
		}

		$video_id = $_REQUEST['video_id'];
		//判断视频id 是否传来
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '请求数据不存在';
			$this->response($data);				
		}		
		//判断视频是否存在红包
		$red = M('city_video_red')->where(array('video_id'=>$video_id))->field('money,money_num')->find();

		$money = $red['money'];


		$num = $red['money_num'];

		if (!is_numeric($money)) {
			$data['code'] = 204;
			$data['message'] = '该视频不存在红包';
			$this->response($data);				
		}

		//判断此红包是否重复领
		$record = M('city_video_redenvelopes')->where(array('video_id'=>$video_id))->find();
		
		$users = explode(',', $record['users']);


		if (in_array($user_id, $users)) {
			$data['code'] = 204;
			$data['message'] = '已经领取过了';
			$this->response($data);	
		}

		//判断红包内是否还有余额
		$number = count($users);



		if ($money <= 0 || $number == $num) {

			$data['code'] = 200;
			$data['message'] = '你来晚了！红包被抢完了';
			$this->response($data);

		}


		//领取红包
		$moneys = explode(',', $record['money']);

		$count  = count($users) - 1;

		$redEnvelopes = $moneys[$count];

		$result = M('user')->where(array('user_id'=>$user_id))->setinc('account',$redEnvelopes);
        
		//删减红包余额
		$redUpdate = M('city_video_red')->where(array('video_id'=>$video_id))->setDec('money',$redEnvelopes);


		//领取用户id 添加到字段 

		array_push($users,$user_id);

		$str = trim(implode(',', $users),',');

		$sql = "UPDATE city_video_redenvelopes SET `users` = '{$str}'  WHERE video_id = {$video_id};";
		$rr  = M('city_video_redenvelopes')->execute($sql);
		if ($rr && $result && $redUpdate) {
			$data['code'] = 200;
			$data['message'] = "恭喜你领取了 {$redEnvelopes} 嘿币";
			$this->response($data);				
		}else{
			$data['code'] = 204;
			$data['message'] = '领取失败请重试';
			$this->response($data);						
		}
	}

	/*
	获取上传视频的地址,并存到数据库
	@param  $video_path
	*/
	public function getVideoPath() {
		$user_id = $this->_USERID;
		//检测用户是否登陆
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		//先传过来视频地址,添加视频表 返回视频id		
		$video_path = $_REQUEST['video_path'];
		if (!$video_path) {
			$data['code'] = 204;
			$data['message'] = '参数不正确';
			$this->response($data);			
		} 
		//判断用户是否是厂家  
		$seller = M('user')->where(array('user_id'=>$user_id))->field('is_seller')->find();
		if ($seller['is_seller'] == 0) {
			$type = 1;
		}else{
			$type = 2;
		}
		//添加视频表
		$arr = [
			'user_id'    => $user_id,
			'click'      => 0,
			'video_path' => $video_path,
			'uploadetime'=> time(),
			'num_commen' => 0,
			'num_fabulous'   => 0,
			'num_collection' => 0,
			'num_share'  => 0,
			'city'       => 'tmp',
			'title'      => 'tmp',
			'cover'      => 'tmp',
			'sort_id'    => 0,
			'type'       => $type,
		];
		$res  = M('city_video')->add($arr);
		if (!$res) {
			$data['code']    = 204;
			$data['message'] = '添加错误';
			$this->response($data);	
		}else{
			$id   = M('city_video')->getLastInsID();
			$data['code']    = 200;
			$data['message'] = '添加成功';
			$data['video_id']= $id;
			$this->response($data);				
		}
	}

	/*
	获取上传封面 
	@param $video_id   视频id
	@parm  $cover_path 图片地址
	*/
	public function getCoverPath() {
		$user_id = $this->_USERID;
		//检测用户是否登陆
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		//搜索更新		
		$cover_path = $_REQUEST['cover_path'];
		$video_id   = $_REQUEST['video_id'];
		if (!$cover_path || !$video_id) {
			$data['code'] = 204;
			$data['message'] = '参数不正确';
			$this->response($data);			
		} 	
		//搜索视频表更新他
		$res = M('city_video')->where(array('user_id'=>$user_id,'id'=>$video_id))->find();
		if (!$res) {
			$data['code'] = 204;
			$data['message'] = '搜索不到此视频id';
			$this->response($data);				
		}
		//更新视频表
		$sql    = "UPDATE city_video SET cover = '{$cover_path}' WHERE id = {$video_id} AND user_id = {$user_id};";
		$result = M('city_video')->execute($sql);
		if (!$result) {
			$data['code'] = 204;
			$data['message'] = '添加视频封面地址失败';
			$this->response($data);				
		}else{
			$data['code'] = 200;
			$data['message'] = '添加视频封面地址成功';
			$this->response($data);					
		}
	}

	/* 
	整合上传
	@pararm $video_id  视频Id
	*/
	public function add() {
		$user_id = $this->_USERID;
		//检测用户是否登陆
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		//搜索更新		
		$video_id   = $_REQUEST['video_id'];
		if (!$video_id) {
			$data['code'] = 204;
			$data['message'] = '参数不正确';
			$this->response($data);			
		} 

		$title    = $_REQUEST['title'];  //标题
		$dingdan  = $_REQUEST['dingdan']; //红包支付成功订单号
		$ns       = $_REQUEST['ns'];     //地理位置
		$classify = $_REQUEST['classify']; //分类Id

		//城市检测
		if (empty($ns)) {
			$res  = $this->analysisIp($_SERVER['REMOTE_ADDR']);	//没有经纬度从ip查询
			$city = $res->city;
		}else{
			$arr  = explode(',', $ns);
			$json = $this->analysis($arr[0],$arr[1]);
			$city = $json->city;
		}

		//检测标题
		$content = $_REQUEST['title'];
		if (empty($content)) {
			$data['code'] = 204;
			$data['message'] = '标题不能为空';
			$this->response($data);			
		}
		if ($this->length_number($content) < 5) {
			$data['code'] = 204;
			$data['message'] = '标题字数不能小于5';
			$this->response($data);					
		}
		if ($this->length_number($content) > 255) {
			$data['code'] = 204;
			$data['message'] = '标题字数不能大于255';
			$this->response($data);					
		}
		//更新视频表
		$sql = "UPDATE city_video SET city = '{$city}', title = '{$content}', sort_id = {$classify} WHERE id = {$video_id} AND user_id = {$user_id};";

		$res = M('city_video')->execute($sql);
		$arr = [
			'cid'    => $video_id,
			'user_id'=> $user_id,
			'pid'    => 0,
			'content'=> $content,
			'create_time'  => time(),
			'fabulous_num' => 0,
			'reply_num'    => 0,
			'from_uid'     => 0,
			'is_message'   => 1,
		];
		M('city_video_comment')->add($arr);
		//红包检测
		if (!empty($dingdan) || !is_null($dingdan)) {
			//检测订单是否存在
			$model = M('city_video_red');
			$redInfo = $model->where(array('dingdan'=>$dingdan))->find();
			if (!$redInfo) {
				$this->repsonse(reTurnJSONArray('204','订单号不存在'));
			}

			//检测订单号是否已经使用
			if($redInfo['video_id']) {
				$data['code'] = 204;
				$data['message'] = '订单号重复使用';
				$this->response($data);
			}
			//不为空 更新红包表
			$arr = $this->sendRandBonus($redInfo['money'],$redInfo['money_num']);
			$str = implode(',', $arr);
			$array = [
				'video_id' => $video_id,
				'money'    => $str
			];
			//更新红包订单表中的id
			M('city_video_red')->query("UPDATE city_video_red SET video_id = {$video_id} WHERE dingdan = '{$dingdan}'; ");
			$redUpdate = M('city_video_redenvelopes')->add($array);			
		}

		if (!is_bool($res)) {
			$this->response(reTurnJSONArray("200","上传视频成功"));
		}else{
			$this->response(reTurnJSONArray("204","上传视频失败"));
		}		

	}

	/*
	红包支付
	*/
	public function redPay() {
		$user_id = $this->_USERID;
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);			
		}
		$money = $_REQUEST['money'];
		$money_num = $_REQUEST['money_num'];

		//检测用户余额是否充足
		$model = M('user');

		$userinfo = $model->where(array('user_id'=>$user_id))->field('integral')->find();

		if ($userinfo['integral'] < $money) {
			$data['code'] = 204;
			$data['message'] = '账户余额不足请充值';
			$this->response($data);					
		}
		$model->startTrans();
		$integral = $model->where(array('user_id'=>$user_id))->setDec('integral',$money);
		$orderid  = $this->AddOrderId();
		$message  = $_REQUEST['message'];
		//先去生成订单号 填充到数据库
		$arr = [
			'user_id'=> $user_id,
			'money'  => $money,
			'dingdan'=> $orderid,
			'money_num' => $money_num,
			'message' => $message 
		];
		$redUpdate = M('city_video_red')->add($arr);
		if ($integral) {
			$model->commit();
			$data['code'] = 200;
			$data['message'] = '红包支付成功';
			$data['order'] = $orderid;
			$this->response($data);
		}else{
			$model->rollback();
			$data['code'] = 204;
			$data['message'] = '红包支付失败';
			$this->response($data);
		}	
	}

	/* 
	红包退款
	*/
	public function redRefund () {
		$user_id = $this->_USERID;
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);				
		}
		$dingdan = $_REQUEST['dingdan'];
		//判断订单
		if (empty($dingdan)) {
			$data['code'] = 204;
			$data['message'] = '订单不能为空';
			$this->response($data);			
		}
		//搜索订单
		$info  = M('city_video_red')->where(array('dingdan' => $dingdan))->find();
		if (!$info) {
			$data['code'] = 204;
			$data['message'] = '订单编号不存在';
			$this->response($data);					
		}
		if (!empty($info['video_id'])) {
			$data['code'] = 204;
			$data['message'] = '红包已经使用!';
			$this->response($data);				
		}
		//判断用户id是否跟 红包用户id一致
		if ($user_id != $info['user_id']) {
			$data['code'] = 204;
			$data['message'] = '这个红包不是你的';
			$this->response($data);					
		}
		//更新用户表
		$model = M('user');
		$model->startTrans();
		$res   = M('user')->where(array('user_id'=>$info['user_id']))->setinc('integral',$info['money']);
		//删除订单号
		$rr    = M('city_video_red')->where(array('dingdan' => $dingdan))->delete();
		if ($rr && $res) {
			$model->commit();
			$data['code'] = 200;
			$data['message'] = '红包退款成功';
			$this->response($data);
		}else{
			$model->rollback();
			$data['code'] = 204;
			$data['message'] = '红包退款失败';
			$this->response($data);
		}	
	}

	/*
	生成唯一订单号
	*/
	public function AddOrderId() {
		$danhao = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
		return $danhao;	
	}


	/*
	根据经纬度解析城市
	@ns 
	*/
	public function analysisCity($ns) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/geocode/regeo?key='.$key.'&location='.$ns;

		$res = file_get_contents($url);

		$ob = json_decode($res);

		$city =  $ob->regeocode->addressComponent->city;
		$data['code'] = 200;
		$data['message'] = '解析成功';
		$data['city'] = $city;
		$this->response($data);
	}

	/*
	根据经纬度解析城市
	@ns 
	*/
	public function analysisCitys($ns) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/geocode/regeo?key='.$key.'&location='.$ns;

		$res = file_get_contents($url);

		$ob = json_decode($res);

		$city =  $ob->regeocode->addressComponent;
		return $city;
	}
	/*
	随机红包算法
	*/
	private function sendRandBonus ($total_bean, $total_packet) {    
	    $min = 1;
	    $max = $total_bean -1;
	    $list = [];
	    
	    $maxLength = $total_packet - 1;
	    while(count($list) < $maxLength) {
	        $rand = mt_rand($min, $max);
	        empty($list[$rand]) && ($list[$rand] = $rand);
	    }
	    
	    $list[0] = 0; //第一个
	    $list[$total_bean] = $total_bean; //最后一个
	    
	    sort($list); //不再保留索引
	    
	    $beans = [];
	    for ($j=1; $j<=$total_packet; $j++) {
	        $beans[] = $list[$j] - $list[$j-1];
	    }
	    
	    return $beans;
	}


	/*
	是不是已经关注了此用户
	@param  $uid       用户id
	@param  $fid       需要对照的用户id
	@return bool       已经关注返回true  没关注返回false
	*/	
	protected function is_follow($uid,$fid) {
		$res = M('follow')->where(array('user_id'=>$uid,'follow_id'=>$fid))->find();

		if($res){
			return true;
		}
		return false;
	}

	/*
	评论是否点赞
	@param  $uid         用户id
	@param  $comment_id  帖子id
	return  bool 已经点赞返回true 未点赞返回false
	*/
  	protected function is_fabulous($uid,$comment_id) {
  		$result = M('city_video_comment_fabulous')->where(array('user_id'=>$uid,'comment_id'=>$comment_id))->find();
  		if($result) {
  			return true;
  		}
  		return false;
  	}

	/*
	视频是否收藏
	@param  $uid         用户id
	@param  $video_id    帖子id	
	return  bool 已经收藏返回true 未关注收藏false
	*/
	protected function is_collection($uid,$video_id) {
		$result = M('city_video_collection')->where(array('video_id'=>$video_id,'user_id'=>$uid))->find();
  		if($result) {
  			return true;
  		}
  		return false;		
	}
	/*
	视频是否点赞
	@param  $uid         用户id
	@param  $video_id  帖子id
	return  bool 已经点赞返回true 未点赞返回false	
	*/
	protected function is_videoFabulous($uid,$video_id) {
		$result = M('city_video_fabulous')->where(array('video_id'=>$video_id,'user_id'=>$uid))->find();
  		if($result) {
  			return true;
  		}
  		return false;			
	}

	/*
	根据经纬度解析
	@x
	@y     经纬度
	*/
	protected function analysis($x,$y) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/geocode/regeo?key='.$key.'&location='.$x.','.$y;

		$res = file_get_contents($url);

		$ob = json_decode($res);

		return $ob->regeocode->addressComponent;
	}

	/*
	根据ip解析
	@ip ip真实地址
	*/
	protected function analysisIp($ip) {

		$url = 'http://ip.taobao.com//service/getIpInfo.php?ip=' . $ip;


		$res = file_get_contents($url);

		$ob  = json_decode($res);

		$data = $ob->data;

		return $data;
	}
	/*
	根据经纬度计算距离
	@start  string 开始经纬度
	@end    string 结束经纬度
	以千米做单位
	http://restapi.amap.com/v3/distance?key=e7f2b31552584c5c2ea743466df4eb54&origins=113.66053999999997,34.75358&destination=116.397499,39.908722; 高德接口 
	*/

	protected function calNs($start,$end) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/distance?key='.$key.'&origins='.$start.'&destination='.$end;

		$res = file_get_contents($url);

		$ob  = json_decode($res);

		$number = $ob->results[0]->distance;
		
		return $number/1000;
	}

    /*
	字符串长度检测
    */
    private function length_number($str) {

        preg_match_all("/./us", $str, $match);

        $str_arr=$match[0];

        $length_val=count($str_arr);//字符串长度 

		return $length_val;  	
	}
	private function array_newKey($arr) {

		$num = 0;
		$newArr = [];
		foreach($arr as $value) {
			$newArr[$num] = $value;
			$num++;
		}
		return $newArr;
	}
}