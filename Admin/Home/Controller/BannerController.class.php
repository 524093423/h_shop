<?php
/*
 * 后台管理
 * 轮播图控制器
 * 2016.12.22
 */
namespace Home\Controller;
use Think\Controller;
class BannerController extends CheckController {
	 /**
	  * 添加图片接口
	  * 2016.12.21
	  */
	public function AddBanner(){
		$bnnerid      = I("get.id");
		$banner   = array();
		$regionId = 0;
		$parentid = 0;
		$str 			= 0;//如果为no即为新增数据
		if(!empty($bnnerid)){
			$banner 	= D("Banner")->GetCarouselOnly($bnnerid);
			$str 			= $bnnerid;
			$regionId = $banner['cityid'];
			$parentid = $banner['citypid'];
		}
		/**
		 * 商品分类 暂时不需要
		 * $cate 	= D("Classify")->sortOut($gcid);
		 * $this->assign("cate",$cate);
		 */
        $shopinfo = M("business_info")->field("business_info.b_id AS bid,business_info.`name`")
            ->join("h_seller_detail on business_info.seller_id = h_seller_detail.seller_id")
            ->where("h_seller_detail.`status`=2")->select();
        $this->assign("shopinfo",$shopinfo);
		$this->assign("regionId",$regionId);//城市id
		$this->assign("parentid",$parentid);//城市所属省id
		$this->assign("upstr",$str);
		$this->assign("banner",$banner);
		$this->display("banner_add");
	}
	/**
	 * 上传图片接口
	 * 2016.12.21
	 */
	public function UploadImg(){
		 	if(!empty($_FILES)){
		 		$upload = new \Think\Upload();// 实例化上传类
		 		$upload->maxSize = 3145728;// 设置附件上传大小
		 		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		 		$upload->rootPath = './Uploads/'; // 设置附件上传根目录
		 		$upload->savePath = 'carousel/'; // 设置附件上传（子）目录
		 		// 上传文件
		 		$info = $upload->upload();
		 		if(!$info){
		 			$src = "";
		 			$issuccess = 1;
		 		}else{
		 			$issuccess = 0;
		 			$filename = $info['filedata']['savename'];
		 			$src = "./Uploads/".$info['filedata']['savepath'].$filename;
		 			$info= "请点击提交保存按钮进行保存";
		 		}
		 	}else{
		 		$src = "";
		 		$issuccess = 1;
		 		$info="图片不存在";
		 	}
            echo "<script>window.parent.uploadSuccess(\"$info\",\"$src\",\"$issuccess\");</script>";
	}
	/**
	 * 录入数据
	 * 2017.03.06
	 */
	public function newlyImg(){
		//$data['cityid'] = $_POST['city'];
		//$rstate = D("Banner")->checkIsCarouselNum($data['cityid']);
		//CheckClassImgCount D("Classify")
		$upid 			= I("post.upid");
		$gclid 			= I("post.SelectClass");
		/*$state 			= D("Classify")->CheckClassImgCount($gclid);
		if($upid =="0"){
			if($state >=5){
				echo 2;
				exit();
			}
		}*/
		$imgurl 			= I("post.imgurl");
		$data['url'] 		= $imgurl;
		$photo = $_POST['fsrc'];
		$photo = str_replace("./", "/", $photo);
		$data['c_photo']= $photo;
		//城市
		$data['cityid']   = I("post.regionIds");
		$data['citypid']  = I("post.parentids");
		$data['uploadtime'] = date("Y-m-d");
		$data['bid']    = I('post.bid');
        $data['name']    = I('post.name');
		//print_r($data);die;
		if($upid !="0"){
			$return 	= M("carousel_photo")->data($data)->filter('strip_tags')->where("c_id=$upid")->save();
			if(!is_bool($return)){
				echo  1;
			}else{
				echo  0;
			}
			exit();
		}
		$data['sort'] 			 = rand(1,9);
		$return = M("carousel_photo")->data($data)->filter('strip_tags')->add();
		if(!is_bool($return)){
			echo  1;
		}else{
			echo  0;
		}
	}
	/**
	 * 图片列表
	 * 2017.03.06
	 */
	public function BannerList(){
		$page = $_GET['page']?$_GET['page']:1;
		if($page == 1){
			$no = 1;
		}else{
			$no = ($page-1) * 15 +1;
		}
		$model= D("Banner");
		$data = $model->GetBannerList($page);
		$list = $data['list'];
		$page = $data['page'];
		$this->assign("no",$no);
		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display("banner_list");
	}
	/**
	 * 删除本地图片
	 * 2017.03.06
	 */
	public function delimg(){
		$img = $_POST['frc'];
		$img = str_replace("./Uploads", "/Uploads", $img);
		$result = @unlink ($img);
		if ($result == false) {
			echo 0;
		} else {
			echo 1;
		}
	}
}