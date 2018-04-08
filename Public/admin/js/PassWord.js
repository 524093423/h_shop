function EditPassWord() {
    if (!veriform('OldPassWord')) {
        tip('OldPassWord', '请输入原始密码');
        $("#OldPassWord").focus();
        return false;
    }
    else if (!veriform('PassWord')) {
        tip('PassWord', '请输入新密码');
        $("#PassWord").focus();
        return false;
    }
    else if ($.trim($("#PassWord").val()).length < 6) {
        tip('PassWord', '密码为6—10位！');
        $("#PassWord").focus();
        return false;
    }
    else if (!veriform('PassWord1')) {
        tip('PassWord1', '请在此输入新密码');
        $("#PassWord1").focus();
        return false;
    }
    else if ($.trim($("#PassWord1").val()) != $.trim($("#PassWord").val())) {
        tip('PassWord1', '确认密码错误');
        $("#PassWord1").focus();
        return false;
    }
    else {
        $.post("./admin.php?c=Config&a=EditPassWord", { OldPassWord: $.trim($("#OldPassWord").val()), PassWord: $.trim($("#PassWord").val()), UserName: $.trim($("#UserName").val())}, function (msg) {
            	type = msg['type'];
            	mesage = msg['message'];
            	if(type == 200){
            		$.jBox.tip(mesage,"success");
            	}else{
            		$.jBox.tip(mesage,"error");
            	}
        },"json");
    }
}