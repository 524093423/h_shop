<?php
/*
搜索控制器
2018年3月21日16:24:39
贺小奎
*/
namespace Home\Controller;
header('content-type:text/html;charset=utf-8');
use Think\Controller\RestController;
class SearchController extends RestController {
    private $_MODEL;
    public function __construct(){
        $this->_MODEL  = M('user');
    }


	/*
	换一批
	*/
	public function batch() {

		$model = M('key_word');

		$number = $model->count();

		$data[0] = $model->where(array('id'=>mt_rand(3,$number)))->find();

		$data[1] = $model->where(array('id'=>mt_rand(3,$number)))->find();

		$data[2] = $model->where(array('id'=>mt_rand(3,$number)))->find();

		$arr = [];

		foreach ($data as $key => $value) {

			$id = $value['id'];

			$number = M('emergencysell')->where(array('key_id'=>$id))->field('count(key_id) as num')->select();
			
			$value['number'] = $number[0]['num'];

			$arr['data'][$key]= $value;

		}

		$this->response($arr);		
	}

	/*
	显示更多关键词
	*/
	public function GetMoreKeyWord() {

		$data = M('emergencysell')->field('count(key_id) as count,key_id')->group('key_id DESC')->select();

		$arr  = [];

		foreach ($data as $key => $value) {

			$k   = $value['key_id'];

			$res = M('key_word')->field('name')->where(array('id'=>$k))->find();

			$arr['data'][$key] = array_merge($value,$res);

		}
		$this->response($arr);			
	}



	/*
	搜索同城
	*/
	public function searchAll() {

		$search = trim($_REQUEST['search']);

		if (empty($search)) {
			$data['code'] = 204;
			$data['message'] ="请输入搜索内容";
			$this->response($data);			
		}

		$sql = "SELECT user_id,title,cover FROM emergencysell where INSTR(title,'{$search}');";

		$result = M('emergencysell')->query($sql);

		if (!$result) {
			$data['code'] = 204;
			$data['message'] ="暂无此 '{$search}' 信息";
			$this->response($data);					
		}
		
		$data['code'] = 200;
		$data['message'] = '获取成功';
		$data['data'] = $result;
		$this->response($data);
	}


	/*
	搜索
	@type 1-用户 2-视频
	*/
	public function  searchSet() {

		$type = $_REQUEST['type'];

		$search = trim($_REQUEST['search']);

		if (empty($search)) {
			$data['code'] = 204;

			$data['message'] ="请输入搜索内容";

			$this->response($data);		
		}

		if ($type == 1) {

			$model = $this->_MODEL;
			
			$sql = "SELECT user_id,user_name,user_photo,token,follow,fans FROM user where INSTR(user_name,'{$search}');";

			$Result = $model->query($sql);

			if (!$Result) {

				$data['code'] = 204;

				$data['message'] ="没找到相关用户";

				$this->response($data);

				return false;						
			}

			$arr = [];

			$arr['code'] = 200;

			$arr['message'] ="搜索用户成功";

			//检测用户是登陆

			//未登陆全部都是未关注

	        $token  = $_REQUEST['token'];

	        if ($token)  {

	        	$user_id  =  D("Register")->GetUserIdFromToken($token);

				foreach ($Result as $key => $value) {
					//如果搜索到自己
					if ($value['user_id'] == $user_id) {

						$value['state'] = 1;

					}

					$value['is_follow'] = $this->is_follow($user_id,$value['user_id']) ? 1 : 0;  // 0-未关注 1-已经关注

					$arr['data'][$key] = $value;
				}
				$this->response($arr);

	        }else{

				foreach ($Result as $key => $value) {

					$value['is_follow'] =  0;  // 0-未关注 1-已经关注

					$arr['data'][$key] = $value;
				}
				$this->response($arr);	   

	        }
	        

		}elseif ($type == 2) {
			
			$sql = "select id,user_id,click,title,cover from city_video where INSTR(title,'{$search}');";

			$result = M('city_video')->query($sql);

			if (!$result) {

				$data['code'] = 204;

				$data['message'] ="没找到相关视频";

				$this->response($data);				
			}

			$model = $this->_MODEL;

			$arr = [];

			$ns = $_REQUEST['ns'];

			foreach ($result as $key => $value) {

				$userid = $value['user_id'];

				$userinfo = $model->where(array('user_id'=>$userid))->field('user_name,user_photo,ns')->find();

				$userinfo['distance'] = sprintf("%.1f", $this->calNs($ns,$userinfo['ns']));

				unset($value['user_id']);

				unset($userinfo['ns']);

				$arr[$key] = array_merge($value,$userinfo);
				
			}

			$data['code'] = 200;

			$data['message'] = '搜索视频成功';

			$data['data'] = $arr;

			$this->response($data);

		}

	}

	public function follow_is() {
		$token   = $_REQUEST['token'];
		
		$user_id = D("Register")->GetUserIdFromToken($token);
		
		$fid     = $_REQUEST['userid'];
		
        $res     = $this->is_follow($user_id,$fid);

        if ($res) {
			$data['code'] = 200;

			$data['message'] = '用户已经关注';

			$this->response($data);

        }else{
			$data['code'] = 204;

			$data['message'] = '用户没有关注';

			$this->response($data);
        }
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
	获取用户关注数量
    */
    private function follow_num($uid) {
    	$model = $this->_MODEL;
    	$sum = $model->where(array('id'=>$uid))->field('follow')->find();
    	return $sum;
    }	

    /*
	获取用户粉丝数量
    */
    private function fans_num($uid) {
    	$model = $this->_MODEL;
    	$sum = $model->where(array('id'=>$uid))->field('fans')->find();
    	return $sum;
    }

    /*
	根据经纬度计算距离
    */
	protected function calNs($start,$end) {

		$key = 'e7f2b31552584c5c2ea743466df4eb54';

		$url = 'http://restapi.amap.com/v3/distance?key='.$key.'&origins='.$start.'&destination='.$end;

		$res = file_get_contents($url);

		$ob  = json_decode($res);

		$number = $ob->results[0]->distance;
		
		return $number/1000;
	}    
}