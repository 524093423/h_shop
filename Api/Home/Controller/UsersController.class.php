<?php
/*
用户控制器
2018年3月22日14:07:24
贺小奎
*/
namespace Home\Controller;
header('content-type:text/html;charset=utf-8');
use Think\Controller\RestController;
class UsersController extends RestController {
    private $_USERID;
    private $_MODEL;
    public function __construct(){
		$token  = $_REQUEST['token'];
		if (!$token) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);
		}
		
		$this->_USERID  = D("Register")->GetUserIdFromToken($token);
		
        $this->_MODEL  = M('user');
    }
    /*
	关注用户/取消关注用户
    */
	public function follow_store() {
		$model = $this->_MODEL;
		$user_id = $this->_USERID;
		$follow_id = $_REQUEST['follow_id'];
		$state = $_REQUEST['state'];   //1.关注  2.取消关注
		
		$se = $this->is_follow($user_id,$follow_id);
		if ($se) {
			//用户关注 -1 	取关者粉丝 -1
			$follow_del = $model->where(array('user_id'=>$user_id))->setDec('follow');

			$fans_del  = $model->where(array('user_id'=>$follow_id))->setDec('fans');

			//更新用户关注列表
			$user_follow_del = M('follow')->where(array('user_id'=>$user_id,'follow_id'=>$follow_id))->delete();

			if ($follow_del && $fans_del && $user_follow_del)	{
				$data['code'] = 200;
				$data['message'] = "取消关注成功";
				$this->response($data);		
			}else{
				$data['code'] = 204;
				$data['message'] = "取消关注失败";
				$this->response($data);					
			}
		}
		//被关注者粉丝+1  关注者关注+1
		$fans_up = $model->where(array('user_id'=>$follow_id))->setinc('fans');
		$follow_up = $model->where(array('user_id'=>$user_id))->setinc('follow');

		//更新用户关注列表
		$arr = [
			'user_id' =>  $user_id,
			'follow_id' => $follow_id,
			'create_time'=> time()
		];
		$user_follow_up = M('follow')->add($arr);

		if ($fans_up && $follow_up && $user_follow_up) {
			$data['code'] = 200;
			$data['message'] = "关注成功";
			$this->response($data);					
		}else{
			$data['code'] = 204;
			$data['message'] = '关注失败';
		}
	}
	
	/* 
	获取用户个人信息
	*/
	public function GetUser() {
		$user_id  = $this->_USERID;

		$res = M('user')->where(array('user_id'=>$user_id))->field('user_name,user_photo,integral,follow,fans,sex,message,user_id')->find();

		if (!$res) {
			$data['code']    = 204;
			$data['message'] = '获取用户信息失败';
			$this->response($data);
		}

		$data['code']     = 200;
		$data['message']  = '获取用户信息成功';
		$data['data']     = $res;
		$this->response($data);
	}

	/* 
	获取用户动态圈  
	查找用户 关注的用户的 更新 根据时间排序
	*/
	public function GetUserDynamic() {
		$user_id = $this->_USERID;
		//查找用户关注
		$res = M('follow')->where(array('user_id'=>$user_id))->select();
		if (!$res) {
			$data['code'] = 204;
			$data['message'] = '你的关注列表空空的,暂时无法查看动态';
			$this->response($data);
		}

		foreach ($res as $key => $value) {
			//拼接用户id
			$str .=  "'" . $value['follow_id'] . "'" . ',';
		}
		$str = trim($str,',');
		$sql = "select * from city_video where user_id in({$str})  ORDER BY uploadetime DESC;";
		$video_info = M('city_video')->query($sql);
		
		if (!$video_info) {
			$data['code'] = 204;
			$data['message'] = '暂时无动态';
			$this->response($data);			
		}
		$arr = [];
		foreach($video_info as $key => $value) {
			//根据用户id 查找用户头像和用户名
			$userinfo = M('user')->field('user_name,user_photo')->where(array('user_id'=>$value['user_id']))->find();
			$value['is_collection'] = $this->is_collection($user_id,$value['id']) ? 1 : 0;
			$value['is_fabulous']   = $this->is_fabulous($user_id,$value['id'])   ? 1 : 0;
			unset($value['click']);
			unset($value['user_id']);
			unset($value['city']);
			unset($value['sort_id']);
			unset($value['type']);
			$arr[$key] = array_merge($value,$userinfo);
		}
		$data['code'] = 200;
		$data['message'] = '获取成功';
		$data['data'] = $arr;
		$this->response($data);

	}

	/*
	用户上传头像
	@param $face_path  头像地址
	*/
	public function uploadFace() {
		$user_id = $this->_USERID;
		$model   = M('user');
		$model->user_photo = $_REQUEST['face_path']; 
		$res  = $model->where(array('user_id'=>$user_id))->save();
		if ($res) {
			$data['code'] = 200;
			$data['message'] = '上传头像成功';
			$this->response($data);
		}else{
			$data['code'] = 204;
			$data['message'] = '上传头像失败,请重新选择';
			$this->response($data);
		}
	}

	/*
	获取用户的收藏
	*/
	public function getUserCollection () {
		$user_id = $this->_USERID;
		$res     = M('city_video_collection')->where(array('user_id'=>$user_id))->order('create_time DESC')->select();
		if (!$res) {
			$data ['code'] = 204;
			$data ['message'] = '你的收藏列表空空的';
			$this->response($data);
		}
		//遍历循环出用户头像,名字 与视频点击量封面
		$arr = [];
		foreach ($res as $key => $value) {
			$video_id   = $value['video_id'];
			$video_info = M('city_video')->where(array('video_id'=>$video_id))->find();
			$user_id    = $video_info['user_id'];
			$user_info  = M('user')->field('user_name,user_photo')->where(array('user_id'=>$user_id))->find();
			$arr[$key]  = array_merge($user_info,$video_info);
		}
		$data['code']   = 200;
		$data['message']= '获取成功';
		$data['data']   = $arr;
		$this->response($data); 
	}

	/*
	获取用户的上传
	*/
	public function getUserUpload() {
		$user_id = $this->_USERID;
		$res     = M('city_video')->where(array('user_id'=>$user_id))->order('uploadetime DESC')->select();
		if ($res) {
			$data['code'] = 200;
			$data['message'] = '获取成功';
			$data['data'] = $res;
			$this->response($data);
		}else{
			$data['code'] = 204;
			$data['message'] = '你暂时没有上传视频';
			$this->response($data);
		}
	}

	/*
	删除收藏
	*/
	public function delCollection() {
		//传过来的字符串  1,1,2
		$user_id   = $this->_USERID;
		$video_ids = $_REQUEST['multiple'];
		if (empty($video_ids)) {
			$data['code'] = 204;
			$data['message'] = '参数错误';
			$this->response($data);
		}
		$str       = trim($video_ids,',');
		$arr       = explode(',', $str);
		//循环删除
		$num 		= 0;
		foreach ($arr as $key => $value) {
			$res1 = M('city_video_collection')->where(array('user_id'=>$user_id,'video_id'=>$value))->delete();
			$res2 = M('city_video')->where(array('id'=>$value))->setDec('num_collection');
			if ($res1 && $res2) {
				$num++;
			}
		}
		$count = count($arr);
		if ($num == $count) {
			$data['code'] = 200;
			$data['message'] = '删除成功';
			$this->response($data);
		}else{
			$data['code'] = 204;
			$data['message'] = '删除失败';
			$this->response($data);
		}
	}

	/*
	获取当前用户嘿币余额
	*/
	public function getUserBalance () {
		$user_id   = $this->_USERID;
		$user_info = M('user')->where(array('user_id'=>$user_id))->find();
		$balance   = $user_info['integral'];
		$data['code'] = 200;
		$data['message'] = '获取成功';
		$data['balance'] = $balance;
		$this->response($data);
	}

	/* 
	用户点击查看他人的详细资料时
	*/
	public function viewUserInfo() {
		$user_id  = $this->_USERID;
		$from_id  = $_REQUEST['user_id'];
		//数据库中查询这个用户个人信息
		$user_info = M('user')->where(array('user_id'=>$from_id))->field('user_id,user_name,user_photo,fans,follow,message,sex')->find();
		$user_info['is_follow'] = $this->is_follow($user_id,$from_id) ? 1 : 0; 

		$data['code'] = 200;
		$data['message'] = '获取用户信息成功';
		$data['data'] = $user_info;

		$this->response($data);
	}

	/* 
	 * 用户查看他人的上传
	 */
	public function viewUserUpload() {
		$user_id = $_REQUEST['user_id'];
		if (!$user_id) {
			$data['code']    = 204;
			$data['message'] = '数据错误';
			$this->response($data);
		}
		$res     = M('city_video')->where(array('user_id'=>$user_id))->order('uploadetime DESC')->select();
		if ($res) {
			$data['code'] = 200;
			$data['message'] = '获取成功';
			$data['data'] = $res;
			$this->response($data);
		}else{
			$data['code'] = 204;
			$data['message'] = '你暂时没有上传视频';
			$this->response($data);
		}			
	}
	/* 
	* 用户查看他人的收藏
	*/
	public function viewUserCollection() {
		$user_id = $_REQUEST['user_id'];
		if (!$user_id) {
			$data['code']    = 204;
			$data['message'] = '数据错误';
			$this->response($data);
		}
		$res     = M('city_video_collection')->where(array('user_id'=>$user_id))->order('create_time DESC')->select();
		if (!$res) {
			$data ['code'] = 204;
			$data ['message'] = '他收藏列表空空的';
			$this->response($data);
		}
		//遍历循环出用户头像,名字 与视频点击量封面
		$arr = [];
		foreach ($res as $key => $value) {
			$video_id   = $value['video_id'];
			$video_info = M('city_video')->where(array('video_id'=>$video_id))->find();
			$user_id    = $video_info['user_id'];
			$user_info  = M('user')->field('user_name,user_photo')->where(array('user_id'=>$user_id))->find();
			$arr[$key]  = array_merge($user_info,$video_info);
		}
		$data['code']   = 200;
		$data['message']= '获取成功';
		$data['data']   = $arr;
		$this->response($data); 
	}

	/*  
	修改用户简介
	*/
	public function editUserMessage() {
		$user_id = $this->_USERID;
		$newMessage = $_REQUEST['message'];
		if (empty($newMessage)) {
			$data['code']    = 204;
			$data['message'] = '新简介不能为空';
			$this->response($data);
		}
		if ($this->length_number($newMessage) < 5) {
			$data['code'] = 204;
			$data['message'] = '新简介字数不能小于5';
			$this->response($data);					
		}
		if ($this->length_number($newMessage) > 255) {
			$data['code'] = 204;
			$data['message'] = '新简介字数不能大于255';
			$this->response($data);					
		}	
		//更新用户表
		$model = M('user');
		$model->message = $newMessage;
		$bool  = $model->where(array('user_id'=>$user_id))->save();
		if (!is_bool($bool)) {
			$data['code']    = 200;
			$data['message'] = '修改成功';
		}else{
			$data['code']    = 204;
			$data['message'] = '修改失败';
		}
		$this->response($data);
	}

	/*
	是不是已经关注了此用户
	@return 关注true  没关注false
	*/	
	private function is_follow($uid,$fid) {
		$res = M('follow')->where(array('user_id'=>$uid,'follow_id'=>$fid))->find();
		if($res){
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

	/**
	* 视频是否点赞
	* @param  $uid         用户id
	* @param  $video_id  帖子id
	* return  bool 已经点赞返回true 未点赞返回false	
	*/
	protected function is_fabulous($uid,$video_id) {
		$result = M('city_video_fabulous')->where(array('video_id'=>$video_id,'user_id'=>$uid))->find();
  		if($result) {
  			return true;
  		}
  		return false;			
	}	

    /** 
	* 字符串长度检测
    */
    private function length_number($str) {

        preg_match_all("/./us", $str, $match);

        $str_arr=$match[0];
		
		$length_val=count($str_arr);//字符串长度 
		
		return $length_val;  	
	}	
}