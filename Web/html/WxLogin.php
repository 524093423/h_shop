<?php
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
session_start();
$openid  =$_SESSION['openid'];
$nickname= $_SESSION['nickname'];
$headimg =$_SESSION['headimg'];
$flag   = 0;
if(!empty($openid)){
    $flag   = 1;
}
$postData   = array("third_num"=>$openid,"third_name"=>$nickname,"pho"=>$headimg,"login_type"=>2,"posturl"=>1);
$returndata = postCurl("http://www.heigushop.com/index.php/AppLogin/ULogin",$postData,"","");
$returndata = iconv("gbk", "utf-8", $returndata);
$returndata = json_encode($returndata);
$returndata = json_decode(json_decode($returndata));
if($returndata->code==200){
    $info   = $returndata->info;
    $_SESSION['userName']   = $info->user_name;
    $_SESSION['Phone']   = $info->user_phone;
    $_SESSION['Photo']   = $info->user_photo;
    $_SESSION['account']   = $info->account;
    $_SESSION['intl']   = $info->integral;
    $_SESSION['isseller']   = $info->is_seller;
    $_SESSION['ispaypwd']   = $info->is_paypwd;
    $_SESSION['token']   = $info->token;
    $_SESSION['login_type']   = 2;
    //print_r($_SESSION);die;
    exit("<script>location.href='http://www.heigushop.com/Web/html/index2.php'</script>");
}else{
    exit("<script type='text/javascript'>location.href='./login.html';</script>");
}
?>