/**
 * Created by ZL on 2017/3/23.
 */





//1. 获取屏幕像素比值    1/像素比值   1/2=0.5

var num = 1/window.devicePixelRatio;

/*
//2. 根据像素比值动态生成视口标签设置最佳视口大小

document.write('<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale='+num+', maximum-scale='+num+', minimum-scale='+num+'",user-scalable=no/>');
*/

//3.获取屏幕的十分之的宽

var fontSize = document.documentElement.clientWidth/10;

//4. 通过取巧的方式将页面的1%大小设置为html的字号

document.getElementsByTagName('html')[0].style.fontSize = fontSize+'px';
//获取url中的参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) return unescape(r[2]); return null; //返回参数值
}

/**
 *   假设设计稿宽750  里面有一个375的盒子    这个盒子在设计稿中占50%
 *   因此在任何设备下这个盒子都应该占屏幕的50%
 *   放在1242的设备里面 这个盒子宽多少？
 *   rem永远是相对于html标签的字号
 *   1rem 就是一个html的字号
 *   html的字号是页面的是1%
 *   50rem、
 *    1%不可取因为字号小于12依然是12像素。
 * 可以用10%  20%
 *  快速计算元素的rem是多少。   元素的宽高/设计稿的1/10
 *   元素宽160    设计稿是1242   rem单位就是 160/124.2 rem
 *
 */
/*底部页面  跳转*/
$('.bo-list li').eq(0).click(function(){
    $('.bo-list li').eq(1).find('img').attr('src','../images/b2.jpg');
    window.location.href = './index.html';
});
$('.bo-list li').eq(1).click(function(){
	layer.msg("请下载APP端体验更多功能...",{time:1*1000},function(){
		console.log("跳转了");
	});
});
$('.bo-list li').eq(2).click(function(){
    $('.bo-list li').eq(1).find('img').attr('src','../images/b2.jpg');
    window.location.href = './ShopcCar.html';
});
$('.bo-list li').eq(3).click(function(){
    var userToken	= sessionStorage.getItem("token");
    if(userToken=="" || userToken==null || userToken==undefined){
        layer.msg("请在公众号搜索【黑谷商城】进行登录..",{time:1000});
    }
    $('.bo-list li').eq(1).find('img').attr('src','../images/b2.jpg');
    window.location.href = './myFollow.html';
});
$('.bo-list li').eq(4).click(function(){
    $('.bo-list li').eq(1).find('img').attr('src','../images/b2.jpg');
    window.location.href = './my.html';
});
