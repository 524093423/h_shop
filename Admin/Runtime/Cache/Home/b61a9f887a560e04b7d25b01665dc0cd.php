<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo C('APP_TITLE');?></title>
    <link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
 
</head>
<body class="mainbody">
    <div class="navigation">
        首页 &gt; 商品分类管理 &gt; 分类列表</div>
    <div class="tools_box">
        <div class="tools_bar">
            <div class="search_box">
                <span>按类名搜素：</span>
                <input id="txtKeywords" type="text" class="txtInput" value=""
                    x-webkit-speech="" lang="zh-CN" />
                <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
            </div>
            <a href="./admin.php?c=Classify&a=AdClassify" class="tools_btn"><span><b class="add">添加类别</b></span></a>
            <a href="javascript:void(0);" class="tools_btn" id="savasort"><span><b class="send">保存排序</b></span></a>
        </div>
        <div class="select_box">
            请选择：<?php echo ($cate); ?>
        </div>
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
        <tr>
            <th width="5%">
                <input id="Checkbox1" type="checkbox" class="cbk" style="margin: 0px;" />
            </th>
            <th width="8%">
                编号
            </th>
            <th width="57%" align="left">
                分类名称
            </th>
             <th width="10%" align="center">
                排序
            </th>
            <th width="10%" align="center">
                添加时间
            </th>
            <th width="10%" align="center">
                操作
            </th>
        </tr>
        <?php if(is_array($cateList)): foreach($cateList as $key=>$clt): ?><tr>
            <td align="center">
                <input name="ck" type="checkbox" class="cbk" value="<?php echo ($clt["id"]); ?>" />
            </td>
            <td align="center">
                <?php echo ($key+1); ?>
            </td>
            <td align="left">
                <!--  <img src='/h_shop/Public/admin/images/jia.gif'/>新闻中心(3)-->
                <?php echo ($clt["html"]); ?>
                <?php echo ($clt["str"]); ?>
                <?php if( $clt["html"] == ''): ?><img src='/h_shop/Public/admin/images/jia.gif'/>
                <?php else: ?>
                <img src='/h_shop/Public/admin/images/jian.gif'/><?php endif; ?>
                <?php echo ($clt["name"]); ?>
            </td>
            <td align="center">
               <input type="text"  value="<?php echo ($clt["gcsort"]); ?>" id="<?php echo ($clt["id"]); ?>" class="sort" style=" width:50px; height:13px"/>
            </td>
            <td align="center">
                <?php echo ($clt["ctime"]); ?>
            </td>
            <td style="text-align: center;">
                <a href="./admin.php?c=Classify&a=AdClassify&id=<?php echo ($clt["id"]); ?>"><img  src="/h_shop/Public/admin/images/edit.png"  title="编辑" alt="编辑"/></a> <a class="del" href="javascript:void(0)"
                    rel='<?php echo ($clt["id"]); ?>'><img  src="/h_shop/Public/admin/images/delete.gif"  title="删除" alt="删除"/></a>
            </td>
        </tr><?php endforeach; endif; ?> 
        
    </table>
    <div class="line15">
    </div>
    <div class="page_box">
        <div id="PageContent" class="flickr right">
        </div>
    </div>
    <div class="line10">
    </div>
</body>
</html>
	<script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
    <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        $(function () {
            tablecolor();
            $("#btnSearch").click(function () {
                Classsearch();
            });
            $("#SelectClass").change(function(){
            	Classsearch();
            });
            $("table tbody tr td a.del").click(function () {
            	del($(this), "goods_classify");
            });
            $("#savasort").click(function () {
                /*$.jBox.tip("正在保存排序...", 'loading');*/
                var strval = "", strid = "";
                $(".sort").each(function () {
                    strval += $(this).val() + "-";
                    strid += $(this).attr("id") + "-";
                });
                $.ajax({
                    url:"./admin.php?c=Classify&a=typeSort",
                    type:"post",
                    dataType:"json",
                    data:{strval: strval.substring(0, strval.length - 1), strid: strid.substring(0, strid.length - 1)},
                    success:function(data1){
                        $.jBox.tip(data1.message, 'success');
                        setTimeout("window.history.go(0)","500");
                    }
                })
            });
        });
    </script>