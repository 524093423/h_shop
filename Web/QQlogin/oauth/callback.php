<?php
require_once("../API/qqConnectAPI.php");
$qc = new QC();
echo $qc->qq_callback();
echo $qc->get_openid();
echo "Code:".$_GET['code']."<br/>";
echo "state:".$_GET['state'];
$getUrl	= "https://graph.qq.com/oauth2.0/authorize";
$code	= $_GET['code'];
$state	= $_GET['state'];
$appid	= "101405623";
$redirect_uri	= urlencode("www.heigushop.com/Web/QQlogin/oauth/getToken.php");
$url 			= $getUrl."?response_type=".$code."&client_id=".$appid."&redirect_uri=".$redirect_uri."&state=".$state;
echo $url;die;
?>
<script>console.log(<?php echo $url;?>);return false;</script>
<script>window.location.href='<?php echo $url;?>';</script>
