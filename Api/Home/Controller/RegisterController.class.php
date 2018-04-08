<?php
/**
 * 用户注册类
 * 2017.02.04
 */
namespace Home\Controller;
use Think\Controller\RestController;
class RegisterController extends RestController {
   /**
    *手机号注册
    *@phe 手机号
    *@code 验证码
    *@pwd 用户密码
    *@register_type 用户注册类型
    */
	public function UserRegister(){
		$phe = $_POST['phe'];
		$code = $_POST['code'];
		$pwd = $_POST['pwd'];
		$p_pwd = $_POST['p_pwd'];
		$register_type = $_POST['register_type'] ? $_POST['register_type'] : 1;
		$data = D("Register")->URegister($phe,$code,$pwd,$p_pwd,$register_type);
		$this->response($data);
	}
	// 测试注册环信账号
	public function Huanxin_create(){
		$username = '123456789123456789';
		$nickname = '17133851067';
		$huan = D('Huanxin')->createUser($username,$nickname);
		return $huan;
	}
	// 测试修改环信昵称
	public function editNickname(){
		$username = 'a5619a5e6d744f3132a44411e352f5b6';
		$nickname = '17133851067';
		$res = D('Huanxin')->editNickname($username,$nickname);
		var_dump($res);
	}
	public function Huan_getUser(){
		$h = D('Huanxin')->getUser('46a8f23e8217269f2473782bd0ce4c16');
		var_dump($h);
	}
}