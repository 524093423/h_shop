<?php
/**
 * 用户聊天列表
 * 2017.06.15
 */
 function HandleArray1($array,$token){
     if(empty($array)){
         return array();
     }
     $msgList = array();
     $m = 0;
     for ($i = 0; $i < count($array); $i++) {
         @$filedata= (array)json_decode(file_get_contents("./cache/".$array[$i]));
         //print_r($filedata);die;
         if(!empty($filedata)){
             if($token==$filedata['touser']){
                 $msgList[$m]['shopt'] =@$filedata['fuser'];
                 $msgList[$m]['shopn'] =@$filedata['fromhead'];
                 $msgList[$m]['shopl'] =@$filedata['fromphoto'];
             }else{
                 $msgList[$m]['shopt'] =@$filedata['touser'];
                 $msgList[$m]['shopn'] =@$filedata['shopname'];
                 $msgList[$m]['shopl'] =@$filedata['logo'];
             }
             $m++;

         }
     }
     $msgList   =  array_merge(array_filter(checkNull($msgList)));
     return $msgList;
 }

/**
 * @param $arr
 * @param $key
 * @return mixed
 * 删除二维数组中重复的
 */
function second_array_unique_bykey($arr, $key){
$tmp_arr = array();
foreach($arr as $k => $v)
{
    if(in_array($v[$key], $tmp_arr))   //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
    {
        unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值
    }
    else {
        $tmp_arr[$k] = $v[$key];  //将不同的值放在该数组中保存
    }
}
//ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值
return $arr;
}
 function checkNull($array){
     foreach ($array as $key=>&$val){
         if(empty($val['shopt']) || empty($val['shopn']) || empty($val['shopl'])){
             unset($array[$key]);
         }else{

         }
     }
     return $array;
 }
try {
    $handler = opendir('./cache/');
    $token  = $_POST['token'];
    if(empty($token)){
        exit(json_encode(array("code"=>"204","message"=>"列表为空")));
    }
    $fileArray  = array();
    $i  = 0;
    while( ($filename = readdir($handler)) !== false ){
            if($filename != "." && $filename != ".."){
                $fileArray[$i++]    = $filename;
            }
    }
    //print_r($fileArray);die;
    if(empty($fileArray)){
        exit(json_encode(array("code"=>"204","message"=>"列表为空")));
    }
    closedir($handler);
    $newArray   = array();
    $newArray2  = array();
    $n          = 0;
    $m          = 0;
    for ($j=0;$j<count($fileArray);$j++){
        if(!is_bool(strpos($fileArray[$j],"dialogue_".$token))){
            $newArray[$n++] = $fileArray[$j];
        }elseif(!is_bool(strpos($fileArray[$j],"-".$token))){
            $newArray2[$m++] = $fileArray[$j];
        }
    }
   /* print_r($newArray);
    echo "<pre>";
    print_r($newArray2);*/
    $info1 = HandleArray1($newArray,$token);
    $info2 = HandleArray1($newArray2,$token);
   /*  print_r($info1);
    print_r($info2);die; */
    $info  = array();
    if(!empty($info1)){
        if(!empty($info2)){
            $info   = array_merge($info1,$info2);
        }else{
            $info   = $info1;
        }
    }else{
        if(!empty($info2)){
            $info   = $info2;
        }
    }
    $info   =  second_array_unique_bykey($info,"shopt");
    exit(json_encode(array("code"=>"200","message"=>"列表为空","info"=>$info)));
} catch (Exception $e) {
    exit(json_encode(array("code"=>"204","message"=>"列表为空")));
}
?>