<?php
/**
 * 环信模型类
 *2017.6.10
 */
namespace Home\Model;
use Think\Model;
header("Content-code: text/html; charset=UTF-8");
class HuanxinModel extends Model {
	//设置测试
	//APPKey   1161170306178203#comdlwxmorgancar
	// private $client_id='YXA66vpyQEnLEeeS4LE3grnp8g';
	// private $client_secret='YXA63Dw8mQjYsuJGjvewTxzRKsdZCFE';
	// private $base_url="https://a1.easemob.com/1161170306178203/comdlwxmorgancar/";
	
	// 	黑谷商城（环信）：
	// appkey:1193170619115245#heigushangcheng
	// ClientID:YXA6YF_UQFS7Eee5IKXC1Fs2nw
	// ClientSecret:YXA6Y0Mk6YtKZknjmxCyt8fsVnmxNYQ
	private $client_id='YXA6YF_UQFS7Eee5IKXC1Fs2nw';
	private $client_secret='YXA6Y0Mk6YtKZknjmxCyt8fsVnmxNYQ';
	private $base_url="https://a1.easemob.com/1193170619115245/heigushangcheng/";
	public function sub_getToken($user_name){
		// 获取token
		$huanxin_token['client_id'] = $this->client_id;
		$ss = M('z_huanxin_token')->where(array('client_id'=>$this->client_id))->find();
		if($ss){
			if(strtotime($ss['end_time']) > time()){
				$token =  "Authorization:Bearer ".$ss['access_token'];
			}else{
				$huanxin_to = $this->getToken();
				$huanxin_to['end_time'] = date('Y-m-d H:i:s',time()+$huanxin_to['expires_in']);
				M('z_huanxin_token')->where(array('id'=>$ss['id']))->save(array('end_time'=>$huanxin_to['end_time']));
				$token =  "Authorization:Bearer ".$huanxin_to["access_token"];
			}
		}else{
			$huanxin_token = $this->getToken();
			$huanxin_token['end_time'] = date('Y-m-d H:i:s',time()+$huanxin_token['expires_in']);
			$huanxin_token['client_id']=$this->client_id;
			M('z_huanxin_token')->add($huanxin_token);	
			$token =  "Authorization:Bearer ".$huanxin_token["access_token"];
		}
		return $token;
	}
	/*
		修改用户昵称
	*/
	function editNickname($username,$nickname){
		$url=$this->base_url.'users/'.$username;
		$options=array(
			"nickname"=>$nickname
		);
		$body=json_encode($options);
		$header=array($this->sub_getToken($username));
		$result = postCurl($url,$body,$header,'PUT');
		return $result;
	}
	/**
	*获取token 
	*/
	function getToken()
	{
		//将外面的变量变成全局
		// global $client_id,$client_secret;
		
		$options=array(
		"grant_type"=>"client_credentials",
		"client_id"=>$this->client_id,
		"client_secret"=>$this->client_secret
		);
		//json_encode()函数，可将PHP数组或对象转成json字符串，使用json_decode()函数，可以将json字符串转换为PHP数组或对象
		$body=json_encode($options);
		//使用 $GLOBALS 替代 global
		// $url=$GLOBALS['base_url'].'token';
		$url=$this->base_url.'token';
		$tokenResult = postCurl($url,$body);
		//var_dump($tokenResult['expires_in']);
		// var_dump($tokenResult);
		return $tokenResult;
		// return "Authorization:Bearer ". $tokenResult["access_token"];	
	}
	/**
	  授权注册
	*/
	function createUser($username,$nickname){
		// 注册环信用户
		// $users = $this->createUser($user_name,$token);
		$url=$this->base_url.'users';
		$options=array(
			"username"=>$username,
			"password"=>'123456',
			"nickname"=>$nickname
		);
		$body=json_encode($options);
		$header=array($this->sub_getToken($username));
		$result=postCurl($url,$body,$header);
		return $result;
	}
	/*
		获取单个用户
	*/
	public function getUser($username){
		$url=$this->base_url.'users/'.$username;
		// $header=array($token);
		$header = array($this->sub_getToken($username));
		$result=postCurl($url,'',$header,"GET");
		// var_dump($result);
		// var_dump($header);
		return $result;
	}
	public function isOnline($username) {
		$url = $this->base_url. "users/" . $username . "/status";
		$access_token = $this->getToken ();
		$header [] = 'Authorization: Bearer ' . $access_token;
		$result = $this->postCurl ( $url, '', $header, $type = "GET" );
		//return $result;
		echo $result;
	}
}