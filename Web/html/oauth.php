<?php
$code  = @$_GET['code'];
$state = @$_GET['state'];
$flag  = 0;
//换成自己的接口信息
$appid = 'wx95ae61f7bd64319c';
$appsecret = '9e54e351b340f7afa817e4f0c6f7aeec';
if (empty($code)) $this->error('授权失败');
$token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
$token = json_decode(file_get_contents($token_url));
if (isset($token->errcode)) {
    echo '<h1>error：</h1>'.$token->errcode;
    echo '<br/><h2>errorInfo：</h2>'.$token->errmsg;
    exit;
}
$access_token_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$token->refresh_token;
//转成对象
$access_token = json_decode(file_get_contents($access_token_url));
if (isset($access_token->errcode)) {
    echo '<h1>error：</h1>'.$access_token->errcode;
    echo '<br/><h2errorInfo：</h2>'.$access_token->errmsg;
    exit;
}
$user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token->access_token.'&openid='.$access_token->openid.'&lang=zh_CN';
//转成对象
$user_info = json_decode(file_get_contents($user_info_url));
if (isset($user_info->errcode)) {
    echo '<h1>error：</h1>'.$user_info->errcode;
    echo '<br/><h2>errorInfo：</h2>'.$user_info->errmsg;
    exit;
}
//打印用户信息
$openid     = $user_info->openid;//用户openid
$_SESSION['openid'] = $openid;
$nickname   = $user_info->nickname;//用户昵称
$_SESSION['nickname'] = $nickname;
$headimg    = $user_info->headimgurl;//用户头像
$_SESSION['headimg'] = $headimg;
if(!empty($openid)){
   exit("<script>location.href='http://www.heigushop.com/Web/html/WxLogin.php'</script>"); 
}else{
   exit("<script>alert('授权失败,请重新授权');</script>");
}
?>