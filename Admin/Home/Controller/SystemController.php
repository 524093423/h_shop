<?php
/**
 * 系统设置
 * 20170520
 * 
 */
namespace Home\Controller;
use Think\Controller;
class SystemController extends CheckController {
	/**
	 * [about_us 关于我们]
	 * @return [type] [description]
	 */
	public function About_us(){
		$sys = $_GET['sys_id'];
		$info = M('h_system_mes')->where(array('sys_id'=>$sys_id))->find();
		var_dump($info);
	}

}