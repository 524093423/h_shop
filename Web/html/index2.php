<?php
$flag   = 0;
if(!empty($_GET)){
	session_start();
    $flag   = 1;
	$_SESSION['openid']	= $_GET['openid'];
}
?>
<script src="../js/jquery1.9.1.js"></script>
<script type="text/javascript">
var login	= <?php echo $flag;?>;
  $(function(){ //判断 是 安卓 还是IOS 手机
        /* var u = navigator.userAgent;
        var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
        var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
        if(isAndroid){
            return;
        }
        if(isiOS){
            $('.top').css({'height':'1.765625rem'})
            $('.content').css({'margin-top':'1.765625rem'})
            $('.topWrap').css({'margin-top':'0.328125rem'})
        } */
        if(login==1){
        	//layer.msg("授权成功",{time:1000});
        	sessionStorage.setItem('userName',<?php echo "'".$_GET['username']."'";?>);//用户名
			sessionStorage.setItem('Phone',<?php echo "'".$_GET['phone']."'"; ?>);//用户手机号
			sessionStorage.setItem('Photo',<?php echo "'".$_GET['photo']."'"; ?>);//用户头像
			sessionStorage.setItem('account',<?php echo "'".$_GET['account']."'"; ?>);//用户账户余额
			sessionStorage.setItem('intl',<?php echo "'".$_GET['intl']."'"; ?>);//用户积分数量
			sessionStorage.setItem('isseller',<?php echo "'".$_GET['isseller']."'"; ?>);//用户是否为商户 0否
			sessionStorage.setItem('ispaypwd',<?php echo "'".$_GET['ispaypwd']."'"; ?>);//用户是否设置支付密码2否
			sessionStorage.setItem('token',<?php echo "'".$_GET['token']."'"; ?>);//用户token
			sessionStorage.setItem("login_type",<?php echo "'".$_GET['logintype']."'"; ?>);
			sessionStorage.setItem("openid",<?php echo "'".$_GET['openid']."'"?>);
			//sessionStorage.setItem("city",<?php echo "'".$_GET['city']."'"?>);
			window.location.href="http://www.heigushop.com/Web/html/index.html";
        }else{
        	window.location.href="http://www.heigushop.com/Web/Wxlogin/jsapi.php";
			return false;
        }
  });
</script>
</html>