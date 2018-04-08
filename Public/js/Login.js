$(function () {
    var cdate = new Date();
    cdate.setFullYear(new Date().getFullYear() + 1);
    $("#UserName").val(Cookies.get("loginname"))
    $("#cbRememberId").attr("checked", Cookies.get("loginnamec"));
    $("#UserName, #PassWord,#txtCode").keydown(function () {
        if (window.event.keyCode == 13) {
            $("#sub").click();
        }
    });
    $("#sub").click(function () {
        if ($.trim($("#UserName").val()) == "") {
            $("#tip").text("请输入登陆账号").show();
            $("#UserName").focus();
            return false;
        }
        if ($.trim($("#PassWord").val()) == "") {
            $("#tip").text("请输入登陆密码").show();
            $("#PassWord").focus();
            return false;
        }
        if ($.trim($("#txtCode").val()) == "") {
            $("#tip").text("请输入验证码").show();
            $("#txtCode").focus();
            return false;
        }
        if ($("#cbRememberId").attr("checked")) {
            Cookies.set("loginnamec", true, cdate); Cookies.set("loginname", $.trim($("#UserName").val()), cdate);
        }
        else {
            Cookies.clear("loginnamec"); Cookies.clear("loginname");
        }
        $.jBox.tip("正在登录中，请稍后...", 'loading');
        $.post("scissors.axd", { Action: "UserLogin", UserName: $.trim($("#UserName").val()), PassWord: $.trim($("#PassWord").val()), checkcode: $.trim($("#txtCode").val()), ip:returnCitySN.ip }, function (data, textStatus) {
            eval(arguments[0]);
        });
    });
});
