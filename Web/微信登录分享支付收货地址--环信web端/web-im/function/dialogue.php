<?php
/**
 * 处理对话
 * 2017.06.15
 */
try {   
     $fromUser  = $_POST['fromtoken'];//发送人员
     $touser    = $_POST['totoken'];//接收人员
     $shopname  = $_POST['shopname'];//店铺名称
     $logo      = $_POST['logo'];//店铺log
     $msg       = $_POST['msg'];//消息
     $fromname  = $_POST['fromhead'];//姓名
     $fromphoto = $_POST['fromphoto'];//图片
     $file      = "dialogue_".$fromUser."-$touser".".json";
     $timeFormat= date("Y-m-d H:i:s");
     $timeStamp = time();
     $msgData   = array("fromhead"=>$fromname,"fromphoto"=>$fromphoto,"fuser"=>$fromUser,"touser"=>$touser,"msg"=>$msg,"tformat"=>$timeFormat,"tstamp"=>$timeStamp,"shopname"=>$shopname,"logo"=>$logo);
     @$filedata= file_get_contents("./cache/$file");
     if(!empty($filedata)){
       exit(json_encode(array("code"=>"200","message"=>"成功")));
       $oldMsg  = (array)json_decode($filedata);//对象数组转化为数组
     }else{
        /*  $file     = "dialogue_".$touser."_$fromUser".".json";
         @$filedata= file_get_contents("./cache/$file");
         if(!empty($filedata)){
             $oldMsg  = (array)json_decode($filedata);//对象数组转化为数组
         } */
     }
     if(!empty($oldMsg)){
         $strMsg    = json_encode(array_merge($oldMsg,$msgData));
     }else{
         $strMsg    = json_encode($msgData);
     }
     $fielModel = fopen("./cache/$file","w+");
     fwrite($fielModel,$strMsg);
     fclose($fielModel);
     exit(json_encode(array("code"=>"200","message"=>"成功")));
} catch (Exception $e) {   
    exit(json_encode(array("code"=>"204","message"=>"失败")));
}   
?>