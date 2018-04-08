<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>系统参数设置</title>
    <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mainbody">
    <div class="navigation">
        首页 &gt; 控制面板 &gt; 密码修改</div>
    <div id="contentTab">
        <ul class="tab_nav">
            <li class="selected"><a onclick="tabs('#contentTab',0);" href="javascript:;">密码修改</a></li>
        </ul>
        <div class="tab_con" style="display: block;">
            <table class="form_table">
                <col width="180px">
                <col>
                <tbody>
                    <tr>
                        <th>
                            当前用户：
                        </th>
                        <td>
                            <input type="text" id="UserName" value="<?php echo ($_SESSION['nmt_name']); ?>" disabled="disabled"
                                class="txtInput normal required" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            原始密码：
                        </th>
                        <td>
                            <input type="password" id="OldPassWord" class="txtInput normal required" />
                    </tr>
                    <tr>
                        <th>
                            新的密码：
                        </th>
                        <td>
                            <input type="password" id="PassWord" class="txtInput normal required" />
                    </tr>
                    <tr>
                        <th>
                            确认密码：
                        </th>
                        <td>
                            <input type="password" id="PassWord1" class="txtInput normal required" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="foot_btn_box">
            <input id="sub" type="button" value="提交保存" class="btnSubmit" onclick="EditPassWord()" />
        </div>
    </div>
</body>
</html>
<script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript" ></script>
<script src="/h_shop/Public/admin/js/function.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
<link rel="stylesheet" href="/h_shop/Public/admin/js/tips/tip-yellowsimple/tip-yellowsimple.css"
    type="text/css" />
<script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script src="/h_shop/Public/admin/js/PassWord.js" type="text/javascript"></script>