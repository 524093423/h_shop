<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>商品管理</title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />
<script type="text/javascript" src="/h_shop/Public/admin/js/showdate.js"></script>
</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 商品管理 &gt; 商品列表</div>
    <div class="tools_box">
	    <div class="tools_bar">
            <div class="search_box"><span>按商品名称或者商家搜索：</span>
			    <input id="txtKeywords" type="text"   class="txtInput" value="<?php echo ($search); ?>" x-webkit-speech="" lang="zh-CN"/>
                <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
		    </div>
		    <a href="javascript:void(0);"  id="seleallbybtton" class="tools_btn"><span><b class="all">全选</b></span></a>
            <a href="javascript:void(0);" id="Button1" class="tools_btn"><span><b class="delete">批量删除</b></span></a>
        </div>
        <div class="select_box">
                 商品分类：<?php echo ($cate); ?>
                 选择日期:<input type="text" id="st" name="st" readonly="readonly" class="txtInput" onclick="return Calendar('st');" value="<?php echo ($st); ?>" class="text" style="width:76px;"/>-<input type="text" class="txtInput" readonly="readonly" id="et" onclick="return Calendar('et');" value="<?php echo ($et); ?>" name="et" class="text" style="width:76px;"/>
                 销量：<select id="SelectSales" class="select">
                    <option value="0">请选择</option>
                    <option value="1" <?php if( $sales == 1 ): ?>selected<?php else: endif; ?>>由低到高</option>
                    <option value="2" <?php if( $sales == 2 ): ?>selected<?php else: endif; ?>>由高到低</option>
                 </select>
                  <input id="btnSearch1" type="button" value="搜 索" class="btnSearch" />
                  <input id="resetBtn" type="button" value="重置" class="btnSearch" />
       <div style="float:right">
	   
	    </div> 
	    </div>
	    
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
       <th width="1%"><input id="Checkbox1" type="checkbox" class="cbk" style=" margin:0px;"/></th>
                <th width="1%">编号</th>
                <th width="5%"  align="center">商品名称</th>
                <th width="5%"  align="center">商家名称</th>
                <th width="5%"  align="center">商品图片</th>
                 <th width="4%"  align="center">商品分类</th>
                 <th width="6%"  align="center">单价</th>
                <th width="3%"  align="center">商品销量</th>
                  <th width="3%"  align="center">上架日期</th>
                <th width="3%" align="center">操作</th>
      </tr>
      		<?php if(is_array($goods)): foreach($goods as $key=>$lt): ?><tr>   
                <td  align="center">  <input name="ck"  type="checkbox" class="cbk"  value="<?php echo ($lt["gid"]); ?>"/></td>
                <td align="center"><?php echo ($no+$key); ?></td>
                <td align="center"><a href="./admin.php?c=Goods&a=GoodsDetail&id=<?php echo ($lt["gid"]); ?>"><?php echo ($lt["gn"]); ?></a></td>
                <td align="center"><?php echo ($lt["bn"]); ?></td>
                 <td align="center"><img src=".<?php echo ($lt["gp"]); ?>" width="100px" height="50px"></td>
                 <td align="center"><?php echo ($lt["gc"]); ?></td>
                <td align="center">
                 	￥<?php echo ($lt["ge"]); ?>元
                </td>
                <td align="center">[共<?php echo ($lt["gss"]); ?>笔交易]</td>
                <td align="center">
                	<?php echo ($lt["grk"]); ?>
                </td>
                <td style=" text-align:center;">
                <a class="" href="./admin.php?c=Goods&a=GoodsDetail&id=<?php echo ($lt["gid"]); ?>"    ><img  src="/h_shop/Public/admin/images/look.png"  title="查看" alt="查看"/></a>
                   <a class="del" href="javascript:void(0)"   rel='<?php echo ($lt["gid"]); ?>' ><img  src="/h_shop/Public/admin/images/delete.gif"  title="删除" alt="删除"/></a>
                </td>
            </tr><?php endforeach; endif; ?>
      </table>


   
    <div class="line15"></div>
    <div class="page_box">
      <div id="PageContent" class="flickr right">
        <?php echo ($page); ?>
      </div>
     
    </div>
    <div class="line10"></div>
</body>
</html>
<script type="text/javascript" src="/h_shop/Public/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="/h_shop/Public/admin/js/function.js?r=123"></script>
   <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
    var province = $("#pe").val();
    var city = $("#cy").val();
        $(function () {
            tablecolor();
            $("#btnSearch").click(function () {
            	integrateSearch();
            });
            $("#btnSearch1").click(function () {
            	integrateSearch();
            });
            $("#resetBtn").click(function(){
            	ResetBtnGoods();
            });
            $("#Select1").change(function () {
            	integrateSearch();
            });
            $("#Select2").change(function () {
            	integrateSearch();
            });
            $("#Button1").click(function () {
                alldel("delallNews");
            });
            $("table tbody tr td a.del").click(function () {
                del($(this), "goods");
            });
            $("#seleallbybtton").toggle(function () {
                $(this).find("span b").text("取消");
                $(".cbk").each(function () {
                    $(this).attr("checked", true);
                });
            }, function () {
                $(this).find("span b").text("全选");
                $(".cbk").each(function () {
                    $(this).attr("checked", false);
                });
            });
            $("#SelectProvince").change(function(){
                var id = $("#SelectProvince").val();
                var strp = " <option value=\"0\">请选择</option>";
                $.getJSON("./admin.php?c=Common&a=getCity&city="+id,function(msg){
                	if(msg.length >0){
                		for(var i=0;i<msg.length;i++){
                			strp += " <option value="+msg[i]['id']+">"+msg[i]['name']+"</option>";
                		}
                	}
                	$("#SelectCity").html(strp);
                });
            });
            $("#SelectCity").change(function(){
            	integrateSearch();
            })
            $("#Checkbox1").click(function () {
                allselecheck($(this));
            });
        });
   
    </script>