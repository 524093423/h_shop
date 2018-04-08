<?php 
/**
 * 系统管理控制器
 * 20170513
 */
namespace Home\Controller;
use Think\Controller\RestController;
Class SystemController extends RestController {
	/**
	 * [About_us 关于我们]
	 */
	public function About_us(){
		$info = M("h_system_mes")->field('title,content')->where(array('sys_id'=>1))->find();
		if($info){
			$data['code'] = 200;
			$data['message'] ="获取成功";
			$data['info'] =$info;
		}else{
			$data['code'] = 204;
			$data['message'] ="系统错误，请检查";
		}
		$this->response($data,I("get.callback"));
	}
	public function help(){
		$info = M("h_system_mes")->field('title,content')->where(array('type'=>2))->select();
		if($info){
			$data['code'] = 200;
			$data['message'] ="获取成功";
			$data['info'] =$info;
		}else{
			$data['code'] = 204;
			$data['message'] ="系统错误，请检查";
		}
		$this->response($data,I("get.callback"));
	}
	public function userProtocol(){
		$info = M("h_system_mes")->field('title,content')->where(array('type'=>3))->find();
		if($info){
			$data['code'] = 200;
			$data['message'] ="获取成功";
			$data['info'] =$info;
		}else{
			$data['code'] = 204;
			$data['message'] ="系统错误，请检查";
		}
		$this->response($data,I("get.callback"));
	}
}