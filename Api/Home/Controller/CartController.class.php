<?php
/**
 * 购物车
 *2017.02.11
 * 
 */
namespace Home\Controller;
use Think\Controller\RestController;
class CartController extends RestController {
    private $_USERID;
    public function __construct(){
        $token  = $_REQUEST['token'];
        $this->_USERID  = D("Register")->GetUserIdFromToken($token);
    }
	/**
	 * 加入购物车
	 * @userid  用户id
	 * @gid       商品id
	 * @gnum   商品数量
	 * @spid      规格id（无规格的 不传该参数）
	 * 更新 2017.05.27
	 */
	public function JoinCart(){
	    $userid = $this->_USERID;
	    if(empty($userid)){
	        $this->response(reTurnJSONArray("204","用户信息错误"));
	    }
		$curr['userid']   = $userid;
		$curr['godsid']   = I('post.gsid')?I('post.gsid'):I('get.gsid');
		$curr['gnum']     = I('post.gnum')?I('post.gnum'):I('get.gnum');
		$curr['specid']		= I('post.specid')?I('post.specid'):I('get.specid');
		$curr['gnum']     = preg_replace('/\$(\d+\.?\d+)/',"",intval($curr['gnum']));
		$curr['adtime']=date("Y-m-d H:i:s");
		if(!D("Common")->CheckGoodsIsSelf($userid,$curr['godsid'])){
		    $this->response(reTurnJSONArray("204","禁止恶意刷单"),I("get.callback"));
		}
		//print_r($curr);die;
		$data 		= D("Cart")->InsertCart($curr);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 购物车商品信息修改
	 * @gnum 商品数量
	 * 2017.02.13
	 */
	public function AlterCart(){
		$cartsave 		= $_POST['cart'];//I('post.cart');
//		$data[0]        = array("gctid"=>2,"gnum"=>12);
//		$data[1]        = array("gctid"=>3,"gnum"=>13);
//		$info['ct']     = $data;
//		$cartsave  =  json_encode($info);
//		Rewrite_log("cart",$cartsave);
		Rewrite_log("cart",$cartsave);
		if(!is_array($cartsave)){
			//$cartsave	= htmlspecialchars_decode($cartsave);
		    $cartsave 		= json_decode($cartsave);
		    $cartsave 		= json_array_cart($cartsave);
			//$cartsave		= $cartsave['ct'];
		}
		$data 				= D("Cart")->AlterCarts($cartsave);
		$this->response($data);
	}
	/**
	 * 购物车列表获取
	 * @userid 用户Id
	 * @page   分页页码
	 * 2017.02.13
	 */
	public function CartList(){
	    $userid = $this->_USERID;
	    if(empty($userid)){
	        $this->response(reTurnJSONArray("204","用户信息错误"));
	    }
		$page 		= I("get.page");
		$idstr      = I("get.idIn");
		$data 		= D("Cart")->GetCartList($userid,$page,"",$idstr);
		$this->response($data,I("get.callback"));
	}
	/**
	 * 购物车商品删除
	 * @gctid  购物车id
	 * @userid  用户Id
	 * 2017.02.13
	 */
	public function CartDel(){
		$gctid 		= I("post.gctid");
		$userid = $this->_USERID;
		if(empty($userid)){
		    $this->response(reTurnJSONArray("204","用户信息错误"));
		}
		$data 		= D("Cart")->DeleteCart($gctid,$userid);
		$this->response($data);
	}
}