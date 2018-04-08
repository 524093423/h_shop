<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo C('APP_TITLE');?></title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />

</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 消息管理 &gt; 系统消息列表</div>
    <input type="hidden" id="type" value="<?php echo ($type); ?>" />
    <div class="tools_box">
	    <div class="tools_bar">
            <a href="./admin.php?c=Sysmess&a=Add&act=2" class="tools_btn"><span><b class="add">添加推送类系统消息</b></span></a>
		    <a href="javascript:void(0);"  id="seleallbybtton" class="tools_btn"><span><b class="all">全选</b></span></a>
            <a href="javascript:void(0);" id="Button1" class="tools_btn"><span><b class="delete">批量删除</b></span></a>
        </div>    
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
            <th width="3%"><input id="Checkbox1" type="checkbox" class="cbk"  style=" margin:0px;"/></th>
            <th width="15%"  align="center">消息标题</th>
            <th width="15%"  align="center">消息内容</th>
            <th width="10%"  align="center">时间</th>
            <th width="25%"  align="center">操作</th>
      </tr>
  		    <?php if(is_array($info)): $k = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?><tr>   
                <td  align="center">  <input name="ck"  type="checkbox" class="cbk" id="cbkone<?php echo ($v["m_id"]); ?>" value="<?php echo ($v["m_id"]); ?>"/></td>
                <td align="center"><?php echo ($v["title"]); ?></td>
                <td align="center"><?php echo ($v["message"]); ?></td>
                <td align="center"><?php echo ($v["create_time"]); ?></td>
                <td style=" text-align:center;">
                   <a class="del" href="javascript:void(0)" onclick="Del(<?php echo ($v["m_id"]); ?>)" ><img  src="/h_shop/Public/admin/images/delete.gif"  title="删除" alt="删除"/></a>
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
            $("#SelectUser").change(function () {
            	userSearch();
            });
            $("#Button1").click(function () {
                // alldel("delallNews");
                // 批量删除
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
                                url: "admin.php?c=Sysmess&a=DelAll",
                                // data:$("#submitForm").serialize(),
                                data:{idstr:str},
                                success: function (data) {
                                    if(data > 0){
                                        $.jBox.tip('批量删除成功。', 'success');
                                        window.setTimeout(function () {
                                        window.history.go(0);}, 1500); //跳转页面
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
        function Del(m_id){
            $("#cbkone"+m_id).attr("checked", true);
            var submit = function (v, h, f) {
                if (v == 'ok'){
                $.ajax({
                  type: "POST",
                  dataType: "text",
                  url: "admin.php?c=Sysmess&a=Del",
                  data:{m_id:m_id},
                  success: function (data) {
                      if(data > 0){
                        $.jBox.tip('删除成功。', 'success');
                        window.setTimeout(function () {
                            window.history.go(0);}, 1500); //跳转页面
                      }else{
                        $.jBox.tip('服务器连接超时，删除失败', 'error');
                      }
                    }
                });
                }else if (v == 'cancel'){
                    $("#cbkone"+m_id).attr("checked", false);
                    jBox.tip('取消删除', 'info');  
                }  
                return true; //close  
            };    
            $.jBox.confirm("确认删除吗？删除不可恢复", "温馨提示", submit);             
        }
    </script>