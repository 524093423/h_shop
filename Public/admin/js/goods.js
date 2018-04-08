$(function () {
    $("#sub").click(function () {
        addgoods();
    });
});
function addgoods() {
    getUploadImg();
    if ($("#SelectClass").val() == 0) {
    	layer.msg('请选择商品分类', {icon: 3});
        return false;
    }
    else if (!veriform('godstitle')) {
        layer.msg('商品名称不能为空', {icon: 3});
        return false;
    }else if($("#s").attr("src") == ""){
    	layer.msg('商品列表图不能为空', {icon: 3});
        return false;
    }else if($("#gprice").val()==""){
    	layer.msg('商品的价格区间不可为空', {icon: 3});
    	return false;
    }else if($("#gnum").val()=="" || $("#gnum").val()==0){
        layer.msg("商品库存不可为空",{icon:3});
        return false;
    }else if($("#contents").val()==""){
    	layer.msg('描述不能为空', {icon: 3});
        return false;
    }else if($("#cover").attr("src") == ""){
    	layer.msg('视频封面图不能为空', {icon: 3});
        return false;
    }else if($("#sf").attr("src")==""){
    	layer.msg('商品视频描述不可为空', {icon: 3});
        return false;
    }
    else {
        //intlbl: $(".SelectZS").val(),intlpaybl: $(".SelectSY").val(),isup:$("#isup").val(),
    	var flashs=$("#sf").attr("src");
        $.post("./admin.php/Goods/AdGoods_ajax", { godsclass: $("#SelectClass").val(), godstitle: $("#godstitle").val(), flash: flashs,
            content:$("#contents").val(), intpay: gethone('intpay'),gprice:$("#gprice").val(),thumb:$("#s").attr("src"),
            home: $("#home").val(),bids:bid, cover:$("#cover").attr("src"),gnum:$("#gnum").val(),isup:$("#isup").val(),uploadimg:imgids,delimg:delimgid
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