<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo C('APP_TITLE');?></title>
        <script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/ui/skins/Aqua/css/ligerui-all.css" rel="stylesheet" type="text/css" />
    <script src="/h_shop/Public/admin/js/ui/js/ligerBuild.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        var tab = null;
        var accordion = null;
        var tree = null;
        $(function () {
            //页面布局
            $("#global_layout").ligerLayout({ leftWidth: 180, height: '100%', topHeight: 65, bottomHeight: 24, allowTopResize: false, allowBottomResize: false, allowLeftCollapse: true, onHeightChanged: f_heightChanged });
            var height = $(".l-layout-center").height();
            //Tab
            $("#framecenter").ligerTab({ height: height });
            //左边导航面板
            $("#global_left_nav").ligerAccordion({ height: height - 25, speed: null });

            $(".l-link").hover(function () {
                $(this).addClass("l-link-over");
            }, function () {
                $(this).removeClass("l-link-over");
            });

            //设置频道菜单
            $("#global_channel_tree").ligerTree({
                url: './admin.php?c=Common&a=getMeun',
                checkbox: false,
                nodeWidth: 112,
                //attribute: ['nodename', 'url'],
                onSelect: function (node) {
                    if (!node.data.url) return;
                    var tabid = $(node.target).attr("tabid");
                    if (!tabid) {
                        tabid = new Date().getTime();
                        $(node.target).attr("tabid", tabid)
                    }
                    f_addTab(tabid, node.data.text, node.data.url);
                }
            });




            //快捷菜单已删
            //快捷菜单回调函数


            tab = $("#framecenter").ligerGetTabManager();
            accordion = $("#global_left_nav").ligerGetAccordionManager();
            tree = $("#global_channel_tree").ligerGetTreeManager();
            tree.expandAll(); //默认展开所有节点
            $("#pageloading_bg,#pageloading").hide();

            $("#exit").click(function () {
                if (confirm("确定停止一切操作并退出么？")) {
                	var login	= <?php echo ($_SESSION['logintype']); ?>;
                	if(login==1){
                		location.href="./admin.php?c=Index&a=index";
                	}else{
                		location.href="./seller.php";
                	}
                }
            });
        });
        function f_heightChanged(options) {
            if (tab)
                tab.addHeight(options.diff);
            if (accordion && options.middleHeight - 24 > 0)
                accordion.setHeight(options.middleHeight - 24);
        }
        //添加Tab，可传3个参数
        function f_addTab(tabid, text, url, iconcss) {
            if (arguments.length == 4) {
                tab.addTabItem({ tabid: tabid, text: text, url: url, iconcss: iconcss });
            } else {
                tab.addTabItem({ tabid: tabid, text: text, url: url });
            }
        }
        //提示Dialog并关闭Tab
  
    </script>
</head>
<body style="padding: 0px;">
    <div class="pageloading_bg" id="pageloading_bg">
    </div>
    <div id="pageloading">
        数据加载中，请稍等...</div>
    <div id="global_layout" class="layout" style="width: 100%">
        <!--头部-->
        <div position="top" class="header">
            <div class="header_box">
                <div class="header_right">
                    <span><?php echo ($_SESSION['nmt_groupname']); ?><b>【<?php echo ($_SESSION['nmt_admin']); ?>】</b>您好，欢迎光临</span>
                    <br />
                    <a href="javascript:f_addTab('home','管理中心','./admin.php?c=Admin&a=Center')">管理中心</a><!--   | <a target="_blank"
                        href="../index.aspx">预览网站</a>--> |
                    <a  href="javascript:void(0)" id="exit">安全退出</a>
                </div>
                <div class="logo"></div>
            </div>
        </div>
        <!--左边-->
        <div position="left" title="管理菜单" id="global_left_nav">
            <div title="模块管理" iconcss="menu-icon-model" class="l-scroll">
                <ul id="global_channel_tree" style="margin-top: 3px;">
                </ul>
            </div>
            <div title="控制面板" iconcss="menu-icon-setting">
                <ul class="nlist">
                    <!--  <li><a class="l-link" href="javascript:f_addTab('sys_config','系统参数设置','setting/webconfig.aspx')">
                        系统参数设置</a></li>
                    <li><a class="l-link" href="javascript:f_addTab('manager_log','系统日志','loginlog/loginlog.aspx')">
                        系统日志管理</a></li>
                    <li><a class="l-link" href="javascript:f_addTab('sys_manager','管理员管理','loginlog/loginlog.aspx')">
                        管理员管理</a></li>-->
                    <?php if( $_SESSION['logintype'] == 1 ): ?><li><a class="l-link" href="javascript:f_addTab('sys_repwd','修改密码','./admin.php?c=Config&a=EidtUserPwd')">
                                    修改密码</a></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div position="center" id="framecenter" toolsid="tab-tools-nav">
            <div tabid="home" title="管理中心" iconcss="tab-icon-home" style="height: 300px">
                <iframe frameborder="0" name="sysMain" src="./admin.php?c=Admin&a=Center"></iframe>
            </div>
        </div>
        <div position="bottom" class="footer">
            <div class="copyright">
                Copyright &copy; 2012-2013. 动力无限. All Rights Reserved.</div>
        </div>
    </div>
</body>
</html>