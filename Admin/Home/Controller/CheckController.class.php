<?php
/*
 * 农牧通公共控制器
 * 冯晓磊
 * Date: 2016-10-19
 * */
namespace Home\Controller;
use Think\Controller;
Class CheckController extends Controller{
 public function __construct() {
        parent::__construct();
       $ID  = session("nmt_adminid");
       if(empty($ID)){
       		echo "<script>if (top.location != self.location){top.location=self.location;}location.href='./admin.php?c=Index&a=index'</script>";
       } 
    }
}