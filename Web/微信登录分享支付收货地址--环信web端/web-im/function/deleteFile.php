<?php
/**
 * 删除聊天文件
 */ 
 $token = $_GET['token'];//用户token
 $sellertoken   = $_GET['sellerToken'];//店铺token
 $filename  = "dialogue_".$token."-".$sellertoken.".json";
 if(!file_exists("./cache/".$filename)){
     exit(json_encode(array("code"=>"200","message"=>"成功")));
 }
 if (!unlink("./cache/".$filename)){
     exit(json_encode(array("code"=>"204","message"=>"失败")));
 } else{
     exit(json_encode(array("code"=>"200","message"=>"成功")));
 }
?>