<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo C('APP_TITLE');?></title>
 <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mainbody">

<div class="navigation"><a href="javascript:history.go(-1);" class="back">后退</a>首页 &gt; 商品分类管理 &gt; <?php echo ($title); ?></div>
<div id="contentTab">
    <ul class="tab_nav">
        <li class="selected"><a onclick="tabs('#contentTab',0);" href="javascript:;">基本信息</a></li>
    </ul>

    <div class="tab_con" style="display:block;">
        <table class="form_table">
            <col width="150px"><col>
            <tbody>
            <tr>
                <th>所属类别：</th>
                <td><?php echo ($cate); ?>
                  </td>
            </tr>
            
        	<?php if( $state == 1): ?><tr> 
                <th>分类名称：</th>
                <td><input type="text" id="classname" value=""   class="txtInput normal required" /></td>
            </tr>

              <tr id="fcous">
                        <th>
                             分类缩略图：
                        </th>
                        <td>
                            <form id="form2" enctype="multipart/form-data" target="fileupload" action="./admin.php?c=Goods&a=UploadImg"
                            method="post">
                            <input type="file" name="filedata" id="file" />
                            <input type="hidden" name="type" value="uploadfocus" />
                            <input type="submit" value="上传" id="tijiao" />
                            <input type="hidden" name="pah" value="thumb">
                            <span id="tip" style="font-size: 12px; color: Red;"></span>
                            </form>
                        </td>
                        <iframe name="fileupload" src="about:blank" style="display: none;"></iframe>
                    </tr>
                    <tr>
                        <th>
                        </th>
                        <td>
                            <img id="s" style="display: none;width:100px;height:150px" />
                        </td>
                    </tr>
        	<?php else: ?>
        	<?php if(is_array($info)): foreach($info as $key=>$ios): ?><tr> 
                <th>分类名称：</th>
                <td><input type="text" id="classname" value="<?php echo ($ios["name"]); ?>"   class="txtInput normal required" /></td>
            </tr>

              <tr id="fcous">
                        <th>
                             分类缩略图：
                        </th>
                        <td>
                            <form id="form2" enctype="multipart/form-data" target="fileupload" action="./admin.php?c=Goods&a=UploadImg"
                            method="post">
                            <input type="file" name="filedata" id="file" />
                            <input type="hidden" name="type" value="uploadfocus" />
                            <input type="submit" value="上传" id="tijiao" />
                            <input type="hidden" name="pah" value="thumb">
                            <span id="tip" style="font-size: 12px; color: Red;"></span>
                            </form>
                        </td>
                        <iframe name="fileupload" src="about:blank" style="display: none;"></iframe>
                    </tr>
                    <tr>
                        <th>
                        </th>
                        <td>
                            <img id="s" src=".<?php echo ($ios["img"]); ?>" style="display: block;width:100px;height:150px" />
                        </td>
                    </tr><?php endforeach; endif; endif; ?>  
    
            </tbody>
        </table>
    </div>
    <div class="foot_btn_box">
     <input id="sub" type="button" value="提交保存" class="btnSubmit"/>
    </div>
</div>
</body>
</html>
 <script src="/h_shop/Public/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/h_shop/Public/admin/js/function.js"></script>
    <script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
    <link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
 <script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
 <link href="/h_shop/Public/admin/js/tips/tip-yellow/tip-yellow.css" rel="stylesheet" type="text/css" />
   <script>
   var state = <?php echo ($state); ?>;
   var titles = '<?php echo ($title); ?>';
   var idstr  = <?php echo ($idstr); ?>;
	   function uploadSuccess(info, src, issuccess) {
		    if (parseInt(issuccess) == 0) {
		        $("#s").attr("src", src).css("display", "block")
		    }else{
		    	window.setTimeout(function () { $.jBox.tip(info, 'error'); }, 1)
		    	return false;
		    }
		    window.setTimeout(function () { $.jBox.tip(info, 'success'); }, 1);
		}
       $(function () {
           $("#sub").click(function () {
               if (!veriform('classname')) { tip('classname', '请填写分类名称'); $("#classname").focus(); return false; }
               else {
                   $.post("./admin.php?c=Classify&a=AddClassify", { Action: "classify", classname: $.trim($("#classname").val()), parintid: $("#SelectClass").val(),imgurl:$("#s").attr("src"),isstate:state,id:idstr
                   }, function (data) {
                	   //data = eval(data);
                	   //alert(data.text);
                	   $.jBox.tip(titles+"成功", "success");
                   });
               }
           });
       });
  </script>