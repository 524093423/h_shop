/*
*系统设置
*20170527
*/
/**
 * 关于我们
 */
function getAboutUs(){
	$.getJSON(webIp+"System/About_us?callback=?",function(result){
		var html = "";
		var index = layer.load(3);
		if(result.code=="200"){
			layer.close(index);
		}
		var about_us	= result.info;
		console.log(about_us)
		html+="<h4>"+about_us['title']+"</h4><p>"+about_us['content']+"</p>";
		$(".ti").append(html);
	});
}
/**
 * 帮助
 */
function getHelp(){
	$.getJSON(webIp+"System/help?callback=?",function(result){
		var html = "";
		var index = layer.load(3);
		if(result.code=="200"){
			layer.close(index);
		}
		var help	= result.info;
		for(var i=0;i<help.length;i++){
			html+="<li><h3>"+help[i]['title']+"</h3><div class='help-wo wrap'><p>"+help[i]['content']+"</p></div></li>";
		}
		$(".help-list").append(html);
	});
}
/**
 * 用户协议
 */
function getuserProtocol(){
	$.getJSON(webIp+"System/userProtocol?callback=?",function(result){
		var html = "";
		var index = layer.load(3);
		if(result.code=="200"){
			layer.close(index);
		}
		var userProtocol = result.info;
		html+=userProtocol['content'];
		$(".content").append(html);
		$("p").addClass('wrap user-p');
	});
}
