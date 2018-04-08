<?php
/**
 * 分页模型
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class PageModel extends Model {
   /*
    * 公共分页sql
    * 2016.12.16
    */
	public function SqlStr($TableName="",$Coll="",$WhereStr="",$WhereStr2="",$PageIndex="",$PageSize="",$OrderKey="",$OrderType="",$join="",$joinId=""){
            $sql = "";
            if ($WhereStr == ""){
                $WhereStr = "Where 1=1";
                $WhereStr2 = "";
            }else{
                $WhereStr = "Where 1=1  ".$WhereStr;
                $WhereStr2 = "Where  1=1 ".$WhereStr2;
            }
            if ($PageIndex > 1){
                $sql = "Select  ".$Coll." FROM " . $TableName . " " . " ".$join." ".$WhereStr . " and ".$joinId."<(Select MIN(".$joinId.") FROM (Select ".$joinId." FROM " . $TableName . " ". " ".$join." ". $WhereStr2 . " order by " . $OrderKey . " " . $OrderType ." "."limit"." ".($PageIndex -1) * $PageSize.") t) order by " . $OrderKey . " " . $OrderType." "."limit ".$PageSize;
            }else{
                $sql = "select ".$Coll." from " . $TableName . " ".$join." " . $WhereStr2 . " order by " . $OrderKey . " " . $OrderType." "."limit ".$PageSize;
            }
            //echo $sql;
            return $sql;
	}
	/*
	 * 分页文本
	 * 2016.12.17
	 */
	public function PageText($url="",$page="",$pagenum="",$show=""){
// 		$html = "<span class='disabled'>«上一页</span>";
// 		$html .="<span class='current'>1</span>";
// 		$html .="<a href=\"\">..</a>";
// 		$html .="<a href=\"\">下一页»</a>";
		if($pagenum ==0){
			return "";
		}
		$spage = "";
		if(($page-1)<=0){
			$spage = 1;
		}else{
			$spage = $page-1;
		}
		$xpage = "";
		if(($page+1)>$pagenum){
			$xpage = $pagenum;
		}else{
			$xpage = $page+1;
		}
		$html = "";
		if($page == 1){
			$html .="<span class='disabled'>«上一页</span>";
		}else{
			$html .="<a href=".$url.$spage.">«上一页</a>";
		}
		if($page > ($show + 1)){
			$html .= "<a href=".$url.($page - $show - 1).">..</a>";
		}
		for($i=$page-$show;$i<=$page+$show;$i++){
			if($i == $page){
				$html .= "<span class='current'>".$page."</span>";
			}else if($i>0 && $i<=$pagenum){
				$html .="<a href=".$url.$i.">".$i."</a>";
			}
		}
		if($page < ($pagenum - $show)){
			$html .="<a href=".$url.($page + $show + 1).">..</a>";
		}
		if($page == $pagenum){
			$html.="<span class='disabled'>下一页»</span>";
		}else{
			$html.="<a href=".$url.$xpage.">下一页»</a>";
		}
		return $html;
	}
}