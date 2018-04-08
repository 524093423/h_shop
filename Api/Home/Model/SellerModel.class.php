<?php
/**
 *商家模型
 *2017.05.23
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class SellerModel extends Model {
	private $_AdminUrl;
	/**
	 * 通过商家id获取商家的铺名
	 * @bid 商家id
	 * 2017.05.23 
	 */
	public function GetSellerName($bid){
		$model = M("business_info");
		$result 	= $model->field("`name`")->where("b_id=$bid")->find();
		return $result['name'];
	}
	/**
	 * 获取店铺列表
	 * @param string $search
	 * @param string $city
	 * @param unknown $PageIndex
	 * 2017.05.23
	 */
	public function ShopList($search="",$city="",$PageIndex){
	    if(!empty($city)){
	        $WhereStr  = " AND INSTR(`h_seller_detail`.city,'".$city."')";
	    }
	    if(!empty($search)){
	        $WhereStr  = " AND INSTR(`business_info`.`name`,'".$search."')";
	    }
	    $PageSize  = 10;
	    $this->_AdminUrl = D("Common")->getUrl();
	    $joinid = "b_id";
	    $join = "LEFT JOIN h_seller_detail ON business_info.seller_id = h_seller_detail.seller_id";
	    $Coll="business_info.b_id AS bid,h_seller_detail.city,business_info.bdesc AS `desc`,REPLACE(`blogo`,'/Uploads','".$this->_AdminUrl."/Uploads') AS shopimg,business_info.`name` AS seller";
	    $sql = SqlStr2($TableName="business_info",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="h_seller_detail.sranksort",$OrderType="desc",$join,$joinid);
	    $model = M("business_info");
	    $list  = $model->query($sql);
	    if(is_bool($list)){
	        $returnData['code'] = 204;
	        $returnData['message'] = "找不到了";
	    }else{
	        $returnData['code'] = 200;
	        $returnData['message'] = "获取店铺列表成功";
	        $returnData['info'] = $list;
	    }
	    return $returnData;
	}

	/**
	 * @param $sellerid
	 * @return int
	 * 获取商家店铺待处理订单数量
	 */
	public function pendOrder($sellerid){
		$model	= M("goods_order");
		$sql	= "SELECT
	order_detail.odid
FROM
	goods_order
INNER JOIN order_detail ON goods_order.goid = order_detail.goid2
LEFT JOIN goods ON order_detail.godsid = goods.gid
LEFT JOIN business_info ON goods.bid = business_info.b_id
WHERE
	goid IN (
		SELECT
			goid
		FROM
			(
				SELECT
					goid
				FROM
					goods_order
				INNER JOIN order_detail ON goods_order.goid = order_detail.goid2
				LEFT JOIN goods ON order_detail.godsid = goods.gid
				LEFT JOIN business_info ON goods.bid = business_info.b_id
				WHERE
					goods_order.ispay = 2 AND business_info.seller_id = {$sellerid}
						AND goods_order.goodstotal != '0.00' AND order_detail.isfh = 1
						AND order_detail.isth = 1
						AND isdel = 0
			) AS t
	)
AND business_info.seller_id = {$sellerid}
AND goods_order.goodstotal != '0.00'
AND goods_order.ispay = 2
AND order_detail.isfh = 1
AND order_detail.isth = 1
AND isdel = 0
UNION
SELECT
	order_detail.odid
FROM
	goods_order
INNER JOIN order_detail ON goods_order.goid = order_detail.goid2
LEFT JOIN goods ON order_detail.godsid = goods.gid
LEFT JOIN business_info ON goods.bid = business_info.b_id
WHERE
	goid IN (
		SELECT
			goid
		FROM
			(
				SELECT
					goid
				FROM
					goods_order
				INNER JOIN order_detail ON goods_order.goid = order_detail.goid2
				LEFT JOIN goods ON order_detail.godsid = goods.gid
				LEFT JOIN business_info ON goods.bid = business_info.b_id
				WHERE
 business_info.seller_id = {$sellerid}
AND goods_order.goodstotal != '0.00'
AND goods_order.ispay = 2
AND order_detail.isth = 2
			) AS t
	)
AND business_info.seller_id = {$sellerid}
AND goods_order.goodstotal != '0.00'
AND goods_order.ispay = 2
AND order_detail.isth = 2";
		$result	= $model->query($sql);
		if(is_bool($result)){
			return 0;
		}else{
			return count($result);
		}
	}
}