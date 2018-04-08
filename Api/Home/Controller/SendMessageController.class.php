<?php 
/**
 * 农牧通短信控制器
 * 2017.01.18
 */
namespace Home\Controller;
use Think\Controller\RestController;
Class SendMessageController extends RestController {
	Public function SendMsg(){
		$vmodel = D("Verification");
		$v_phe =$vmodel::isMobile($_GET['phe']);
		if(!$v_phe){
			$data['code'] = 204;
			$data['message'] ="手机号格式错误";
			$this->response($data,I("get.callback"));
			return false;
		}
		$data = D("Function")->generateCode($_GET['phe'],$_GET['state']);
		//Verification
		$this->response($data,I("get.callback"));
	}
}
?>