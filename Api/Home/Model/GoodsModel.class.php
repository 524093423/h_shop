<?php
/**
 * 商品模型
 * 2017.05.18
 */
namespace Home\Model;
use Think\Model;
use Think;
header("Content-Type: text/html; charset=UTF-8");
class GoodsModel extends Model {
	private $_AdminUrl;
   /**
       *根据条件获取数据
	   *2017.05.18
	   * @classid  分类id
	   * @page    分页id
	   * @search  搜索
	   * @#num   每页显示条数
	   * @sale      销量 【低到高1，高到低2】
	   * @unit      价格 【低到高1，高到低2】
	   * @sels        是否热销【1否2是】
	   * @ptes       是否推荐【1否2是】
	   *更新2017.05.23
    */
	public  function GetGoodsInfo($classid="",$page="",$search="",$num="",$sale="",$unit="",$sels="",$ptes="",$bid="",$city="",$callback="",$userid=""){
		$OrderType	="";
		$WhereStr	="";
		if(!empty($classid)){
			$WhereStr .=" and (goods_classify.gcid =$classid or goods_classify.pid=$classid)";
			$OrderType.="classrank desc";
		}
		if(!empty($search)){
		    $WhereStr .="  and (INSTR(goods.gname,'".$search."') or INSTR(goods_classify.gcname,'".$search."') or INSTR(business_info.`name`,'".$search."'))";
		}
		if(!empty($city)){
		    $WhereStr  .= " AND (INSTR(`h_seller_detail`.city,'".$city."') Or (INSTR(`h_seller_detail`.province,'".$city."')))";
		}
		if(!empty($callback)){
			$PageSize = 200;
		}else{
			if(!empty($bid)){
				$PageSize = 400;
			}else{
				$PageSize = $num?$num:10;
			}
		}
		if(!empty($userid)){
		    //$bid2  = $this->getBusinessIdFromUserid($userid);
			$WhereStr .= " AND buserid !=$userid";
		}
		if(!empty($sale)){
			if($sale==1){
				$OrderType .="sales asc";
				if(!empty($unit)){
					if($unit ==1){
						$OrderType .=",gprice asc";
					}else if($unit==2){
						$OrderType .=",gprice desc";
					}
				}
			}else if($sale==2){
				$OrderType .="sales desc";
				if(!empty($unit)){
					if($unit ==1){
						$OrderType .=",gprice asc";
					}else if($unit==2){
						$OrderType .=",gprice desc";
					}
				}
			}
		}else{
			if(!empty($unit)){
				if($unit ==1){
					$OrderType ="gprice asc";
				}else if($unit==2){
					$OrderType ="gprice desc";
				}
			}else{
				if(!empty($classid)){
					$OrderType	= "classrank desc";
				}else{
					$OrderType	= "hotrank desc";
				}
			}
		}
		$WhereStr .=" and goods.state =0";
		if(!empty($bid)){
		    $WhereStr .=" and bid=$bid";
		}
		if(empty($OrderType)){
			$OrderType = " viewnum desc";
		}
		$this->_AdminUrl =D("Common")->getUrl();
		$PageIndex = $page?$page:1;
		$join = " inner join goods_classify on goods.gclid=goods_classify.gcid INNER Join business_info on goods.bid=business_info.b_id INNER JOIN h_seller_detail ON business_info.seller_id = h_seller_detail.seller_id";
		$joinid = "gid";
		$Coll="gid as goodsid,gname,REPLACE(`gphoto`,'/Uploads','".$this->_AdminUrl."/Uploads') AS listimg,gprice AS price,goods.bid,viewnum as views";
		$sql = SqlStr($TableName="goods",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,"",$OrderType,$join,$joinid);
		//echo $sql;die;
		$goodsModel = M("goods");
		$result=$goodsModel->query($sql);//

		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "无商品列表信息";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "商品列表信息";
			$result = $result?$result:array();
			if(empty($result)){
				$sql	= str_replace($city,"郑州",$sql);
				$result	= $result=$goodsModel->query($sql);
			}
			if(!empty($result)){
				$result = join_carouse_url($result,$this->_AdminUrl,"gpo");
				$result = join_carouse_url($result, $this->_AdminUrl, "video");
			}
			$returnData['info'] = $result;
		}
		return $returnData;
	}
	/**
	 * 商品详情
	 * @param unknown $userId
	 * @param unknown $goodsid
	 * 2017.05.18
	 */
	public function GoodsDetail($userId,$goodsid){
	    $data  = array();
	    $this->_AdminUrl =D("Common")->getUrl();
	    $info  = "";
	    $model = M("goods");
	    $result    = $model->field("goods.state,goods_unit,h_seller_detail.city,goods.gdesc,token as seller_token,bid,h_seller_detail.seller_phone as shoptel,business_info.`name` as name,isintpay as isintl,gname as goodsname,intl_num as jfnum,gprice as price,goods_number as goodsnum,REPLACE(`coverimg`,'/Uploads','".$this->_AdminUrl."/Uploads') as cover,REPLACE(`blogo`,'/Uploads','".$this->_AdminUrl."/Uploads') as shopimg,viewnum as views,REPLACE(`flashs`,'/Uploads','".$this->_AdminUrl."/Uploads') as flash,series")
	                       ->join("left join business_info on goods.bid=business_info.b_id")
	                       ->join("left join h_seller_detail on business_info.seller_id=h_seller_detail.seller_id")
	                       ->join("left join `user` on h_seller_detail.user_id=user.user_id")
	                       ->where("gid=$goodsid")->find();
	    //print_r($result);die;
	    if(is_bool($result)){
	        $data['code']  = "204";
	        $data['message']   = "商品不存在";
	    }else{
			if($result['state']==1){
				$data['code']  = "204";
				$data['message']   = "商品已下架";
				return $data;
			}
	        $data['code']  = "200";
	        $data['message']   = "商品详细信息";
	        if(!empty($result)){
	            if(!empty($userId)){
	                $this->GoodsViewNo($goodsid,$userId);//更新商品浏览量
	            }
	            if(!stripos($result['flash'],"mp4"))
                {
                    $result['flash']    = "";
                }
	            $result['rate']    = $this->m_GetRate($goodsid,0,1);//获取评论信息
	            $result['ishouse'] = $this->JudgeGoodsIsHouse($userId,$goodsid);
				$result['carousel']= $this->AdCarousel($goodsid);
				$result['specinfo']= $this->getCurrGoodsSpecInfo($goodsid,"");
	        }else{
	            $result   = array();
	        }
	        $data['info']      = $result;
	    }
	    return $data;
	}
	/**
	 * 获取商品的轮播图
	 * @array  待处理数组
	 */
	public function AdCarousel($goodsid){
		$carousel = M("goods_photo");
		$array = join_carouse_url($carousel->field("img_path as img,gp_id as imgid")->where("gid=$goodsid")->select(),$this->_AdminUrl,"img");
		if(empty($array)){
			return array();
		}
		return $array;
	}
	/**
	 * 更新商品浏览量
	 * @param unknown $goodsid
	 * 2017.05.18
	 */
	public function GoodsViewNo($goodsid,$userId){
	   $model  = M("goods");
	   $status	= $this->GoodsViewsTable($goodsid,$userId);
	   if($status){
		 $model->execute("update goods set viewnum = viewnum + 1");
	   }

	}

	/**
	 * @param $goodsid
	 * @param $userid
	 * @return bool
	 * 检验是否增加浏览量
	 */
	private function GoodsViewsTable($goodsid,$userid){
		$model	= M("goods_views");
		$result	= $model->field("lasttime")->where("userid={$userid}")->find();
		if(empty($result)){
			$model->add([
				"goodsid"=>$goodsid,
				"userid"=>$userid,
				"lasttime"=>time()
			]);
			return true;
		}else{
			$lasttime	= $result['lasttime'];
			$exprtime	= $lasttime + 5*60;
			if($exprtime > time()){
				return false;
			}else{
				$model->where("userid={$userid}")->save([
					"lasttime"=>time()
				]);
				return true;
			}
		}
	}
	/**
	 * 新增  2017.04.21
	 * 给商品绑定客服信息
	 * 2017.04.21
	 */
	public function BindingCustom($result){
		for ($i=0;$i<count($result);$i++){
			$result[$i]['mobile'] 	= $this->getGoodsCustom($result[$i]['bid']);
		}
		return $result;
	}
	/**
	 * 计算该商品被评论次数
	 * 2017.04.27
	 */
	/**
	 * 判断商品是否被用户收藏
	 * @userid  用户Id
	 * @godsid 商品Id
	 * @0 未收藏 1收藏
	 */
	public function JudgeGoodsIsHouse($userid,$godsid){
	    if(empty($userid) || empty($godsid)){
	        return 0;
	    }
		$model = M("goods_house");
		$data     = array();
		$result = $model->where("userid=$userid and godsid=$godsid")->select();
		if(empty($result)){
			$returnData  = 0;
		}else{
			$returnData  = 1;
		}
		return $returnData;
	}
	/**
	 * 将商品加入用户收藏
	 * @userid  用户id
	 * @godsid 商品Id
	 */
	public function SetGoodsIsHouse($userid,$godsid){
		$model = M("goods_house");
		$curr['userid'] = $userid;
		$curr['godsid']= $godsid;
		$result = $model->where("userid=$userid and godsid=$godsid")->select();
		if(!empty($result)){
			$dresult = $model->where("userid=$userid and godsid=$godsid")->delete();
			if(is_bool($dresult)){
				$returnData['code'] = 204;
				$returnData['message'] = "找不到了";
			}else{
				if(!empty($dresult)){
					$message = "取消成功";
					$data['state'] = 0;
				}else{
					$message = "取消失败";
					$data['state'] = 1;
				}
				$returnData['code'] = 200;
				$returnData['message'] = $message;
				$returnData['info']  = $data;
			}
			return $returnData;
		}
		$curr['ghtime']= date("Y-m-d");
		$result = $model->add($curr);
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "找不到了";
		}else{
			if(!empty($result)){
				$message = "收藏成功";
				$data['state'] = 1;
			}else{
				$message = "收藏失败";
				$data['state'] = 0;
			}
			$returnData['code'] = 200;
			$returnData['message'] = $message;
			$returnData['info']  = $data;
		}
		return $returnData;
	}
	/**
	 * 我的收藏
	 * @userid 用户id
	 * @page  分页页码
	 * @num   每页显示条数
	 */
	public function GetMyHouse($userid,$page,$num=""){
	    $this->_AdminUrl =D("Common")->getUrl();
		//$WhereStr	= "";//检索条件
		$OrderKey = "ghtime";
		$OrderType = "desc";
		$PageSize = $num?$num:10;
		$PageIndex = $page?$page:1;
		$WhereStr =" and goods.state =0";
		if(!empty($userid)){
			$WhereStr .= " and goods_house.userid=$userid";
		}else{
			$returnData['code'] = 204;
			$returnData['message'] = "无收藏信息";
			return $returnData;
		}
		$join = " inner join goods_house on goods.gid=goods_house.godsid ";
		$joinid = "ghid";
		$Coll="gid,gname,REPLACE(`gphoto`,'/Uploads','".$this->_AdminUrl."/Uploads') AS listimg,gprice AS price,goods.bid";
		$sql = SqlStr($TableName="goods",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		$goodsModel = M("goods");
		$result=$goodsModel->query($sql);
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "无收藏信息";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			if(!empty($result)){
				//$result = $this->AdCarousel(join_carouse_url($result,$this->_AdminUrl,"gpo"));
				//$result = $this->AdSpec($result);
			}
			$returnData['info'] = $result;
		}
		return $returnData;
	}
	/**
	 * 获取前端需要显示的数据
	 * @goodsid  商品Id
	 * @page       页码
	 * @state        如果为1则需要显示产品详情页数据
	 * 2017.05.18
	 */
	public function m_GetRate($goodsid,$page,$state){
		$where = " and ggid=$goodsid";
		if($state ==1){
			$result = $this->RateList($where,$page,3);
			if($result['code'] ==200){
				$info['list'] = $result['info'];
				$info['num'] = $this->GetRateNumber($goodsid);
				return $info;
			}else{
				return 0;
			}
		}else{
			$data = $this->RateList($where,$page);
		}
		return $data;
	}
	/**
	 * 通过商品id 获取 全部评价数量
	 * @goodsid     商品Id
	 * 2017.02.16
	 */
	public function GetRateNumber($goodsid){
		$model 	= M("goods_rate");
		$num 		= $model->where("ggid=$goodsid")->count();
		$num        = $num?$num:0;
		return $num;
	}
	/**
	 * 当前商品的评价列表
	 * @where  条件
	 * @page    页码
	 * @num     每页显示数量
	 * 2017.02.16
	 */
	public function RateList($where="",$page="",$num=""){
		$WhereStr	= "";//检索条件
		$OrderKey = "ratetime";
		$OrderType = "desc";
		$PageSize = $num?$num:10;
		$PageIndex = $page?$page:1;
		$WhereStr  .=$where;
		$join = "inner join user on goods_rate.userid=`user`.user_id";
		$joinid = "grid";
		$Coll="ratetime,`user`.user_phone as phe,rateinfo,ratelevel";
		$sql = SqlStr($TableName="goods_rate",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
		$goodsModel = M("goods_rate");
		$result=$goodsModel->query($sql);
		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "无评价信息";
		}else{
			$result = $result?$result:array();
			if(!empty($result)){
				for($i=0;$i<count($result);$i++){
					$result[$i]['phe'] = substr_replace($result[$i]['phe'],'****',3,4);
				}
			}
			$returnData['code'] = 200;
			$returnData['message'] = "获取成功";
			$returnData['info'] 		= $result;
		}
		return $returnData;
	}
	/**
	 * 用户添加商品评价
	 * @param  [type] $user_id   [description]
	 * @param  [type] $good_id   [description]  订单id
	 * @param  [type] $rate_info [description]
	 * @return [type]            [description]
	 * 修改于2017.04.27
	 */
	public function getAddRate($user_id,$orderid,$rate_info,$ratelevel)
	{
		if(!$user_id || !$orderid  || !$rate_info){
			$returnData['code'] = 204;
			$returnData['message'] ="请完善您的填写信息";
			return $returnData;
		}
		$user = M('user')->where("user_id = '$user_id'")->find();
		if(empty($user)){
			$returnData['code'] = 204;
			$returnData['message'] ="用户不存在,请确认用户信息";
			return $returnData;
		}
		$good = M('order_detail')->field("godsid")->where("goid2 = '$orderid'")->select();
		if(empty($good)){
			$returnData['code'] = 204;
			$returnData['message'] ="商品不存在,请确认商品信息";
			return $returnData;
		}
		for($i=0;$i<count($good);$i++){
			$data['userid'] = $user_id;
			$data['ggid'] = $good[$i]['godsid'];
			$data['rateinfo'] = $rate_info;
			$data['ratelevel']=$ratelevel;
			$data['ratetime'] = date('YmdHis');
			$result = M('goods_rate')->add($data);
		}
		if($result){
			$returnData['code'] = 200;
			$returnData['message'] ="评价提交成功";
			M('goods_order')->where("goid='$orderid'")->save(array("israte"=>2));
			return $returnData;
		}else{
			$returnData['code'] = 204;
			$returnData['message'] ="评价提交失败";
			return $returnData;
		}
		return $returnData;
	}
	/**
	 * 获取全部分类
	 * 2017.05.23
	 */
	public function ClassifyList($field="",$where="",$order=""){
	    $field 		= $field?$field:"gcid as classid,gcname as classname,level,ppid as parentid";
	    $where 	=$where?$where:"state = 0";
	    $order 		=$order?$order:"gcid asc";
	    $model 	= M("goods_classify");
	    $data 		= $model->field($field)->where($where)->order($order)->select();
	    //echo $model->getLastSql();die;
	    return returnArrayBool($data);
	}
	/**
	 * 商家商品信息的上传
	 * @param unknown $user_id
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @param unknown $classid
	 * @param unknown $goodsname
	 * @param unknown $goodsprice
	 * @param unknown $goodsnum
	 * @param unknown $goodsdesc
	 * @param unknown $goodsimg
	 * @param unknown $goodsflash
	 * 2017.05.24
	 */
	public function AddGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goods_unit,$goodscarousel,$specidStr,$series){
	    $state = $this->CheckUserisShop($user_id, $sellerid, $bid);
	    if(!$state){
	        return array("code"=>"204","message"=>"商家信息错误");
	    }
		set_time_limit(0);
	    //收集入库数据
	    $data['gname']     = $goodsname;
	    $data['bid']       = $bid;
		$data['buserid']	= $user_id;
	    $data['gphoto']    = "";
	    $data['gclid']     = $classid;
	    $data['gprice']    = $goodsprice;
	    $data['intl']      = 1;
	    $data['sales']     = 0;
		$data['isintpay'] = 1;
	    $data['rktime']    = date("Y-m-d");
	    $data['state']     = 0;
	    $data['useintl']   = 0;
	    $data['givintl']   = 0;
	    $data['viewnum']   = 0;
		$data['series']		= $series;
		$data['intl_num'] = floor($goodsprice);
		$data['goods_unit']= $goods_unit;
	    $data['gdesc']     = $goodsdesc;
	    $data['goods_number']  = $goodsnum;
	    //filesize 文件大小 is_file
	    $model     = M("goods");
	    $model->startTrans();
	    $result    = $model->add($data);
	    if(is_bool($result)){
	        $model->rollback();
	        return reTurnJSONArray("204","提交信息有误");
	    }
	    //处理封面图
	    $img       =  D("Common")->goodsImgAndVideo($goodsimg,"goods/cover",$bid,"goodscover"."_".$result."_","jpg");
	    $imgFile   = $img['savepath'];
	    if(filesize($imgFile) <=0){
	        $model->rollback();
	        return reTurnJSONArray("204","提交图片信息有误");
	    }
	    $save['coverimg']  = $img['datapath'];
		$save['gphoto']    = $img['datapath'];
	    //处理视频
	    if(!empty($goodsflash)){
			$video     = D("Common")->goodsImgAndVideo($goodsflash,"goods/flash",$bid,"flash"."_".$result."_","mp4");
			$videofile = $video['savepath'];
			if(filesize($videofile) <=0){
				$model->rollback();
				return reTurnJSONArray("204","提交视频信息有误");
			}
			$save['flashs']= $video['datapath'];
		}else{
			$save['flashs']= "";
		}
	    $saveResult    = $model->where("gid=$result")->save($save);
	    if(is_bool($saveResult)){
	        $model->rollback();
	        return reTurnJSONArray("204","提交图片信息有误");
	    }
	    $model->commit();
		if(!empty($goodscarousel)){
			M("goods_photo")->where("gp_id in ($goodscarousel)")->save(array("gid"=>$result));
			$this->UpSpec($specidStr,$result);//更新规格
		}
	    return reTurnJSONArray("200","上传商品信息成功");
	}
	/**
	 * 商家商品信息的更新
	 * @param unknown $user_id
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @param unknown $classid
	 * @param unknown $goodsname
	 * @param unknown $goodsprice
	 * @param unknown $goodsnum
	 * @param unknown $goodsdesc
	 * @param unknown $goodsimg
	 * @param unknown $goodsflash
	 */
	public function UpdateGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goodsid,$goods_unit,$goodscarousel,$specidStr,$series){
		$state = $this->CheckUserisShop($user_id, $sellerid, $bid);
		if(!$state){
			return array("code"=>"204","message"=>"商家信息错误");
		}
		if(!empty($goodsimg)){
			if(strpos($goodsimg,"/Uploads")){
				$data['coverimg']  = str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsimg);
				$data['gphoto']    = str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsimg);
			}else{
				$img       =  D("Common")->goodsImgAndVideo($goodsimg,"goods/cover",$bid,"goods"."_".$goodsid."_".rand(1,9)."_","jpg");
				$imgFile   = $img['savepath'];
				if(filesize($imgFile) <=0){
					return reTurnJSONArray("204","提交图片信息有误");
				}
				$data['coverimg']  = $img['datapath'];
				$data['gphoto']    = $img['datapath'];
			}
		}
		if(!empty($goodsflash)){
			if(strpos($goodsflash,"/Uploads")){
				if(strpos($goodsflash,C("SITE_URL"))){
					$data['flashs']    =str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsflash);
				}else{
					$data['flashs']    =$goodsflash;
				}
			}else{
				if($goodsflash==1){
					$data['flashs']= "";
				}else{
					$video     = D("Common")->goodsImgAndVideo($goodsflash,"goods/flash",$bid,"flash"."_".$goodsid."_","mp4");
					$videofile = $video['savepath'];
					if(filesize($videofile) <=0){
						return reTurnJSONArray("204","提交视频信息有误");
					}
					$data['flashs']= $video['datapath'];
				}
			}
		}
		set_time_limit(0);
		//收集入库数据
		$data['intl_num'] = floor($goodsprice);
		$data['gname']     = $goodsname;
		$data['bid']       = $bid;
		$data['buserid']	= $user_id;
		$data['gclid']     = $classid;
		$data['gprice']    = $goodsprice;
		$data['rktime']    = date("Y-m-d");
		$data['gdesc']     = $goodsdesc;
		$data['goods_number']  = $goodsnum;
		$data['goods_unit']	= $goods_unit;
		$data['series']		= $series;
		//filesize 文件大小 is_file
		$model     = M("goods");
		$model->startTrans();
		$result    = $model->where("gid=$goodsid")->save($data);
		if(is_bool($result)){
			$model->rollback();
			return reTurnJSONArray("204","提交信息有误");
		}
		$model->commit();
		if(!empty($goodscarousel)){
			M("goods_photo")->where("gp_id in ($goodscarousel)")->save(array("gid"=>$goodsid));
			$this->UpSpec($specidStr,$goodsid);//更新规格
		}
		return reTurnJSONArray("200","商品信息修改成功");
	}
	/**
	 * 删除商品信息
	 * @param unknown $userid
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @param unknown $goodsid
	 * 2017.05.24
	 */
	public function DelGoodsInfo($userid,$sellerid,$bid,$goodsid){
	    $state = $this->CheckUserisShop($userid, $sellerid, $bid);
	    if(!$state){
	        return array("code"=>"204","message"=>"商家信息错误");
	    }
	    $model = M("goods");
	    $goodsid   = intval($goodsid);
	    $result= $model->where("gid=$goodsid")->save(array("state"=>1));
	    if(is_bool($result)){
	        return reTurnJSONArray("204", "商品信息删除失败");
	    }else{
	        return reTurnJSONArray("200", "商品信息删除成功");
	    }
	}
	/**
	 * 验证商家信息是否为空
	 * @param unknown $userid
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @return boolean
	 * 2017.05.24
	 */
	public function CheckUserisShop($userid,$sellerid,$bid){
        $model  = M("h_seller_detail");
        $sql    = "SELECT 
                    h_seller_detail.seller_id 
                    FROM 
                    h_seller_detail INNER JOIN business_info ON h_seller_detail.seller_id=business_info.seller_id 
                    WHERE 
                    h_seller_detail.`status`=2 AND h_seller_detail.user_id={$userid}  AND business_info.b_id={$bid} AND business_info.seller_id={$sellerid}";
        $result = $model->query($sql);
        if(!empty($result)){
            return true;
        }else{
            return false;
        }
	}
	/**
	 * 根据用户id返回用户商家id
	 * @param unknown $userid
	 */
	public function getBusinessIdFromUserid($userid){
	    $model  = M("h_seller_detail");
	    $sql    = "SELECT
	    business_info.b_id
	    FROM
	    h_seller_detail INNER JOIN business_info ON h_seller_detail.seller_id=business_info.seller_id
	    WHERE
	    h_seller_detail.`status`=2 AND h_seller_detail.user_id={$userid} ";
	    $result = $model->query($sql);
	    if(!empty($result)){
	        return $result[0]['b_id'];
	    }else{
	        return 0;
	    }
	}

	/**
	 *
	 * @param $goodsid
	 * @return array
	 */
	public function getUpdateGoodsInfo($goodsid){
		$model	= M("goods");
		$siteurl	= C("SITE_URL");
		$this->_AdminUrl =D("Common")->getUrl();
		$result	= $model
				->field("goods_unit,gcname as classname,gdesc,gid as godsid,gname,gclid as classid,REPLACE(gphoto,'/Uploads','".$siteurl."/Uploads') as goodslogo,REPLACE(coverimg,'/Uploads','".$siteurl."/Uploads') as cover,REPLACE(flashs,'/Uploads','".$siteurl."/Uploads') as flash,goods_number as gnum,gprice as price,series")
				->join("left join goods_classify ON  goods.gclid	= goods_classify.gcid")
				->where("gid=$goodsid")->find();
		if(!empty($result)){
			$result['carousel']	= $this->AdCarousel($goodsid);//商品轮播图
			$result['specinfo']	= $this->getCurrGoodsSpecInfo($goodsid,$result['series']);//当前产品的规格
			return array("code"=>"200","message"=>"商品信息获取成功","info"=>$result);
		}else{
			return reTurnJSONArray("204", "商品不存在");
		}
	}

	/**
	 * @param $img
	 * @return array
	 * 上传轮播图
	 */
	public function UploadImg($img){
		$img		=  D("Common")->goodsImgAndVideo($img,"goods/carousel","carousel","goods"."_".time()."_".rand(1,9)."_","jpg");
		$imgFile	= $img['savepath'];
		$imgdata	= $img['datapath'];
		if(!empty($imgFile)){
			$model	= M("goods_photo");
			$data['img_path']	= $imgdata;
			$data['uploadtime']=date("Y-m-d");
			$data['sort']		= time();
			$result	= $model->data($data)->add();
			if(!is_bool($result)){
				return array("code"=>200,"message"=>"图片上传成功","info"=>array("imgid"=>$result));
			}
		}
		return reTurnJSONArray("204","图片上传失败");
	}
	/**
	 * 商家商品信息的上传--安卓
	 * @param unknown $user_id
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @param unknown $classid
	 * @param unknown $goodsname
	 * @param unknown $goodsprice
	 * @param unknown $goodsnum
	 * @param unknown $goodsdesc
	 * @param unknown $goodsimg
	 * @param unknown $goodsflash
	 * 2017.05.24
	 */
	public function NewAddGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goods_unit,$goodscarousel,$specidStr,$series){
		$state = $this->CheckUserisShop($user_id, $sellerid, $bid);
		if(!$state){
			return array("code"=>"204","message"=>"商家信息错误");
		}
		set_time_limit(0);
		//收集入库数据
		$data['gname']     = $goodsname;
		$data['bid']       = $bid;
		$data['buserid']	= $user_id;
		$data['gphoto']    = "";
		$data['gclid']     = $classid;
		$data['gprice']    = $goodsprice;
		$data['intl']      = 1;
		$data['sales']     = 0;
		$data['isintpay'] = 1;
		$data['rktime']    = date("Y-m-d");
		$data['state']     = 0;
		$data['useintl']   = 0;
		$data['givintl']   = 0;
		$data['viewnum']   = 0;
		$data['series']		= $series;
		$data['intl_num'] = floor($goodsprice);
		$data['goods_unit']= $goods_unit;
		$data['gdesc']     = $goodsdesc;
		$data['goods_number']  = $goodsnum;
		//filesize 文件大小 is_file
		$model     = M("goods");
		$model->startTrans();
		$result    = $model->add($data);
		if(is_bool($result)){
			$model->rollback();
			return reTurnJSONArray("204","提交信息有误");
		}
		//处理封面图
		$img       =  D("Common")->goodsImgAndVideo($goodsimg,"goods/cover",$bid,"goodscover"."_".$result."_","jpg");
		$imgFile   = $img['savepath'];
		if(filesize($imgFile) <=0){
			$model->rollback();
			return reTurnJSONArray("204","提交图片信息有误");
		}
		$save['coverimg']  = $img['datapath'];
		$save['gphoto']    = $this->imgThumb($img['datapath'],$bid);
		//处理视频
		if(!empty($goodsflash)){
			/*$video     = D("Common")->goodsImgAndVideo($goodsflash,"goods/flash",$bid,"flash"."_".$result."_","mp4");
			$videofile = $video['savepath'];
			if(filesize($videofile) <=0){
				$model->rollback();
				return reTurnJSONArray("204","提交视频信息有误");
			}*/
			if(!empty($goodsflash['flash'])){
				if($goodsflash['flash']['error']==0){
					$names	= "flash_".$result."__".$bid.".mp4";
					move_uploaded_file($goodsflash['flash']['tmp_name'],"./Uploads/goods/flash/{$names}");
				}
			}
			$save['flashs']= "/Uploads/goods/flash/{$names}";
		}else{
			$save['flashs']= "";
		}
		$saveResult    = $model->where("gid=$result")->save($save);
		if(is_bool($saveResult)){
			$model->rollback();
			return reTurnJSONArray("204","提交图片信息有误");
		}
		$model->commit();
        $this->UpSpec($specidStr,$result);//更新规格
		if(!empty($goodscarousel)){
			M("goods_photo")->where("gp_id in ($goodscarousel)")->save(array("gid"=>$result));
			//$this->UpSpec($specidStr,$result);//更新规格
		}
		return reTurnJSONArray("200","上传商品信息成功");
	}
	/**
	 * 商家商品信息的更新
	 * @param unknown $user_id
	 * @param unknown $sellerid
	 * @param unknown $bid
	 * @param unknown $classid
	 * @param unknown $goodsname
	 * @param unknown $goodsprice
	 * @param unknown $goodsnum
	 * @param unknown $goodsdesc
	 * @param unknown $goodsimg
	 * @param unknown $goodsflash
	 */
	public function NewUpdateGoods($user_id,$sellerid,$bid,$classid,$goodsname,$goodsprice,$goodsnum,$goodsdesc,$goodsimg,$goodsflash,$goodsid,$goods_unit,$goodscarousel,$specidStr,$series){
		$state = $this->CheckUserisShop($user_id, $sellerid, $bid);
		if(!$state){
			return array("code"=>"204","message"=>"商家信息错误");
		}
		if(!empty($goodsimg)){
			if(strpos($goodsimg,"/Uploads")){
				$data['coverimg']  = str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsimg);
				$data['gphoto']    = $this->imgThumb(str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsimg),$goodsid);
			}else{
				$img       =  D("Common")->goodsImgAndVideo($goodsimg,"goods/cover",$bid,"goods"."_".$goodsid."_".rand(1,9)."_","jpg");
				$imgFile   = $img['savepath'];
				if(filesize($imgFile) <=0){
					return reTurnJSONArray("204","提交图片信息有误");
				}
				$data['coverimg']  = $img['datapath'];
				$data['gphoto']    = $this->imgThumb($img['datapath'],$goodsid);
			}
		}
		if(!empty($goodsflash)){
			if(strpos($goodsflash,"/Uploads")){
				if(strpos($goodsflash,C("SITE_URL"))){
					$data['flashs']    =str_replace(C("SITE_URL")."/Uploads","/Uploads",$goodsflash);
				}else{
					$data['flashs']    =$goodsflash;
				}
			}else{
				if($goodsflash==1){
					$data['flashs']= "";
				}else{
					if($goodsflash['flash']['error']==0){
						$names	= "flash_".$goodsid."__".$bid.".mp4";
						move_uploaded_file($goodsflash['flash']['tmp_name'],"./Uploads/goods/flash/{$names}");
					}
					/*$video     = D("Common")->goodsImgAndVideo($goodsflash,"goods/flash",$bid,"flash"."_".$goodsid."_","mp4");
					$videofile = $video['savepath'];
					if(filesize($videofile) <=0){
						return reTurnJSONArray("204","提交视频信息有误");
					}*/
					$data['flashs']= "/Uploads/goods/flash/{$names}";
				}
			}
		}
		set_time_limit(0);
		//收集入库数据
		$data['intl_num'] = floor($goodsprice);
		$data['gname']     = $goodsname;
		$data['bid']       = $bid;
		$data['buserid']	= $user_id;
		$data['gclid']     = $classid;
		$data['gprice']    = $goodsprice;
		$data['rktime']    = date("Y-m-d");
		$data['gdesc']     = $goodsdesc;
		$data['goods_number']  = $goodsnum;
		$data['goods_unit']	= $goods_unit;
		$data['series']		= $series;
		//filesize 文件大小 is_file
		$model     = M("goods");
		$model->startTrans();
		$result    = $model->where("gid=$goodsid")->save($data);
		if(is_bool($result)){
			$model->rollback();
			return reTurnJSONArray("204","提交信息有误");
		}
		$model->commit();
        $this->UpSpec($specidStr,$goodsid);//更新规格
		if(!empty($goodscarousel)){
			M("goods_photo")->where("gp_id in ($goodscarousel)")->save(array("gid"=>$goodsid));
		}
		return reTurnJSONArray("200","商品信息修改成功");
	}
    public function imgThumb($file,$goodsid){
	    try
        {
            $file   = ".".$file;
            $route1  = "./Uploads/thumb/";
            $route2  = "/Uploads/thumb/";
            $thumb  = "thumb_{$goodsid}_".time().".png";
            $newimg = $route1.$thumb;
            $retrurnimg = $route2.$thumb;
            $image = new \Think\Image();
            $image->open($file);
            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
            $image->thumb(400, 400)->save($newimg);
            return $retrurnimg;
        }catch (Think\Exception $exception)
        {
            return $file;
        }
    }
	/**
	 * @param $postData
	 * @param $userid
	 * @return array
	 * 新增商品规格信息
	 */
	public function AddSpec($postData,$userid){
		$returnArray	= []; //返回数据
		if(empty($postData['specprice'])){ return reTurnJSONArray("204","产品价格不可低于零");}
		$model	= M("goods_spec");
		$data['gsdesc']	= $postData['spectitle'];
		$data['gstock']	= $postData['specnum'];
		$data['gprice']	= $postData['specprice'];
		$data['jfnum']	= floor($postData['specprice']);
		if(!empty($postData['specid'])){
			$text	= "更新";
			$result	= $model->where("gsid={$postData['specid']}")->save($data);
			$specid	= $postData['specid'];
		}else{
			$text	= "添加";
			$result	= $model->data($data)->add();
			$specid	= $result;
		}
		if(is_bool($result)){
			$returnArray	= ["code"=>"204","message"=>"商品规格{$text}失败"];
		}else{
			$returnArray	= ["code"=>"200","message"=>"商品规格{$text}成功","info"=>array("specid"=>"$specid")];
		}
		return $returnArray;
	}

	/**
	 * @param $postData
	 * @param $userid
	 * @return array
	 * 规格信息的删除
	 */
	public function DelSpec($postData,$userid){
		$returnArray	= [];
		$where	= "gsid={$postData['specid']}";
		$specInfo	= $this->getSpecInfo($where);
		$authwhere	= "";
		if(empty($specInfo)){return reTurnJSONArray("204","规格信息不存在");}
		$goodsid	= $specInfo['gdsid'];
//		$authwhere	= "gdsid={$goodsid}";
//		$goodsAllSpecNum	= $this->SpecNum($authwhere);
//		if($goodsAllSpecNum==1){
//			return reTurnJSONArray("204","规格信息不存在");
//		}
		if($goodsid !=0){
			$state	= $this->JudgeGoodsIsUser($userid,$goodsid);//判断该规格下产品是否为当前用户的产品
			if(!$state){return reTurnJSONArray("204","商品规格信息不可为空");}
            $status = $this->JudgeGoodsIsempty($userid,$goodsid);
            if(!$status)
            {
                return reTurnJSONArray("204","商品规格信息不可为空");
            }
		}
		$model	= M("goods_spec");
		$result	= $model->where($where)->delete();
		if(is_bool($result)){
			$returnArray	= reTurnJSONArray("204","商品规格删除失败");//["code"=>"204","message"=>""];
		}else{
			$returnArray	= reTurnJSONArray("200","商品规格删除成功");
		}
		return $returnArray;
	}

	/**
	 * @param $where
	 * @return int
	 * 获取当前规格是否为空
	 */
	private function SpecNum($where){
		$model	= M("goods_spec");
		$count	= $model->field("gsid")
				->where($where)
				->count();
		return $count?$count:0;
	}
	/**
	 * @param $idstr
	 * @param $goodsid
	 * @return bool
	 * 规格和产品绑定
	 */
	public function UpSpec($idstr,$goodsid){
		$model	= M("goods_spec");
		$result	= $model->where("gsid in({$idstr})")->save(array("gdsid"=>$goodsid));
		if(!is_bool($result)){
            $result	= $model->field("MIN(gprice) AS price")->where("gdsid ={$goodsid}")->find();
            M("goods")->where("gid={$goodsid}")->save(["gprice"=>$result['price']]);
			return true;
		}
		return false;
	}
	/**
	 * @param $userid
	 * @param $goodsid
	 * @return bool
	 * 判断产品是否属于该用户
	 */
	public function JudgeGoodsIsUser($userid,$goodsid){
		$where	= " goods.buserid={$userid} AND goods.gid={$goodsid}";
		$data	= $this->getGoodsData($where);
		if(empty($data)){
			return false;
		}
		return true;
	}
    /**
     * @param $userid
     * @param $goodsid
     * @return bool
     * 判断产品是否剩余一个规格
     */
    public function JudgeGoodsIsempty($userid,$goodsid){
        $where	= "gdsid={$goodsid}";
        $model	= M("goods_spec");
        $data	= $model
            ->field("gsid")
            ->where($where)
            ->select();
        if(count($data)==1){
            return false;
        }
        return true;
    }

	/**
	 * @param $where
	 * @param $field
	 * @return mixed
	 * 获取单条规格信息
	 */
	public function getSpecInfo($where,$field=""){
		$field	= $field?$field:"gdsid";
		$model	= M("goods_spec");
		$result	= $model->field($field)
						->where($where)
						->find();
		return $result;
	}
	/**
	 * @param $where
	 * @param $join
	 * @param $field
	 * @return mixed
	 * 获取单条产品的信息
	 */
	public function getGoodsData($where,$join="",$field="gid"){
		$model	= M("goods");
		$data	= $model->field($field)
						->join($join)
						->where($where)
						->find();
		return $data;
	}

	/**
	 * @param $goodsid
	 * @return array
	 * 获取当前产品的规格信息
	 */
	public function getCurrGoodsSpecInfo($goodsid,$str=""){
		$model	= M("goods_spec");
		$result	= $model->field("gsdesc as spectitle,gsid as specid,gstock as specnum,gprice as specprice,jfnum")
				->where("gdsid={$goodsid}")
				->select();
		$result	= $result?$result:array();
		if(is_array($result)){
			for($i=0;$i<count($result);$i++){
				$result[$i]['spectitle']	= str_replace($str,"",$result[$i]['spectitle']);
			}
		}
		return $result;
	}
}