
//=============================切换验证码======================================
function ToggleCode(obj, codeurl) {
    $(obj).attr("src", codeurl + "?time=" + Math.random());
}

//表格隔行变色
function tablecolor() {
    $(".msgtable tr:nth-child(odd)").addClass("tr_odd_bg"); //隔行变色
    $(".msgtable tr").hover(
			    function () {
			        $(this).addClass("tr_hover_col");
			    },
			    function () {
			        $(this).removeClass("tr_hover_col");
			    }
		    );
}
//==========================页面加载时JS函数结束===============================
//===========================系统管理JS函数开始================================
//Tab控制函数
function tabs(tabId, tabNum) {
    //设置点击后的切换样式
    $(tabId + " .tab_nav li").removeClass("selected");
    $(tabId + " .tab_nav li").eq(tabNum).addClass("selected");
    //根据参数决定显示内容
    $(tabId + " .tab_con").hide();
    $(tabId + " .tab_con").eq(tabNum).show();
}
//检查表单是否为空
function veriform(obj) {
    if ($("#" + obj).val().length < 1 || $("#" + obj).val()==0) {
        return false;
    }
    else {
        return true;
    }
}
function tip(obj, content) {
    $("#" + obj).poshytip({ className: 'tip-yellowsimple', content: content, showOn: 'none', alignTo: 'target', alignX: 'right', alignY: 'center', offsetX: 10, offsetY: 5, timeOnScreen: 3000 }).poshytip('show');
}
function checkvideo(obj,content) {
    if (!/\bhttp\:\/\/.*\b\.swf\b$/.test($("#" + obj).val())) {
        $("#" + obj).poshytip({ className: 'tip-yellowsimple', content: content, showOn: 'none', alignTo: 'target', alignX: 'right', alignY: 'center', offsetX: 10, offsetY: 5, timeOnScreen: 3000 }).poshytip('show').focus();
        return false;
    }
    return true;
}

//搜索
function search(page) {
    location.href = page+".aspx?key=" + $("#txtKeywords").val();
}

//类别变化
function selechange(page) {
    if ($("#Select1").val() == "0")
        location.href = page + ".aspx";
    else
        location.href = page + ".aspx?cid=" + $("#Select1").val();
}
//批量删除
function alldel(action, deletype) {
    var str = "";
    $("[name=ck]:checked").each(function () {
        str += $(this).val() + "-";
    });
    if (str == "") {
        $.jBox.error('请先选中', '错误')
    }
    else {
        var submit = function (v, h, f) {
            if (v == true) {
                $.jBox.tip("正在删除数据...", 'loading');
                $.post("scissors.axd", { Action: action, idstr: str.substring(0, str.length - 1), deletype: deletype }, function (data, textStatus) {
                    if (data.length > 0) {
                        location.reload();
                    }
                });
            }
            else
                return true;
        };
        $.jBox.confirm("此操作不可恢复，是否继续？？", "温馨提示", submit, { buttons: { '是': true, '否': false} });
    }
}
//没有系统分类这一条件的删除
function del(obj,action) {
    var submit = function (v, h, f) {
        if (v == true) {
            $.jBox.tip("正在删除数据...", 'loading');
            $.post("scissors.axd", { Action: action, idstr: obj.attr("rel") }, function (data, textStatus) {
                if (data.length > 0) {
                    obj.parent().parent().fadeOut("slow"); 
                     window.setTimeout(function () { $.jBox.tip('删除成功。', 'success'); }, 1);
                }
            });
        }
        else
            return true;
    };
    $.jBox.confirm("此操作不可恢复，是否继续？？", "温馨提示", submit, { buttons: { '是': true, '否': false} });
}
//有系统分类这一条件的删除  deletype和数据库里的proclass里的type对应
function delorsys(obj, action, deletype) {
    if (obj.attr("rel").split("-")[1] == 0) {
        $.jBox.info('系统内置内容不可删除', '提示');
    }
    else {
        var submit = function (v, h, f) {
            if (v == true) {
                $.jBox.tip("正在删除数据...", 'loading');
                $.post("scissors.axd", { Action: action, idstr: obj.attr("rel").split("-")[0], deletype: deletype }, function (data, textStatus) {
                    if (data.length > 0) { obj.parent().parent().fadeOut("slow"); window.setTimeout(function () { $.jBox.tip('删除成功。', 'success'); }, 1); }
                });
            }
            else
                return true;
        };
        $.jBox.confirm("删除该信息将删除所有相关联的信息？", "温馨提示", submit, { buttons: { '是': true, '否': false} });
    }
}
function allselecheck(obj) {
    if (obj.attr("checked") == true) {
        $("#seleallbybtton").find("span b").text("取消");
    }
    else {
        $("#seleallbybtton").find("span b").text("全选");
    }
    $(".cbk").each(function () {
        $(this).attr({ "checked": obj.attr("checked") });
    });
}
function changestate(obj, table, column, did) {
    $.ajax({
        type: "POST",
        dataType: "text",
        url: "/admin/server/comm.ashx",
        data: "type=changestate&table=" + table + "&column=" + column + "&id=" + did,
        beforeSend: function () {
            $(obj).children("img").attr("src", "/admin/images/load3.gif");
        },
        success: function (data) {
            if (data > 0) {
                $(obj).children("img").attr("src", "../Images/11.png");
            }
            else {
                $(obj).children("img").attr("src", "../Images/10.png");
            }
        }
    });
}
function changestatexml(obj, filename, xpath, column) {
    $.ajax({
        type: "POST",
        dataType: "text",
        url: "/admin/server/comm.ashx",
        data: "type=changestatexml&filename=" + filename + "&xpath=" + xpath + "&column=" + column,
        beforeSend: function () {
            $(obj).children("img").attr("src", "/admin/images/load3.gif");
        },
        success: function (data) {
            if (data > 0) {
                $(obj).children("img").attr("src", "../Images/11.png");
            }
            else {
                $(obj).children("img").attr("src", "../Images/10.png");
            }
        }
    });
}

