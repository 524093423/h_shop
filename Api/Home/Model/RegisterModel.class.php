<?php
/**
 * 用户注册（登陆）模型类
 *2017.5.12
 */
namespace Home\Model;
use Think\Model;
header("Content-code: text/html; charset=UTF-8");
class RegisterModel extends Model {
	private $_APIURl;
    /**
    *用户注册
    *@phe 手机号
    *@code 验证码
    *@pwd 用户密码
    *@register_type 用户注册类型
     */
    public function URegister($phe,$code,$pwd,$p_pwd,$register_type){
        if($pwd != $p_pwd){
            $data['code'] = 204;
            $data['message'] ="两次密码输入不一致";
            return $data;
        }
    	$vmodel = D("Verification");
    	$vphe     = $vmodel::isMobile($phe);
    	if(!$vphe  || strlen($phe)>11){
    		$data['code'] = 204;
			$data['message'] ="手机号格式错误";
			return $data;
    	}
    	$vpwd      = $vmodel::isPWD($pwd);
    	if(!$vpwd){
    		$data['code'] = 204;
    		$data['message'] ="用户密码格式错误";
    		return $data;
    	}
        // 手机注册类型
        if($register_type != 1){
            $data['code'] = 204;
            $data['message'] ="用户注册类型错误";
            return $data;
        }
    	$vcode = $this->verifyAppCode($phe,$code);
    	if($vcode==-1){
    		$data['code'] = 204;
    		$data['message'] ="验证码输入有误";
    		return $data;
    	}else if($vcode ==2){
    		$data['code'] = 204;
    		$data['message'] ="验证码已经失效，请重新发送";
    		return $data;
    	}
    	$data = $this->A_user($phe,$pwd,$p_pwd);
    	return $data;
    }
    /**
     * 新增一个用户
     * @phe 手机号
     * @pwd 用户密码
     * @paypwd 用户支付密码 
     * 2017.02.04
     */
    public function A_user($phe,$pwd,$paypwd){
    	$user   = M("user");
    	$telephone     = trim($phe);
    	$password = trim($pwd);
        // 查重复
        $res = $user->where(array('user_phone'=>$telephone,'register_type'=>1))->select();
        if(!empty($res)){

            $returnData['code'] = 204;

            $returnData['message']="手机号码已经注册，请直接登录";

        }else{
            $Random = new \Org\Net\Random();

            $salt = $Random->word(6);

            $new_add = array(
                'user_phone' => $telephone,
                'user_password' => md5($password.$salt),
                'user_name' => $telephone,//默认新用户昵称为手机号
                'create_time' => date("Y-m-d H:i:s"),
                'accountbalance' => 0,
                'salt' => $salt,
                'register_type' => 1,
                'integral'=>10000,
                'user_photo'=>"/Uploads/rentou.png",
                );

            $new_add['token'] = md5($new_add['user_password'].$salt);

        	$registerState  = $user->add($new_add);

        	$ls = M("register_emporary");

        	$ls->where("is_phone='".$new_add['user_phone']."'")->delete();

            // 注册一个环信账户、昵称修改一起

            $huan = D('Huanxin')->createUser($new_add['token'],$telephone);

            // 站内信 新增消息

            $title = '注册消息';

            $message = '恭喜您已经成功注册为会员';

            $type = 1;//type 1系统消息 2站内信

            $h_message = D('Message')->add($registerState,$title,$message,$type);

        	if(empty($registerState)){

        		$returnData['code'] = 204;

        		$returnData['message']="注册失败";

        	}else{

        		$returnData['code'] = 200;

        		$returnData['message']="注册成功，请登录";

        	}
        }
    	return $returnData;
    }
    
    /**
     * 验证输入的手机验证码是否正确(是否超出验证码时间限制目前是10分钟 后台管理可操作)
     * @phe 手机号
     * @code 验证码
     * 返回值：-1 验证码输入有误 1 验证码输入正确 2验证码已经失效
     * 日期：2017.02.04
     */
    public function verifyAppCode($user_phone,$code){
    	$ls = M("register_emporary");
    	$data   =$ls->where("is_phone='$user_phone' and code='$code'")->select();
    	$send_time  = strtotime($data[0]['send_time']);
    	$curr_time  = strtotime(date("Y-m-d H:i:s"));
    	$codetime   = $this->GetCodeTime();
    	$codetime   = $codetime * 60;
    	if(empty($data)){
    		return -1;
    	}else{
    		$coud_time  = $curr_time-$send_time;
    		if($coud_time > $codetime){
    			return 2;
    		}
    		return 1;
    	}
    }
    /**
     * 获取验证码失效时间
     * 日期：2017.02.04
     */
    public function GetCodeTime(){
    	$model  = M("publicsetting");
    	$data   = $model->where("`set`='codetime'")->find();
    	if(!empty($data)){
    		return $data['content'];
    	}else{
    		return 30;
    	}
    }
    
    
    /**
     * 用户登陆
     * @phe 手机号
     * @pwd 密码
     * 2017.02.04
     */
    public function Login($phe,$pwd,$login_type){
        $vmodel = D("Verification");
        $vphe   = $vmodel::isMobile($phe);
        if(!$vphe  || strlen($phe)>11){
            $data['code'] = 204;
            $data['message'] ="手机号格式错误";
            return $data;
        }
        $vpwd    = $vmodel::isPWD($pwd);
        if(!$vpwd){
            $data['code'] = 204;
            $data['message'] ="用户密码格式错误";
            return $data;
        }
    	$user   = M("user");
    	$userinfo   = $user->field("user_id,user_password,user_phone,user_photo,user_name,integral,account,pay_password,state,is_seller,salt,token")->where(array('user_phone'=>$phe,'register_type'=>$login_type))->find();
    	if(empty($userinfo)){
    		$returnData['code'] = 204;
    		$returnData['message']="手机号未注册，请重新注册";
    		return $returnData;
    	}
    	$user_password   = $userinfo['user_password'];
        $token = $userinfo['token'];
        $salt = $userinfo['salt'];
        if(md5($pwd.$salt) == $user_password){
            $info['token'] = $token;
            $info['user_name'] = $userinfo['user_name'];
            $info['user_phone'] = $userinfo['user_phone'];
            $info['is_seller'] = $userinfo['is_seller'];
            $info['integral'] = $userinfo['integral'];
            $info['account'] = $userinfo['account'];
            if($userinfo['pay_password']){
                $info['is_paypwd']= 1;
            }else{
                $info['is_paypwd']= 2;
            }
            // 根据user_id获取用户的商铺状态，商家状态
            $store = D("Register")->getSellerStore($userinfo['user_id']);
            $info['seller_id'] = $store['seller_id'];
            $info['seller_status'] = $store['seller_status'];
            $info['store_status'] = $store['store_status'];
            $this->_APIURl=D("Common")->getUrl();
            $info['user_photo'] = $this->_APIURl.$userinfo['user_photo'];
            $info['login_type'] =$login_type;
            $info['user_id']    = $userinfo['user_id'];
            $userState = 200;
            $userstring = "登录成功";
            $returnData['info'] = $info;
        }else{
            $userState = 204;
            $userstring = "密码错误";
        }
    	$returnData['code'] = $userState;
    	$returnData['message']=$userstring;
    	return $returnData;
    }
     /**
     * 第三方登陆
     * @third_num 第三方账号
     * @third_name 第三方昵称 user_name
     * @login_type 登录方式
     * 2017.02.04
     */
    public function ThirdLogin($third_num,$third_name,$login_type,$pho){
        if(!trim($third_num) || !trim($third_name) || !$login_type){
            $returnData['code'] = 204;
            $returnData['message'] ="数据不全，请检查";
        }else{
            $user = M('user');
            $map['third_num']  = array('like', $third_num);
            $userinfo  = $user->where(array('third_num'=>$third_num,'register_type'=>$login_type))->find();
            if(!$userinfo){
                // 没有第三方账号，新注册
                $Random = new \Org\Net\Random();
                $salt = $Random->word(6);
                $new_add = array(
                    'third_num' => $third_num,
                    'user_name' => $third_name,//默认新用户昵称为手机号
                    'create_time' => date("Y-m-d H:i:s"),
                    'accountbalance' => 0,
                    'salt' => $salt,
                    'integral'=>"10000",
                    'register_type' => $login_type,
                    'user_photo' => $pho,
                );
                $new_add['token'] = md5(md5($third_num.$salt).$salt);
                $registerState  = $user->add($new_add);
                // 注册一个环信账户、昵称修改一起
                $huan = D('Huanxin')->createUser($new_add['token'],$third_name);
                if($registerState){
                    $info['token'] = $new_add['token'];
                    $info['user_name'] = $new_add['user_name'];
                    $info['user_phone'] = "";
                    $info['is_seller'] = "0";
                    $info['integral'] = "10000";
                    $info['account'] = "0";
                    $info['is_paypwd']= 2;
                    $info['user_photo'] = $pho;
                    $info['login_type'] =$login_type;
                    $info['seller_id'] = '0';
                    $info['seller_status'] = '0';
                    $info['store_status'] = '0';
                    $info['user_id']    = $registerState;
                    $returnData['info'] = $info;
                    $returnData['code'] = 200;               
                    $returnData['message'] = "登录成功";                   
                }else{
                    $returnData['code'] = 204;               
                    $returnData['message'] = "登录失败";
                }
                return $returnData;
            }else{
                // 有第三方账号
                // 更新昵称
                $token = $userinfo['token'];
                $salt = $userinfo['salt'];
                if($third_name != $userinfo['user_name']){
                    $user->where(array('token'=>$token,'register_type'=>$login_type))->save(array('user_name'=>$third_name,'user_photo'=>$pho));
                    // 若不是商家，修改环信昵称；若是，此处不修改环信昵称
                    if($userinfo['is_seller'] != 1){
                        D('Huanxin')->editNickname($token,$third_name);
                    }
                }
                if(md5(md5($third_num.$salt).$salt) == $token){
                    $info['token'] = $token;
                    $info['user_name'] = $third_name;
                    $info['user_phone'] = $userinfo['user_phone']?$userinfo['user_phone']:'';
                    $info['is_seller'] = $userinfo['is_seller'];
                    $info['integral'] = $userinfo['integral'];
                    $info['account'] = $userinfo['account'];
                    if($userinfo['pay_password']){
                        $info['is_paypwd']= 1;
                    }else{
                        $info['is_paypwd']= 2;
                    }
                    $info['user_photo'] = $pho;
                    $info['login_type'] =$login_type;
                    $info['user_id']    = $userinfo['user_id'];
                    // 根据user_id获取用户的商铺状态，商家状态
                    $store = D("Register")->getSellerStore($userinfo['user_id']);
                    $data['seller_id'] = $store['seller_id'];
                    $data['seller_status'] = $store['seller_status'];
                    $data['store_status'] = $store['store_status'];
                    $userState = 200;
                    $userstring = "登录成功";
                    $returnData['info'] = $info;
                }else{
                    $userState = 204;
                    $userstring = "密码错误";
                }
            }  
        }
        $returnData['code'] = $userState;
        $returnData['message']=$userstring;
        return $returnData;
    }
    /**
     *用户找回密码
     *@phe 手机号
     *@code 验证码
     *@pwd 第一次密码
     *@ppwd 第二次密码
     */
    public function F_UserPwd($phe,$code,$pwd,$p_pwd,$salt){
    	$vmodel = D("Verification");
    	if($pwd != $p_pwd){
    		$data['code'] = 204;
    		$data['message'] ="两次密码输入不一致";
    		return $data;
    	}
    	$vphe     = $vmodel::isMobile($phe);
    	if(!$vphe  || strlen($phe)>11){
    		$data['code'] = 204;
    		$data['message'] ="手机号格式错误";
    		return $data;
    	}
    	$vpwd      = $vmodel::isPWD($pwd);
    	if(!$vpwd){
    		$data['code'] = 204;
    		$data['message'] ="用户密码格式错误";
    		return $data;
    	}
    	$vcode = $this->verifyAppCode($phe,$code);
    	if($vcode==-1){
    		$data['code'] = 204;
    		$data['message'] ="验证码输入有误";
    		return $data;
    	}else if($vcode ==2){
    		$data['code'] = 204;
    		$data['message'] ="验证码已经失效，请重新发送";
    		return $data;
    	}
    	$state = $this->FindPaswordCurl($phe,$pwd,$salt);
    	if($state ==1){
    		$data['code'] = 200;
    		$data['message'] ="密码找回成功";
    	}else{
    		$data['code'] = 204;
    		$data['message'] ="密码找回失败";
    	}
    	return $data;
    }
    /**
     * 找回密码操作
     * 返回值：1更新成功；2更新失败
     * 日期：2017.02.04
     */
    public function FindPaswordCurl($userPhone,$user_password,$salt){
    	$user_phone = $userPhone;
    	$data['user_password'] = md5($user_password.$salt);
    	// $data['findpas_time'] = date("Y-m-d H:i:s");
    	$user   =M("user");
        $reset = $user->where(array('user_phone'=>$user_phone,'register_type'=>1))->save(array('user_password'=>null));
    	$updatestate = $user->where(array('user_phone'=>$user_phone,'register_type'=>1))->save($data);
    	$ls = M("register_emporary");
    	$ls->where("is_phone='$user_phone'")->delete();
    	if(empty($updatestate)){
    		return 2;
    	}else{
    		return 1;
    	}
    }
    /**
     * 用户修改/绑定手机号码
     * @token  用户唯一标识token
     * @code  验证码
     * @phe   手机号码
     */
    public function M_UpUserPhe($token,$code,$phe){
        $vmodel = D("Verification");
        $vcode = $this->verifyAppCode($phe,$code);
        if($vcode==-1){
         $data['code'] = 204;
         $data['message'] ="验证码输入有误";
         return $data;
        }else if($vcode ==2){
         $data['code'] = 204;
         $data['message'] ="验证码已经失效，请重新发送";
         return $data;
        }
        $vphe     = $vmodel::isMobile($phe);
        if(!$vphe || strlen($phe)>11){
            $returnData['code'] = 204;
            $returnData['message'] ="手机号格式错误";
            return $returnData;
        }
        $userinfo = $this->getUserBasicInfo($token);
        if(!$userinfo)
            return array("code"=>"204","message"=>"该用户不存在");
        $old_phe = $userinfo['user_phone'];
        if($phe == $old_phe){
            $returnData['code'] = 204;
            $returnData['message']  = "新手机号码与原手机号码一致，请重新填写";
            return $returnData;
        }
        $register_type = $userinfo['register_type'];
        // 查是否与其他用户的手机号码重复
        $other = M("user")->where( "token !='$token' and user_phone ='$phe'  and register_type = '$register_type'")->select();
        if($other){
            $returnData['code'] = 204;
            $returnData['message'] ="手机号已经被占用,请核对";
            return $returnData;
        }
        $currdata['user_phone'] = $phe;
        // 更新手机号码
        $state = M("user")->where(array('token'=>$token))->save($currdata);
        $ls = M("register_emporary");
        $ls->where("is_phone='$phe'")->delete();
        if(!empty($state))
            return array("code"=>"200","message"=>"手机号码修改成功");
        return array("code"=>"204","message"=>"手机号码修改失败");
    }
    /**
     * 修改用户密码
     * @token  用户唯一标识token
     * @cpwd  用户当前密码
     * @pwd    用户新密码
     * @p_pwd 确认新密码
     * 20170513
     */
    public function M_UpUserPwd($token,$cpwd,$pwd,$phe,$p_phe){
        if($cpwd == $pwd){
            $data['code'] = 204;
            $data['message'] ="新密码与当前密码相同，请重新填写";
            return $data;
        }
        if($pwd == $p_pwd){
            $data['code'] = 204;
            $data['message'] ="两次新密码，请重新填写";
            return $data;
        }
    	$vmodel = D("Verification");
    	$vpwd      = $vmodel::isPWD($pwd);
    	if(!$vpwd){
    		$data['code'] = 204;
    		$data['message'] ="用户新密码格式错误";
    		return $data;
    	}
    	$vcpwd      = $vmodel::isPWD($cpwd);
    	if(!$vcpwd){
    		$data['code'] = 204;
    		$data['message'] ="用户当前密码格式错误";
    		return $data;
    	}

    	$userinfo = $this->getUserBasicInfo($token);
    	if(!$userinfo)
    		return array("code"=>"204","message"=>"该用户不存在");
    	
    	$datacpwd = $userinfo['user_password'];
        $salt = $userinfo['salt'];
    	if(md5($cpwd.$salt) != $datacpwd){
    		$data['code'] = "204";
    		$data['message'] ="当前密码输入有误";
    		return $data;
    	}
    	$currdata['user_password'] = md5($pwd.$salt);
    	$state = M("user")->where(array('token'=>$token))->save($currdata);
        $u = M("user")->field('user_id')->where(array('token'=>$token))->find();
    	$ls = M("register_emporary");
    	$ls->where("is_phone='$phe'")->delete();
    	if(!empty($state))
            // 站内信 新增消息
            $title = '密码修改';
            $message = '登录密码修改成功，请谨记！';
            $type = 1;//type 1系统消息 2站内信
            $h_message = D('Message')->add($u['user_id'],$title,$message,$type);
    		return array("code"=>"200","message"=>"密码修改成功");
    	return array("code"=>"204","message"=>"密码修改失败");
    }
    
    /**
     * 通过用户id获取用户基本信息
     * @userid 用户唯一标识id
     * 2017.02.04
     */
    public function getUserBasicInfo($token){
        $info = M("user")->field("user_id,pay_password,user_password,user_phone,user_photo,user_name,is_seller,integral,account,freeze_account,register_type as login_type,salt,token")->where(array("token"=>$token))->find();
    	if(empty($info)){
			return false;
        }else{
            $info['account'] = $info['account']-$info['freeze_account'];
            unset($info['freeze_account']);
            return $info;
        }
    	return $info;
    }
    public function getUserBasicInfo2($userid){
        $info = M("user")->field("user_id,pay_password,user_password,user_phone,user_photo,user_name,is_seller,integral,account,freeze_account,register_type as login_type,salt,token")->where(array("user_id"=>$userid))->find();
        if(empty($info)){
            return false;
        }else{
            $info['account'] = $info['account']-$info['freeze_account'];
            unset($info['freeze_account']);
            return $info;
        }
        return $info;
    }

    // 根据token获取用户的商铺状态，商家状态
    public function getSellerStore($user_id){
        $seller = M('h_seller_detail')->field('seller_id,status')
            ->where(array('user_id'=>$user_id))->find();
        if($seller){
            $arr['seller_id'] = $seller['seller_id'];
            $arr['seller_status'] = $seller['status'];
            $store = M('business_info')
                ->where(array('seller_id'=>$seller['seller_id']))->find();
            if($store){
                $arr['store_status'] ='1';
            }else{
                $arr['store_status'] ='0';
            }
        }else{
            $arr['seller_id'] ='0';
            $arr['seller_status'] ='0';
            $arr['store_status'] ='0';
        }

        return $arr;
    }
    /**
     * 用户修改支付密码
     * @userid  用户唯一标识id
	 * @code   验证码
	 * @phe  用户手机号
	 * @paypwd 第一次输入的支付密码
	 * @paypwds  新支付密码
     */
    public function UpUserPayPwd($phe,$userid,$code,$paypwd,$paypwds){
    	if($paypwd != $paypwds){
    		$data['code'] = 204;
    		$data['message'] ="两次支付密码输入不一致";
    		return $data;
    	}
    	$vmodel = D("Verification");
    	$vpay      = $vmodel::isPayPWD($paypwd);
    	if(!$vpay){
    		$data['code'] = 204;
    		$data['message'] ="支付密码格式错误";
    		return $data;
    	}
    	$vcode = $this->verifyAppCode($phe,$code);
    	if($vcode==-1){
    		$data['code'] = 204;
    		$data['message'] ="验证码输入有误";
    		return $data;
    	}else if($vcode ==2){
    		$data['code'] = 204;
    		$data['message'] ="验证码已经失效，请重新发送";
    		return $data;
    	}
    	return $this->UpdateUserPaypwd($phe,$userid, $paypwd);
    }
    /**
     * 修改用户支付密码的操作
     * @userid  用户唯一标识id
     * @paypwd 用户支付密码
     * 2017.02.04
     */
    public function UpdateUserPaypwd($phe,$userid,$paypwd){
    	$data['pay_password'] = hash("sha256",$paypwd);
    	$user= M("user");
    	$state = $user->where("user_id=$userid")->save($data);
    	//echo $user->getLastSql();die;
    	$ls = M("register_emporary");
    	$ls->where("is_phone='$phe'")->delete();
    	if(!empty($state))
    		return array("code"=>"200","message"=>"支付密码修改成功");
    	return array("code"=>"204","message"=>"支付密码修改失败");
    }
    
    /**
     * 头像上传
     * @userid  用户唯一标识id
     * @filename 头像文件存放的相对路径
     * 日期：2017.02.04
     * 返回值：1成功 0 失败
     */
    public function UploadHead($token,$filename){
    	$user   = M("user");
        $old_user_photo = $user->field('user_photo')->where("token='$token'")->find();
        if($old_user_photo){
            unlink($old_user_photo);
        }
    	$data['user_photo']  = $filename;
    	$returnState   = $user->where("token='$token'")->save($data);
    	if(!empty($returnState)){
    		$this->_APIURl=D("Common")->getUrl();
    		$returnData['code'] = 200;
    		$returnData['message']  = "头像上传成功";
    		$returnData['info']['uhead']     = $this->_APIURl.$filename;
    	}else{
    		$returnData['code'] = 204;
    		$returnData['message']  = "头像上传失败";
    	}
    	return $returnData;
    }
    
    /**
     * 修改昵称
     * @token 用户唯一标识token
     * @username 用户昵称
     * 2017.02.04
     */
    Public function UpdateUserNickName($token,$user_name){
    	$model = M("user");
    	$data['user_name'] = $user_name;
        $userinfo = $model->where(array('token'=>$token))->find();
        if(!$userinfo){
            $returnData['code'] = 204;
            $returnData['message']  = "用户信息不存在，请确认信息";
            return $returnData;
        }
        if($user_name == $userinfo['user_name']){
            $returnData['code'] = 204;
            $returnData['message']  = "新昵称与当前昵称一致，请重新填写昵称";
            return $returnData;
        }
    	$state = $model->where(array('token'=>$token))->save($data);
    	if(!empty($state))
              // 若不是商家，修改环信昵称；若是，此处不修改环信昵称
                if($userinfo['is_seller'] != 1){
                    D('Huanxin')->editNickname($token,$user_name);
                }
    		return  array("code"=>"200","message"=>"昵称修改成功");
    	return  array("code"=>"204","message"=>"昵称修改失败");
    }
    /**
     * 通过token返回对应的用户id
     * @param unknown $token
     * @return Ambigous <>|boolean
     */
    public function GetUserIdFromToken($token){
        if(!empty($token)){
            $data   = M("user")->field("user_id as userid")->where("token='%s'",$token)->find();
            if(!empty($data)){
                return $data['userid'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 获取用户信息【web-im】
     * @param unknown $userid
     * @param unknown $token
     */
    public function getUserInfoFromToken($userid,$token){
        $data    = $this->getUserBasicInfo($token);
		if($data){
		    $_AdminUrl=D("Common")->getUrl();
			if($data['login_type'] == 1){
				$data['user_photo'] = $_AdminUrl.$data['user_photo'];
			}
			if($data['is_seller']==1){
			    $shopinfo    = $this->GetSellerInfoFromUserid($userid, $_AdminUrl);
			}
			if(!empty($shopinfo)){
			    $data  = array_merge($data,$shopinfo);
			}
			$returndata['code'] =200;
			$returndata['message'] = "用户信息获取成功";
			$returndata['info']  = $data;
		}else{
			$returndata['code'] =204;
			$returndata['message'] = "用户信息不存在";
		}
		return $returndata;
    }
    /**
     * 获取用户商家信息【web-im】
     * @param unknown $userid
     * @param unknown $Url
     */
    public function GetSellerInfoFromUserid($userid,$Url){
        $seller = M("h_seller_detail");
        $result = $seller->field("business_info.name as shopname,REPLACE(business_info.`blogo`,'/Uploads','".$Url."/Uploads') as blogo")
                  ->join("business_info ON h_seller_detail.seller_id=business_info.seller_id","INNER")
                  ->where("h_seller_detail.user_id=$userid")->find();
        if(!empty($result)){
            return $result;
        }else{
            return 0;
        }
    }
}
