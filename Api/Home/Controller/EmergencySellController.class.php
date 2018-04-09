<?php
/*
急购急售
2018年3月21日09:55:57
贺小奎
*/
namespace Home\Controller;
header("content-type:text/html;charset=utf-8");
use Think\Controller\RestController;

class EmergencySellController extends RestController {

	private $_USERID;

    public function __construct(){
		$token  = $_REQUEST['token'];
		$userinfo = M('user')->where(array('token' => $token))->field('user_id')->find();
		$this->_USERID  = $userinfo['user_id'];
	}
	
	/* 
	获取急购急售信息
	*/
	public function GetContent() {
		$type  =  $_REQUEST['type'] ? $_REQUEST['type'] : 1;
		$page  =  $_REQUEST['page'] ? $_REQUEST['page'] : 1;
		$model = M('emergencysell');
		//获取ns信息
		$ns = $_REQUEST['ns'];
		//解析获取用户所在城市
		$city = $this->analysis($ns)->city;
		/*
		1代表急购 2代表急售
		急购不存在视频而急售存在
		  */
		if ($type == 1) {
			$count = $model->where(array('type'=>1,'city'=>$city))->count();
			//计算分页,
			$maxResult   = 20;  //每一页最多数据
			$totalPage   = ceil($count / $maxResult); //总页数
			//(页数 - 1) * 每页数据
			$start  = ($page -1) * $maxResult;

			$sql = "SELECT id,title,price,click,city,Bidder,`number` FROM emergencysell WHERE type = 1 AND city = '{$city}' LIMIT $start,$maxResult";
			$result = $model->query($sql);
			
			//从表中获取封面信息
			$arr = [];
			foreach($result as $key => $value) {
				$cover  = M('emergencysell_img')->where(array('post_id'=>$value['id']))->field('cover')->find();
				//计算出多少人报价
				if ($value['Bidder']){
					$value['Bidder'] = count(explode(',',$value['Bidder']));					
				}else{
					$value['Bidder'] = 0;
				}
				$arr[$key] = array_merge($value,$cover);	
			}
			$data['code']     = '200';
			$data['message']  = '获取急购信息成功';
			$data['totalPage']= $totalPage;
			$data['data']     = $arr;
			$this->response($data);  
		}elseif($type == 2) {
			$count = $model->where(array('type'=>2,'city'=>$city))->count();	
			//计算分页,
			$maxResult   = 20;  //每一页最多数据
			$totalPage   = ceil($count / $maxResult); //总页数
			//(页数 - 1) * 每页数据
			$start  = ($page -1) * $maxResult;
			$sql = "SELECT id,title,price,click,city FROM emergencysell WHERE type = 2 AND city = '{$city}' LIMIT $start,$maxResult";
			$result = $model->query($sql);

			//从表中获取封面信息
			$arr = [];
			foreach($result as $key => $value) {
				$cover  = M('emergencysell_img')->where(array('post_id'=>$value['id']))->field('cover')->find();
				$arr[$key] = array_merge($value,$cover);	
			}
			$data['code']     = '200';
			$data['message']  = '获取急购信息成功';
			$data['totalPage']= $totalPage;
			$data['data']     = $arr;
			$this->response($data);  						
		}else{
			$data['code']    = '204';
			$data['message'] = '类型错误';
			$this->response($data);
		}
	}

	/* 
	我要报价 -- 急购 仅有急购可以报价
	*/ 
	public function getOffer() {
		$user_id = $this->_USERID;
		if (empty($user_id)) {
			$this->response(reTurnJSONArray('204','请登录'));
		}
		$post_id = $_REQUEST['post_id'];
		if (empty($post_id)) {
			$this->response(reTurnJSONArray('204','数据错误'));
		}

		$post_info = M('emergencysell')->where(array('id'=>$post_id))->field('Bidder,offer,number')->find();
		$tmp       = $post_info['Bidder'];
		$bidder    = explode(',',$tmp);
		$offer     = explode(',',$post_info['offer']);
		//检测报价人数是否以满
		$type 	   =  $_REQUEST['type'];

		$count     = count($bidder);
		if ($count >= $post_info['number']) {
			$this->response(reTurnJSONArray('204','你来晚了一步呢'));			
		}		
		//检测订单是否存在
		$dingdan   = $_REQUEST['dingdan'];
		if (!$dingdan) {
			$this->response(reTurnJSONArray('204','你尚未支付订单'));				
		}
		$money= $_REQUEST['money'];
		//更新急购表
		$str  = $tmp . ',' . $user_id;
		$str1 = $post_info['offer'] . ',' . $money;
		$sql  = "UPDATE emergencysell SET Bidder = '{$str}' , offer = '{$str1}' WHERE id = {$post_id}";
		$bool = M('emergencysell')->execute($sql);
		if ($bool)  {
			$data['code'] = 200;
			$data['message'] = '报价成功';
			$this->response($data);					
		}else{
			$data['code'] = 204;
			$data['message'] = '数据错误';
			$this->response($data);					
		}
		
	}

	/* 
	支付嘿币
	支付嘿币获取订单
	*/
	public function pay() {
		$user_id = $this->_USERID;;
	
		$balance   = M('user')->where(array('user_id'=>$user_id))->field('integral')->find()['integral'];

		if ($balance < 1000) {
			$this->response(reTurnJSONArray('204','用户余额不足'));
		}

		//用户减去1000余额  更新订单表,返回订单
		$model= M('user');
		$model->startTrans();
		$res = $model->where(array('user_id'=>$user_id))->setDec('integral',1000);
		$order_id = $this->AddOrderId();
		$arr = [
			'user_id' => $user_id,
			'dingdan' => $order_id
		];
		$rr = M('emergencysell_pay')->add($arr);
		if ($res && $rr) {
			$model->commit();
			$data['code'] = 200;
			$data['message'] = '支付成功';
			$data['dingdan'] = $order_id;
			$this->response($data);
		}else{
			$model->rollback();
			$data['code'] = 204;
			$data['message'] = '支付失败';
			$this->response($data);			
		}
	}


	/**
	 * 急购分步上传__获取帖子信息 
	 * 急购不存在视频 急售存在 急购不需要收费
	 * state 1代表可以与我建立私信窗口 0代表不可以  
	 *@return  帖子id
	 */
	public function UrgentPurchase_GetInfo() {
		$user_id = $this->_USERID;
		$state   = $_REQUEST['state'] ? $_REQUEST['state'] : 1 ;
		//检测用户
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);					
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
		$ns      = $_REQUEST['ns'];
		//城市检测
		if (empty($ns)) {
			$res  = $this->analysisIp($_SERVER['REMOTE_ADDR']);	//没有经纬度从ip查询
			$city = $res->city;
		}else{
			$json = $this->analysis($ns);
			$city = $json->city;
		}		
		$message = $_REQUEST['message'];
		//检测描述
		if (empty($message)) {
			$data['code'] = 204;
			$data['message'] = '描述不能为空';
			$this->response($data);			
		}
		if ($this->length_number($message) < 5) {
			$data['code'] = 204;
			$data['message'] = '描述字数不能小于5';
			$this->response($data);					
		}
		if ($this->length_number($message) > 255) {
			$data['code'] = 204;
			$data['message'] = '描述字数不能大于255';
			$this->response($data);					
		}	
		$number  = $_REQUEST['number'] ? $_REQUEST['number'] : 5;

		//检测报价人数
		if ($number < 5) {
			$data['code'] = 204;
			$data['message'] = '报价人数不能小于5人';
			$this->response($data);					
		}
		$money   = $_REQUEST['money'];
		//更新数据库
		$arr = [
			'user_id' => $user_id,
			'price'   => $money,
			'title'   => $content,
			'click'   => 0,
			'city'    => $city,
			'message' => $message,
			'type'    => 1,
			'number'  => $number,
			'state'   => $state,
			'create_time' => time()
		];
		$result =  M('emergencysell')->add($arr);

		if ($result) {
			$data['code']    = 200;
			$data['message'] = '添加数据成功';
			$data['post_id'] = M('emergencysell')->getLastInsID();
		}else{
			$data['code']    = 204;
			$data['message'] = '添加数据失败';			
		}
		$this->response($data);
	}

	/* 
	急购分步上传__获取图片地址 并整合
	*/
	public function UrgentPurchase_GetImg_path() {
		$user_id = $this->_USERID;
		//获取帖子id,并验证
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code']    = 204;
			$data['message'] = '数据错误';
			$this->response($data);
		}
		$res = M('emergencysell')->where(array('id'=>$post_id))->find();

		if (!$res) {
			$data['code']    = 204;
			$data['message'] = '帖子id不存在';
			$this->response($data);
		}

		if($res['user_id'] != $user_id) {
			$data['code']    = 204;
			$data['message'] = '信息不正确';
			$this->response($data);
		}	

		$cover     = $_REQUEST['cover'];
		if (!$cover) {
			$data['code']    = 204;
			$data['message'] = '封面地址不存在';
			$this->response($data);			
		}
		$arr['cover']  = $cover;

		$img_path  = $_REQUEST['img_path'];   //..img_path 类似与 1.jpg,2.png
	
		if (!empty($img_path)) {
			$img = explode(',',$img_path);
			for ($i=0; $i <count($img) ; $i++) { 
				$arr['img_'.($i+1)] = $img[$i];
			}
		}

		//添加到图片表
		$add = M('emergencysell_img')->add($arr); 
		if ($add) {
			$data['code']    = 200;
			$data['message'] = '上传成功';
		}else{
			$data['code']    = 204;
			$data['message'] = '上传失败请重试';
 		}
		$this->response($data);
	}


	/* 
	急购分步上传_获取帖子信息
	急售存在视频  急售需要收费
	*/
	public function UrgentSell_GetInfo() {
		$user_id = $this->_USERID;

		$state   = $_REQUEST['state'] ? $_REQUEST['state'] : 1 ;

		//检测用户
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);					
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
		$ns      = $_REQUEST['ns'];
		//城市检测
		if (empty($ns)) {
			$res  = $this->analysisIp($_SERVER['REMOTE_ADDR']);	//没有经纬度从ip查询
			$city = $res->city;
		}else{
			$json = $this->analysis($ns);
			$city = $json->city;
		}		
		$message = $_REQUEST['message'];
		//检测描述
		if (empty($message)) {
			$data['code'] = 204;
			$data['message'] = '描述不能为空';
			$this->response($data);			
		}
		if ($this->length_number($message) < 5) {
			$data['code'] = 204;
			$data['message'] = '描述字数不能小于5';
			$this->response($data);					
		}
		if ($this->length_number($message) > 255) {
			$data['code'] = 204;
			$data['message'] = '描述字数不能大于255';
			$this->response($data);					
		}	
		$money   = $_REQUEST['money'];

		//检测订单是否存在;
		$dingdan = $_REQUEST['dingdan'];
		if (empty($dingdan)) {
			$data['code'] = 204;
			$data['message'] = '您尚未支付1000嘿币,不能发表';
			$this->response($data);					
		}

		//从订单表查询
		$ding = M('emergencysell_pay')->where(array('dingdan'=>$dingdan))->find();


		if (empty($ding) || is_null($ding)) {
			$data['code'] = 204;
			$data['message'] = '未查询到此订单';
			$this->response($data);					
		}	

		//更新数据库
		$arr = [
			'user_id' => $user_id,
			'price'   => $money,
			'title'   => $content,
			'click'   => 0,
			'city'    => $city,
			'message' => $message,
			'type'    => 2,
			'number'  => 0,
			'state'   => $state,
			'create_time' => time()
		];
		$result =  M('emergencysell')->add($arr);

		//更新订单表
		$endId = M('emergencysell')->getLastInsID();
		$sql = "UPDATE emergencysell_pay SET post_id = $endId WHERE dingdan = $dingdan";
		M('emergencysell_pay')->execute($sql);

		if ($result) {
			$data['code']    = 200;
			$data['message'] = '添加数据成功';
			$data['post_id'] = $endId;
		}else{
			$data['code']    = 204;
			$data['message'] = '添加数据失败';	
		}		
		$this->response($data);		
	}

	/* 
	急售分步上传__获取视频地址
	*/
	public function UrgentSell_GetVideo_Path() {
		$user_id = $this->_USERID;
		//获取帖子id,并验证
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code']    = 204;
			$data['message'] = '数据错误';
			$this->response($data);
		}
		$res = M('emergencysell')->where(array('id'=>$post_id))->find();
		if (!$res) {
			$data['code']    = 204;
			$data['message'] = '帖子id不存在';
			$this->response($data);
		}
		if($res['user_id'] != $user_id) {
			$data['code']    = 204;
			$data['message'] = '信息不正确';
			$this->response($data);
		}
		$video_path  = $_REQUEST['video_path'];
		if (!$video_path) {
			$data['code']    = 204;
			$data['message'] = '视频地址不存在';
			$this->response($data);
		}
		//更新视频表
		$arr = [
			'post_id' => $post_id,
			'path'    => $video_path
		];

		$add = M('emergencysell_video')->add($arr);
		if ($arr) {
			$data['code'] = 200;
			$data['message'] = '更新视频地址成功';
		}else{
			$data['code'] = 204;
			$data['message'] = '更新视频地址失败';
		}
		$this->response($data);
	}

	/* 
	急售分步上传__获取封面以及图片地址
	*/
	public function UrgentSell_GetImg_path() {
		$user_id = $this->_USERID;
		//获取帖子id,并验证
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code']    = 204;
			$data['message'] = '数据错误';
			$this->response($data);
		}
		$res = M('emergencysell')->where(array('id'=>$post_id))->find();

		if (!$res) {
			$data['code']    = 204;
			$data['message'] = '帖子id不存在';
			$this->response($data);
		}

		if($res['user_id'] != $user_id) {
			$data['code']    = 204;
			$data['message'] = '信息不正确';
			$this->response($data);
		}	

		$cover     = $_REQUEST['cover'];
		if (!$cover) {
			$data['code']    = 204;
			$data['message'] = '封面地址不存在';
			$this->response($data);			
		}
		$arr['cover']   = $cover;
		$arr['post_id'] = $post_id;
		$img_path  = $_REQUEST['img_path'];   //..img_path 类似与 1.jpg,2.png
		if (!empty($img_path)) {
			$img = explode(',',$img_path);
			for ($i=0; $i <count($img) ; $i++) { 
				$arr['img_'.($i+1)] = $img[$i];
			}
		}

		//添加到图片表
		$add = M('emergencysell_img')->add($arr); 
		if ($add) {
			$data['code']    = 200;
			$data['message'] = '上传成功';
		}else{
			$data['code']    = 204;
			$data['message'] = '上传失败请重试';
 		}
		$this->response($data);
	}

	/* 
	用户余额查询
	*/
	public function selectBalance() {
		$user_id = $this->_USERID;
		$balance   = M('user')->where(array('user_id'=>$user_id))->field('integral')->find();
		if ($balance < 1000) {
			$this->response(reTurnJSONArray('204','用户余额不足'));
		} 
		$data['code'] = 200;
		$data['message'] = '用户余额充足';
		$data['balance'] = $balance - 1000;
		$this->response($data);
	}
	/* 
	获取急购急售帖子详情
	@param $Post_id 帖子id
	*/
	public function GetDetails () {
		$user_id = $this->_USERID;
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);				
		}
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code'] = 204;
			$data['message'] = '数据不全,请重试';
			$this->response($data);			
		}

		$res = M('emergencysell')->where(array('id' => $post_id))->find();
		if (!$res) {
			$data['code'] = 204;
			$data['message'] = '数据错误请重试';
			$this->response($data);
		}
		//帖子点击量 +1
		M('emergencysell')->where(array('id' => $post_id))->setinc('click');

		//判断是急购还是急售  急购在判断用户是否报价,然后返回详情  急售直接返回详情
		if ($res['type'] == 1) {
			$bidder = explode(',',$res['Bidder']);
			//如果已经报价
			if (in_array($user_id,$bidder)) {
				//急购 查找用户头像 获取报价人数
				$userinfo  = M('user')->where(array('user_id' => $res['user_id']))->field('user_name,user_photo,user_phone')->find();

				if (!is_null($res['Bidder']) || !empty($res['Bidder'])){
					$res['bidder'] = count($bidder);					
				}else{
					$res['bidder'] = 0;
				}

				//获取封面  获取图片 1-3
				$img      =  M('emergencysell_img')->field('cover,img_1,img_2,img_3')->where(array('post_id'=>$post_id))->find();
				$res = array_merge($res,$img);

				unset($res['type']);
				unset($res['Bidder']);
				unset($res['user_id']);

				$data['code'] = 200;
				$data['message'] = '获取成功';
				$data['data'] = array_merge($res,$userinfo);
				$this->response($data);
			}else{
				//没报价返回用户头像
				$userinfo  = M('user')->where(array('user_id' => $res['user_id']))->field('user_photo')->find();

				if (!is_null($res['Bidder']) || !empty($res['Bidder'])){
					$res['bidder'] = count($bidder);				
				}else{
					$res['bidder'] = 0;
				}

				//获取封面  获取图片 1-3
				$img      =  M('emergencysell_img')->field('cover,img_1,img_2,img_3')->where(array('post_id'=>$post_id))->find();
				$res = array_merge($res,$img);		

				unset($res['type']);
				unset($res['Bidder']);
				unset($res['offer']);
				unset($res['user_id']);
				$data['code'] = 200;
				$data['message'] = '获取成功';
				$data['data'] = array_merge($res,$userinfo);
				$this->response($data);				
			}

		}elseif($res['type'] == 2) {
			//直接返回用户数据 没有报价
			$userinfo  = M('user')->where(array('user_id' => $res['user_id']))->field('user_name,user_photo,user_phone')->find();

			//获取封面  获取图片 1-3
			$img      =  M('emergencysell_img')->field('cover,img_1,img_2,img_3')->where(array('post_id'=>$post_id))->find();
			$res = array_merge($res,$img);
			//获取视频地址
			$video = M('emergencysell_video')->field('path as video_path')->where(array('post_id'=>$post_id))->find();
			$res = array_merge($res,$video);
			unset($res['type']);
			unset($res['Bidder']);
			unset($res['offer']);
			unset($res['user_id']);	
			unset($res['number']);
			$data['code'] = 200;
			$data['message'] = '获取成功';
			$data['data'] = array_merge($res,$userinfo);
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
	字符串长度检测
    */
    private function length_number($str) {

        preg_match_all("/./us", $str, $match);

        $str_arr=$match[0];

        $length_val=count($str_arr);//字符串长度 

		return $length_val;  	
	}
		
	/*
	根据经纬度解析
	@x
	@y     经纬度
	*/
	protected function analysis($ns) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/geocode/regeo?key='.$key.'&location='.$ns;

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

}