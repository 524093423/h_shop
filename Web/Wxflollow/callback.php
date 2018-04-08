<?php
require_once 'Lib_cache.php';
ini_set('date.timezone','Asia/Shanghai');
session_start();
/**
 * Curl请求
 * @param string $url
 * @return mixed
 */
function GETCURL($url=""){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//如果成功只将结果返回，不自动输出任何内容。
    curl_setopt($curl, CURLOPT_TIMEOUT, 500); //作为最大延续500毫秒，超过这个时间将不去读取页面
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//不检测服务器的证书是否由正规浏览器认证过的授权CA颁发
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//不检测服务器的域名与证书上的是否一致
    curl_setopt($curl, CURLOPT_URL, $url);//设置提交地址路径
    $res = curl_exec($curl);//执行，并接收返回结果
    curl_close($curl);//关闭.
    return $res;
}
$appid = "wx95ae61f7bd64319c";
$secret = "9e54e351b340f7afa817e4f0c6f7aeec";
/*$appid  = "wx5b935cc8db24fca9";
$secret = "1eb2ba3c2812a77dc0872d22eb1c1a78";*/
$code = @$_GET["code"];
//根据openid和access_token查询用户信息
$openiduser = $_SESSION['openid'];
$token =Lib_Cache::read('../WxShare/access_token',3600);
/*if (empty($token) || strlen($openiduser) < 6) {
    $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
    $res    = GETCURL($get_token_url);
    $json_obj = json_decode($res,true);
    $token      = $json_obj['access_token'];
    $openiduser = $json_obj['openid'];
    //$token =file_get_contents('../WxShare/access_token',7200);
    Lib_Cache::write('openid',$json_obj['openid']);
    Lib_Cache::write('../WxShare/access_token',$token);
}*/
$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
$res    = GETCURL($get_token_url);
$json_obj = json_decode($res,true);
$token      = $json_obj['access_token'];
$openiduser = $json_obj['openid'];
//$token =file_get_contents('../WxShare/access_token',7200);
Lib_Cache::write('openid',$json_obj['openid']);
if(empty($token))
{
    Lib_Cache::write('../WxShare/access_token',$token);
}
$access_token = $token;
$openid = $openiduser;
//$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
$get_user_info_url  = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
$res    = GETCURL($get_user_info_url);
//解析json
$user_obj = json_decode($res,true);
$subscribe  = 1;
if(!empty($user_obj['errcode']))
{
    echo "<script>
    sessionStorage.setItem('subscribe',{$subscribe});
    var urls  = sessionStorage.getItem('return_url');
    window.location.href=urls;
</script>";exit();
}
$subscribe  = @$user_obj['subscribe'];
echo "<script>
    sessionStorage.setItem('subscribe',{$subscribe});
    var urls  = sessionStorage.getItem('return_url');
    window.location.href=urls;
</script>";exit();
?>