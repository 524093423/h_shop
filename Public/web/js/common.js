/**
 * Created by ZL on 2017/3/23.
 */





//1. 获取屏幕像素比值    1/像素比值   1/2=0.5

var num = 1/window.devicePixelRatio;

//2. 根据像素比值动态生成视口标签设置最佳视口大小

document.write('<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale='+num+', maximum-scale='+num+', minimum-scale='+num+'" />');

//3.获取屏幕的十分之的宽

var fontSize = document.documentElement.clientWidth/10;

//4. 通过取巧的方式将页面的1%大小设置为html的字号

document.getElementsByTagName('html')[0].style.fontSize = fontSize+'px';



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
