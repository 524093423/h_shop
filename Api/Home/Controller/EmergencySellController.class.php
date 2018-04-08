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
	获取急购急售的信息
    */
    public function GetContent() {
	    $userid = $this->_USERID;
		$type   = $_REQUEST['type']; 
	    if(empty($userid)){
	        $this->response(reTurnJSONArray("204","用户信息错误"));
	    }    	
		//获取ns信息
		$ns = $_REQUEST['ns'];
		//解析获取用户所在城市
		$city = $this->analysis($ns)->city;
		if ($type == 1) {
			//从数据库中查找相对应的城市
			$res  = M('emergencysell')->where(array('city'=>$city,'type'=>1))->field('id,title,Bidder,price,click,cover,number')->select();
			$arr  = []; 
			foreach ($res as $key => $value) {
				if ($value['Bidder']){
					$bidder = count(explode(',',$value['Bidder']));					
				}else{
					$bidder = 0;
				}

				$value['bidder'] = $bidder;
				unset($value['Bidder']);
				unset($value['offer']);	
				unset($value['type']);
				unset($value['message']);
				unset($value['user_id']);
				$arr [$key] = $value;
			}
		
		}elseif($type == 2) {
			$arr  = M('emergencysell')->where(array('city'=>$city,'type'=>2))->field('id,title,price,cover,click')->select();
		}
		$data['code']    = 200;
		$data['message'] = '获取成功';
		$data['data']    = $arr;
		$this->response($data);

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
			$this->response(reTurnJSONArray('204','你尚未支付'));				
		}
		$money= $_REQUEST['money'];
		//更新急购表
		$str  = $tmp . ',' . $user_id;
		$str1 = $post_info['offer'] . ',' . $money;
		$sql  = "UPDATE emergencysell SET Bidder = '{$str}'  offer = '{$str1}' WHERE id = {$post_id}";
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
		$user_id = $this->_USERID;
		$post_id = $_REQUEST['post_id'];

		$balance   = M('user')->where(array('user_id'=>$user_id))->field('integral')->find();
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
		}else{
			$model->rollback();
			$data['code'] = 204;
			$data['message'] = '支付失败';
			$this->response($data);			
		}

	}

	/* 
	急购分步上传
	*/

	/* 
	发表急购  //急购不存在视频 急售存在 //急购不需要收费
	        state 1代表可以与我建立私信窗口 0代表不可以  
	*/
	public function UrgentPurchase() {
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

		$cover   = $_REQUEST['cover_path'];
		//更新数据库
		$arr = [
			'user_id' => $user_id,
			'price'   => $money,
			'cover'   => $cover,
			'title'   => $content,
			'click'   => 0,
			'city'    => $city,
			'message' => $message,
			'type'    => 1,
			'number'  => $number,
			'state'   => $state
		];
		$result =  M('emergencysell')->add($arr);
		
		if ($result) {
			echo '{"code":200,"message":"发表成功"}';
			
		}else{
			unlink($cover);
			echo '{"code":200,"message":"发表失败"}';		
		}
	}

	/*
	发表急售 //第一步,报错上传视频存放在七牛云中的地址
	*/
	public function UrgentSell_GetVideo_Path() {
		$user_id  = $this->_USERID;
		//检测用户
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);					
		}

		$video_path = $_REQUEST['video_path'];
		//更新数据库
		$arr = [
			'user_id' => $user_id,
			'price'   => 0,
			'cover'   => 'tmp',
			'title'   => 'tmp',
			'click'   => 0,
			'video_path' => $video_path,
			'city'    => 'tmp',
			'message' => 'tmp',
			'type'    => 2,
			'number'  => 0,
			'state'   => 0
		];
		$result =  M('emergencysell')->add($arr);

		//更新订单表
		$endId = M('emergencysell')->getLastInsID();
		if ($result) {
			$data['code'] = 200;
			$data['message'] = '添加成功';
			$data['post_id'] = $endId;
			$this->response($data);
		}else{
			M('emergencysell')->where(array('id'=>$endId))->delete();
			$data['code'] = 204;
			$data['message'] = '添加失败';
			$this->response($data);		
		}

	}
	/*
	获取视频封面
	*/
	public function UrgentSell_GetCover_Path() {
		$user_id = $this->_USERID;
		//检测用户
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);					
		}
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code'] = 204;
			$data['message'] = '未知错误';
			$this->response($data);					
		}		
		$cover = $_REQUEST['cover_path'];

		//更新数据库
		$model = M('emergencysell');

		$model->cover = $cover;

		$bool = $model->where(array('user_id'=>$user_id,'id'=>$post_id))->save();
		if ($bool) {
			$data['code'] = 200;
			$data['message'] = '更新成功';
			$data['post_id'] = $post_id;
			$this->response($data);			
		}else{
			$data['code'] = 204;
			$data['message'] = '更新失败';
			$this->response($data);						
		}
	}

	/*
	整合上传
	*/
	public function UrgentSell_add() {
		$user_id = $this->_USERID;
		$state   = $_REQUEST['state'] ? $_REQUEST['state'] : 1 ;
		//检测用户
		if (!$user_id) {
			$data['code'] = 204;
			$data['message'] = '请登录';
			$this->response($data);					
		}
		$post_id = $_REQUEST['post_id'];
		if (!$post_id) {
			$data['code'] = 204;
			$data['message'] = '未知错误';
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
		if (!$dingdan) {
			$data['code'] = 204;
			$data['message'] = '未查询到此订单';
			$this->response($data);					
		}	
		//更新急购表
		$model = M('emergencysell');
		$model->state = $state;
		$model->price = $money;
		$model->title = $content;
		$model->city  = $city;
		$model->message = $message;
		$model->type  = 2;
		$model->number= $number;
		$bool = $model->where(array('id'=>$post_id,'user_id'=>$user_id))->save();
		if ($bool) {
			$data['code'] = 200;
			$data['message'] = '发表成功';
			$this->response($data);				
		}else{
			$model->where(array('id'=>$post_id,'user_id'=>$user_id))->delete();
			$data['code'] = 204;
			$data['message'] = '发表失败';
			$this->response($data);					
		}
	}

	/* 
	发表急售 //急售存在视频  急售需要收费
	*/
	public function UrgentSell() {

		$user_id = $this->_USERID;

		$state   = $_REQUEST['state'] ? $_REQUEST['state'] : 1 ;

		$video    = $_FILES?$_FILES:I("post.video");

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
		if (!$dingdan) {
			$data['code'] = 204;
			$data['message'] = '未查询到此订单';
			$this->response($data);					
		}



		//处理视频
		if(!empty($video)){
			//检测上传视频类型是不是视频
			if ($video['video']['type'] != 'video/mp4') {
				$this->response(reTurnJSONArray("204","请选择视频文件"));
			}

			if(!empty($video['video'])){
				if($video['video']['error']==0){
					$names	= time() . ".mp4";
					$rr = move_uploaded_file($video['video']['tmp_name'],"./Uploads/emergencysell/video/{$names}");
				}
			}
			if(!$rr) {
				$this->response(reTurnJSONArray("204","上传视频失败"));				
			}
			$video_path = "http://127.0.0.1/h_shop/Uploads/emergencysell/video/{$names}";

		}else{
			$this->response(reTurnJSONArray("204","提交视频信息有误"));

		}		

		//处理图片
		$base64_img = trim($_REQUEST['cover']);

		$up_dir = './Uploads/emergencysell/cover/';//存放在当前目录的uploads文件夹下
		 
		if(!file_exists($up_dir)){
			mkdir($up_dir,0777);
		}


		if(preg_match('/^(data:\s*image\/(\w+);base64,)/',$base64_img, $result)){
		
			$type = $result[2];
			  
		  	if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){

				@$new_file = $up_dir.date('YmdHis',time()).'.'.$type;

				if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
					$cover = 'http://127.0.0.1/h_shop/'.$new_file;
				}else{

					$data['code'] = 204;
					$data['message'] = '图片上传失败';
					$this->response($data);

				}

		  	}else{

				$data['code'] = 204;
				$data['message'] = '图片类型错误';
				$this->response($data);

		  	}
		 
		}else{

			$data = base64_decode($base64_img);
			@$new_file = $up_dir.date('YmdHis',time()).'.jpg';
			file_put_contents($new_file,$data);
			$cover  = 'http://127.0.0.1/h_shop/'.$new_file;	

		}
		//更新数据库
		$arr = [
			'user_id' => $user_id,
			'price'   => $money,
			'cover'   => $cover,
			'title'   => $content,
			'click'   => 0,
			'video_path' => $video_path,
			'city'    => $city,
			'message' => $message,
			'type'    => 2,
			'number'  => $number,
			'state'   => $state
		];
		$result =  M('emergencysell')->add($arr);

		//更新订单表
		$endId = M('emergencysell')->getLastInsID();

		$sql = "UPDATE emergencysell_pay SET post_id = $endId WHERE dingdan = $dingdan";

		M('emergencysell_pay')->execute($sql);

		if ($result) {
			echo '{"code":200,"message":"发表成功"}';
			
		}else{
			unlink($cover);
			echo '{"code":200,"message":"发表失败"}';		
		}
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

				$cover      =  M('emergencysell_img')->field('cover')->where(array('post_id'=>$post_id))->select();
			
				for ($i=0; $i < count($cover);$i++){
					
					$res["img_$i"] = $cover[$i]['cover'];
					
				}
				unset($res['video_path']);
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

				$cover      =  M('emergencysell_img')->field('cover')->where(array('post_id'=>$post_id))->select();

				for ($i=0; $i < count($cover);$i++){
					$res["img"][$i] = $cover[$i]['cover'];					
				}			

				unset($res['video_path']);
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
			$cover      =  M('emergencysell_img')->field('cover')->where(array('post_id'=>$post_id))->select();
			for ($i=0; $i < count($cover);$i++){
				$res["img_$i"] = $cover[$i]['cover'];
			}				
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