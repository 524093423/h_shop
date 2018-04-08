<?php
/**
 * 商品分类管理
 *2017.02.09
 * 
 */
namespace Home\Controller;
header("content-type:text/html;charset=utf-8");
use Think\Controller\RestController;
class ClassifysController extends RestController {
	/**
	 * 获取分类信息
	 * 2017.02.06
	 */
	public function GetClassify(){
		$token    = $_REQUEST['token'];
		$user_id  = D("Register")->GetUserIdFromToken($token);
		if ($this->is_seller($user_id)) {
			$data = M('classify')->where(array('state'=>1))->field('id,name,img')->select();
			$arr  = [];
			$arr['code'] = 200;
			$arr['message'] = '获取分类信息成功';
			foreach ($data as $key => $value) {
				$value['img'] = 'http://www.heigushop.com' . $value['img'];
				$arr['data'][$key] = $value;
			}
			$this->response($arr);
		}else{
			$data = M('classify')->where(array('state'=>1,'id'=>1))->field('id,name,img')->select();
			$arr  = [];
			$arr['code'] = 200;
			$arr['message'] = '获取分类信息成功';
			foreach ($data as $key => $value) {
				$value['img'] = 'http://www.heigushop.com' . $value['img'];
				$arr['data'][$key] = $value;
			}
			$this->response($arr);			
		}
	}

	/*
	判断用户是否是商家
	是返回true 不是 返回false
	*/
	public function is_seller($uid) {
		$user_info = M('user')->where(array('user_id'=>$uid))->field('is_seller')->find();
		if ($user_info['is_seller'] == 1) {
			return true;
		}else{
			return false;
		}
	}
}