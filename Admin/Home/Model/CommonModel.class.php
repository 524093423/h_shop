<?php
/**
 * 后台公共模型
 *2017.03.08
 */
namespace Home\Model;
use Home\Controller\ApiController;
use Org\Util\ArrayList;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class CommonModel extends Model {
	/**
	 * 获取项目地址
	 * 2017.02.08
	 */
	public function getUrl(){
		$m=M("publicsetting");
		$result = $m->field("content")->where("`set`='adminUrl'")->find();
		return $result['content'];
	}
	/**
	 * 获取栏目列表
	 * @param 管理员ID $adminid
	 * @param 管理员pinyin $adminpinyin
	 */
 	public function GetMenu($adminid,$adminpinyin,$groupid){
 		if($adminpinyin =="manager"){
 			$column 	 	= $this->GetPartnerMenu();
 		}elseif($adminpinyin=="admin"){
			$column			= $this->getAdminColumn($groupid);
 		}else{
			$column 		= $this->GetAdminMenu($groupid);
		}
 		$column 		= unlimitedForLayer($column,"children");
 		return $this->FormatMenu($column);
 	}
 	/**
 	 * 获取超级管理员栏目
 	 */
 	public function GetPartnerMenu(){
 		$model 	= M("nmt_column");
 		$result 		= $model->field("id,title as text,url,pid as parentid")->where("id!=3")->order("sort asc")->select();
 		return $result;
 	}
 	public function FormatMenu($result){
 		for ($i=0;$i<count($result);$i++){
 			$result[$i]['isexpand'] 	= 'false';
 		}
 		return $result;
 	}
 	/**
 	 * 获取除超级管理员外的栏目
 	 * @param unknown $groupid
 	 * @return unknown
 	 */
 	public function GetAdminMenu($groupid){
 		$model 	= M("nmt_column_admin");
 		$result 		= $model->field("nmt_column.id,title as text,url,pid as parentid,1 as isrole")
						 		->join("nmt_column ON nmt_column_admin.ncid=nmt_column.id")
						 		->where("nagid=$groupid")
						 		->order("nmt_column.sort asc")->select();
 		return $result;
 	}
 	/**
 	 * 获取管理员类型
 	 * @return \Think\mixed
 	 */
  public function GetAdminType($type=""){
  	if(!empty($type)){
  		$where = " pinyin ='admin'";
  	}
  	$model 	= M("nmt_admin_group");
  	$result 		= $model->where($where)->select();
  	return $result;
  }
  /**
   * 检测管理员名称是否重复
   * @param unknown $admin
   */
  public function CheckAdmin($admin){
  	$model 	= M("admin");
  	$result 		= $model->field("admin_id")->where("admin_name ='".$admin."'")->find();
  	if(!empty($result)){
  		return 2;
  	}else{
  		return 1;
  	}
  }
  /**
   * 获取发货方式
   * @param string $field
   * @param string $where
   * @param string $order
   * @return Ambigous <boolean, unknown>
   */
  public function GetDelivery($field="",$where="",$order=""){
  	$field 		= $field?$field:"odyid as id,`describe` as dtitle";
  	$where 	= $where?$where:"";
  	$order 		= $order?$order:"odyid asc";
  	$model 	= M("order_delivery");
  	$result 		= $model->field($field)->where($where)->select();
  	return returnArrayBool($result);
  }
  /**
   * 开始发货
   * @param 商品 $goid
   * @param 管理员id $adminid
   * @param 管理员类型拼音 $adminpinyin
   */
  public function SendGoods_m($goid,$adminid,$adminpinyin,$bid){
  	$state = 1;
  	if($adminpinyin == 'manager'){
  		$state 	= $this->HandleGoods($goid);
  	}else{
  		$return = $this->CheckAdminIsAuth($adminid, $goid, $bid,$adminpinyin);
  		if($return){
  			$state 	= $this->HandleGoods($goid);
  		}else{
  			$state 	= 2;
  		}
  	}
  	return $state;
  }
  /**
   * 检测管理员是否有权限操控该订单
   * @param 管理员id $adminid
   * @param 商品id $goid
   */
  public function CheckAdminIsAuth($adminid,$goid,$bid,$adminpinyin){
	  return true;
  	if($adminpinyin=="partner"){
  		$where = "goods_order.bid=$bid AND goods_order.goid =$goid AND  admin.adminpid=$adminid";
  	}else{
  		$where = "goods_order.bid=$bid AND goods_order.goid =$goid AND admin.admin_id=$adminid";
  	}
  	$model 	= M("goods_order");
  	$sql 		= "SELECT
					goods_order.goid
				FROM
					goods_order
				LEFT JOIN business_info ON goods_order.bid = business_info.b_id 
  				LEFT JOIN admin ON business_info.b_id=admin.bid 
  				WHERE {$where}";
  	$result 	= $model->query($sql);
  	if(!empty($result)){return true;}else{return false;}
  }
  /**
   * 进行发货操作
   * @param unknown $goid
   * @return number
   */
  public function HandleGoods($goid){
  		$model 		= M("order_detail");
  		$data['isfh'] 	= 2;
	  	$data['delivertime']	= date('Y-m-d H:i:s');
	  	$data['autoshtime']	= date('Y-m-d H:i:s', strtotime('+7 days'));
  		$save 		= $model->where("goid2=$goid")->save($data);
  		if(!is_bool($save)){
			$orderInfo = M("goods_order")->field("goods_order.userid,order_detail.gname")->join("order_detail ON goods_order.goid=order_detail.goid2")->where("order_detail.goid2=$goid AND order_detail.isth=1")->select();
			//print_r($orderInfo);die;
			if(!empty($orderInfo)){
				$goodsdesc	= "";
				for($i=0;$i<count($orderInfo);$i++){
					$goodsdesc	.="[ ".$orderInfo[$i]['gname']." ],";
				}
				$userphone	= D("Common")->GetUserPhoneFromUserid($orderInfo[0]['userid']);
				if(!empty($orderInfo[0]['userid'])){
					$this->PushSystem("用户下单","【嘿谷商城】提示您：您购买的商品：".$goodsdesc."已经在路上了",2,$orderInfo[0]['userid']);//用户下单通知商家站内信
					$this->jpushonly($orderInfo[0]['userid'],"【嘿谷商城】提示您：您购买的商品：".$goodsdesc."已经在路上了",array("isseller"=>0,"state"=>3));
				}
				if(!empty($userphone)){
					$content	= "【嘿谷商城】提示您：您购买的商品：".$goodsdesc."已经在路上了";
					$this->sendPhoneCodeFromOrder($userphone,$content);
				}
			}
  			return 2;
  		}else{
  			return 1;
  		}
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
     * 极光推送多个
     */
    public function jpushAll($content,$extras){
        include_once './ThinkPHP/Library/Vendor/Jpush/autoload.php';
        $title   =  '嘿谷商城';
        $appkeys = "84e5cedc91211ed2585f55eb";
        $masterSecret = "e424b9816c6ca06439148c3b";
        $client = new \JPush\Client($appkeys,$masterSecret);
        // ->message($content,['title' => 'Hello','content_type' => 'text','extras' => ['key' => 'value']
        $result = $client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->iosNotification($content, [
                'title' => $title,
                'sound' => 'sound',
                'badge' => '+1',
                'extras'=>array("isseller"=>6,"state"=>$extras)
            ])
            ->androidNotification($content, [
                'title' => $title,
                'sound' => 'sound',
                'badge' => '+1',
                'extras'=>array("isseller"=>6,"state"=>$extras)
            ])
            ->options(array(
                "apns_production" => false  //true表示发送到生产环境(默认值)，false为开发环境
            ))
            ->send();
        //echo 'Result=' . json_encode($result);
        //echo 'Result=' . json_encode($result);
    }
	/**
	 * 极光推送 单个
	 */
	public function jpushonly($tag1,$content,$extras=""){
		if(is_string($tag1)){
			$tag     = $tag1;
		}else{
			$tag     = (string)$tag1;
		}
		include_once './ThinkPHP/Library/Vendor/Jpush/autoload.php';
		$title   =  '嘿谷商城';
		$appkeys = "84e5cedc91211ed2585f55eb";
		$masterSecret = "e424b9816c6ca06439148c3b";
		$client = new \JPush\Client($appkeys,$masterSecret);
		// ->message($content,['title' => 'Hello','content_type' => 'text','extras' => ['key' => 'value']
		$result = $client->push()
				->setPlatform(['ios', 'android'])
				->addTag($tag)
				->iosNotification($content, [
						'title' => $title,
						'sound' => 'sound',
						'badge' => '+1',
						'extras'=>$extras
				])
				->androidNotification($content, [
						'title' => $title,
						'sound' => 'sound',
						'badge' => '+1',
						'extras'=>$extras
				])
				//->message($content,['title' =>$title,'content_type' => 'text'])
				->send();
		//echo 'Result=' . json_encode($result);
	}
	/*
     * 发送订单类的验证码
     */
	public function sendPhoneCodeFromOrder($mobile,$content){
		$userid = '400376';
		$timespan = date('YmdHis');
		$password = '606638';
		$pwd = strtoupper(md5($password.$timespan));
		// $random = new \Org\Net\Random();
		// $code = $random->number(4);
		$str = $content;
		$content = base64_encode($str);

		$url = "http://api.shumi365.com:8090/sms/send.do";

		$post_data['userid'] = $userid;
		$post_data['pwd'] = $pwd;
		$post_data['timespan'] = $timespan;
		$post_data['msgfmt'] = 'UTF8';
		$post_data['mobile'] = $mobile;
		$post_data['content'] = $content;

		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($post_data)); // Post提交的数据包
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
		$result = curl_exec($curl); // 执行操作
		if (curl_errno($curl)) {
			echo 'Error POST'.curl_error($curl);
		}
		curl_close($curl); // 关键CURL会话
		//echo $result;exit;
		if(strlen($result)>5){
			return true;
		}else{
			return false;
		}
	}
  /**
   * 计算每个支付方式收到的金额
   * @param unknown $payid
   * @return string
   */
  public function GetPayAccount_m($payid){
  	$model 	= M("goods_order");
  	$result 		= $model->field("actulpay,refund_total")->where("payid=$payid AND ispay=2")->select();
  	$total 		= CountTotal($result, "actulpay");
	  //echo "实付款".$total."<br>";
    $total1		= CountTotal($result,"refund_total");
	  //echo "退款".$total1."<br>";
    $total2		= bcsub($total,$total1,2);
	  //echo "实到账".$total2."<br>";die;
  	$str 		= "<font style='color:red'>￥".$total2."</font>";
  	return $str;
  }
  /**
   * 验证用户密码是否正确
   * @param unknown $pass
   * @param unknown $adminId
   * @return number
   */
  public function AuthInfo($pass,$adminId){
  	$adminInfo 	= $this->GetAdminInfo($adminId);
  	if(hash("sha256",$pass) == $adminInfo['admin_password']){
  		return 1;
  	}else{
  		return 2;
  	}
  }
  /**
   * 获取管理员信息
   * @param string $adminId
   * @param string $where
   * @param string $field
   * @return \Think\mixed
   */
  public function GetAdminInfo($adminId="",$where="",$field=""){
  	$field 		= $field?$field:"admin_password";
  	$where 	= $where?$where:"admin_id=$adminId";
  	$model 	= M("admin");
  	$result 		= $model->field($field)->where($where)->find();
  	return $result;
  }
  /**
   * 审核提现功能
   * @param unknown $adminId
   * @param unknown $adminpinyin
   * @param unknown $tid
   * @param unknown $userid
   * @return string|number
   */
  public function AuthTx($adminId,$adminpinyin,$tid,$userid){
  	if($adminpinyin == "manager"){
  		$state 		= $this->HandleAuth($tid);
  	}else{
  		$state 		= $this->CheckHandleUser($tid);
  	}
  	if(!$state){
  		return "您没有操作该用户数据的权限";
  	}else{
  		return 1;
  	}
  }
  /**
   * 处理审核结果
   * @param unknown $tid
   * @return boolean
   */
  public function HandleAuth($tid){
  	$model 	= M("nmt_user_tx");
  	$save['isauth'] 		= 2;
  	$result 		= $model->where("nutid=$tid")->save($save);
  	if(!empty($result)){
  		return true;
  	}else{
  		return false;
  	}
  }
  /**
   * 查看管理员是否有操作该用户的权限
   * 2017.03.18
   */
  public function CheckHandleUser($adminId,$userId){
  	$model 	= M("user");
  	$result 		= $model->where("adminid=$adminId AND user_id=$userId")->find();
  	if(!empty($result)){
  		return true;
  	}else{
  		return false;
  	}
  }
  /**
   * 检测管理员是否有操作用户的权限
   * @param unknown $bId
   * @param unknown $adminId
   * @return boolean
   */
  public function CheckAdminHandleBusiness($bId,$adminId){
  	$model 	= M("admin");
  	$result 		= $model->where("bid=$bId AND adminpid = $adminId")->find();
  	if(!empty($result)){
  		return true;
  	}else{
  		return false;
  	} 	
  }
  /**
   * 检测该管理员是否存在代发货订单
   * @param unknown $adminid
   * @param unknown $adminpinyin
   * @param unknown $bid
   * @return unknown
   */
  public function CheckIsOrder($adminid,$adminpinyin,$bid,$groupid){
  	if($adminpinyin == "manager"){
  		$data 		= $this->FindFHOrder("isfh=1 AND goods_order.ispay=2");
  	}elseif ($adminpinyin =="seller"){
  		$data 		= $this->FindFHOrder("bid=$bid  AND goods_order.ispay=2 AND isfh=1");
  	}elseif ($adminpinyin == "admin"){
		$data		= $this->getAdminColumn($groupid);
		$flag		= 0;
		for($i=0;$i<count($data);$i++){
			if($data[$i]['id']==28){
				return $this->FindFHOrder(" isfh=1  AND goods_order.ispay=2");
			}
		}
  		return $flag;
  	}
  	return $data;
  }
	/**
	 * 检测改管理员是否存在退货申请
	 */
	public function CheckReturnOrder($adminid,$adminpinyin,$bid,$groupid){
		if($adminpinyin == "manager"){
			$data 		= $this->FindFHOrder("isth=2 AND goods_order.ispay=2");
		}elseif ($adminpinyin =="seller"){
			$data 		= $this->FindFHOrder("bid=$bid  AND goods_order.ispay=2 AND isth=2");
		}elseif ($adminpinyin == "admin"){
			$data		= $this->getAdminColumn($groupid);
			$flag		= 0;
			for($i=0;$i<count($data);$i++){
				if($data[$i]['id']==54){
					return $this->FindFHOrder(" isth=2  AND goods_order.ispay=2");
				}
			}
			return $flag;
		}
		return $data;
	}
  /**
   * 获取待处理订单的条数
   * @param unknown $where
   * @param string $join
   * @return unknown
   */
  public function FindFHOrder($where,$join=""){
  	$model 	= M("goods_order");
  	$result 		= $model->field("goods_order.goid")->join("order_detail on order_detail.goid2=goods_order.goid")->where($where)->count();
  	/*echo $model->getLastSql();
	  print_r($result);die;*/
  	return $result;
  }
  /**
   * 检测该管理员下是否有人提现
   * @param unknown $adminid
   * @param unknown $adminpinyin
   * @param unknown $bid
   * @return Ambigous <number, \Home\Model\unknown>
   */
  public function CheckIsWithDraw($adminid,$adminpinyin,$bid){
  	$data = 0;
  	if($adminpinyin == "manager"){
  		$data 		= $this->FindWithDraw("isauth=1");
  	}elseif ($adminpinyin == "partner"){
  		$join 		= "LEFT JOIN `user` ON nmt_user_tx.userid2 = `user`.user_id ";
  		$data 		= $this->FindWithDraw("`user`.adminid=$adminid AND isauth=1",$join);
  	}
  	return $data;
  }
  /**
   * 获取提现申请条数
   * @param string $where
   * @param string $join
   * @return unknown
   */
  public function FindWithDraw($where="",$join=""){
  	$model 		= M("nmt_user_tx");
  	$result 			= $model->field("nmtid")->where($where)->count();
  	return $result;
  }
  /**
   * 获取省市区等信息
   * @param string $where
   * @param string $oreder
   */
  public function getRegion($where="",$oreder="code asc"){
      $data     = array();//返回数组
      $data     = M("h_region")->field("code,fullName")->where($where)->order($oreder)->select();
      if(is_bool($data)){
          return 0;
      }else{
          if(!empty($data)){
              return $data;
          }else{
              return 0;
          }
      }
  }

	/**
	 * 获取角色设置的栏目
	 * @param $groupid
	 * @return array
	 */
	public function getSetGroupColumn($groupid){
		$column1 	= $this->getAdminColumn($groupid);
		$column2	= $this->GetisSetMenu();
		$column 		= unlimitedForLevel($this->HandleSetColumn($column1,$column2));
		return $column;
	}

	/**
	 * 获取管理员的栏目
	 * @param $groupid
	 * @return int
	 */
	public function getAdminColumn($groupid){
		$model	= M("nmt_admin_group");
		$result	= $model->field("columnidstr")->where("id=$groupid")->find();
		$idstr	= $result['columnidstr'];
		if(empty($idstr)){
			return 0;
		}
		$model 	= M("nmt_column");
		$result 		= $model->field("id,title as text,url,pid as parentid,0 as isrole")->where("id in($idstr)")->order("sort asc")->select();
		return $result;
	}
	/**
	 * 获取可以设置的栏目
	 * @return mixed
	 */
	public function GetisSetMenu(){
		$model 	= M("nmt_column");
		$result 		= $model->field("id,title as text,pid as parentid,0 as isrole")->where("id not in(3,16,17,18,55)")->order("sort asc")->select();
		return $result;
	}

	/**
	 * 处理可以设置的栏目
	 * @param $column1
	 * @param $column2
	 * @return mixed
	 */
	public function HandleSetColumn($column1,$column2){
			if(!empty($column1)){
				for($i=0;$i<count($column2);$i++){
					for($j=0;$j<count($column1);$j++){
						if($column2[$i]['id']==$column1[$j]['id']){
							$column2[$i]['isrole']	= 1;
						}
					}
				}
				return $column2;
			}else{
				return $column2;
			}
	}

	/**
	 * 设置角色栏目权限
	 * @param $groupid
	 * @param $columnid
	 * @return array
	 */
	public function SetRoleColumn($groupid,$columnid){
		$model	= M("nmt_admin_group");
		$save['columnidstr']	= substr($columnid,0,(strlen($columnid)-1));
		$result	= $model->where("id=$groupid")->save($save);
		if(is_bool($result)){
			return array("code"=>204,"message"=>"提交数据有误");
		}else{
			return array("code"=>200,"message"=>"设置成功");
		}
	}
	public function sendMsgAll($text){
		$model	= M("`user`");
		$result	= $model->field("user_phone as phe")->where("user_phone != ''")->select();
		$this->PushSystem("系统消息",$text,"3","");
		if(!empty($result)){
			for($i=0;$i<count($result);$i++){
				if(preg_match('/^\d{4,11}$/', $result[$i]['phe'])){
					$this->sendPhoneNotice($result[$i]['phe'],$text);
				}
			}
		}
		return 1;
	}
	public function businesList(){
        $result = M("business_info")->field("b_id as bid,`name`")->select();
    }
	/*
     * 发送通知
     * 日期：2017.02.04
     */
	public function sendPhoneNotice($mobile,$text){
		$userid = '400376';
		$timespan = date('YmdHis');
		$password = '606638';
		$pwd = strtoupper(md5($password.$timespan));
		// $random = new \Org\Net\Random();
		// $code = $random->number(4);
		$str = '【嘿谷商城】'.$text;
		$content = base64_encode($str);

		$url = "http://api.shumi365.com:8090/sms/send.do";

		$post_data['userid'] = $userid;
		$post_data['pwd'] = $pwd;
		$post_data['timespan'] = $timespan;
		$post_data['msgfmt'] = 'UTF8';
		$post_data['mobile'] = $mobile;
		$post_data['content'] = $content;

		$curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($post_data)); // Post提交的数据包
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout); // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
		$result = curl_exec($curl); // 执行操作
		if (curl_errno($curl)) {
			echo 'Error POST'.curl_error($curl);
		}
		curl_close($curl); // 关键CURL会话
		//echo $result;exit;
		if(strlen($result)>5){
			return true;
		}else{
			return false;
		}
	}
}