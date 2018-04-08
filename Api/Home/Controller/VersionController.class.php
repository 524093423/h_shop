<?php
// 版本检测
namespace Home\Controller;
use Think\Controller\RestController;
class VersionController extends RestController {
	/**
	 * [CheckVersion 版本检测]
	 */
	public function CheckVersion(){
		$version_no = $_GET['version_no'];
		if(!$version_no){
			$data['code'] = 204;
			$data['result'] ="版本号或平台类型不行为空";
			$this->response($data);
			return false;
		}
		$data = D("Version")->GetCheck($version_no);
		$this->response($data);
	}
}