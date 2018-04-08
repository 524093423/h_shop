<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<!-- saved from url=(0053)https://test.dscmall.cn/admin/privilege.php?act=login -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<title>登录页</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<!-- <link rel="shortcut icon" href="https://test.dscmall.cn/favicon.ico"> -->
<!-- <link rel="icon" href="https://test.dscmall.cn/animated_favicon.gif" type="image/gif"> -->
<link href="/h_shop/Public/newadmin/login.css" rel="stylesheet" type="text/css">
<script  src="/h_shop/Public/js/jquery-1.8.3.min.js"></script>
<script src="/h_shop/Public/admin/js/layer/layer.js"></script>
<!--[if lte IE 8]>
	<script type="Text/Javascript" language="JavaScript">
    	$(function(){
            pb({
                id:'notIe',
                content:'<div class="noContent"><div class="noText"><p class="p1">您当前浏览器版本过低</p><p class="p1">不支持浏览</p><p class="p2">建议使用</p><p class="p3">谷歌、火狐、360极速、IE9以上版本</p></div></div>',
                drag:false,
                head:false,
                cl_cBtn:false,
                width:316,
                height:376,
                ok_title:"确定",
                onOk:function(){
                    location.href = "../index.php";
                }
            });
            
            $("#pb-mask").css('cssText','position: fixed; width: 100%; height: 100%; top: 0px; left: 0px; opacity: 1; overflow: hidden; z-index: 100002; background-color:#fff;')
        });
    </script>
<![endif]-->
</head>

<body>
	<div class="login-layout">
    	<div class="logo"><img src="/h_shop/Public/newadmin/loginImg.png"></div>
            <div class="login-form" style="position: relative">
                <div class="formContent">
                	<div class="title">管理中心</div>
                    <div class="formInfo">
                    	<div class="formText">
                        	<i class="icon icon-user"></i>
                            <input type="text" name="username" onblur="checkUsename(0,'username')" id="username" autocomplete="off" class="input-text" value="" placeholder="用户名">
                        </div>
                        <div class="formText">
                        	<i class="icon icon-pwd"></i>
                                <input type="password" style="display:none"> 
                            <input type="password" name="password" id="userpwd" onblur="checkUsePwd(1,'userpwd')" autocomplete="off" class="input-text" value="" placeholder="密  码">
                        </div>
                        <div class="formText zl-code clearfix">
                        	<i class="icon icon-pwd icon-code"></i>
                            <input type="text" name="authcode" onblur="checkCode(2,'code')" id="code" autocomplete="off" class="input-text" value="" placeholder="验证码">
                            <div class="zl-img">
                            	<img src="<?php echo U('Index/verify');?>" id ="verify"/>
                            </div>
                        </div>
                        <div class="formText submitDiv">
                                                        <span class="submit_span">
                            	<input style="border-radius: 50px;margin:0 auto;display: block;" type="button" id="formsubmit" name="submit" class="sub" value="登录">
                            </span>
                                                    </div>
                    </div>
                </div>
                <div id="error" style="position: absolute;left:0px;bottom: 30px;text-align: center;width:395px;">

                </div>
            </div>
    </div>
    <div id="bannerBox">
        <ul id="slideBanner" class="slideBanner" style="position: relative; width: 1920px; height: 935px;">
            <li style="position: absolute; width: 1920px; left: 0px; top: 0px; display: none;"><img src="/h_shop/Public/newadmin/banner_1.jpg"></li>
            <li style="position: absolute; width: 1920px; left: 0px; top: 0px;"><img src="/h_shop/Public/newadmin/banner_2.jpg"></li>
            <li style="position: absolute; width: 1920px; left: 0px; top: 0px; display: none;"><img src="/h_shop/Public/newadmin/banner_3.jpg"></li>
            <li style="position: absolute; width: 1920px; left: 0px; top: 0px; display: none;"><img src="/h_shop/Public/newadmin/banner_4.jpg"></li>
            <li style="position: absolute; width: 1920px; left: 0px; top: 0px; display: none;"><img src="/h_shop/Public/newadmin/banner_5.jpg"></li>
        </ul>
    </div>
</body></html>
<script src="/h_shop/Public/admin/js/adminlogin.js?a=123"></script>