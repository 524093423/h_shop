$(function () {
    $("#sub").click(function () {
        addnews();
    });
});
function addnews() {
	//alert(specs.length);
	getCarousel();//return false;
	//getCarousel();return false;
	CheckName();
    if (states ==2) {
        tip('shoptitle', '店铺名称不可为空或店铺名已存在'); $("#shoptitle").focus();
        return false;
    }else if (!veriform('shopadd')) {
        tip('shopadd', '店铺地址不可为空'); $("#shopadd").focus();
        return false;
    }else if($("#s").attr("src") == ""){
    	tip('s', '店铺logo'); $("#s").focus();
        return false;
    }else if(arr.length ==0){
    	//tip('s1', '店铺轮播图不能为空'); $("#s1").focus();
    	$.jBox.tip("店铺轮播图不能为空", 'success');
    	return false;
    }else if(!veriform("shoppeople")){
    	tip('shoppeople', '店铺负责人'); $("#shoppeople").focus();
        return false;
    }else if(getEditorData()==""){
    	tip('content', '店铺描述不能为空'); $("#content").focus();
        return false;
    }else if(!veriform('shopadmin')){
    	tip('shopadmin', '店铺管理员账号不可为空'); $("#shopadmin").focus();
        return false;
    }else if(!veriform('shoppwd')){
    	tip('shoppwd', '店铺管理员密码不可为空'); $("#shoppwd").focus();
        return false;
    }
   // alert(123);return false;
    $.post("./admin.php/Shop/AdShop_ajax", { shoptitle: $("#shoptitle").val(), shopadd: $("#shopadd").val(), logo: $("#s").attr("src"),shopimgarray:arr, shoppeople: $("#shoppeople").val(),
        content: getEditorData(), shopadmin: $("#shopadmin").val(),shoppwd:$("#shoppwd").val(),search:$("#shopsearch").val(),shoptel:$("#shopTel").val(),shopid:$("#shopid").val(),type:types
    }, function (data, textStatus) {
        eval(arguments[0]);
    });
}
function veriform(reg){
	var text = $("#"+reg).val();
	if(text==""){
		return false;
	}else{
		return true;
	}
}