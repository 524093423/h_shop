<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo C('APP_TITLE');?></title>
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/style.css" />
<link type="text/css" rel="stylesheet" href="/h_shop/Public/admin/images/pagination.css" />

</head>
<body class="mainbody">
    <div class="navigation">首页 &gt; 用户管理 &gt; 用户列表</div>
    <div class="tools_box">
	    <div class="tools_bar" style="height:0px;">
            <div class="search_box"><span>按用户推荐码搜索：</span>
			   
		    </div>
		    <!-- <a href="javascript:void(0);"  id="seleallbybtton" class="tools_btn"><span><b class="all">全选</b></span></a>
            <a href="javascript:void(0);" id="Button1" class="tools_btn"><span><b class="delete">批量删除</b></span></a> -->
        </div>
        <div class="select_box">
                  请选择封号状态：<select id="SelectUser" class="select">
            <option value="0">全部</option>
                     <option value="-1"  <?php if( $zt == -1 ): ?>selected<?php else: endif; ?>>  未冻结</option>
                     <option value="1"  <?php if( $zt == 1 ): ?>selected<?php else: endif; ?>>  已冻结</option>
            </select>
          按用户手机号或用户名搜索：
			    <input id="txtKeywords" type="text"  class="txtInput" value="<?php echo ($ext); ?>" x-webkit-speech="" lang="zh-CN"/>
                <input id="btnSearch" type="button" value="搜 索" class="btnSearch" />
       <div style="float:right">
           <a href="javascript:void(0)" class="sendMsg">发送短信</a>
	    <img  src="/h_shop/Public/admin/images/10.png"  />表示未封号<img src="/h_shop/Public/admin/images/11.png"  />表示封号
	    </div> 
	    </div>
	    
    </div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="msgtable">
      <tr>
      <!--<th width="5%"><input id="Checkbox1" type="checkbox" class="cbk"  style=" margin:0px;"/></th>-->
                <th width="10%">编号</th>
                <th width="10%"  align="center">用户名</th>
                <th width="15%"  align="center">联系方式</th>
                 <th width="15%"  align="center">账户头像</th>
                <th width="10%"  align="center">注册日期</th>
                 <th width="5%"  align="center">注册类型</th>
                 <th width="5%"  align="center">嘿币数量</th>
                <th width="10%"  align="center">帐号余额</th>
                <th width="10%"  align="center">帐号状态</th>
                 <th width="10%"  align="center">操作</th>
      </tr>
      		<?php if(is_array($list)): foreach($list as $key=>$lt): ?><tr>   
                <!--<td  align="center">  <input name="ck"  type="checkbox" class="cbk"  value="<?php echo ($vo["phe"]); ?>"/></td>-->
                <td align="center"><?php echo ($no+$key); ?></td>
                <td align="center"><?php echo ($lt["nickname"]); ?></td>
                <td align="center"><?php echo ($lt["phe"]); ?></td>
                <td align="center"><a class="style" href=".<?php echo ($lt["uhead"]); ?>">点击查看</a></td>
                <td align="center"><?php echo ($lt["rtime"]); ?></td>
                <td align="center"><?php if($lt['register_type'] == 1){ echo "<span style='color:#22acf8;'>手机注册</span>";}elseif($lt['register_type'] == 2){ echo "<span style='color:#1ac91a;'>微信注册</span>";}elseif($lt['register_type'] == 3){echo "<span style='color:#ec5f54;'>QQ注册</span>";} ?></td>
                <td align="center"><?php echo ($lt["intl"]); ?></td>
                <td align="center"><?php echo ($lt["account"]); ?></td>
                 <td align="center">
	                	 <?php if( $lt["isfh"] == 0 ): ?><span id="start<?php echo ($lt["uid"]); ?>"><a href="javascript:void(0)" onclick='changestate(this,"user","state",<?php echo ($lt["uid"]); ?>,1,11,10,"user_id")'>   <img src="/h_shop/Public/admin/images/10.png"  /></a></span>
	                	<span id="end<?php echo ($lt["uid"]); ?>" style="display:none"><a href="javascript:void(0)" onclick='changestate(this,"user","state",<?php echo ($lt["uid"]); ?>,0,10,11,"user_id")'>   <img src="/h_shop/Public/admin/images/11.png"  /></a></span>
	                	<?php else: ?>
	                	<span id="start<?php echo ($lt["uid"]); ?>" style="display:none"><a href="javascript:void(0)" onclick='changestate(this,"user","state",<?php echo ($lt["uid"]); ?>,1,11,10,"user_id")'>   <img src="/h_shop/Public/admin/images/10.png"  /></span></a>
	                	<span id="end<?php echo ($lt["uid"]); ?>" ><a href="javascript:void(0)" onclick='changestate(this,"user","state",<?php echo ($lt["uid"]); ?>,0,10,11,"user_id")'>   <img src="/h_shop/Public/admin/images/11.png"  /></a></span><?php endif; ?>
                	</td>
                <td style=" text-align:center;">
                	<a class="look"  rel="<?php echo ($lt["uid"]); ?>" href="javascript:void(0)" ><img  src="/h_shop/Public/admin/images/look.png"  title="查看用户基本信息" alt="查看用户基本信息"/></a>
                	<!--  <a  href="./admin.php?c=User&a=setUser&uid=<?php echo ($lt["uid"]); ?>" ><img  src="/h_shop/Public/admin/images/auth.png"  title="添加权限" alt="添加权限"/></a>-->
                	<!--  <a  href="javascript:void(0)" onclick="BackInfo(<?php echo ($lt["uid"]); ?>)" ><img  src="/h_shop/Public/admin/images/email_03.png"  title="发送消息" alt="发送消息"/></a>-->
                   <a class="del" href="javascript:void(0)"   rel='<?php echo ($lt["uid"]); ?>' ><img  src="/h_shop/Public/admin/images/delete.gif"  title="删除" alt="删除"/></a>
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
<script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
<link href="/h_shop/Public/admin/js/fany/fany.css" rel="stylesheet" type="text/css" />
<script src="/h_shop/Public/admin/js/fany/fany.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/layer/layer.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
    var province = $("#pe").val();
    var city = $("#cy").val();
        $(function () {
        	$(".style").fancybox({
                "titleShow": false
            });
        	$("table tbody tr td a.look").click(function () {
        		var index = layer.open({
        			  type: 2,
        			  content: './admin.php?c=User&a=SingleShopping&userid='+$(this).attr("rel"),
        			  area: ["100%","100%"],
        			  maxmin: true
        			});
            });
            tablecolor();
            $("#btnSearch").click(function () {
            	userSearch();
            });
            $(".sendMsg").click(function(){
                layer.prompt({title: '请输入短信内容', formType: 2}, function(text, index){
                    layer.close(index);
                    if(text==""){
                        layer.msg("输入内容不合格",{time:1000});
                        return false;
                    }
                    $.ajax({
                        url:"./admin.php?c=Common&a=sendMsgAll",
                        type:"post",
                        data:{"text":text},
                        success:function(result){
                            if(result > 0){
                                layer.msg("发送成功",{time:1000});
                            }else{
                                layer.msg("系统繁忙",{time:1000});
                            }
                        },
                        error:function(){
                            layer.msg("服务器连接超时",{time:1000});
                        }
                    })
                });
            });
            $("#SelectUser").change(function () {
            	userSearch();
            });
            $("#Button1").click(function () {
                alldel("delallNews");
            });
            $("table tbody tr td a.del").click(function () {
                del($(this), "user");
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
        function SelectGrade(userid,grade){
        	$.ajax({
        		url:"admin.php?c=User&a=UpdateUserGrade",
        		type:"post",
        		data:{"userid":userid,"grade":grade},
        		success:function(msg){
        			if(msg > 0){
        				$.jBox.tip("设置成功","success");
        			}else{
        				$.jBox.tip("设置失败","error");
        			}
        		}
        	});
        }
		function BackInfo(uid){
        	
        	var text = "<div style='margin-top:20px;margin-bottom:20px'>请输入发送的内容：<br/><textarea id='answer' name='answer' style=\"margin: 0px; height: 166px; width: 285px;\"></textarea>";
        	var html = "<div style='width:285px;margin-top:10px;margin-left:30px'>"+text+"</div></div>";
        	var submit = function (v, h, f) {
        	    if (f.answer == '') {
        	        $.jBox.tip("请输入发送的系统信息。", 'error', { focusId: "answer" }); // 关闭设置 yourname 为焦点
        	        return false;
        	    }
        	    $.ajax({
        	    	url:"./admin.php?c=Message&a=SubSystem",
        	    	type:"post",
        	    	data:{"info":f.answer,"userid":uid},
        	    	success:function(msg){
        	    		if(msg == 1){
        	    			$.jBox.tip("发送成功","success");
        	    		}else{
        	    			$.jBox.tip("连接服务器超时","error");
        	    		}
        	    	}
        	    });
        	    //$.jBox.tip("你叫：" + h.find("#yourname").val());
        	    //$.jBox.tip("你叫：" + h.find(":input[name='yourname']").val());

        	    return true;
        	};
			$.jBox(html, { title: "发送系统消息", submit: submit });
        	 	
        }
   
    </script>