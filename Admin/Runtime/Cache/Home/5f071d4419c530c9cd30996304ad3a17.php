<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>盈亏管理</title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />
</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 盈亏管理 &gt; <?php echo ($title); ?></div>
    <div class="tools_box">
	    <div class="tools_bar"  style="height:0px;">
            <div class="search_box">
		    </div>
		    <!--  <a href="javascript:void(0);"  id="seleallbybtton" class="tools_btn"><span><b class="all">全选</b></span></a>
            <a href="javascript:void(0);" id="Button1" class="tools_btn"><span><b class="delete">批量删除</b></span></a>-->
        </div>
        <div class="select_box">
                 选择日期:
                 <input class="laydate-icon" value="<?php echo ($st); ?>"  id="st"  readonly="readonly" onclick="laydate({format: 'YYYY-MM-DD',festival: true, choose: function(datas){  }})" style="width:76px;">
                  <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
       <div style="float:right">
	   
	    </div> 
	    </div>
	    
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
                <th width="7.5%">编号</th>
                <th width="10%"  align="center">订单号</th>
                <th width="10%"  align="center">用户名</th>
                <th width="7.5%"  align="center">商品总价</th>
                 <th width="7.5%"  align="center">运费</th>
                 <th width="7.5%"  align="center">积分抵销金额</th>
                <th width="7.5%"  align="center">订单总计</th>
                <th width="7.5% align="center">实付款</th>
                <th width="7.5% align="center">返嘿币</th>
                <th width="10% align="center">日期</th>
      </tr>
      		<tbody class="profitDay"></tbody>
      </table>


   
    <div class="line15"></div>
    <div class="page_box">
      <div id="PageContent" class="flickr right">
       	<div class="profitDayPage"></div>
      </div>
     
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
                <th width="15%">总收入</th>
                <th width="15%"  align="center">商品总价</th>
                <th width="15%"  align="center">运费总价</th>
                <th width="20%"  align="center">亏损</th>
                 <th width="15%"  align="center">盈利</th>
                 <th width="5%"  align="center">实付款</th>
      </tr>
      		<tbody class="Statistics"></tbody>
      </table>
    <div class="line10"></div>
</body>
</html>
<script type="text/javascript" src="/h_shop/Public/js/jquery-1.8.3.min.js"></script>
<script src="/h_shop/Public/admin/js/laydate/laydate.js"></script>
<script type="text/javascript" src="/h_shop/Public/admin/js/showdate.js"></script>
<script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
   <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
    var isstate = <?php echo ($state); ?>;
        $(function () {
            $("#btnSearch").click(function () {
            	ProfitSearch();
            });
            var url = "./admin.php?c=ProfitLoss&a=getDayList&state="+isstate;
        	getDayList(url);
        	var url1 = "./admin.php?c=ProfitLoss&a=GetDayStatistics&state="+isstate;
        	getDayStatistics(url1); 
        });
      //获取日盈亏
        function getDayList(url){
    		$.getJSON(url,function(data){
    			var str = "<tr><td align='center' colspan='12'>获取中.......</td></tr>";
    			$(".profitDay").html(str);
    			 if(data.page.length > 0){
    				 $(".profitDay").html("");
    				 $(".profitDay").html(data.list);
    				 $(".profitDayPage").html(data.page);
    				 tablecolor();
    				 $(".profitDayPage a.page").click(function () {
    					 getDayList($(this).attr("rel"));
    		            });
    			}else{
    				var str = "<tr><td align='center' colspan='12'>无记录.......</td></tr>";
    				$(".profitDay").html(str);
    				$(".profitDayPage").html("");
    			} 
    		});
    	}
      //获取日盈利统计
      function getDayStatistics(url){
	    	  $.getJSON(url,function(data){
	  			var str = "<tr><td align='center' colspan='7'>获取中.......</td></tr>";
	  			$(".Statistics").html(str);
	  			 if(data.text.length > 0){
	  				 $(".Statistics").html("");
	  				 $(".Statistics").html(data.text);
	  			}else{
	  				var str = "<tr><td align='center' colspan='7'>无记录.......</td></tr>";
	  				$(".Statistics").html(str);
	  			} 
	  		});
      }
      //搜索
      function ProfitSearch(){
    	  var st = $("#st").val();
    	  var url = "./admin.php?c=ProfitLoss&a=getDayList&date="+st+"&state="+isstate;
    	  var url1 = "./admin.php?c=ProfitLoss&a=GetDayStatistics&date="+st+"&state="+isstate;
      		getDayStatistics(url1); 
      		getDayList(url);
      }
    </script>