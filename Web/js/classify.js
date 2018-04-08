/*
*获取分类信息
*创建时间 ：2017.05.25
*/
//获取首页显示分类
function getHomeClassify(){
	$.getJSON(webIp+"Classify/GetHomeClassify?callback=?",function(result){
		var html = "";
		var index = layer.load(3);
		if(result.code=="200"){
			layer.close(index);
		}
		var classList	= result.info;
		for(var i=0;i<classList.length;i++){
			html+="<li><img src="+classList[i]['gimg']+" alt="+classList[i]['gcid']+" /><p>"+classList[i]['gcname']+"</p></li>";
		}
		$(".pic-list").html(html);
		$(".pic-list").find("li").click(function(){
			window.location.href="./search.html?classid="+$(this).find("img").attr("alt");
		});
	});
}