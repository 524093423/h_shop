<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo C('APP_TITLE');?></title>
    <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mainbody">
    <div class="navigation">
        <a href="javascript:history.go(-1);" class="back">后退</a>首页 &gt; 图片管理 &gt; 添加图片</div>
    <div id="contentTab">
        <ul class="tab_nav">
            <li class="selected"><a onclick="tabs('#contentTab',0);" href="javascript:;">基本信息</a></li>
        </ul>
        <div class="tab_con" style="display: block;">
            <table class="form_table">
                <col width="150px">
                <col>
                <tbody>
                    <!--  <tr>
                        <th>
                            图片位置：
                        </th>
                        <td>
                             <?php echo ($cate); ?>
                        </td>
                    </tr>
                    -->
                <tr>
                        <th>
                            图片地区：
                        </th>
                        <td>
                <select id="SelectProvince" style="float:left" class="select">
                    <option value="0">中国</option>
                 </select>
                 <div id="CityChange" style="display:none;float:left">
                 	&nbsp;&nbsp;市：<select id="SelectCity" class="select">
                    <option value="0">请选择</option>
                 </select>
                 </div> 
                        </td>
                    </tr>
                    <tr id="fcous">
                        <th>
                            选择图片：
                        </th>
                        <td>
                            <form id="form2" enctype="multipart/form-data" target="fileupload" action="./admin.php?c=Common&a=Uploads"
                            method="post">
                            <input type="file" name="filedata" id="file" />
                            <input type="hidden" name="type" value="uploadfocus" />
                            <input type="submit" value="上传" id="tijiao" />
                            <input type="hidden" name="UpFilePath" value="carousel">
                            <span id="tip" style="font-size: 12px; color: Red;"></span><font style="color:red">(图片尺寸建议[600*300])</font>
                            </form>
                        </td>
                        <iframe name="fileupload" src="about:blank" style="display: none;"></iframe>
                    </tr>
                    <tr>
                        <th>
                           微信链接地址：
                        </th>
                        <td>
                             <input id="imgurl" type="text" class="txtInput" value="<?php echo ($banner["url"]); ?>" x-webkit-speech="" lang="zh-CN" /><font style="color:red">(注:0代表图片无链接地址，请勿输入其他数字，并注意url地址的规范，如果需要微信端同步，请去店铺管理复制微信分享链接)</font>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            APP地址：
                        </th>
                        <td>
                            <select id="SelectShop" class="select">
                            <option value="0">请选择</option>
                            <?php if(is_array($shopinfo)): foreach($shopinfo as $key=>$sp): if( $sp["bid"] == $banner["bid"] ): ?><option value="<?php echo ($sp["bid"]); ?>" data="<?php echo ($sp["name"]); ?>" selected><?php echo ($sp["name"]); ?></option>
                                    <?php else: ?>
                                    <option value="<?php echo ($sp["bid"]); ?>" data="<?php echo ($sp["name"]); ?>"><?php echo ($sp["name"]); ?></option><?php endif; endforeach; endif; ?>
                            </select>
                            <font style="color:red">(注:不选择则无链接地址)</font>
                        </td>
                    </tr>
                    <tr>
                        <th>
                        </th>
                        <td>
                            <?php if( $upstr == 'no'): ?><img id="s" style="display: none;width:400px;height:200px;" />
                            <?php else: ?>
                            <img id="s" src=".<?php echo ($banner["c_photo"]); ?>" style="display: block;width:400px;height:200px;" /><?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="foot_btn_box">
            <input id="sub" type="button" value="提交保存" class="btnSubmit" />
        </div>
    </div>
</body>
</html>
<script type="text/javascript" src="/h_shop/Public/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="/h_shop/Public/admin/js/layer/layer.js"></script>
<script src="/h_shop/Public/admin/js/function.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
<link rel="stylesheet" href="/h_shop/Public/admin/js/tips/tip-yellowsimple/tip-yellowsimple.css"   type="text/css" />
<script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/fany/fany.css" rel="stylesheet" type="text/css" />
    <script src="/h_shop/Public/admin/js/fany/fany.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/h_shop/Public/admin/js/layer/layer.js"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">
	var upstr = <?php echo ($upstr); ?>;
	var regionId=<?php echo ($regionId); ?>;
	var parentid=<?php echo ($parentid); ?>;
    $(function () {
    	//获取省级单位和中国
    	$.getJSON("./admin.php?c=Common&a=GetProvince",function(region){
    		var str = "";
    		for(var i=0;i<region.length;i++){
    			if(region[i]['code'] == parentid){
    				str +="<option value="+region[i]['code']+" selected>"+region[i]['fullName']+"</option>";
    			}else{
    				str +="<option value="+region[i]['code']+">"+region[i]['fullName']+"</option>";	
    			}
    		}
    		$("#SelectProvince").append(str);
    	});
    	if(upstr != 0){
    		if(parentid != 0){
    			$.getJSON("./admin.php?c=Common&a=GetCity&code="+regionId,function(region){
    				var str="<option value=0>请选择</option>";
        			for(var i=0;i<region.length;i++){
        				if(region[i]['code'] == regionId){
            				str +="<option value="+region[i]['code']+" selected>"+region[i]['fullName']+"</option>";
            			}else{
            				str +="<option value="+region[i]['code']+">"+region[i]['fullName']+"</option>";	
            			}
        			}
        			$("#SelectCity").html(str);
        			$("#CityChange").attr("style","display:block");
        		});
    		}
    	}
    	//更改省级单位切换地区
    	$("#SelectProvince").change(function(){
    		var code=$(this).val();
    		parentid 	= code;
    		$.getJSON("./admin.php?c=Common&a=GetCity&code="+code,function(region){	
    			if(region=='0'){
    				regionId 	= code;
    				$("#CityChange").attr("style","display:none");
    				return false;
    			}
    			var str="<option value=0>请选择</option>";
    			for(var i=0;i<region.length;i++){
    				str +="<option value="+region[i]['code']+">"+region[i]['fullName']+"</option>";
    			}
    			$("#SelectCity").html(str);
    			$("#CityChange").attr("style","display:block");
    		});
    	});
    	//更改地区记录操作信息
    	$("#SelectCity").change(function(){
    		regionId	= $(this).val();
    	});
        $("#sub").click(function () {
            if ($.trim($("#s").attr("src")) == "") {
                tip('file', '请上传图片');
                $("#file ").focus();
                return false;
            }
            var SelectClass = 0;
           	/* if($("#SelectClass").val() != ""){
           		SelectClass 	= $("#SelectClass").val();
           	} */
           	var imgurl = 0;
           	if($("#imgurl").val() != ""){
           		imgurl 		= $("#imgurl").val();
           	}
                $.post("./admin.php?c=Banner&a=newlyImg",
                    {
                    fsrc: $("#s").attr("src"),
                    SelectClass: SelectClass ,
                    regionIds:regionId,
                    parentids:parentid,
                    imgurl:imgurl,
                    upid:upstr,
                    bid:$('#SelectShop').val(), name:$("#SelectShop").find('option:selected').text()
                }, function (data) {
                   if(data == 1){
                	   layer.msg('提交成功', {
                		   icon: 1,
                		   time: 1000 //2秒关闭（如果不配置，默认是3秒）
                		 }, function(){
                			province = "";
                			cityid  = "";
                			stype     = 0;
                			history.go(0);
                		 }); 
                   }else if(data == 0){
                	   layer.msg('提交失败', {
                		   icon: 2,
                		   time: 1000 //2秒关闭（如果不配置，默认是3秒）
                		 }); 
                   }else{
                	   layer.msg('该类图片数量已到上限', {
                		   icon: 2,
                		   time: 1000, //2秒关闭（如果不配置，默认是3秒）
                		   function(){
                			   delPhoto();
                		   }
                		 });
                   }
                });
        });
        $("#tijiao").click(function () {
            $.jBox.tip("正在上传，请稍后...", 'loading');
        });
    });
    function uploadSuccessCar(info, src, issuccess) {
	    if (parseInt(issuccess) == 0) {
	        $("#s").attr("src", src).css("display", "block")
	    }else{
	    	window.setTimeout(function () { $.jBox.tip(info, 'error'); }, 1)
	    	return false;
	    }
	    window.setTimeout(function () { $.jBox.tip(info, 'success'); }, 1);
	}
</script>