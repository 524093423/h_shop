<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo C('APP_TITLE');?></title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />

</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 商户管理 &gt; 提现账号列表</div>
    <input type="hidden" id="type" value="<?php echo ($type); ?>" />
    <div class="tools_box">
	    <div class="tools_bar">
            <div class="search_box"><span>按用户名&nbsp;<b>/</b>&nbsp;法人姓名&nbsp;<b>/</b>&nbsp;公司名称：</span>
			    <input id="txtKeywords" type="text"  class="txtInput" value="<?php echo ($ext); ?>" x-webkit-speech="" lang="zh-CN"/>
                <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
		    </div>
		    <a href="javascript:void(0);"  id="seleallbybtton" class="tools_btn"><span><b class="all">全选</b></span></a>
            <a href="javascript:void(0);" id="Button1" class="tools_btn"><span><b class="delete">批量删除</b></span></a>
        </div>    
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
            <th width="3%"><input id="Checkbox1" type="checkbox" class="cbk"  style=" margin:0px;"/></th>
            <th width="15%"  align="center">用户名称/手机</th>
            <th width="15%"  align="center">法人姓名/手机</th>
            <th width="15%"  align="center">公司名称</th>
            <th width="10%"  align="center">账号</th>
            <th width="10%"  align="center">类型</th>
            <th width="10%"  align="center">创建时间</th>
            <th width="10%"  align="center">修改时间</th>
            <th width="15%"  align="center">操作</th>
      </tr>
  		    <?php if(is_array($info)): $k = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?><tr>   
                <td  align="center">  <input name="ck"  type="checkbox" class="cbk" id="cbkone<?php echo ($v["bank_id"]); ?>" value="<?php echo ($v["bank_id"]); ?>"/></td>
                <td align="center"><?php echo ($v["user_name"]); ?>/<?php echo ($v["user_phone"]); ?></td>
                <td align="center"><?php echo ($v["seller_name"]); ?>/<?php echo ($v["seller_phone"]); ?></td>
                <td align="center"><?php echo ($v["company_name"]); ?></td>
                <td align="center"><?php if($v['type'] == 1){ echo $v['number'];}elseif($v['type'] == 2){ ?><img src=".<?php echo ($v["number"]); ?>" style="width:200px;height:200px;" alt="" /><?php } ?></td>
                <td align="center"><?php if($v['type'] == 1){ echo "<span style='color:#22acf8;'>支付宝</span>";}elseif($v['type'] == 2){ echo "<span style='color:#1ac91a;'>微信</span>";} ?></td>
                <td align="center"><?php echo ($v["create_time"]); ?></td>
                <td align="center"><?php echo ($v["update_time"]); ?></td>
                <td style=" text-align:center;">
                	<!-- <a href="./admin.php?c=Order&a=Design&id=<?php echo ($v["id"]); ?>" ><img  src="/h_shop/Public/admin/images/look.png"  title="去设计" alt="去设计"/></a> -->&nbsp;&nbsp;&nbsp;
                   <a class="del" href="javascript:void(0)" onclick="DelOne(<?php echo ($v["bank_id"]); ?>)" ><img  src="/h_shop/Public/admin/images/delete.gif"  title="删除" alt="删除"/></a>
                </td>
                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
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
<script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
   <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
    var province = $("#pe").val();
    var city = $("#cy").val();
        $(function () {
            tablecolor();
            $("#btnSearch").click(function () {
            	// userSearch();
                var search = $("#txtKeywords").val();
                var type = $("#type").val();
                // alert(type);
                if (search == "") {
                    $.jBox.error('请填写搜索内容', '错误')
                }else{
                location.href ="./admin.php?c=Bank&a=BankList&ext="+search; 
                }
            });
            $("#SelectUser").change(function () {
            	userSearch();
            });
            $("#Button1").click(function () {
                // alldel("delallNews");
                // 批量删除
                var type = $("#type").val();
                var str = "";
                $("[name=ck]:checked").each(function () {
                    str += $(this).val() + ",";
                });
                if (str == "") {
                    $.jBox.error('请先选中', '错误')
                }
                else {
                    var submit = function (v, h, f) {
                        if (v == true) {
                            // alert(typeof str);
                            $.ajax({
                                type: "POST",
                                dataType: "text",
                                url: "admin.php?c=Bank&a=BankDelAll",
                                // data:$("#submitForm").serialize(),
                                data:{idstr:str},
                                success: function (data) {
                                    if(data > 0){
                                        $.jBox.tip('批量删除成功。', 'success');
                                        window.setTimeout(function () {
                                        location.href = "./admin.php?c=Bank&a=BankList";}, 1500); //跳转页面
                                    }else{
                                        $.jBox.tip('服务器连接超时，删除失败', 'error');
                                    }
                                }
                            });
                        }
                        else
                            return true;
                    };
                    $.jBox.confirm("此操作不可恢复，是否继续？？", "温馨提示", submit, { buttons: { '是': true, '否': false} });
                }
            });
            $("table tbody tr td a.del").click(function () {
                // del($(this), "delUser");
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
            $("#Checkbox1").click(function () {
                allselecheck($(this));
            });
        });
        // 单个删除
        function DelOne(bank_id){
            $("#cbkone"+bank_id).attr("checked", true);
            var submit = function (v, h, f) {
                if (v == 'ok'){
                $.ajax({
                  type: "POST",
                  dataType: "text",
                  url: "admin.php?c=Bank&a=BankDel",
                  data:{bank_id:bank_id},
                  success: function (data) {
                      if(data > 0){
                        $.jBox.tip('删除成功。', 'success');
                        window.setTimeout(function () {
                            location.href = "./admin.php?c=Bank&a=BankList";}, 1500); //跳转页面
                      }else{
                        $.jBox.tip('服务器连接超时，删除失败', 'error');
                      }
                    }
                });
                }else if (v == 'cancel'){
                    $("#cbkone"+bank_id).attr("checked", false);
                    jBox.tip('取消删除', 'info');  
                }  
                return true; //close  
            };    
            $.jBox.confirm("确认删除吗？删除不可恢复", "温馨提示", submit);             
        }
   
    </script>