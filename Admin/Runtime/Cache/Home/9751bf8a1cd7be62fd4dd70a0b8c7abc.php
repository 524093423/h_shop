<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>编辑资讯信息</title>
 <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mainbody">

<div class="navigation"><!--<a href="javascript:history.go(-1);" class="back">后退</a>-->首页 &gt; 系统管理 &gt; 关于我们

</div>
<div id="contentTab">
    <ul class="tab_nav">
        <li class="selected"><a onclick="tabs('#contentTab',0);" href="javascript:;">基本信息</a></li>
    </ul>

    <div class="tab_con" style="display:block;">
        <table class="form_table">
            <col width="150px"><col>
            <tbody>
             <tr> 
                <th>标题：</th>
                <td><input type="text" id="title" value="<?php echo ($info["title"]); ?>"   class="txtInput normal required" name="question"/>
                <input type="hidden" id="sys_id" value="<?php echo ($info["sys_id"]); ?>"   class="txtInput normal required" name="sys_id"/>
                </td>
            </tr>
            <tr> 
                <th>文案内容：</th>
                <td>
                <textarea name="content" id="content" cols="80" rows="8"><?php echo ($info["content"]); ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="foot_btn_box">
     <input id="sub" type="button" value="提交保存" class="btnSubmit"/>
    </div>
</div>
</body>
</html>
 <script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript" ></script>
 <script src="/h_shop/Public/admin/js/function.js" type="text/javascript" ></script>
 <script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
 <link href="/h_shop/Public/admin/js/tips/tip-yellow/tip-yellow.css" rel="stylesheet" type="text/css" />
     <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
   <script>
       $(function () {
           $("#sub").click(function () {
               if (!veriform('title')) { tip('title', '请填写问题'); $("#title").focus(); return false; }
               if (!veriform('content')) { tip('content', '请填写答案'); $("#content").focus(); return false; }
               var sys_id = $("#sys_id").val();
               var title = $("#title").val();
               var content = $("#content").val();
                 $.ajax({
                      type: "POST",
                      url: "admin.php?c=System&a=About_us",
                      data:{sys_id:sys_id,title:title,content:content},
                      success: function (data) {
                          if(data > 0){
                            $.jBox.tip('更新成功。', 'success');
                             window.setTimeout(function () {
                            location.href = "./admin.php?c=System&a=About_us&sys_id=1";}, 2000);
                          }else{
                            $.jBox.tip('服务器连接超时，更新失败', 'success');
                          }
                      }
                  });
           });
       });
  </script>