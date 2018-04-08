<?php
/**
 * App公共数据接口
 *2017.02.11
 * 
 */
namespace Home\Controller;
use Think\Controller\RestController;
header('Content-type:text/html;charset=utf-8');
class ApiController extends RestController {
    private $_USERID;
    public function __construct(){
        $token  = $_REQUEST['token'];
        $this->_USERID  = D("Register")->GetUserIdFromToken($token);

    }
	/**
	 * 获取首页轮播图
	 * @city   城市名称
	 * 2017.02.11
	 */
	public function GetCarousel(){
		$data = D("Common")->GetHomeCarousels($city);
		$this->response($data);
	}
	/**
	 * 检查版本
	 * 2017.04.17
	 */
	public function GetAppEditionInfo(){
		$user   = M("app_config");
		$data = $user->field("edition_no,editioninfo,edition_name,updatetime,downloadaddress as download
        ")->find();
		if(!empty($data)){
			$returndata['code'] = 200;
			$returndata['message']  = "获取App信息成功";
			$returndata['info'] = $data;
		}else{
			$returndata['code'] = 204;
			$returndata['message']  = "当前已经是最新版本";
		}
		$this->response($returndata);
	}

	public function GetUserInfoFromToken(){
	    $userid    = $this->_USERID;
	    $token     = $_GET['token'];
	    if(empty($userid)){
	      $this->response(reTurnJSONArray("204", "用户不存在请重新登录"));  
	    }
	    $data  = D("Register")->getUserInfoFromToken($userid,$token);
	    $this->response($data);
	}

	/*
	获取首页信息
	视频点击量到达100
	*/
	public function GetHome() {
		$model = M('city_video');

		$count  = $model->field('id,user_id,click,video_path,title')->where('sort_id != 1 AND type !=1 ')->order('uploadetime DESC')->count();

		$page        = $_REQUEST['page'] ? $_REQUEST['page'] : 1;

		$maxResult   = 20;  //每一页最多数据

		$totalPage   = ceil($count / $maxResult); //总页数

		//(页数 - 1) * 每页数据
		$start  = ($page -1) * $maxResult;

		$sql  = "SELECT id,user_id,click,video_path,title,cover FROM city_video WHERE sort_id != 1 AND type !=1 ORDER BY uploadetime DESC  LIMIT $start,$maxResult";

		$res  = $model ->query($sql);

		$arr  = [];
		//循环遍历出用户头像
		foreach ($res as $key => $value) {
			$user_id    = $value['user_id'];
			$user_info  = M('user')->field('user_name,user_photo')->where(array('user_id'=>$user_id))->find();	
			$arr[$key] = array_merge($user_info,$value);
		}
		$data['code'] = 200;
		$data['message'] = '获取成功';
		$data['totalPage'] = $totalPage;
		$data['data'] = $arr;
		$this->response($data);
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
		return $city;
	}	
}