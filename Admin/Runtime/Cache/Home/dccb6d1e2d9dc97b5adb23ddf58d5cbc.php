<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>商品详情</title>
    <link href="/h_shop/Public/admin/images/style.css" rel="stylesheet" type="text/css" />
</head>
<body class="mainbody" class="mainbody" >
    <div class="navigation">
        <a href="javascript:history.go(-1);" class="back">后退</a>首页 &gt; 商品管理 &gt;商品详情</div>
    <div id="contentTab">
        <ul class="tab_nav">
            <li class="selected"><a onclick="tabs('#contentTab',0);" href="javascript:;">基本信息</a></li>
            <li><a onclick="tabs('#contentTab',1);" href="javascript:;">其他属性</a></li>
        </ul>
        <div class="tab_con" style="display: block;">
        <?php if(is_array($basic)): foreach($basic as $key=>$gba): ?><table class="form_table">
                <col width="150px" />
                <tbody>
                    <tr>
                        <th>
                            商品分类：
                        </th>
                        <td>
                             <?php echo ($cate); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            商品名称：
                        </th>
                         <td>
                            <input type="text" id="godstitle" value="<?php echo ($gba["gn"]); ?>" class="txtInput normal required" />
                        </td>
                    </tr>
                    <tr id="fcous">
                        <th>
                             商品列表图：
                        </th>
                        <td>
                            <form id="form2" enctype="multipart/form-data" target="fileupload" action="./admin.php?c=Goods&a=UploadImg"
                            method="post">
                            <input type="file" name="filedata" id="file" />
                            <input type="hidden" name="type" value="uploadfocus" />
                            <input type="submit" value="上传" id="tijiao" />
                            <input type="hidden" name="pah" value="s">
                            <span id="tip" style="font-size: 12px; color: Red;"></span>
                            </form>
                        </td>
                        <iframe name="fileupload" src="about:blank" style="display: none;"></iframe>
                    </tr>
                    <tr>
                        <th>
                        </th>
                        <td>
                            <img id="s"  src=".<?php echo ($gba["gp"]); ?>" style="display: block;width:100px;height:150px" />
                        </td>
                    </tr>
                      <!--<tr>
                        <th>
                            赠送积分比例：
                        </th>
                        <td>
                            <select  class="SelectZS" >
                            	<option value="0" <?php if( $gba["gii"] == 0 ): ?>selected<?php else: endif; ?>>请选择</option>
                            	<option value="0.1" <?php if( $gba["gii"] == 0.1 ): ?>selected<?php else: endif; ?> >10%</option>
                            	<option value="0.2" <?php if( $gba["gii"] == 0.2 ): ?>selected<?php else: endif; ?> >20%</option>
                            	<option value="0.3" <?php if( $gba["gii"] == 0.3 ): ?>selected<?php else: endif; ?>>30%</option>
                            	<option value="0.4" <?php if( $gba["gii"] == 0.4 ): ?>selected<?php else: endif; ?>>40%</option>
                            	<option value="0.5" <?php if( $gba["gii"] == 0.5 ): ?>selected<?php else: endif; ?>>50%</option>
                            	<option value="0.6" <?php if( $gba["gii"] == 0.6 ): ?>selected<?php else: endif; ?>>60%</option>
                            	<option value="0.7" <?php if( $gba["gii"] == 0.7 ): ?>selected<?php else: endif; ?>>70%</option>
                            	<option value="0.8" <?php if( $gba["gii"] == 0.8 ): ?>selected<?php else: endif; ?>>80%</option>
                            	<option value="0.9" <?php if( $gba["gii"] == 0.9 ): ?>selected<?php else: endif; ?>>90%</option>
                            	<option value="1" <?php if( $gba["gii"] == 1 ): ?>selected<?php else: endif; ?>>100%</option>
                            </select>
                            &nbsp;(请选择积分与价格比例,如果不送积分可不选)
                        </td>
                    </tr> -->
                   <!-- <tr>
                    	<th>是否可以使用积分购买</th>
                    	<td><?php if( $gba["isintpay"] == 0 ): ?><input id="intpay" type="checkbox" value="yes"   /><?php else: ?><input id="intpay" type="checkbox" value="yes"   checked/><?php endif; ?></td>
                    </tr>
                    <tr>
                        <th>
                            使用积分比例：
                        </th>
                        <td>
                            <select class="SelectSY" >
                            	<option value="0" <?php if( $gba["usei"] == 0 ): ?>selected<?php else: endif; ?>>请选择</option>
                            	<option value="0.1" <?php if( $gba["usei"] == 0.1 ): ?>selected<?php else: endif; ?>>10%</option>
                            	<option value="0.2" <?php if( $gba["usei"] == 0.2 ): ?>selected<?php else: endif; ?>>20%</option>
                            	<option value="0.3" <?php if( $gba["usei"] == 0.3 ): ?>selected<?php else: endif; ?>>30%</option>
                            	<option value="0.4" <?php if( $gba["usei"] == 0.4 ): ?>selected<?php else: endif; ?>>40%</option>
                            	<option value="0.5" <?php if( $gba["usei"] == 0.5 ): ?>selected<?php else: endif; ?>>50%</option>
                            	<option value="0.6" <?php if( $gba["usei"] == 0.6 ): ?>selected<?php else: endif; ?>>60%</option>
                            	<option value="0.7" <?php if( $gba["usei"] == 0.7 ): ?>selected<?php else: endif; ?>>70%</option>
                            	<option value="0.8" <?php if( $gba["usei"] == 0.8 ): ?>selected<?php else: endif; ?>>80%</option>
                            	<option value="0.9" <?php if( $gba["usei"] == 0.9 ): ?>selected<?php else: endif; ?>>90%</option>
                            	<option value="1" <?php if( $gba["usei"] == 1 ): ?>selected<?php else: endif; ?>>100%</option>
                            </select>
                           &nbsp; (请选择积分与价格比例,如果不可使用积分购买则不选)
                        </td>
                    </tr> -->
                    <tr>
                        <th>
                          描述：
                        </th>
                        <td>
                            <textarea name="content" cols="100" rows="8" style="width: 99%; height: 200px;" id="contents"><?php echo ($gba["gdesc"]); ?></textarea>
                        </td>
                    </tr> 
                    <!--  
                    <tr>
                        <th valign="top">
                            是否推荐：
                        </th>
                        <td>
                        <?php if( $gba["gpe"] == 1 ): ?><input id="home" type="checkbox" value="yes" checked='checked' />
                        <?php else: ?>
                        <input id="home" type="checkbox"  value="" /><?php endif; ?>  
                        </td>
                    </tr>
                    <tr>
                        <th valign="top">
                            是否热销：
                        </th>
                        <td>
                         <?php if( $gba["gsl"] == 1 ): ?><input id="sel"  type="checkbox" value="yes" checked='checked' />
                          <?php else: ?>
                            <input id="sel" type="checkbox" value="" /><?php endif; ?>
                        </td>
                    </tr>
                    -->
    					<input type="hidden" value="<?php echo ($gba["gid"]); ?>" id="isup">
                </tbody>
            </table>
        </div>
        <!-- 其他属性 -->
        <div class="tab_con">
        	<table class="form_table">
                <col width="150px" />
                <tbody>
                     <tr style="width:200px;">
                        <th>
                            价格：
                        </th>
                        <td><input type="text" id="gprice" placeholder="请输入商品的价格" value="<?php echo ($gba["gprice"]); ?>" class="txtInput normal required" /><font style="color:gray">(商品单价 小数点后两位有效)</font></td>
                    </tr>
                     <tr style="width:200px;">
                         <th>
                             库存：
                         </th>
                         <td><input type="text" id="gnum" placeholder="请输入商品的库存" value="<?php echo ($gba["goods_number"]); ?>" class="txtInput normal required" /><font style="color:gray">(商品库存整数有效)</font></td>
                     </tr>
                    <!--  
                    <tr style="width:200px;">
                        <th>
                            商品规格：
                        </th>
                        <td>
                            <input type="text" id="spec" placeholder="请输入规格" value="" style="width:225px"/>&nbsp;&nbsp;库存：<input type="text" id="cun" placeholder="请输入库存"  value="" style="width:100px"/>&nbsp;&nbsp;出厂价：<input type="text" id="ccj" placeholder="请输入出厂价"   value="" style="width:100px"/>&nbsp;&nbsp;售价：<input type="text" id="jia" placeholder="请输入售价"   value="" style="width:100px"/>&nbsp;&nbsp;<input type="button" value="添加" onclick="addGdiv()">
                            
                            <div style="height:5px;clear:both"></div>
                            <div class="specNum"  style="">
                            <?php if(is_array($spec)): foreach($spec as $key=>$sp): ?><div class="specs" id=spec<?php echo ($key+1); ?> style="height:20px;float:left">规格:<span class='gg'><?php echo ($sp["gsdesc"]); ?></span> 库存:<span class='kc'><?php echo ($sp["gstock"]); ?></span>出厂价:<span class='ccj'><?php echo ($sp["ccj"]); ?></span>&nbsp;售价:<span class='dj'><?php echo ($sp["gprice"]); ?></span>&nbsp;|&nbsp;<a href="javascript:void(0)" onclick="DeleteDiv(<?php echo ($key+1); ?>)">删除</a></div>&nbsp;&nbsp;<?php endforeach; endif; ?>
                            </div>
                        </td>
                    </tr>
    				<tr style="width:200px;">
                        <th>
                            商品重量：
                        </th>
                        <td>
                            <input type="text" id="weight" placeholder="请输入重量" value="<?php echo ($gba["wt"]); ?>" style="width:105px"/>
                            &nbsp;<font style="color:gray"> (填写商品重量，如果是虚拟产品重量默认是0，单位克)</font>
                        </td>
                    </tr>
                    -->
                    <tr id="fcous">
                        <th>
                             视频封面图：
                        </th>
                        <td>
                            <form id="form2" enctype="multipart/form-data" target="coverfileupload" action="./admin.php?c=Goods&a=UploadImg"
                            method="post">
                            <input type="file" name="filedata" id="file" />
                            <input type="hidden" name="type" value="uploadfocus" />
                            <input type="submit" value="上传" id="tijiao" />
                            <input type="hidden" name="pah" value="cover">
                            <span id="tip" style="font-size: 12px; color: Red;"></span>
                            </form>
                        </td>
                        <iframe name="coverfileupload" src="about:blank" style="display: none;"></iframe>
                    </tr>
                     <tr>
                         <th>
                         </th>
                         <td>
                             <img id="cover" src=".<?php echo ($gba["coverimg"]); ?>" style="display: block;width:100px;height:150px" />
                         </td>
                     </tr>
                     <tr>
                         <th>
                             商品轮播：
                         </th>
                         <td>
                             <div class="clear">
                             </div>
                             <input type="hidden" name="focus_photo" id="focus_photo" value="" />
                             <div id="show">
                                 <form id="form2" enctype="multipart/form-data" target="fileupload" action="./admin.php?c=Common&a=Uploads"
                                       method="post">
                                     <input type="file" name="filedata" id="file" />
                                     <input type="hidden" name="type" value="uploadfocus" />
                                     <input type="submit" value="上传" id="tijiao" />
                                     <input type="hidden" name="UpFilePath" value="carousel">
                                     <span id="tip" style="font-size: 12px; color: Red;"></span>
                                 </form>
                             </div>
                             <div id="show_list">
                                 <ul>
                                     <?php if(is_array($alb)): foreach($alb as $key=>$alb): ?><li>
                                             <input type="hidden" name="hide_photo_remark" value="">
                                             <input type="hidden" name="hide_photo_name" value="">
                                             <div onclick="javascript:;" class="img_box current"><img src="<?php echo ($alb["img"]); ?>" data-imgid="<?php echo ($alb["id"]); ?>" id="s<?php echo ($key+1); ?>" class="shopCarousel" bigsrc=""></if>
                                                 <span class="remark"><i><?php echo ($alb["text"]); ?></i></span>
                                             </div>
                                             <a href="javascript:;"  onclick="del_img(<?php echo ($key+1); ?>)">删除</a>
                                         </li><?php endforeach; endif; ?>
                                 </ul>
                                 <font style="color:red">(图片尺寸建议[600*300])</font>
                             </div>
                         </td>
                     </tr>
                  <tr> 
                <th>商品视频：</th>
                <td>
                    <form id="form2" enctype="multipart/form-data" target="flashupload" action="./admin.php?c=Common&a=UploadFlash"
                    method="post">
                    <input type="file" name="filedata_flash" id="file_flash" />
                    <input type="hidden" name="type" value="uploadfocus_flash" />
                    <input type="submit" value="提交视频" id="tijiao_flash" />
                    <span id="tip_flash" style="font-size: 12px; color: Red;"></span>
                    </form>
                </td>
                <iframe name="flashupload" src="about:blank" style="display: none;"></iframe>
            </tr>
            <tr>
                <th>
                </th>
                <td>
                  <video width="320" id="old_video" height="200" controls autoplay>
                    <source id="sf" src=".<?php echo ($gba["fs"]); ?>" type="video/mp4">
                   </video>
                   <input type="hidden" name='flash_id' id='flash_id' value="<?php echo ($flash[0]['id']); ?>"/>
                   <embed id="sf" style="display: none;" width="320" height="200" controls autoplay> 
                </td>
            </tr>
                </tbody>
            </table><?php endforeach; endif; ?>
        </div>
        <div class="foot_btn_box">
            <input id="sub" type="button" value="提交更新" class="btnSubmit" />
        </div>
    </div>
</body>
</html>

<script type="text/javascript" src="/h_shop/Public/js/jquery-1.8.3.min.js"></script>
<script src="/h_shop/Public/admin/js/function.js" type="text/javascript"></script>
<script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/fany/fany.css" rel="stylesheet" type="text/css" />
    <script src="/h_shop/Public/admin/js/fany/fany.js" type="text/javascript"></script>
<link rel="stylesheet" href="/h_shop/Public/admin/js/tips/tip-yellowsimple/tip-yellowsimple.css"  type="text/css" />
<script src="/h_shop/Public/admin/js/jbox/jquery.jBox-2.2.min.js" type="text/javascript"></script>
<link href="/h_shop/Public/admin/js/jbox/jbox.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/h_shop/Public/admin/js/layer/layer.js"></script>
<script src="/h_shop/Public/admin/js/tips/jquery.poshytip.js" type="text/javascript"></script>
<link rel="stylesheet" href="/h_shop/Public/admin/js/tips/tip-yellowsimple/tip-yellowsimple.css"
    type="text/css" />
<script src="/h_shop/Public/admin/js/goods.js?r="+Math.random()+" type="text/javascript"></script>
<script>
var cNum = 0;
var inum = 0;
var bid=<?php echo ($bid); ?>;
var specNum =0;
var imgids= "";
var delimgid= "";
$(document).ready(function(){
	$("#tijiao_flash").click(function(){
		$.jBox.tip("正在上传视频..","loading");
	});
});
//商品图片上传
function uploadSuccess(info, src, issuccess,id) {
    if (parseInt(issuccess) == 0) {
        $("#"+id).attr("src", src).css("display", "block")
    }else{
    	window.setTimeout(function () { $.jBox.tip(info, 'error'); }, 1)
    	return false;
    }
    window.setTimeout(function () { $.jBox.tip(info, 'success'); }, 1);
}
//商品视频上传
function uploadSuccessflash(info, src, issuccess){
	 if (parseInt(issuccess) == 0) {
         $("#old_video").css('display',"none");
         $("#sf").attr("src", src).css('display',"block");
     }else{
       window.setTimeout(function () { $.jBox.tip(info, 'error'); }, 1500)
       return false;
     }
	 //console.log(src);
     window.setTimeout(function () { $.jBox.tip(info, 'success'); }, 1500);
}
//轮播图上传
function uploadSuccessCar(info, src, issuccess,imgid) {
    if (parseInt(issuccess) == 0) {
        for(var i=1;i<6;i++){
            if($("#s"+i).attr("src") == ""){
                $("#s"+i).attr("src",src);
                $("#s"+i).attr("data-imgid",imgid);
                return false;
            }
        }
        //$("#s").attr("src", src).css("display", "block")
    }else{
        window.setTimeout(function () { $.jBox.tip(info, 'error'); }, 1)
        return false;
    }
    window.setTimeout(function () { $.jBox.tip("轮播图最多五张", 'success'); }, 1);
}
//删除上传的图片
function del_img(id){
    var imgurl = $("#s"+id).attr("src");
    var imgid   = $("#s"+id).attr("data-imgid")+",";
    if(imgurl == ""){
        $.jBox.tip("图片不存在","error");
        return false;
    }
    $.post("./admin.php?c=Common&a=unlinkImg",{img:imgurl},function(data,TextStatus){
        delimgid    += imgid;
        console.log(delimgid);
        $("#s"+id).attr("src","");
        $("#s"+id).attr("data-imgid","");
        eval(arguments[0]);
    });
}
//上传的商品轮播图
function getUploadImg(){
    imgids  = "";
    var imgstr  = "";
    for(var i=1;i<6;i++){
        if($("#s"+i).attr("src") != ""){
            imgstr  =$("#s"+i).attr("data-imgid")+",";
            imgids  +=imgstr;
        }
    }
    console.log(imgids)
}
</script>