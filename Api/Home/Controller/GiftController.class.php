<?php
namespace Home\Controller;
header("content-type:text/html;charset=utf-8");
use Think\Controller\RestController;
class GiftController extends RestController {
    private $_USERID;
    public function __construct() {
        $token  = $_REQUEST['token'];
		$userinfo = M('user')->where(array('token' => $token))->field('user_id')->find();
        $this->_USERID  = $userinfo['user_id'];
        if (!$this->_USERID) {
            $data['code'] = 204;
            $data['message'] = '未登录';
            $this->response($data);
        }
    }

    /* 
    获取礼物列表
    */    
    public function getGiftList() {
        $list = M('gift')->select();
        $data['code'] = 200;
        $data['message'] = '获取成功';
        $data['data'] = $list;
        $this->response($data);
    }

    /* 
    用户观看视频发送礼物
    @param $gift_id  礼物id
    @param $video_id 视频id
    @param $num      礼物数量
    @return json 
    */
    public function sendGift() {
        //检测视频Id 是否存在
        $user_id   = $this->_USERID;
        $video_id  = $_REQUEST['video_id'];
        if (!$video_id) {
            $data['code'] = 204;
            $data['message'] = '信息错误';
            $this->response($data);
        }
        //从数据中查询视频
        $result = M('city_video')->field('user_id')->where(array('id'=>$video_id))->find();
        if (!$result) {
            $data['code'] = 204;
            $data['message'] = '视频不存在';
            $this->response($data);            
        }
        $gift_id = $_REQUEST['gift_id'];
        $num     = $_REQUEST['num'] ? $_REQUEST['num'] : 1;
        //查询礼物所对应的价格
        $gift_info = M('gift')->where(array('id'=>$gift_id))->find();
        $price     = $gift_info['price']; 
        //从用户表中查看用户的余额是否足够,不够报错
        $integral  = M('user')->where(array('user_id'=>$user_id))->field('integral')->find()['integral'];
        if ($num * $price > $integral) {
            $data['code'] = 204;
            $data['message'] = '用户余额不足';
            $this->response($data);
        }
        //余额充足 添加礼物表
        $arr = [
            'user_id'    => $user_id,
            'video_id'   => $video_id,
            'gift_id'    => $gift_id,
            'num'        => $num,
            'money'      => $num * $price,
            'create_time'=> time()
        ];
        $add = M('city_video_gift')->add($arr);
        
        
        if ($num * $price != 0) {
            //扣除用户余额
            $model = M('user');
            $model->startTrans();
            $update = $model->where(array('user_id'=>$user_id))->setDec('integral',$num * $price);
            //视频用户 增加相应的余额
            $add    = $model->where(array('user_id'=>$result['user_id']))->setInc('integral',ceil($num * $price /2));
            if ($arr && !is_bool($update) && !is_bool($add)) {
                $model->commit();
                $data['code'] = 200;
                $data['message'] = '赠送成功';
                $this->response($data);
            }else{
                $model->rollback();
                $data['code'] = 204;
                $data['message'] = '赠送失败';
                $this->response($data);
            }       

        }else{
            $data['code'] = 200;
            $data['message'] = '赠送成功';
            $this->response($data);            
        }

    }
    /* 
    获取赠送视频礼物排行
    @param  $video   视频id
    */    
    public function getVideoRanking() {
        $video_id  = $_REQUEST['video_id'];
        //判断视频id 是否传过来
        if (!$video_id) {
            $data['code'] = 204;
            $data['message'] = '信息错误';
            $this->response($data);
        }
        //查询礼物表
        $sql = "SELECT sum(num) as num,`user_id`,sum(money) as money FROM `city_video_gift` WHERE ( `video_id` = {$video_id} ) GROUP BY user_id ORDER BY money desc LIMIT 10;";
        $list = M('city_video_gift')->query($sql);
        if (!$list) {
            $data['code'] = 204;
            $data['message'] = '暂时无人送礼';
            $this->response($data);            
        }
        foreach ($list as $key => $value) {
            //循环遍历 送礼用户的名字和头像
            $user_info = M('user')->field('user_name,user_photo')->where(array('user_id'=>$value['user_id']))->find();;
            $arr[$key] = array_merge($value,$user_info);    
        }
       $data['code']     = 200;
       $data['message']  = '获取排行榜详情成功';
       $data['data']     = $arr;
       $this->response($data); 
    }
    /* 
    获取赠送视频礼物列表
    */
    public function getVideoList() {
        $video_id = $_REQUEST['video_id'];
        //判断视频id 是否传过来
        if (!$video_id) {
            $data['code'] = 204;
            $data['message'] = '信息错误';
            $this->response($data);
        }
        // xxx --送 x个 xxx  
        $list = M('city_video_gift')->where(array('video_id'=>$video_id))->order('create_time desc')->select();
        $arr = [];
        foreach ($list as $key => $value) {
            //遍历获取用户姓名,头像
            $userinfo = M('user')->field('user_name,user_photo')->where(array('user_id'=>$value['user_id']))->find();
            //遍历获取礼物名字
            $name     = M('gift')->field('name')->where(array('id'=>$value['gift_id']))->find();
            unset($value['gift_id']);
            unset($value['video_id']);
            unset($value['id']);
            $arr[$key] = array_merge($name,$userinfo,$value);
        }
        $data['code']    = 200;
        $data['message'] = '获取成功';
        $data['data']    = $arr;
        $this->response($data);
    }

    /*
    用户之间送礼物
    */
    public function givingGifts(){
        $user_id = $this->_USERID;
        $from_id = $_REQUEST['user_id'];
        $gift_id = $_REQUEST['gift_id'];
        if (empty($from_id) || empty($gift_id)) {
            $data['code'] = 204;
            $data['message'] = '信息错误';
            $this->response($data);
        }
        $num     = $_REQUEST['num'] ? $_REQUEST['num'] : 1;
        //查询礼物所对应的价格
        $gift_info = M('gift')->where(array('id'=>$gift_id))->find();
        $price     = $gift_info['price']; 
        //从用户表中查看用户的余额是否足够,不够报错
        $integral  = M('user')->where(array('user_id'=>$user_id))->field('integral')->find()['integral'];
        if ($num * $price > $integral) {
            $data['code'] = 204;
            $data['message'] = '用户余额不足';
            $this->response($data);
        }
        //余额充足 添加礼物表
        $arr = [
            'user_id'    => $user_id,
            'from_id'    => $from_id,
            'gift_id'    => $gift_id,
            'num'        => $num,
            'money'      => $num * $price,
            'create_time'=> time()
        ];
        $add = M('user_gift')->add($arr);
    
        if ($num * $price != 0) {
            //扣除用户余额
            $model = M('user');
            $model->startTrans();
            $update = $model->where(array('user_id'=>$user_id))->setDec('integral',$num * $price);

            //被送用户 增加相应的余额
            $add    = $model->where(array('user_id'=>$from_id))->setInc('integral',ceil($num * $price /2));

            if ($arr && !is_bool($update) && !is_bool($add)) {
                $model->commit();
                $data['code'] = 200;
                $data['message'] = '赠送成功';
                $this->response($data);
            }else{
                $model->rollback();
                $data['code'] = 204;
                $data['message'] = '赠送失败';
                $this->response($data);
            }       

        }else{
            $data['code'] = 200;
            $data['message'] = '赠送成功';
            $this->response($data);            
        }               
    }

    /*
    获取用户的礼物列表
    */
    public function getUserGiftList() {
        $user_id = $_REQUEST['user_id'];
        //判断视频id 是否传过来
        if (!$user_id) {
            $data['code'] = 204;
            $data['message'] = '信息错误';
            $this->response($data);
        }
        // xxx --送 x个 xxx  
        $list = M('user_gift')->where(array('from_id'=>$from_id))->order('create_time desc')->select();
        $arr = [];
        foreach ($list as $key => $value) {
            //遍历获取用户姓名,头像
            $userinfo = M('user')->field('user_name,user_photo')->where(array('user_id'=>$value['user_id']))->find();
            //遍历获取礼物名字
            $name     = M('gift')->field('name')->where(array('id'=>$value['gift_id']))->find();
            unset($value['gift_id']);
            unset($value['from_id']);
            unset($value['id']);
            $arr[$key] = array_merge($name,$userinfo,$value);
        }
        $data['code']    = 200;
        $data['message'] = '获取成功';
        $data['data']    = $arr;
        $this->response($data);        
    }

    /*
    获取总的打赏排行榜
    */
    public function totalRankingList() {
        //视频总排行榜
        $sql = "SELECT sum(num) as num,`user_id`,sum(money) as money FROM `city_video_gift` GROUP BY user_id ORDER BY money desc LIMIT 10;";
        $video_list = M('city_video_gift')->query($sql);
        //遍历出用户头像和用户名
        $video_arr = [];
        foreach ($video_list as $key => $value) {
            $user_id = $value['user_id'];
            $user_info = M('user')->where(array('user_id'=>$user_id))->field('user_name,user_photo')->find();
            $video_arr[$key] = array_merge($user_info,$value);
        }        
        //用户总排行榜
        $sql = "SELECT sum(num) as num,`user_id`,sum(money) as money FROM `user_gift` GROUP BY user_id ORDER BY money desc LIMIT 10;";
        $user_list = M('user_gift')->query($sql);
        $user_arr  = [];
        foreach ($user_list as $key => $value) {
            $user_id = $value['user_id'];
            $user_info = M('user')->where(array('user_id'=>$user_id))->field('user_name,user_photo')->find();
            $user_arr[$key]  = array_merge($user_info,$value);    
        }
        $data['code'] = 200;
        $data['message'] = '获取成功';
        $data['video'] = $video_arr;
        $data['user']  = $user_arr;
        $this->response($data);
    }
}