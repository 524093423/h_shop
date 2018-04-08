/*
*获取商品信息
*创建时间 ：2017.05.26
*/
var     myScroll,
    pullDownEl, pullDownOffset,
    pullUpEl, pullUpOffset,
    generatedCount = 0;
//获取首页【猜你喜欢】
function getHomeGoods(classid="",bid="",city="",page="",search="",sale="",unit="",state=1,list=1){
		$.ajax({
			url:webIp+"Goods/GetGoods?classid="+classid+"&city="+sessionStorage.getItem("city")+"&search="+search+"&sale="+sale+"&unit="+unit+"&bid="+bid+"&page"+page+"&token="+sessionStorage.getItem("token"),
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
					layer.msg('还没有上传产品哦',{time:500});
					layer.close(index);
					return false;
				}
				for(var i=0;i<GoodsList.length;i++){
					html+="<li>" +
					"<img src="+GoodsList[i]['listimg']+" alt="+GoodsList[i]['goodsid']+" />" +
					"<p class=\"clearfix\">" +
					"<span>"+GoodsList[i]['gname']+"</span>" +
					"<span class=\"money clearfix\">" +
					"<b  class=\"hid\">¥"+GoodsList[i]['price']+"</b> " +
					"<i class=\"hid\">浏览量："+GoodsList[i]['views']+"</i></span>" +
					"</p>" +
					"</li>";
				}
				if(state==1){
					$(".sh-list").append(html);
				}else{
					$(".sh-list").html(html);
				}
				if(list==2){
					$(".sh-list li").click(function(){
    					window.location.href="./productDetails.html?id="+$(this).find("img").attr("alt");
    				});
				}
			},
			error:function(){
				layer.msg('敬请期待');
			}
		
		});
}
//商品详情
function GetProductDetail(goodsid,state="",gnums="",specid=""){
	if(state==2){
		setGwOrder();//购物车结算
		return false;
	}
	$.getJSON(webIp+"Goods/GetGoodsDetail?callback=?&goodsid="+goodsid+"&token="+sessionStorage.getItem('token'),function(result){
		//加载过程中
		sessionStorages();
		var Goodsdetail	= result.info;
		if(state == 1){
			goodsInfo	=  Goodsdetail;
			setljOrder(gnums,specid);//立即购买
		}else{
			//var index = layer.load(3,{time:2*1000});
			if(result.code=="200" && !$.isEmptyObject(Goodsdetail)){
				//layer.close(index);
			}else{
				layer.msg('敬请期待');
				return false;
			}
			//设置商家logo
			$(".logo").html("<span class=\"zl_go\">进入店铺</span><img src="+Goodsdetail['shopimg']+" />");
			$(".logo").click(function(){
				window.location.href="./Store.html?shopid="+Goodsdetail['bid'];
			});
			//设置商家电话
			var shoptel	= Goodsdetail['shoptel'];
			$(".tell").attr("href","tel:"+shoptel);
			//设置视频
			var goodsvideo	= Goodsdetail['flash'];
			if(goodsvideo=="" || goodsvideo == 0){
				$(".videos").remove();
			}else{
				var cover	= Goodsdetail['cover'];
				$("video").attr("src",goodsvideo);
				$("video").attr("poster",cover);
			}
			//设置轮播
			var goodscarousel	= Goodsdetail['carousel'];
			var carousehtml		= "";
			var big_pic = '';
			if(!$.isEmptyObject(goodscarousel)){
				for(var n=0;n<goodscarousel.length;n++){
					carousehtml+="<div class=\"swiper-slide \"><img src="+goodscarousel[n]['img']+" alt=\"\"/></div>";
					big_pic+="<div class=\"swiper-slide \"><img src="+goodscarousel[n]['img']+" alt=\"\"/></div>"
				}
				$(".swiper-wrapper_1").append(carousehtml);
				$(".swiper-wrapper_2").append(big_pic);
				var swiper2 = new Swiper('#swiper-container1', {
					pagination: '.swiper-pagination',
					loop:true,
					observer: true,//修改swiper自己或子元素时，自动初始化swiper
					observeParents: true//修改swiper的父元素时，自动初始化swiper
				});
				/*2017-10-19 ZL*/
				$('.swiper-wrapper_1  img').click(function(){

					var hig = $(window).height();
					var hig_2 = $('.big_pic').height();
					var hig_3 = (hig - hig_2)/2
					$('.mask_2').css('display','block');
					$('.big_pic').css('display','block');
					$('.big_pic').css('marginTop',hig_3+'px');
					$('body').css('overflow','hidden');
					var swiper_2 = new Swiper('#swiper-container2', {
						pagination: '.swiper-pagination_2',
						loop:true,
						observer: true,//修改swiper自己或子元素时，自动初始化swiper
						observeParents: true//修改swiper的父元素时，自动初始化swiper
					});
				});

				$('.mask_2').click(function(){
					$('.mask_2').css('display','none');
					$('.big_pic').css('display','none');

					$('body').css('overflow','auto');
				});
				$('#swiper-container2').click(function(){
					
					$('.mask_2').css('display','none');
					$('.big_pic').css('display','none');

					$('body').css('overflow','auto');
				})

			}
			//设置是否收藏
			var ishouse	= Goodsdetail['ishouse'];
			if(ishouse==1){
				$(".cang").find("img").attr("src","../images/m9.png");
			}else{
				$(".cang").find("img").attr("src","../images/cang.png");
			}
			//设置商品名称  价格 浏览量 商品描述 单位
			var goodsname	= Goodsdetail['goodsname'];
			var goodsprice	= Goodsdetail['price'];
			var goodsviews	= Goodsdetail['views'];
			var goodsdesc	= Goodsdetail['gdesc'];
			var goods_unit	= Goodsdetail['goods_unit'];
			var goodscity	= Goodsdetail['city'];
			$(".pin-location").find("b").html(goodscity);
			$(".pin-bot .llxl").find("i").html("/"+goods_unit);
			$(".gdesc").text(goodsdesc);
			$(".pinBox").find("h3").html(goodsname);
			$(".pin-bot .llxl").find("b").html(goodsprice);
			$(".pinBox").find("h5").html(goodsviews);
			//设置产品规格
			var goodsSpec	= Goodsdetail['specinfo'];
			var specHtml	= "";//页面html文本
			for(i=0;i<goodsSpec.length;i++){
				specHtml	+="<div class=\"wrap fen-btn\"><span data-specid="+goodsSpec[i]['specid']+" data-spece="+goodsSpec[i]['specprice']+" data-stock="+goodsSpec[i]['specnum']+">"+goodsSpec[i]['spectitle']+"&nbsp;&nbsp;&nbsp;￥"+goodsSpec[i]['specprice']+"</span></div>";
			}
			$(".fen-box").append(specHtml);
			$(".fen-box").find("h3 span").text(Goodsdetail['series']);
			//设置规格的js
			proFun().guiClick();
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
			$(".pro-tuwen").find("img").attr("src",Goodsdetail['cover']);
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
			proFun().wxgetConfig();
		}
	});
	//设置立即购买订单详情页
	function setljOrder(gnums="",specid=""){
		sessionStorage.setItem("jfnum",0);
		var goodprice	= 0;//产品价格
		var specname	= "";//规格名称
		var specjf		= "";//规格对应积分
		var specInfo	= goodsInfo['specinfo'];//规格数组
		for(i=0;i<specInfo.length;i++){
			if(specInfo[i]['specid']==specid){
				goodprice	= specInfo[i]['specprice'];
				specname	= specInfo[i]['spectitle'];
				specjf		= specInfo[i]['jfnum'] * gnums;
			}
		}
		var orderHtml	= "";
		orderHtml	+='<div class="car-list"><div class="car-ti "><div class="wrap clearfix"><div class="dianpu-log"><img src="../images/dainpu.png" alt=""/>';
		orderHtml	+='</div><div class="car-name" ><p>'+goodsInfo['name']+'</p></div></div></div>';
		orderHtml	+='<ul class="car-art con-list">';
		orderHtml	+='<li><div class="li-box clearfix wrap"><div class="li-pic">';
		orderHtml	+='<img src='+goodsInfo['cover']+' class="cover" alt='+goodsid+' data-jf='+goodsInfo['jfnum']+' data-bid='+goodsInfo['bid']+' />';
		orderHtml	+='</div><div class="li-wo ">';
		orderHtml	+='<h3>'+goodsInfo['goodsname']+'</h3>';
		orderHtml	+='<h4 class="hid">'+specname+'</h4>';
		orderHtml	+='<p class="clearfix">';
		orderHtml	+='<span class="money danjia">¥<b>'+goodprice+'</b></span>';
		orderHtml	+='<span class="num">X<b>'+gnum+'</b></span></p></div></div>';
		orderHtml	+='</li>';
		orderHtml	+='</ul>';
		orderHtml	+='<div class="freight clearfix wrap"><p>运费：</p><p>默认买家自付</p></div>';
		orderHtml	+='<div class="leaveWorld clearfix wrap"><p>买家留言：</p>';
		orderHtml	+='<input type="text"  placeholder="选填，对本次交易的说明"/>';
		orderHtml	+='</div><div class="or-heji  clearfix con-heji">';
		orderHtml	+='<span class="heMoney">共计<i>1</i>件商品，合计：<b>¥<i>'+parseFloat(goodprice*gnums).toFixed(2)+'</i></b></span></div></div>';
		$(".carBox").html(orderHtml);
		orderFunc().money();
		orderFunc().GoodsSuan();
		var userjf	= sessionStorage.getItem('intl');//用户积分
		var	goodsjfnum= specjf;//商品最多使用积分
		var usejfnum= 0;
		if(parseInt(userjf)>parseInt(goodsjfnum)){
			usejfnum	= goodsjfnum;
		}else{
			usejfnum	= userjf;
		}
		var jfprice	= (usejfnum/100).toFixed(2);//可使用积分数量低压的钱数
		$(".jfn").text(usejfnum);
		$(".jfp").text(jfprice);
		if(parseInt(usejfnum) > 0){
			$(".score").find("p").text();
		}else{
			$(".score").attr("style","display:none");
		}
		sessionStorage.setItem("singleprice",parseFloat(goodprice*gnums).toFixed(2));//存储单个商品结算的价格
		sessionStorage.setItem("jfnum",usejfnum);//存储单个商品结算可使用积分数量
		$(".heji").find("span b").text(parseFloat(goodprice*gnums).toFixed(2));

		// 点击问好
		$('.wenhao').click(function(){
			window.location.href = "./help.html"
		})
	}
	//设置购物车结算
	function setGwOrder(){
		sessionStorage.setItem("jfnum",0);
		var idStr = JSON.parse(sessionStorage.getItem("cartIdStr"));//获取用户将要结算的数据
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
				//var total	= 0;
				var userjf	= sessionStorage.getItem('intl');//用户积分数量
				var jfz			= 0;
				for(var i=0;i<CartList.length;i++){
					orderHtml	+='<div class="car-list"><div class="car-ti "><div class="wrap clearfix"><div class="dianpu-log"><img src="../images/dainpu.png" alt='+CartList[i]['sellerid']+' />';
					orderHtml	+='</div><div class="car-name" ><p>'+CartList[i]['seller']+'</p></div></div></div>';
					orderHtml	+='<ul class="car-art con-list">';
					var goodsInfo	= CartList[i]['cart'];
					var goodsHtml	= "";
					var total	= 0;
					var totalNum	=0;
					for(var j=0;j<goodsInfo.length;j++){
						var jf = 0;
							jf = goodsInfo[j]['jfnum'] * goodsInfo[j]['gnum'];//单个商品
							if(parseInt(userjf) >= parseInt(jf)){
								jf	= jf;
								userjf	= parseInt(userjf)- parseInt(jf);
								jfz	= parseInt(jfz) + parseInt(jf);
							}else{
								jf	= userjf;
								userjf	= parseInt(userjf)- parseInt(jf);
								jfz	= parseInt(jfz) + parseInt(jf);
							}
						//var total += Number(zj);
						goodsHtml	+='<li><div class="li-box clearfix wrap"><div class="li-pic">';
						goodsHtml	+='<img src='+goodsInfo[j]['listimg']+' class="cover" alt='+goodsInfo[j]['gctid']+' data-jf='+jf+' data-bid='+goodsInfo[j]['bid']+' data-specid='+goodsInfo[j]['specid']+' />';
						goodsHtml	+='</div><div class="li-wo ">';
						goodsHtml	+='<h3>'+goodsInfo[j]['gname']+'</h3>';
						goodsHtml	+='<h4 class="hid">'+goodsInfo[j]['spectitle']+'</h4>';
						goodsHtml	+='<p class="clearfix">';
						goodsHtml	+='<span class="money danjia">¥<b>'+goodsInfo[j]['price']+'</b></span>';
						goodsHtml	+='<span class="num">X<b>'+goodsInfo[j]['gnum']+'</b></span></p></div></div>';
						goodsHtml	+='</li>';
						/*total	= parseFloat(total).toFixed(2) + parseFloat(goodsInfo[j]['price']*goodsInfo[j]['gnum']).toFixed(2);
						totalNum	= totalNum + goodsInfo[j]['gnum'];*/
					}
					orderHtml	+=goodsHtml;
					orderHtml	+='</ul>';
					orderHtml	+='<div class="freight clearfix wrap"><p>运费：</p><p>默认买家自付</p></div>';
					orderHtml	+='<div class="leaveWorld clearfix wrap"><p>买家留言：</p>';
					orderHtml	+='<input type="text"  placeholder="选填，对本次交易的说明"/>';
					orderHtml	+='</div><div class="or-heji  clearfix con-heji">';
					orderHtml	+='<span class="heMoney">共计<i class="num-zl">'+totalNum+'</i>件商品，合计：<b>¥<i class="money-zl">'+total+'</i></b></span></div></div>';
				}

				sessionStorage.setItem("singleprice",sessionStorage.getItem("cartTotal"));//存储购物车结算的价格
				sessionStorage.setItem("jfnum",jfz);//存储购物车结算可使用积分数量
				$(".carBox").html(orderHtml);
				var jfprice	= (jfz/100).toFixed(2);//可使用积分数量抵押的现金
				$(".jfn").text(jfz);
				$(".jfp").text(jfprice);
				if(parseInt(jfz) > 0){
					$(".score").find("p").text();
				}else{
					$(".score").attr("style","display:none");
				}
				$(".heji").find("span b").text(sessionStorage.getItem("cartTotal"));
				orderFunc().money();
				orderFunc().suanClick();

				// 点击问好
				$('.wenhao').click(function(){
					window.location.href = "./help.html"
				})
			})
		}

	}
	function sessionStorages(){
		// 滚动高度 本地保存
		// 保存本地存储滚动的高度
		$(window).scroll(function() {
			sessionStorage.setItem('scroll_top2',$(window).scrollTop());
			//alert(  parseInt(sessionStorage.getItem('scroll_top')));
		});
		//获取本地存储的数据
		//保存 已经加载的数据
		var storage_html2 = $('.se-show').html();
		sessionStorage.setItem("list_id", storage_html2);
		var value2 = sessionStorage.getItem("list_id");
		if(value2 !== null && value2 !== undefined && value2 !== ''){
			// alert(111)
			setTimeout(function(){
				var h2 = sessionStorage.getItem('scroll_top2')
				//alert(typeof  parseInt(sessionStorage.getItem('scroll_top')))
				$(document).scrollTop(h2);
			},300)
		}
	}
}
//张国荣先生是一个100分的好人。曾经有一句话这么形容他：与他的才华相比，他的容貌不值一提，与他的人品相比，他的才华不值一提