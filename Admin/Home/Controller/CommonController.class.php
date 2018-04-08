<?php
//后台管理公共函数
namespace Home\Controller;
use Think\Controller;
class CommonController extends CheckController {
	/*
	 * 获取左边栏目
	 * 2016.12.16
	 */
	 public function getMeun(){
	 	$adminid 	= session("nmt_adminid");
	 	$adminpinyin 	= session("nmt_pinyin");
	 	$groupId 	= session("nmt_groupid");
	 	$menu 		= D("Common")->GetMenu($adminid,$adminpinyin,$groupId);
	 	$this->ajaxReturn($menu,"JSON");
	 }
	 /**
	  * 公共上传图片函数
	  * @param path 文件上传路径
	  */
	public function Uploads(){
		$path 		= $_POST['UpFilePath'];
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize = 3145728;// 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath = './Uploads/'; // 设置附件上传根目录
		$upload->savePath = $path.'/'; // 设置附件上传（子）目录
		// 上传文件
		$info = $upload->upload($_FILES);
		//print_r($info);
		if(!$info){
			$issuccess = 1;
			$msg = 0;
			$msgbox = "文件上传失败";
		}else{
			$issuccess = 0;
			$filename = $info['filedata']['savename'];
			$src = "./Uploads/".$info['filedata']['savepath'].$filename;
			//$src1 = "/Uploads/".$info[$path]['savepath'].$filename;
			/* $image = new \Think\Image();
			$image->open($src);
			$thumbName 	= "rect".$filename;
			$thumbsrc 		= "./Uploads/".$path."/".$thumbName;
			$thumbsrc1 		= "/Uploads/".$path."/".$thumbName; 
			//将图片裁剪为400x400并保存为corp.jpg
			$image->thumb(100,100)->save($thumbsrc);*/
			$msg = 1;
			$msgbox = "文件上传成功";
			$model	= M("goods_photo");
			$data['img_path']	= "/Uploads/".$info['filedata']['savepath'].$filename;
			$data['uploadtime']=date("Y-m-d");
			$data['sort']		= time();
			$result	= $model->data($data)->add();
		}
		echo "<script>window.parent.uploadSuccessCar(\"$msgbox\",\"$src\",\"$issuccess\",\"$result\");</script>";
	}
	/*
	 * 上传视频接口
	 * 2016.12.21
	 */
	public function UploadFlash(){
	    if(!empty($_FILES)){
	        $upload = new \Think\Upload();// 实例化上传类
	        $upload->maxSize = 1024*1024*1024;// 设置附件上传大小
	        $upload->exts = array('mp4', 'avi');// 设置附件上传类型
	        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
	        $upload->savePath = 'goods/flash/'; // 设置附件上传（子）目录
	        // 上传文件
	        $info = $upload->upload();
	        if(!$info){
	            $src = "";
	            $issuccess = 1;
	            $info= "文件超出最大限制";
	        }else{
	            $issuccess = 0;
	            $filename = $info["filedata_flash"]['savename'];
	            $src = "./Uploads/".$info["filedata_flash"]['savepath'].$filename;
	            $info= "上传成功";
	        }
	    }else{
	        $src = "";
	        $issuccess = 1;
	        $info="视频不存在";
	    }
	    echo "<script>window.parent.uploadSuccessflash(\"$info\",\"$src\",\"$issuccess\");</script>";
	}
	/**
	 * 删除上传文件
	 * 2017.03.08
	 */
	public function unlinkImg(){
		$imgurl 	= $_POST['img'];
		if(!empty($imgurl)){
			unlink($imgurl);
		}
		echo  returnJs("删除成功");
	}
	/**
	 * 验证管理员name是否重复
	 * 2017.03.09
	 */
	public function CheckAdmin(){
		$admin 		= $_POST['shopadmin'];
		$state 			= D("Common")->CheckAdmin($admin);
		echo $state;
	}
	/**
	 * 更新APP用户帐号状态
	 * 2017.03.09
	 */
	public function changeUser(){
		//print_r($_POST);
		$table = $_POST['table'];
		$column= $_POST['column'];
		$id    = $_POST['id'];
		$vals  = $_POST['vals'];
		$where = $_POST['ext'];
		$data[$column] = $vals;
		$model = M($table);
		$datas = $model->field($column)->where("`$where`= $id")->find();
		//echo $model->getLastsql();
		if($datas[$column] == $vals){
			echo 1;exit();
		}
		$return= $model->where("`$where` = $id")->save($data);
		//echo $model->getLastsql();
		echo $return;
	}
	/**
	 * 获取发货方式(json)
	 * 2017.03.15
	 */
	public function GetDelivery(){
		$data 	= D("Common")->GetDelivery();
		echo json_encode($data);
	}
	/**
	 * 开始发货
	 * 2017.03.16
	 */
	public function SendGoods(){
		$goid 	= $_POST['orderid'];
		$adminid 	= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$bid 			= session("nmt_bid");
		$returnText 		= D("Common")->SendGoods_m($goid,$adminid,$adminpinyin,$bid);
		echo $returnText;
	}
	/**
	 * 验证输入密码是否正确
	 * 2017.03.18
	 */
	public function AuthAdminInfo(){
		$pass 			= $_POST['pass'];
		$adminId 		= session("nmt_adminid");
		$data 			= D("Common")->AuthInfo($pass,$adminId);
		echo $data;
	}
	/**
	 * 审核某条提现
	 * 2017.03.18
	 */
	public function AuthTx(){
		$tid 		= $_POST['tid'];
		$userid = $_POST['userid'];
		$adminId 			= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$state 				= D("Common")->AuthTx($adminId,$adminpinyin,$tid,$userid);
		echo $state;
	}
	/**
	 * 暂停或关闭返利
	 * 2017-3-19
	 * [userid] => 1
    [odid] => 1
    [sg] => 1
	 */
	public function authFl(){
		$userid 	= $_POST['userid'];
		$odid 		= $_POST['odid'];
		$sg 		= $_POST['sg'];
		$adminId 	= session("nmt_adminid");
		$adminpinyin= session("nmt_pinyin");
		$text 		= D("Common")->authFl_m($adminId,$adminpinyin,$userid,$odid,$sg);
		echo $text;
	}
	/**
	 * 暂停单次返利
	 * 2017-3-19
	 */
	public function authFl_detail(){
		//print_r($_POST);
		$userid 	= $_POST['userid'];
		$odid 		= $_POST['odid'];
		$sg 		= $_POST['sg'];
		$adminId 	= session("nmt_adminid");
		$adminpinyin= session("nmt_pinyin");
		$text 		= D("Common")->authFl_detail_m($adminId,$adminpinyin,$userid,$odid,$sg);
		echo $text;
	}
	/**
	 * 检测是否有待处理订单
	 * 2017.03.22
	 */
	public function CheckOrder(){
		$adminid 			= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$bid 					= session("nmt_bid");
		$groupid		= session("nmt_groupid");
		$data 	= D("Common")->CheckIsOrder($adminid,$adminpinyin,$bid,$groupid);
		echo $data;
	}
	public function CheckReturnOrder(){
		$adminid 			= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$bid 					= session("nmt_bid");
		$groupid		= session("nmt_groupid");
		$data 	= D("Common")->CheckReturnOrder($adminid,$adminpinyin,$bid,$groupid);
		echo $data;
	}
	/**
	 * 检测是否有人申请提现
	 * 2017.03.22
	 */
	public function CheckWithDraw(){
		$adminid 			= session("nmt_adminid");
		$adminpinyin 	= session("nmt_pinyin");
		$bid 					= session("nmt_bid");
		$data 	= D("Common")->CheckIsWithDraw($adminid,$adminpinyin,$bid);
		echo $data;
	}
	/**
	 * 获取所有省
	 * 2017.05.16
	 */
	public function GetProvince(){
	    $data  =  D("Common")->getRegion("type=1");
	    echo json_encode($data);
	}
	/**
	 * 获取市
	 * 2017.05.16
	 */
	public function GetCity(){
	    $code  = I("get.code");
	    $data  =  D("Common")->getRegion("type=2 AND (parentCode=$code OR code=$code)");
	    echo json_encode($data);
	}
	/**
	 *获取县
	 *2017.05.16
	 */
	public function GetCounty(){
	    $code  = I("get.code");
	    $data  =  D("Common")->getRegion("type=3 AND (parentCode=$code OR code=$code)");
	    echo json_encode($data);
	}
	/**
	 * 获取城市banner
	 * 2017.05.16
	 */
	public function GetBannerJsonImg(){
	    $cityid    = I("post.cityid");
	    $img  = D("Banner")->getCityBanner($cityid,I("post.title"));
	    //{"title": "友情链接","id": 123,"start": 0,"data": [{"alt": name,"pid": 666,"src": src,"thumb": thumb}]}'
	    //$data = array("title"=>$title ,"id"=>"12","start"=>0,"data"=>array(array("alt"=>$alt,"src"=>$src,"thumb"=>$thumb)));
	    $data['title']     = I("post.title");
	    $data['id']        = $cityid;
	    $data['start']     = 0;
	    $data['data']      = $img;
	    //print_r($data);die;
	    echo json_encode($data);
	}
	public function sendMsgAll(){
		$text	= I("post.text");
		$result	= D("Common")->sendMsgAll($text);
		echo $result;
	}
}