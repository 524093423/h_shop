<?php
/**
 后台用户模型
 * 冯晓磊
 * 2017.03.13
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class AdminModel extends Model {
    /*
   * 验证用户名是否正确
     * 日期:2016年10月9日
     * 返回值：0 用户名输入有误
   */
    public function CheckUsername($username){
        $admin  = M("admin");
        $adminInfo  = $admin->field("admin_id as id,bid,admin_name as adname,admin_password as pwd,usepeople as user,groupname,pinyin,nmt_admin_group.id as groupid")
        					->join("nmt_user_group on admin.admin_id=nmt_user_group.adminid")
        					->join("nmt_admin_group on nmt_user_group.groupid=nmt_admin_group.id")
        					->where("admin_name='%s'",$username)->find();
        if(!empty($adminInfo)){
            return $adminInfo;
        }
        return 0;
    }
    /**
     * 商家登陆
     * @param unknown $phone
     * @param unknown $pwd
     */
    public function CheckSeller($phone,$pwd){
        $model      = M("user");
        $result     = $model->field("user_id,salt,user_password")->where(array("user_phone"=>$phone,"is_seller"=>"1"))->find();
        if(empty($result)){
            return returnJs("您还不是商家用户","error");
        }
        $datapwd    = $result['user_password'];
        $pwd        = $pwd.$result['salt'];
        if(md5($pwd) != $datapwd){
            return returnJs("输入的密码有误", "error");
        }
        $userid     = $result['user_id'];
        $model      = M("h_seller_detail");
        $result     = $model->field("business_info.`name` as shopname,b_id")
                        ->join("business_info ON h_seller_detail.seller_id = business_info.seller_id","INNER")
                        ->where("h_seller_detail.user_id=$userid")
                        ->find();
        if(empty($result)){
            return returnJs("您还不是商家用户","error");
        }
        session("nmt_admin",$result['shopname'],"600");
        session("nmt_adminid","shop-".$userid,"600");
        session("seller_buserid",$userid,"600");
        session("nmt_name",$result['shopname'],"600");
        session("nmt_groupname","商家","600");
        session("nmt_pinyin","seller","600");
        session("nmt_groupid",2,"600");
        session("logintype",2,"600");
        session("nmt_bid",$result['b_id'],"600");
        return 1;
    }
}