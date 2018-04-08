<?php
//print_r($_GET);die;
require_once 'Lib_cache.php';
ini_set('date.timezone','Asia/Shanghai');
/**
 * Curl post 提交
 * @param unknown $url
 * @param unknown $post_data
 * @param unknown $body
 * @param unknown $header
 * @return mixed
 */
function postCurl($url,$post_data,$body, $header){
    //$url = "http://localhost/web_services.php";
    //$post_data = array ("username" => "bob","key" => "12345");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// post数据
    curl_setopt($ch, CURLOPT_POST, 1);// post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);
    curl_close($ch);
    //打印获得的数据
    return $output;
}
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
//error_reporting(E_ERROR);
require_once "./lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';
//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
//①、获取用户openid
$tools = new JsApiPay();
$openId = $tools->GetOpenid();
if(!empty($openId)){
    $openIduser     = $openId['openid'];
    $accesstoken    = $openId['access_token'];
    /*Lib_Cache::write('../WxShare/access_token',$accesstoken);*/
   $url            = $tools->__GetUserInfo($accesstoken,$openIduser);
   $returndata     =GETCURL($url);
   if(!empty($returndata)){
       $filemode    = fopen("./unionidxxx.txt","a+");
       fwrite($filemode,$returndata);
       fclose($filemode);
       //$returndata = iconv("utf-8", "gbk", $returndata);
       //echo mb_detect_encoding($returndata);
       $returndata = json_encode($returndata);
       $returndata = json_decode(json_decode($returndata));//拉取到的用户信息
       $postData   = array("third_num"=>$returndata->unionid,"third_name"=>$returndata->nickname,"pho"=>$returndata->headimgurl,"login_type"=>2,"posturl"=>1);
       $returndata1 = postCurl("http://www.heigushop.com/index.php/AppLogin/ULogin",$postData,"","");
       $returndata1 = iconv("gbk", "utf-8", $returndata1);
       $returndata1 = json_encode($returndata1);
       $returndata1 = json_decode(json_decode($returndata1));
       if($returndata1->code==200){
           $city    = "";
           //通过ip定位百度
           /*$ipUrl  = "http://api.map.baidu.com/location/ip?ak=1jtvvshwlALgPCCRN43N2r6P&coor=bd09ll";
           $iplocationData  = GETCURL($ipUrl);
           $iplocationData = json_encode($iplocationData);
           $iplocationData = json_decode(json_decode($iplocationData));//拉取到的用户位置信息
           $returnCotent    = $iplocationData->content;
           $address_detail  = $returnCotent->address_detail;
           $city            = iconv("utf-8", "gbk", $address_detail->city);*/
           $info   = $returndata1->info;
           $userName   = $info->user_name;
           $Phone   = $info->user_phone;
           $Photo   = $info->user_photo;
           $account = $info->account;
           $intl    = $info->integral;
           $isseller   = $info->is_seller;
           $ispaypwd   = $info->is_paypwd;
           $token   = $info->token;
           $subscribe   = 0;
           $login_type   = 2;
           $openids =$returndata->openid;
           $loginurl   = "http://www.heigushop.com/Web/html/index2.php?username=$userName&phone=$Phone&photo=$Photo&account=$account&intl=$intl&isseller=$isseller&ispaypwd=$ispaypwd&token=$token&logintype=$login_type&openid=$openids&city=$city&subscribe=$subscribe";
           //print_r($_SESSION);die;
           //echo $loginurl;die;
           exit("<script>location.href='".$loginurl."'</script>");
       }else{
           exit("<script type='text/javascript'>location.href='http://www.heigushop.com/Web/Wxlogin/jsapi.php';</script>");
       }
   }
}
//print_r($openId);
?>