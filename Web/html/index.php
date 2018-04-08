<?php 
$appid = 'wx95ae61f7bd64319c';
header('location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=http://www.heigushop.com/Web/html/oauth.php&response_type=code&scope=snsapi_userinfo&state=123&connect_redirect=1#wechat_redirect');
?>