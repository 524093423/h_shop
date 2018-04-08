/*
*获取店铺信息
*创建时间 ：2017.05.26
*/
var     myScroll,
    pullDownEl, pullDownOffset,
    pullUpEl, pullUpOffset,
    generatedCount = 0;
//获取店铺列表
function getShop(city="",page="",search="",state=1){
		$.ajax({
			url:webIp+"Seller/GetShopList?city="+city+"&search="+search+"&page"+page,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success:function(result){
				var html = "";
				//加载过程中
				var index = layer.load(3,{time:2*1000});
				//alert(result.code);
				var ShopList	= result.info;
				if(result.code=="200" && !$.isEmptyObject(ShopList)){
					layer.close(index);
				}else{
					layer.msg('正在努力加载..');
					return false;
				}
				console.log(ShopList);
				//return false;
				for(var i=0;i<ShopList.length;i++){
					html+="<li><div class=\"wrap clearfix\"><div class=\"ne-pic\"><img src="+ShopList[i]['shopimg']+" alt="+ShopList[i]['bid']+" />";
					html+="</div><div class=\"ne-wo\"><h3>"+ShopList[i]['seller']+"</h3><h4>"+ShopList[i]['desc']+"</h4></div>";
					html+="<div class=\"ne-jian\"><img src=\"../images/youjian.png\" alt=\"\" /></div></div></li>";
				}
				if(state==1){
					$(".new1").append(html);
				}else{
					$(".new1").html(html);
				}
				$(".new1 li").click(function(){
					window.location.href="./Store.html?shopid="+$(this).find("img").attr("alt");
				});
			},
			error:function(){
				layer.msg('敬请期待');
			}
		
		});
}
//店铺详情
function GetShopDetail(shopid){
	$.getJSON(webIp+"AppLogin/user_seller_detail?callback=?&b_id="+shopid+"&token="+sessionStorage.getItem('token'),function(result){
		//加载过程中
		var index = layer.load(3,{time:2*1000});
		Shopdetail	= result.info;
		//console.log(Shopdetail);return false;
		if(result.code=="200" && !$.isEmptyObject(Shopdetail)){
			layer.close(index);
		}else{
			layer.msg('敬请期待');
			return false;
		}
		//设置店铺详情头部
		var banner2Img	= Shopdetail['shopback'];
		var shpicLogo	= Shopdetail['shoplogo'];
		var followNum	= Shopdetail['follow'];
		var shopdesc	= Shopdetail['bdesc'];
		var isfollow	= Shopdetail['is_follow'];
		var sellertoken	= Shopdetail['seller_token'];
		sellerid	= Shopdetail['seller_id'];
		$(".to-ti").text(Shopdetail['name']);
		$(".bannerImg").attr("src",banner2Img);
		$(".sh-pic").find("img").attr("src",shpicLogo);
		$(".sh-pic").find("img").attr("alt",sellertoken);
		$(".sh-num").find("span b").html(followNum);
		$(".sto-wo").find('p').html(shopdesc);
		if(isfollow	== '0' || isfollow	== 0){
            $(".guangzhu").find("p").eq(1).removeClass('look-on');
            $(".guangzhu").find("p").eq(1).text('+  关注')
        }else{
        	$(".guangzhu").find("p").eq(1).addClass('look-on');
        	$(".guangzhu").find("p").eq(1).text('已关注')
        }
	});
}