<?php
require_once 'Lib_cache.php';
ini_set('date.timezone','Asia/Shanghai');
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
$appid="wx95ae61f7bd64319c";
$appSecret="9e54e351b340f7afa817e4f0c6f7aeec";
$APPID='wx95ae61f7bd64319c';
$REDIRECT_URI='http://www.heigushop.com/Web/Wxflollow/callback.php';
$scope='snsapi_base';
$state  = "STATE";
//$scope='snsapi_userinfo';//需要授权
$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
header("Location:".$url);
/*











$token = Lib_Cache::read('access_token', 3600);
$goodsid=$_GET['id'];
$res = GETCURL("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appSecret");
$res = json_decode($res, true);
$token = $res['access_token'];
Lib_Cache::write('access_token222',$res);
if (!$token || strlen($token) < 6) {
    $res = GETCURL("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appSecret");
    $res = json_decode($res, true);
    $token = $res['access_token'];
    Lib_Cache::write('access_token',$token);
}
$accesstoken    = $token;
//print_r($openId);die;
$url            = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accesstoken&type=jsapi";
$jsapi_ticket   = Lib_Cache::read("jsapi_tiket", 3600);
if (!$jsapi_ticket || strlen($jsapi_ticket) < 6) {
    $res = GETCURL($url);
    $res = json_encode($res);
    $res = json_decode(json_decode($res));//tiket信息
    $jsapi_ticket  = $res->ticket;//tiket值
    Lib_Cache::write('jsapi_tiket', $jsapi_ticket);
}
if(!empty($jsapi_ticket)){
   //开始组合数据
   $strdata = array("A","B","C","D","E","F","G","H","I","J","k","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
   $noncestr= $strdata[rand(0, 14)].$strdata[rand(0, 7)].$strdata[rand(0, 25)].date("Ymdhis").$strdata[rand(7, 25)];
   $timestamp= time();
   // 注意 URL 一定要动态获取，不能 hardcode.
   //$protocol = (!empty($_SERVER ['HTTPS']) && $_SERVER ['HTTPS'] !== 'off' || $_SERVER ['SERVER_PORT'] == 443) ? "https://" : "http://";
   //$urls = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
   $urls    = "http://www.heigushop.com/Web/html/productDetails.html?id=".$goodsid;
   $strsha1 = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$urls";
   //echo $strsha1;
   $signature = sha1($strsha1);
   $data['noncestr']    = $noncestr;
   $data['timestamp']   = $timestamp;
   $data['signature']   = $signature;
   $data['appid']       = $appid;
   $data['jsapi_ticket']= $jsapi_ticket;
   $data['url']         = $urls;
   $data['strsha1']     = $strsha1;
   exit(json_encode($data));
}

//print_r($openId);*/
?>