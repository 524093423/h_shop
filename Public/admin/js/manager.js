$(function () {
    $("#sub").click(function () {
    	CheckAdmin();
        addnews();
    });
});
function addnews() {
	if(isrenew != 0){
		if (!veriform('shoppeople')) {
	        tip('shoppeople', '负责人名称不可为空'); $("#shoppeople").focus();
	        return false;
	    }else if(!veriform('shopTel')){
	    	tip('shopTel', '负责人联系方式不可为空'); $("#shopTel").focus();
	        return false;
	    }
		$.post("./admin.php/Manager/AdManager_ajax", { shopfzr: $("#shoppeople").val(), shoptel: $("#shopTel").val(), isrenew:isrenew
        }, function (data, textStatus) {
            eval(arguments[0]);
        });
		return false;
	}
	//alert(specs.length);
	//getSpec();return false;
    if ($("#SelectCate").val() == 0) {
        tip('SelectCate', '请选择管理员类型'); $("#SelectCate").focus();
        return false;
    }
    else if (!veriform('shoppeople')) {
        tip('shoppeople', '负责人名称不可为空'); $("#shoppeople").focus();
        return false;
    }else if(!veriform('shopTel')){
    	tip('shopTel', '负责人联系方式不可为空'); $("#shopTel").focus();
        return false;
    }else if(iscf !=0){
    	tip('shopadmin', '帐号不可为空或重复'); $("#shopadmin").focus();
    	return false;
    }else if(!veriform("shoppwd")){
    	tip('shoppwd', '账户密码不可为空'); $("#shoppwd").focus();
        return false;
    }
    else {
        $.post("./admin.php/Manager/AdManager_ajax", { groupid: $("#SelectCate").val(), shopfzr: $("#shoppeople").val(), shoptel: $("#shopTel").val(), shopadmin: $("#shopadmin").val(),
        	password: $("#shoppwd").val(),isrenew:isrenew
        }, function (data, textStatus) {
            eval(arguments[0]);
        });
    }
}
function veriform(reg){
	var text = $("#"+reg).val();
	if(text==""){
		return false;
	}else{
		return true;
	}
}
function gethone(regs) {
	var str = $("#"+regs).attr("checked");
	if(str == "checked"){
		return 1;
	}else{
		return 0;
	}
}