<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>后台管理</title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />

</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 系统管理 &gt; 支付方式</div>
    <div class="tools_box">
	    
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
      <!--  <th width="5%"><input id="Checkbox1" type="checkbox" class="cbk"  style=" margin:0px;"/></th>-->
                <th width="10%">编号</th>
                <th width="22.5%"  align="center">支付方式</th>
                <th width="22.5%"  align="center">收款帐号</th>
                 <th width="22.5%"  align="center">平台收入</th>
                <th width="22.5%"  align="center">操作</th>
      </tr>
      		<?php if(is_array($list)): foreach($list as $key=>$lt): ?><tr>   
                <!-- <td  align="center">  <input name="ck"  type="checkbox" class="cbk"  value="339"/></td> -->
                <td align="center"><?php echo ($key+1); ?></td>
                <td align="center"><img src=".<?php echo ($lt["img"]); ?>" style="width:20px;height:20px;" />&nbsp;&nbsp;<?php echo ($lt["pname"]); ?></td>
                <td align="center"><?php echo ($lt["seller"]); ?></td>
                <td align="center"><?php echo ($lt["total"]); ?>元</td>
                <td style=" text-align:center;">
                	不可操作
                </td>
            </tr><?php endforeach; endif; ?>
      </table>


   
    <div class="line15"></div>
    <div class="page_box">
      <div id="PageContent" class="flickr right">
      </div>
     
    </div>
    <div class="line10"></div>
</body>
</html>
<script type="text/javascript" src="/h_shop/Public/js/jquery-1.8.3.min.js" ></script>
<script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
<script type="text/javascript">
        $(function () {
            tablecolor();
        });
    </script>