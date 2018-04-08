<?php 
/**
 * 公共配置模型
 * 2017.01.18
 */
namespace Home\Model;
use Think\Model;
Class CommonModel extends Model {
	public function po_img($img,$path,$user_id,$other){
		// 上传营业执照
		$file_data = base64_decode($img);//;
		//图片存放路径
		$savePath   = "/Uploads/".$path."/";
		$photosavename  = "$other"."_".rand(0,9999)."$user_id.jpg";
		$file="./".$savePath.$photosavename;
		$dataPath   = $savePath.$photosavename;
		$m=fopen($file,"w");//当参数为"w"时是将内容覆盖写入文件，而当参数为"a"时是将内容追加写入。
		fwrite($m,$file_data);
		fclose($m);
		return $dataPath;
	}
	/**
	 * 商品信息的上传
	 * @param unknown $filedatas 文件流
	 * @param unknown $path  保存路径
	 * @param unknown $bid   店铺id
	 * @param unknown $filename   文件名称
	 * @param unknown $filetype    文件类型
	 */
	public function goodsImgAndVideo($filedatas,$path,$bid,$filename,$filetype){
	    $fileData  = base64_decode($filedatas);
	    $savePath  ="/Uploads/".$path."/";
	    $savename  = $filename."_".$bid.".".$filetype;
		$file=".".$savePath.$savename;
		$dataPath   = $savePath.$savename;
		$m=fopen($file,"w");//当参数为"w"时是将内容覆盖写入文件，而当参数为"a"时是将内容追加写入。
		fwrite($m,$fileData);
		fclose($m);
		$return['savepath']   = $file;
		$return['datapath']   = $dataPath;
		return $return;
	}
	/**
	 * 通过token获取用户的 id
	 */
	public function GetId($token){
		$user_id = M("user")->field('user_id')->where(array('token'=>$token))->find();
		return $user_id['user_id'];
	}
	/**
	 * 验证传输信息是否为空
	 * 2017.01.18
	 * @$nullKey 可为空的键
	 * @$data  待验证数组
	 */
	public function TransportInfo($data,$nullKey=""){
		if(empty($data)){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 获取项目地址
	 * 2017.02.08
	 */
	public function getUrl(){
		return "http://www.heigushop.com";
		$m=M("publicsetting");
		$result = $m->field("content")->where("`set`='adminUrl'")->find();
		return $result['content'];
	}
	/**
	 * 获取轮播图
	 * @city  城市名称
	 * 2017.02.11
	 */
	public function GetHomeCarousel($city=""){
		$model = M("carousel_photo");
		$where    = "";
		if(!empty($city)){
		   $where ="INSTR(`fullName`,'".$city."') or cityid=0";
		}else{
		   $where ="cityid=0";
		}
		$result   = $model->field("c_photo as img,CASE WHEN url='0' THEN ''
ELSE url END AS url,bid,carousel_photo.`name`")->join("LEFT JOIN h_region on carousel_photo.cityid=h_region.`code`")->where("$where")->order("cityid asc,`sort` asc")->select();

		if(is_bool($result)){
			$returnData['code'] = 204;
			$returnData['message'] = "无轮播图信息";
		}else{
			$returnData['code'] = 200;
			$returnData['message'] = "首页轮播图";
			if(!empty($result)){
				$result = join_carouse_url($result,$this->getUrl() ,"img");
				$returnData['info'] = $result;
			}
		}
		return $returnData;
	}

	/**
	 * 获取轮播图
	 * @city  城市名称
	 * 2017.02.11
	 */
	public function GetHomeCarousels(){
		$model = M("carousel_photo");
		$sql = "SELECT c_photo as img,`url`,`name` FROM `carousel_photo` WHERE ( `pid` = 0 )  LIMIT 3";
		$nationwide = $model->query($sql);
		$nationwide = join_carouse_url($nationwide,$this->getUrl() ,"img");
		$data['code']       = 200;
		$data['message']    = '获取首页轮播图成功';
		$data['data']       = $nationwide;
		return $data;
	}


	/**
	 * 获取支付方式信息
	 * @where 查询条件
	 * @field   需要显示的字段
	 * 2017.02.21
	 */
	public function GetPayInfo($where,$field=""){
		$field 		= $field?$field:"p_id as payid,pay_name as payname,img as pimg";
		$where1 	= $where;
		$model 	= M("pay_config");
		$result 		= $model->field($field)->where($where1)->select();
		return returnArrayBool($result);
	}
	/**
	 * 插入系统消息或者是站内信（api）
	 * @param unknown $title
	 * @param unknown $message
	 * @param unknown $type
	 * @param unknown $userid
	 */
	public function PushSystem($title,$message,$type,$userid){
    	 $model    = M("h_message");
    	 $data['title'] = $title;
    	 $data['message']= $message;
    	 $data['type']  = $type;
    	 $data['user_id']=$userid;
    	 $data['create_time']  = date("Y-m-d H:i:s");
    	 $result   = $model->add($data);
    	 if(is_bool($result)){
    	     return false;
    	 }else{
    	     return true;
    	 }
	}
	/**
	 * 检测用户是否恶意刷店铺信息
	 * @param unknown $userid
	 * @param unknown $goodsid
	 */
	public function CheckGoodsIsSelf($userid,$goodsid){
	    $bid1  = $this->FindBidFromUserid($userid);
	    $bid2  = $this->FindBidFromGoodsId($goodsid);
	    if($bid1 != $bid2){
	        return true;
	    }else{
	        return false;
	    }
	}
	/**
	 * 通过用户id返回店铺id
	 * @param unknown $userid
	 */
	public function FindBidFromUserid($userid){
	    $model = M("h_seller_detail");
	    $reuslt= $model->field("business_info.b_id")
	                   ->join("business_info ON h_seller_detail.seller_id=business_info.seller_id","INNER")
	                   ->where("h_seller_detail.user_id=$userid")
	                   ->find();
	    $bid   = $reuslt['b_id']?$reuslt['b_id']:0;
	    return $bid;
	}
	/**
	 * 通过商品id返回商家店铺id
	 * @param unknown $goodsid
	 */
	public function FindBidFromGoodsId($goodsid){
	    $model = M("goods");
	    $result= $model->field("bid")->where("gid=$goodsid")->find();
	    $bid   = $result['bid']?$result['bid']:0;
	    return $bid;
	}
	/**
	 * 通过商家id返回商家绑定的用户id
	 * 2017.07.07
	 */
	public function getUserIdFromBid($bid){
	    $model = M("h_seller_detail");
	    $reuslt= $model->field("h_seller_detail.user_id")
	    ->join("business_info ON h_seller_detail.seller_id=business_info.seller_id","INNER")
	    ->where("business_info.b_id=$bid")
	    ->find();
	    $userid   = $reuslt['user_id']?$reuslt['user_id']:0;
	    return $userid;
	}

	/**
	 * @param $bid
	 * @return int
	 * 通过商家bid返回商家手机号
	 */
	public function GetSellerPhoneFromBusinessId($bid){
		$model = M("h_seller_detail");
		$reuslt= $model->field("h_seller_detail.seller_phone")
				->join("business_info ON h_seller_detail.seller_id=business_info.seller_id","INNER")
				->where("business_info.b_id=$bid")
				->find();
		$mobile   = $reuslt['seller_phone']?$reuslt['seller_phone']:0;
		return $mobile;
	}

	/**
	 * @param $userid
	 * @return mixed
	 * 通过用户id返回用户手机号
	 */
	public function GetUserPhoneFromUserid($userid){
		$model	= M("user");
		$userinfo	= $model->field("user_phone")->where("user_id=$userid")->find();
		return $userinfo['user_phone'];
	}
}
?>