<?php
/**
 * 公共功能模型
 * 冯晓磊
 * Date: 2016年10月27日 10:35:17
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class FunctionModel extends Model {
    /*
     * 用户注册时验证码生成以及发送
     * 返回值：1手机号可注册并发送验证码（已发送） -1手机号不可重复注册
     * 日期：2017.02.04
     */
    public function generateCode($user_phone,$state){
    	$user   = M("user");
    	$data['is_phone'] = $user_phone;
    	//生成四位随机数
    	$phonecode = rand(1000,9999);
    	$data['code']       = $phonecode;
    	//判断该手机是否已经注册  找回密码和修改支付密码 $state = 1
    	$state  = $state?$state:$this->duplicateRegister($user_phone);
    	//print_r($data);die;
    	if($state == 1){
    		$ls = M("register_emporary");
    		$lsPhone    = $ls->field("id")->where("is_phone='$user_phone'")->select();
    		if(empty($lsPhone)){
    			$data['send_time']    = date("Y-m-d H:i:s");
    			$ls->data($data)->add();
    		}else{
    			$send_time   = date("Y-m-d H:i:s");
    			$Id = $lsPhone[0]['id'];
    			$user->execute("insert into `register_emporary`(`id`,`code`,`send_time`) values ('$Id','$phonecode','$send_time')
    					on duplicate key update `code`='$phonecode',`send_time`='$send_time'");
    		}
    		//发送短信
            // $con    = $this->sendPhoneCode($user_phone,$phonecode,'codetext');
    		$con    = $this->sendPhoneCode($user_phone,$phonecode,'codetext');
    		if($con){
    			$returnData['code'] = 200;
    			$returnData['message']="短信发送成功，请查收";
    		}else{
    			$returnData['code'] = 204;
    			$returnData['message']="短信发送失败，请注意填写的手机号信息";
    		}
    		return $returnData;
    	}
    	$returnData['code'] = 204;
    	$returnData['message']="手机号不可重复注册";
    	return $returnData;
    }
    /*
     * 判断手机号是否重复注册
     * 返回值：1可以注册;-1不可重复注册
     * 日期：2017.02.04
     */
    public function duplicateRegister($user_phone){
    	$user   = M("user");
    	$userPhone   = $user->field("user_phone","user_id")->where("user_phone='$user_phone'")->select();
    	if(empty($userPhone)){
    		return 1;
    	}else{
    		return -1;
    	}
    }
    /*
     * 发送验证码
     * 日期：2017.02.04
     */
    public function sendPhoneCode($mobile,$code){
        $userid = '400376';
        $timespan = date('YmdHis');
        $password = '606638';
        $pwd = strtoupper(md5($password.$timespan));
        // $random = new \Org\Net\Random();
        // $code = $random->number(4);
        $str = '【嘿谷商城】您的手机验证码：'.$code.'，请勿向任何单位或个人透漏，感谢使用.';
        $content = base64_encode($str);

        $url = "http://api.shumi365.com:8090/sms/send.do";

        $post_data['userid'] = $userid;
        $post_data['pwd'] = $pwd;
        $post_data['timespan'] = $timespan;
        $post_data['msgfmt'] = 'UTF8';
        $post_data['mobile'] = $mobile;
        $post_data['content'] = $content;

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($post_data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        
        if (curl_errno($curl)) {
            echo 'Error POST'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        //echo $result;exit;
        if(strlen($result)>5){
            return $code;
        }else{
            return false;
        }
    }
    /*
     * 发送订单类的验证码
     */
    public function sendPhoneCodeFromOrder($mobile,$content){
        $userid = '400376';
        $timespan = date('YmdHis');
        $password = '606638';
        $pwd = strtoupper(md5($password.$timespan));
        // $random = new \Org\Net\Random();
        // $code = $random->number(4);
        $str = $content;
        $content = base64_encode($str);

        $url = "http://api.shumi365.com:8090/sms/send.do";

        $post_data['userid'] = $userid;
        $post_data['pwd'] = $pwd;
        $post_data['timespan'] = $timespan;
        $post_data['msgfmt'] = 'UTF8';
        $post_data['mobile'] = $mobile;
        $post_data['content'] = $content;

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($post_data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Error POST'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        //echo $result;exit;
        Rewrite_log("./aaaaaaaa.txt",$result);
        if(strlen($result)>5){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 极光推送 所有
     */
    public function jpushAll($content){
        include_once './ThinkPHP/Library/Vendor/Jpush/autoload.php';
        $title   =  '嘿谷商城';
        $appkeys = "84e5cedc91211ed2585f55eb";
        $masterSecret = "e424b9816c6ca06439148c3b";
        $client = new \JPush\Client($appkeys,$masterSecret);
        $result = $client->push()
            ->setPlatform("ios",'android')
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->options(array(
                "apns_production" => false  //true表示发送到生产环境(默认值)，false为开发环境
            ))
            ->send();
        echo 'Result=' . json_encode($result);
        die;
        $obj = new \Org\Util\Jpush($masterSecret,$appkeys);
        $res = $obj->send($sendno, 3, $receiver_value, 1, $msg_content, $platform);
        print_r($res);
    }
    /**
     * 极光推送 单个
     */
    public function jpushonly($tag1,$content,$extras=""){
        if(is_string($tag1)){
            $tag     = $tag1;
        }else{
            $tag     = (string)$tag1;
        }
        include_once './ThinkPHP/Library/Vendor/Jpush/autoload.php';
        $title   =  '嘿谷商城';
        $appkeys = "84e5cedc91211ed2585f55eb";
        $masterSecret = "e424b9816c6ca06439148c3b";
        $client = new \JPush\Client($appkeys,$masterSecret);
        // ->message($content,['title' => 'Hello','content_type' => 'text','extras' => ['key' => 'value']
        $result = $client->push()
            ->setPlatform(['ios', 'android'])
            ->addTag($tag)
            ->iosNotification($content, [
                'title' => $title,
                'sound' => 'sound',
                'badge' => '+1',
                'extras'=>$extras
            ])
            ->androidNotification($content, [
                'title' => $title,
                'sound' => 'sound',
                'badge' => '+1',
                'extras'=>$extras
            ])
            //->message($content,['title' =>$title,'content_type' => 'text'])
            ->send();
        //echo 'Result=' . json_encode($result);
    }
}
