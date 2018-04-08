<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo C('APP_TITLE');?></title>
    <link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
    <link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />
  
</head>
<body class="mainbody">
    <div class="navigation">
        首页 &gt; 图片管理 &gt; 图片列表</div>
    <div class="tools_box">
        <div class="tools_bar">
            <div class="search_box">
                <span>按名称链接搜素：</span>
                <input id="txtKeywords" type="text" class="txtInput" value=""
                    x-webkit-speech="" lang="zh-CN" />
                <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
            </div>
            <a href="./admin.php?c=Banner&a=AddBanner" class="tools_btn"><span><b class="add">添加图片</b></span></a>
        </div>
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
        <tr>
            <th>
                编号
            </th>
            <th>
                位置
            </th>
            <th>
                图片
            </th>
        </tr>
        <?php if(is_array($list)): foreach($list as $key=>$lt): ?><tr>
            <td align="center">
                <?php echo ($key + $no); ?>
            </td>
            <td align="center">
                <?php echo ($lt["cityname"]); ?>
            </td>
            <td align="center">
                <a class="style" href="javascript:void(0)" onclick="showImg(<?php echo ($lt["cityid"]); ?>,'<?php echo ($lt["cityname"]); ?>')">点击查看</a>
            </td>
        </tr><?php endforeach; endif; ?>
    </table>
    <div class="line15">
    </div>
    <div class="page_box">
        <div id="PageContent" class="flickr right">
              <?php echo ($page); ?>
        </div>
    </div>
    <div class="line10">
    </div>
</body>
</html>
<script src="/h_shop/Public/admin/js/jquery-3.0.0.js"></script>
<script src="/h_shop/Public/admin/js/jquery-3.0.0.min.js"></script>
<script src="/h_shop/Public/admin/js/layer/layer.js?c=123"></script>
<link href="/h_shop/Public/admin/js/fany/fany.css" rel="stylesheet" type="text/css" />
<script src="/h_shop/Public/admin/js/fany/fany.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/function.js" type="text/javascript"></script>
 <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
        $(function () {
            tablecolor();
            $("#btnSearch").click(function () {
                search("imglist");
            });
            $("#Button1").click(function () {
                alldel("delallfouces");
            });
            $("table tbody tr td a.del").click(function () {
                del($(this), "carousel_photo");
            });
            $(".style").fancybox({
                "titleShow": false
            });
        });
        function showImg(cityid,str){
        	$.post("./admin.php?c=Common&a=GetBannerJsonImg",{"cityid":cityid,"title":str},function(data){
        		json = eval("("+data+")");
        		layer.photos({
        			tab: function(obj){},
        		    photos: json //格式见API文档手册页
        		    ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
        		  });
        	});
        }
        function UpImg(id){
        	layer.confirm('选择要进行的操作', {
        		  btn: ['编辑','删除']},
				  function(){
        			  var index = layer.open({
            			  type: 2,
            			  content: './admin.php?c=Banner&a=AddBanner&id='+id,
            			  area: ["100%","100%"],
            			  maxmin: true,
            			  cancel: function(layero, index){
            				  layer.closeAll();
            			  }
            			});
				  },function(){
					  var index = layer.load(1, {
						  shade: [0.1,'#fff'],
						  time:1000
						});
					  $.post("./admin.php?c=Del&a=delData", { idstr: id,table:'carousel_photo' },function(data){
						  if(data==1){
							  layer.msg('删除成功',{icon:1,time:1000});
							  layer.closeAll();
						  }else{
							  layer.msg(data,{icon:2,time:1000});
						  }
					  })
				  }
        		);
        }
    </script>