/*
*获取商品信息
*创建时间 ：2017.05.26
*/
var     myScroll,
    pullDownEl, pullDownOffset,
    pullUpEl, pullUpOffset,
    generatedCount = 0;
//获取首页【猜你喜欢】
function getHomeGoods(classid="",bid="",city="",page="",search="",sale="",unit="",state=1){
		$.ajax({
			url:webIp+"Goods/GetGoods?classid="+classid+"&city="+city+"&search="+search+"&sale="+sale+"&unit="+unit+"&bid="+bid+"&page"+page,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success:function(result){
				var html = "";
				//加载过程中
				var index = layer.load(3);
				//alert(result.code);
				var GoodsList	= result.info;
				if(result.code=="200" && !$.isEmptyObject(GoodsList)){
					layer.close(index);
				}else{
					layer.msg('正在努力加载..');
					layer.close(index);
					return false;
				}
				console.log(GoodsList);
				for(var i=0;i<GoodsList.length;i++){
					html+="<li><img src="+GoodsList[i]['listimg']+" alt="+GoodsList[i]['goodsid']+" /><p class=\"clearfix\"><span>"+GoodsList[i]['gname']+"</span><span class=\"money\">¥<b>"+GoodsList[i]['price']+"</b></span></p></li>";
				}
				if(state==1){
					$(".sh-list").append(html);
				}else{
					$(".sh-list").html(html);
				}
				$(".sh-list li").click(function(){
					var index = layer.open({
          			  title:false,
          			  type: 2,
          			  closeBtn:0,
          			  shift:1,
          			  content: "./productDetails（feng）.html?id="+$(this).find("img").attr("alt"),
          			  area: ["100%","100%"],
          			  maxmin: true,
          			  success:function(){
          				  $('.layui-layer-min').hide();
          				  $('.layui-layer-max').hide(); 
          			  },
          			  end: function () {
          				  //OrderSearch();
          				  //orderFunc().setAddress();
          				//$(".ud-nike b").text(sessionStorage.getItem('userName'));
          	            }
          			});
				});
			},
			error:function(){
				layer.msg('敬请期待');
			}
		
		});
}
//商品详情
function GetProductDetail(goodsid,state=""){
	if(state==2){
		setGwOrder();//购物车结算
		return false;
	}
	$.getJSON(webIp+"Goods/GetGoodsDetail?callback=?&goodsid="+goodsid+"&token="+sessionStorage.getItem('token'),function(result){
		//加载过程中
		var Goodsdetail	= result.info;
		if(state == 1){
			goodsInfo	=  Goodsdetail;
			console.log(goodsInfo);
			setljOrder();//立即购买
		}else{
			var index = layer.load(3,{time:2*1000});
			if(result.code=="200" && !$.isEmptyObject(Goodsdetail)){
				layer.close(index);
			}else{
				layer.msg('敬请期待');
				return false;
			}
			//设置商家logo
			$(".logo").find("img").attr("src",Goodsdetail['shopimg']);
			$(".logo").click(function(){
				window.location.href="./Store.html?shopid="+Goodsdetail['bid'];
			});
			//设置视频
			var goodsvideo	= Goodsdetail['flash'];
			$("video").attr("src",goodsvideo);
			//设置是否收藏
			var ishouse	= Goodsdetail['ishouse'];
			if(ishouse==1){
				$(".cang").find("img").attr("src","../images/m9.png");
			}else{
				$(".cang").find("img").attr("src","../images/cang.png");
			}
			//设置商品名称  价格 浏览量
			var goodsname	= Goodsdetail['goodsname'];
			var goodsprice	= Goodsdetail['price'];
			var goodsviews	= Goodsdetail['views'];
			$(".pinBox").find("h3").html(goodsname);
			$(".pinBox").find("b").html(goodsprice);
			$(".pinBox").find("h5").html(goodsviews);
			//设置商家客服
			var id	= Goodsdetail['seller_token'];
			var shopname	= Goodsdetail['name'];
			$(".hxc").find("img").attr("alt",id);
			$(".hxc").find("img").attr("data-shopname",shopname);
			//设置商品评论条数 和评论记录
			var rate		= Goodsdetail['rate'];
			var ratenum		= rate['num'];
			$(".appraise").find("h3 b").html(ratenum);
			var rateList 	= rate['list'];
			var rateHtml	= "";
			//设置加入购物车弹出
			$(".pro-tuwen").find("img").attr("src",Goodsdetail['cover']);
			$(".pro-tuwen").find("b").html(goodsprice);
			$(".pro-tuwen").find("i").html(Goodsdetail['goodsnum']);
			console.log(goodsname);
			//var ratelv	= "<img src=\"../images/redxing.png\" alt=\"\"/>";
			for(var i=0;i<rateList.length;i++){
				var ratelv	= "";
				for(var j=1;j<=rateList[i]['ratelevel'];j++){
					ratelv +="<img src=\"../images/redxing.png\" alt=\"\"/>";
				}
				rateHtml+="<li><p class=\"clearfix p1\"><img src=\"../images/tou.png\" alt=\"\"/><span>"+rateList[i]['phe']+"</span><i>"+rateList[i]['ratetime']+"</i></p>";
				rateHtml+="<p class=\" p2\">"+rateList[i]['rateinfo']+"</p><p class=\"clearfix p3\">"+ratelv;
				rateHtml+="</p></li>";
			}
			$(".app-list").html(rateHtml);
		}
	});
	//设置立即购买订单详情页
	function setljOrder(){
		var orderHtml	= "";
		orderHtml	+='<div class="car-list"><div class="car-ti "><div class="wrap clearfix"><div class="dianpu-log"><img src="../images/dainpu.png" alt=""/>';
		orderHtml	+='</div><div class="car-name" ><p>'+goodsInfo['seller']+'</p></div></div></div>';
		orderHtml	+='<ul class="car-art con-list">';
		orderHtml	+='<li><div class="li-box clearfix wrap"><div class="li-pic">';
		orderHtml	+='<img src='+goodsInfo['cover']+' class="cover" alt='+goodsid+' data-jf='+goodsInfo['jfnum']+' data-bid='+goodsInfo['bid']+' />';
		orderHtml	+='</div><div class="li-wo ">';
		orderHtml	+='<h3>'+goodsInfo['goodsname']+'</h3>';
		orderHtml	+='<p class="clearfix">';
		orderHtml	+='<span class="money danjia">¥<b>'+goodsInfo['price']+'</b></span>';
		orderHtml	+='<span class="num">X<b>1</b></span></p></div></div>';
		orderHtml	+='</li>';
		orderHtml	+='</ul>';
		orderHtml	+='<div class="freight clearfix wrap"><p>运费：</p><p>包邮</p></div>';
		orderHtml	+='<div class="leaveWorld clearfix wrap"><p>买家留言：</p>';
		orderHtml	+='<input type="text"  placeholder="选填，对本次交易的说明"/>';
		orderHtml	+='</div><div class="or-heji  clearfix con-heji">';
		orderHtml	+='<span class="heMoney">共计<i>1</i>件商品，合计：<b>¥<i>'+goodsInfo['price']+'</i></b></span></div></div>';
		sessionStorage.setItem("singleprice",goodsInfo['price']);//存储单个商品结算的价格
		sessionStorage.setItem("jfnum",goodsInfo['jfnum']);//存储单个商品结算可使用积分数量
		$(".carBox").html(orderHtml);
		orderFunc().money();
		orderFunc().GoodsSuan();
		var	usejfnum= goodsInfo['jfnum'];
		var jfprice	= (usejfnum/100).toFixed(2);//可使用积分数量
		$(".jfn").text(usejfnum);
		$(".jfp").text(jfprice);
		if(parseInt(goodsInfo['jfnum']) > 0){
			$(".score").find("p").text();
		}else{
			$(".score").attr("style","display:none");
		}
		$(".heji").find("span b").text(goodsInfo['price']);
	}
	//设置购物车结算
	function setGwOrder(){
		var idStr = JSON.parse(sessionStorage.getItem("cartIdStr"));//获取用户将要结算的数据
		console.log(idStr);
		var odid  = "";
		var str	  = "";
		for(var i=0;i<idStr.length;i++){
			str += idStr[i].split(",")[0]+",";
		}
		odid=(str.substring(str.length-1)==',')?str.substring(0,str.length-1):str;
		if(odid ==""){
			layer.msg("您提交的商品不存在....",{time:1000});
			return false;
		}else{
			$.getJSON(webIp+"Cart/CartList?callback=?&token="+sessionStorage.getItem('token')+"&idIn="+odid,function(result){
				//加载过程中
				var index = layer.load(3);
				//alert(result.code);
				var CartList	= result.info;
				if(result.code=="200" && !$.isEmptyObject(CartList)){
					layer.close(index);
				}else{
					layer.msg("系统繁忙,稍后重试..",{time:1000},function(){
						layer.close(index);	
					})
					return false;
				}
				var orderHtml	= "";
				var total	= 0;
				var userjf	= sessionStorage.getItem('intl');
				var jfz			= 0;
				for(var i=0;i<CartList.length;i++){
					orderHtml	+='<div class="car-list"><div class="car-ti "><div class="wrap clearfix"><div class="dianpu-log"><img src="../images/dainpu.png" alt='+CartList[i]['sellerid']+' />';
					orderHtml	+='</div><div class="car-name" ><p>'+CartList[i]['seller']+'</p></div></div></div>';
					orderHtml	+='<ul class="car-art con-list">';
					var goodsInfo	= CartList[i]['cart'];
					var goodsHtml	= "";
					for(var j=0;j<goodsInfo.length;j++){
						var jf = 0;
							jf = goodsInfo[j]['jfnum'];
							if(parseInt(userjf) >= parseInt(jf)){
								jf	= jf;
								userjf	= parseInt(userjf)- parseInt(jf);
								jfz	= parseInt(jfz) + parseInt(jf);
							}else{
								jf	= userjf;
								userjf	= parseInt(userjf)- parseInt(jf);
								jfz	= parseInt(jfz) + parseInt(jf);
							}
						console.log(jf);
						//var total += Number(zj);
						//console.log(total);
						goodsHtml	+='<li><div class="li-box clearfix wrap"><div class="li-pic">';
						goodsHtml	+='<img src='+goodsInfo[j]['listimg']+' class="cover" alt='+goodsInfo[j]['gctid']+' data-jf='+jf+' data-bid='+goodsInfo[j]['bid']+' />';
						goodsHtml	+='</div><div class="li-wo ">';
						goodsHtml	+='<h3>'+goodsInfo[j]['gname']+'</h3>';
						goodsHtml	+='<p class="clearfix">';
						goodsHtml	+='<span class="money danjia">¥<b>'+goodsInfo[j]['price']+'</b></span>';
						goodsHtml	+='<span class="num">X<b>'+goodsInfo[j]['gnum']+'</b></span></p></div></div>';
						goodsHtml	+='</li>';
					}
					orderHtml	+=goodsHtml;
					orderHtml	+='</ul>';
					orderHtml	+='<div class="freight clearfix wrap"><p>运费：</p><p>包邮</p></div>';
					orderHtml	+='<div class="leaveWorld clearfix wrap"><p>买家留言：</p>';
					orderHtml	+='<input type="text"  placeholder="选填，对本次交易的说明"/>';
					orderHtml	+='</div><div class="or-heji  clearfix con-heji">';
					orderHtml	+='<span class="heMoney">共计<i class="num-zl">1</i>件商品，合计：<b>¥<i class="money-zl">0</i></b></span></div></div>';
				}
				sessionStorage.setItem("singleprice",sessionStorage.getItem("cartTotal"));//存储购物车结算的价格
				sessionStorage.setItem("jfnum",jfz);//存储购物车结算可使用积分数量
				$(".carBox").html(orderHtml);
				var jfprice	= (jfz/100).toFixed(2);//可使用积分数量抵押的现金
				$(".jfn").text(jfz);
				console.log(jfz);
				$(".jfp").text(jfprice);
				if(parseInt(jfz) > 0){
					$(".score").find("p").text();
				}else{
					$(".score").attr("style","display:none");
				}
				$(".heji").find("span b").text(sessionStorage.getItem("cartTotal"));
				orderFunc().money();
				orderFunc().suanClick();
			})
		}
	}
}