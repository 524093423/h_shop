<?php
/**
 * 商品
 * 2017.05.18
 */
namespace Home\Controller;
use Think\Controller\RestController;
class GoodsController extends RestController {
    private $_USERID;
    public function __construct(){
        $token  = $_REQUEST['token'];
        $this->_USERID  = D("Register")->GetUserIdFromToken($token);
		if(!empty($this->_USERID)){
			session(array('expire'=>120));
			session("VGOTCN_OnLineCount",$this->_USERID);
		}
		session("VGOTCN_OnLineCount","yk");
    }
  /**
   * 获取全部商品 商品列表
   * @classid  分类id
   * @page    分页id
   * @search  搜索
   * @#num   每页显示条数
   * @sale      销量 【低到高1，高到低2】
   * @unit      价格 【低到高1，高到低2】
   * @sels        是否热销【1否2是】
   * @ptes       是否推荐【1否2是】
   * @bid        商家id
   * 2017.05.18
   */
	public function GetGoods(){
	    //print_r($_REQUEST);die;
	    $userid     = $this->_USERID;
		$classid 	= intval(I("get.classid"));//分类信息
		$page 		= intval(I("get.page"));//页码
		$search 	= I("get.search");//关键字
		$searchtype = intval(I("get.searchtype"));//关键字搜索类型
		$num 		= I('get.num')?I("get.num"):40;
		$sale 		= intval(I('get.sale'));//销量
		$unit 		= intval(I('get.unit'));//价格
		$sels 		= I('get.sels');//热销
		$ptes 		= I('get.ptes');//推荐
		$bid        = I('get.bid');//商家id
		if(strpos(I("get.city"), "市")){
		    $city = str_replace("市", "", I("get.city"));
		}else{
			if(strpos(I("get.city"),"全国")){
				$city	= "";
			}else{
				$city = I("get.city");
			}
		}
		$callback   = I("get.callback");
		$data 		=D("Goods")->GetGoodsInfo($classid,$page,$search,$num,$sale,$unit,$sels,$ptes,$bid,$city,$callback,$userid);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 获取商品详情
	 * 2017.05.18
	 * 更新 token 2017.05.22
	 */
	public function GetGoodsDetail(){
	    $userid = $this->_USERID;
	    $goodsId   = intval(I('get.goodsid'));
	    $data      = D("Goods")->GoodsDetail($userid,$goodsId);
	    $this->response($data,I("get.callback"));
	}
	/**
	 * 获取某个商品是否被用户收藏
	 * @userid  用户id
	 * @godsid 商品id
	 * 待删除
	 */
	public function GetGoodsHouse(){
	    $userid = $this->_USERID;
	    if(empty($userid)){
	        $this->response(reTurnJSONArray("204","用户信息错误"));
	    }
		$godsid= $_GET['godsid'];
		$data   = D("Goods")->JudgeGoodsIsHouse($userid,$godsid);
		$this->response($data);
	}
	/**
	 * 将某个商品加入自己的收藏
	 * @userid  用户Id
	 * @goodsid 商品Id
	 * 2017.05.18
	 */
	public function SetGoodsHouse(){
		$userid = $this->_USERID;
		if(empty($userid)){
		    $this->response(reTurnJSONArray("204","用户信息错误"));
		}
		$godsid= I("post.goodsid")?I("post.goodsid"):I("get.goodsid");
		if(!D("Common")->CheckGoodsIsSelf($userid,$godsid)){
		    $this->response(reTurnJSONArray("204","禁止恶意刷单"));
		}
		$data   = D("Goods")->SetGoodsIsHouse($userid,$godsid);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 我的收藏
	 * @userid  用户Id
	 * @page   页码
	 */
	public function MyHouse(){
		$userid = $this->_USERID;
		if(empty($userid)){
		    $this->response(reTurnJSONArray("204","用户信息错误"));
		}
		$page   = I("get.page");
		$data = D("Goods")->GetMyHouse($userid,$page);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 宝贝评价列表信息
	 * @godsid  商品信息
	 * @page     分页页码
	 * @state     1商品详情页 
	 * 更新 2017.05.22
	 */
	public function GetRate(){
		$goodsid 		= I("get.goodsid");
		$page 			= I("get.page");
		$state 			= I("get.state");
		$data 			= D("Goods")->m_GetRate($goodsid,$page,$state);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 订单评价
	 * @user_id 用户id
	 * @good_id 商品id
	 * @rate_info 评价内容
	 * 更新 2017.05.22
	 */
	public function RateAdd(){
		$user_id = $this->_USERID;
		if(empty($user_id)){
		    $this->response(reTurnJSONArray("204","用户信息错误"));
		}
		$order_id  = I('post.orderid');
		$rate_info  = I('post.rate_info');
		$rate_level= I('post.ratelevel');
		if(!$user_id || !$order_id  || !$rate_info || !$rate_level){
			$data['code'] = 204;
			$data['message'] ="请完善您的填写信息";
			$this->response($data);
			return false;
		}else {
			$data = D('Goods')->getAddRate($user_id,$order_id,$rate_info,$rate_level);
		}
		$this->response($data);
	}
	/**
	 * 获取商品分类
	 * 2017.05.23
	 */
	public function GetGoodsClass(){
	    $data  = D("Goods")->ClassifyList();
	    if(is_bool($data)){
	        $return['code']    = "204";
	        $return['message'] = "找不到了";
	    }else{
	        $return['code']    = "200";
	        $return['message'] = "上传商品分类信息列表";
	        $return['info']    = unlimitedForLevel1($data);
	    }
	    $this->response($return);
	}
	/**
	 * 商品信息的上传 ios
	 * 2017.05.24
	 */
	public function AddGoodsInfo(){
        Rewrite_log("./pingguouio.txt",json_encode($_POST));
        Rewrite_log("./pingguouiofile.txt",json_encode($_FILES));
	   set_time_limit(0);
	   $user_id    = $this->_USERID;
	   $sellerid   = I("post.sellerid");
	   $bid        = I("post.bid");
	   $classid    = I("post.classid");//分类id
	   $goodsname  = I("post.goodsname");//商品名称
	   $goodsprice = I("post.price");//商品价格
	   $goodsnum   = I("post.gnum")?I("post.gnum"):0;//商品库存
	   $goodsdesc  = $_POST['goodsdesc'];//商品描述
	   $goodsimg   = $_POST['cover'];//商品封面
	   //$goodsflash = $_POST['flash'];//商品视频
       $goodsflash	= $_FILES?$_FILES:I("post.flash");
       /*$this->response(reTurnJSONArray(200,$_FILES));
       print_r($_FILES);die;*/
	   $series			= I("post.series");//系列
	   $goodsid		= I("post.goodsid");//商品id
	   $goods_unit	= I("post.goods_unit");//商品计量单位
	   $goodscarousel	= $_POST['imgid'];//商品轮播图ida
	   $delImgid		= $_POST['delimgid'];//商品轮播图id
	   $specidStr		= I("post.specid");//产品规格信息
	   if(empty($user_id) || empty($bid) || empty($sellerid)){
	       $this->response(reTurnJSONArray("204","用户信息有误"));
	   }
	   if(empty($bid) || empty($classid) || empty($goodsname) || empty($goodsdesc) || empty($goods_unit)){
	       $this->response(reTurnJSONArray("204","商品基本信息有误"));
	   }
	   /*if(empty($goodsprice)){
	       $this->response(reTurnJSONArray("204","商品价格信息有误"));
	   }*/
	   if(empty($goodsid)){
		   if(empty($goodsimg)){
			   $this->response(reTurnJSONArray("204","商品视图信息有误"));
		   }
	   }
		if(empty($goodscarousel) && empty($goodsflash)){
			$this->response(reTurnJSONArray("204","商品视频和轮播请选择任意一项"));
		}
		if(empty($specidStr)){
			$this->response(reTurnJSONArray("204","规格信息不可为空"));
		}
		if(!empty($delImgid)){
			$this->delImg($delImgid);
		}
		if(!empty($goodsid)){
			$data   = D("Goods")->NewUpdateGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goodsid,$goods_unit,$goodscarousel,$specidStr,$series);
		}else{
			$data   = D("Goods")->NewAddGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goods_unit,$goodscarousel,$specidStr,$series);
		}
	   $this->response($data);
	}
	/**
	 * 删除商品信息
	 * 2017.05.24
	 */
	public function DelGoods(){
	    $userid    = $this->_USERID;
	    $sellerid   = I("get.sellerid");
	    $bid        = I("get.bid");
	    $goodsid    = I("get.goodsid");
	    $data       = D("Goods")->DelGoodsInfo($userid,$sellerid,$bid,$goodsid);
	    $this->response($data);
	}
	/**
	 * 获取修改页面的商品资料
	 */
	public function GetUpdateGoodsInfo(){
		$goodsid= I("get.goodsid");
		if(empty($goodsid)){
			$this->response(reTurnJSONArray("204","提交信息有误"));
		}
		$data	= D("Goods")->getUpdateGoodsInfo($goodsid);
		$this->response($data);
	}
	/**
	 * 商品轮播图上传
	 */
	public function UploadImg(){
		$img	= $_POST['goodscarousel'];
		if(empty($img)){
			$this->response(reTurnJSONArray("204","图片信息为空"));
		}
		$data	= D("goods")->UploadImg($img);
		$this->response($data);
	}
	/**
	 * 轮播图删除api
	 */
	public function delImg($imgid){
		//$imgid	= I("post.imgid");
		if(empty($imgid)){
			return false;
			$this->response(reTurnJSONArray("204","删除图片不存在"));
		}
		$model	= M("goods_photo");
		$result = $model->where("gp_id in($imgid)")->delete();//待修改
		if(!is_bool($result)){
			return false;
			$this->response(reTurnJSONArray("200","图片删除成功"));
		}
		return false;
		$this->response(reTurnJSONArray("204","图片删除失败"));
	}
	/**
	 * 商品信息的上传  安卓
	 * 2017.05.24
	 */
	public function UpdateGoodsInfo(){
        Rewrite_log("./anzhuio.txt",json_encode($_POST));
        Rewrite_log("./anzhuiofile.txt",json_encode($_FILES));
		set_time_limit(0);
		$user_id    = $this->_USERID;
		$sellerid   = I("post.sellerid");
		$bid        = I("post.bid");
		$classid    = I("post.classid");//分类id
		$goodsname  = I("post.goodsname");//商品名称
		$goodsprice = I("post.price");//商品价格
		$goodsnum   = I("post.gnum")?I("post.gnum"):0;//商品库存
		$goodsdesc  = $_POST['goodsdesc'];//商品描述
		$goodsimg   = $_POST['cover'];//商品封面
		//$goodsflash = $_POST['flash'];//商品视频
		$goodsid		= I("post.goodsid");//商品id
		$series			= I("post.series");//系列
		$goodsflash	= $_FILES ? $_FILES:I("post.flash");
		$goods_unit	= I("post.goods_unit");//商品计量单位
		$goodscarousel	= $_POST['imgid'];//商品轮播图id
		$delImgid		= $_POST['delimgid'];//商品轮播图id
		$specidStr		= I("post.specid");//产品规格信息
		if(empty($user_id) || empty($bid) || empty($sellerid)){
			$this->response(reTurnJSONArray("204","用户信息有误"));
		}
		if(empty($bid) || empty($classid) || empty($goodsname) || empty($goodsdesc) || empty($goods_unit)){
			$this->response(reTurnJSONArray("204","商品基本信息有误"));
		}
		/*if(empty($goodsprice)){
			$this->response(reTurnJSONArray("204","商品价格信息有误"));
		}*/
		if(empty($goodsid)){
			if(empty($goodsimg)){
				$this->response(reTurnJSONArray("204","商品视图信息有误"));
			}
		}
		if(empty($goodscarousel) && empty($goodsflash)){
			$this->response(reTurnJSONArray("204","商品视频和轮播请选择任意一项"));
		}
		if(empty($specidStr)){
			$this->response(reTurnJSONArray("204","规格信息不可为空"));
		}
		if(!empty($delImgid)){
			$this->delImg($delImgid);
		}
		if(!empty($goodsid)){
			$data   = D("Goods")->NewUpdateGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goodsid,$goods_unit,$goodscarousel,$specidStr,$series);
		}else{
			$data   = D("Goods")->NewAddGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goods_unit,$goodscarousel,$specidStr,$series);
		}
		$this->response($data);
	}
	/**
	 * 添加规格
	 */
	public function AddSpec(){
		$userid    = $this->_USERID;
		$postData	= I("post.");
		//验证用户是否登录
		if(empty($userid)){$this->response(reTurnJSONArray("204","请先登录"));}
		//验证提交数据是否为空
		if(empty($postData['spectitle'])){$this->response(reTurnJSONArray("204","规格描述不可为空"));}
		if(empty($postData['specprice'])){$this->response(reTurnJSONArray("204","规格价格不可为空"));}
		if(empty($postData['specnum'])){$this->response(reTurnJSONArray("204","规格库存不可为空"));}
		//添加数据
		$model	= D("Goods");
		$data 	= $model->AddSpec($postData,$userid);
		$this->response($data);
	}
	/**
	 * 删除规格
	 */
	public function DelSpec(){
		$userid    = $this->_USERID;
		$postData	= I("post.");
		//验证用户是否登录
		if(empty($userid)){$this->response(reTurnJSONArray("204","请先登录"));}
		//验证删除规格
		if(empty($postData['specid'])){$this->response(reTurnJSONArray("204","请选择要删除的规格"));}
		//删除数据
		$model	= D("Goods");
		$data 	= $model->DelSpec($postData,$userid);
		$this->response($data);
	}
}