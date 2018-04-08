<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo C('APP_TITLE');?></title>
    <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
    <script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
    <script src="/h_shop/Public/admin/js/layer/layer.js" type="text/javascript"></script>
    <script type="text/javascript">
    var state =0;
        $(function () {
            $.getJSON("./admin.php?c=Common&a=CheckOrder", "", function (msg) {
                if (msg > 0) { 
                	layer.open({
              		  type: 1
              		  ,title:"代发货订单"
              		  ,offset: "rt" //具体配置参考：offset参数项
              		  ,content: "<div style=\"padding: 20px 80px;\">您有" + msg + "条待处理订单 <a href=javascript:parent.f_addTab('manager_list1','代发货订单','./admin.php?c=Order&a=OrderList&isfh=1')>去看看？</a></div>"  
              		  ,btnAlign: 'c' //按钮居中
              		  ,shade: 0 //不显示遮罩
              		});
                  }
            });
            $.getJSON("./admin.php?c=Common&a=CheckReturnOrder", "", function (msg) {
                if (msg > 0) {
                    layer.open({
                        type: 1
                        ,title:"代退货订单"
                        ,offset: "lt" //具体配置参考：offset参数项
                        ,content: "<div style=\"padding: 20px 80px;\">您有" + msg + "条退货订单 <a href=javascript:parent.f_addTab('manager_list1','待退货订单','./admin.php?c=Order&a=RefundOrder')>去看看？</a></div>"
                        ,btnAlign: 'c' //按钮居中
                        ,shade: 0 //不显示遮罩
                    });
                }
            });
            $.getJSON("./admin.php?c=Common&a=CheckWithDraw", "", function (msg) {
                if (msg > 0) { 
                	layer.open({
                		  type: 1
                		  ,title:"待处理申请"
                		  ,offset: 'rb' //具体配置参考：offset参数项
                		  ,content: "<div style=\"padding: 20px 80px;\">您有" + msg + "条待处理申请 <a href=javascript:parent.f_addTab('manager_list2','待处理申请','./admin.php?c=Withdraw&a=DrawList&isauth=1')>去看看？</a> </div>"
                		  ,btnAlign: 'c' //按钮居中
                		  ,shade: 0 //不显示遮罩
                		});
                     }
            });
            $("#sysset").click(function () {
                var html = "<div style='padding:10px;'>输入密码：<input type='text' id='pass' name='pass' /></div>";
                var submit = function (v, h, f) {
                    if (f.pass == '') {
                        $.jBox.tip("请输入密码。", 'error', { focusId: "pass" });
                        return false;
                    }
                    else {
                        if (f.pass == "123") {
                            parent.f_addTab('sys_config', '系统参数设置', 'setting/webconfig.aspx');
                            return true;
                        }
                        else {
                            $.jBox.tip("密码错误。", 'error', { focusId: "pass" });
                            return false;
                        }
                    }
                };
                $.jBox(html, { title: "请输入密码？", submit: submit });
            });
            //$("#ips").text(returnCitySN.ip);
            $("#exit").click(function () {
                if (confirm('确定停止一切操作并退出么？')) {
                    window.parent.location.href = './admin.php?c=Index&a=out';
                }
            });
        });
    </script>
</head>
<body class="mainbody">
    <div class="navigation nav_icon">
        你好，<i>(<?php echo ($_SESSION['nmt_admin']); ?>)<?php echo ($_SESSION['nmt_groupname']); ?></i>，欢迎进入后台管理中心</div>
    <div class="line10">
    </div>
   <!--   <div class="nlist1">
        <ul>
            <li>本次登录IP：<span id="ips"></span></li>
                      <li>上次登录IP：<span id="Span1">219.156.118.33</span></li>
                      <li>上次登录时间：2016-12-07 16:41:49</li>
            <li>与服务器连接状态(ping)：<span id="ping"></span></li>
        </ul>
    </div>
    <div class="line10">
    </div>
    -->
   <!--  <div class="nlist2 clearfix">
         <h2>
            站点信息</h2>
        <ul>
            <li>站点名称：郑州网络推广_郑州营销型网站建设_郑州网络公司_郑州app定制开发_郑州网站推广_郑州微信搜索推广_sem整合营销_网络营销推广外包-郑州动力无限科技有限公司</li>
            <li>公司名称：郑州动力无限科技有限公司</li>
            <li>服务器名称：CLOUDVHOST157</li>
            <li>服务器系统：Microsoft Windows NT 6.0.6002 Service Pack 2</li>
            <li>网站安装目录：D:\wwwroot\CS255940\HC265176\WEB\</li>
            <li>服务器IP：122.114.106.157</li>
            <li>服务器DNS：39.164.31.89</li>
            <li>服务器端口：80</li>
            <li>IIS环境：Microsoft-IIS/7.0</li>
            <li>.NET版本：2.0.50727.4253</li>
          
        </ul>
        <div class="line10">
        </div>
    </div>
    -->
    <div class="clear" style="height: 20px;">
    </div>
    <?php if( $_SESSION['logintype'] == 1 ): ?><div class="sub_nav_list">
        <h3>
            系统快捷导航</h3>
        <ul>
           <!--   <li><a href="javascript:void(0)" id="sysset">
                <img src="/h_shop/Public/admin/images/icon_setting.png" /><br />
                系统</a></li>
                   <li><a href="javascript:parent.f_addTab('phoneweb','手机网站状态','phoneweb/phoneweb.aspx')">
                <img src="/h_shop/Public/admin/images/0.jpg" width="32px" height="32px"/><br />
                手机网站</a></li>
            <li><a href="javascript:parent.f_addTab('manager_log','系统登陆日志','loginlog/loginlog.aspx')">
                <img src="/h_shop/Public/admin/images/icon_log.png" /><br />
                系统日志</a></li>-->
                <li><a href="javascript:parent.f_addTab('plugin_repwd','修改密码','./admin.php?c=Config&a=EidtUserPwd')">
                <img src="/h_shop/Public/admin/images/repwd.png"  width="32px" height="32px"/><br />
                修改密码</a></li>
            <li><a href="javascript:void(0)" id="exit">
                <img src="/h_shop/Public/admin/images/logout.png" width="32px" height="32px"/><br />
                退出系统</a></li>
               <!--   <li><a href="../index.aspx" target="_blank">
                <img src="/h_shop/Public/admin/images/icon_plugin.png"  width="32px" height="32px"/><br />
                预览网站</a></li> -->
        
        </ul>
    </div><?php endif; ?>
    <!--  <div class="note_list">
       
        <h3 class="msg">
            动力无限官方消息</h3>
        <div class="ul" id="info">
          <span style="color:red">网站新功能，一键智能生成手机网站。</span><br/>
       在您生成手机网站并上传二维码后(在百度搜索二维码生成，将<span style="color:red">http://www.sem198.com/phone/</span>生成即可得到二维码)，手机网站二维码会自动悬挂网站首页，您将有7天的免费试用时间。在您开启手机网站起，7天后网站自动关闭。如需继续使用，请联系我们的客服。<br/>
       详细参数请点击左侧手机网站。&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;客服电话：0371-60266306
       </div>
    </div>
    -->
</body>
</html>