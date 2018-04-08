<?php 
/**
 * 日期函数
 * 2017.02.27
 * @来自分享
 */
//这个星期的星期一
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function this_monday($timestamp=0,$is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$monday_date = date('Y-m-d', $timestamp-86400*date('w',$timestamp)+(date('w',$timestamp)>0?86400:-/*6*86400*/518400));
		if($is_return_timestamp){
			$cache[$id] = strtotime($monday_date);
		}else{
			$cache[$id] = $monday_date;
		}
	}
	return $cache[$id];
	 
}
 
//这个星期的星期天
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function this_sunday($timestamp=0,$is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$sunday = this_monday($timestamp) + /*6*86400*/518400;
		if($is_return_timestamp){
			$cache[$id] = $sunday;
		}else{
			$cache[$id] = date('Y-m-d',$sunday);
		}
	}
	return $cache[$id];
}
 
//上周一
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function last_monday($timestamp=0,$is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$thismonday = this_monday($timestamp) - /*7*86400*/604800;
		if($is_return_timestamp){
			$cache[$id] = $thismonday;
		}else{
			$cache[$id] = date('Y-m-d',$thismonday);
		}
	}
	return $cache[$id];
}
 
//上个星期天
// @$timestamp ，某个星期的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
function last_sunday($timestamp=0,$is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$thissunday = this_sunday($timestamp) - /*7*86400*/604800;
		if($is_return_timestamp){
			$cache[$id] = $thissunday;
		}else{
			$cache[$id] = date('Y-m-d',$thissunday);
		}
	}
	return $cache[$id];
	 
}
 
//这个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
 
function month_firstday($timestamp = 0, $is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$firstday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp),1,date('Y',$timestamp)));
		if($is_return_timestamp){
			$cache[$id] = strtotime($firstday);
		}else{
			$cache[$id] = $firstday;
		}
	}
	return $cache[$id];
}
 
//这个月的最后一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
 
function month_lastday($timestamp = 0, $is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$lastday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp),date('t',$timestamp),date('Y',$timestamp)));
		if($is_return_timestamp){
			$cache[$id] = strtotime($lastday);
		}else{
			$cache[$id] = $lastday;
		}
	}
	return $cache[$id];
}
 
//上个月的第一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
 
function lastmonth_firstday($timestamp = 0, $is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$firstday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp)-1,1,date('Y',$timestamp)));
		if($is_return_timestamp){
			$cache[$id] = strtotime($firstday);
		}else{
			$cache[$id] = $firstday;
		}
	}
	return $cache[$id];
}
 
//上个月的最后一天
// @$timestamp ，某个月的某一个时间戳，默认为当前时间
// @is_return_timestamp ,是否返回时间戳，否则返回时间格式
 
function lastmonth_lastday($timestamp = 0, $is_return_timestamp=true){
	static $cache ;
	$id = $timestamp.$is_return_timestamp;
	if(!isset($cache[$id])){
		if(!$timestamp) $timestamp = time();
		$lastday = date('Y-m-d', mktime(0,0,0,date('m',$timestamp)-1, date('t',lastmonth_firstday($timestamp)),date('Y',$timestamp)));
		if($is_return_timestamp){
			$cache[$id] = strtotime($lastday);
		}else{
			$cache[$id] =  $lastday;
		}
	}
	return $cache[$id];
}
/**
 * 检测两个时间的大小生成对应的sql语句
 * @time1
 * @time2
 * 2017.02.27
 */
function createTime($time1,$time2,$str=""){
	if(strtotime($time1) > strtotime($time2)){
		$time1 .=" 23:59:59";
		$time2 .=" 00:00:00";
		$where .="  AND $str  between '".$time2."' and '".$time1."' ";
	}else{
		$time2 .=" 23:59:59";
		$time1 .=" 00:00:00";
		$where .="  AND $str  between '".$time1."' and '".$time2."' ";
	}
	return $where;
}
//组合一维数组
function unlimitedForLevel ($cate, $html = '&nbsp;&nbsp;&nbsp;', $pid = 0, $level = 0) {
	$arr = array();
	foreach ($cate as $k => $v) {
		if ($v['parentid'] == $pid) {
			$v['level'] = $level + 1;
			$v['html']  = str_repeat($html, $level);
			if($level >= 1){
				$v['str'] 		= "┣";
			}
			$arr[] = $v;
			$arr = array_merge($arr, unlimitedForLevel($cate, $html, $v['id'], $level + 1));
		}
	}
	return $arr;
}
//组合多维数组
 function unlimitedForLayer ($cate, $name = 'child', $pid = 0) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['parentid'] == $pid) {
			$v[$name] = unlimitedForLayer($cate, $name, $v['id']);
			if(is_array($v)){
				$arr[] = array_filter($v);
			}
		}
	}
	return $arr;
}
//传递一个子分类ID返回所有的父级分类
function getParents ($cate, $id) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['id'] == $id) {
			$arr[] = $v;
			$arr = array_merge(getParents($cate, $v['parentid']), $arr);
		}
	}
	return $arr;
}
//传递一个父级分类ID返回所有子分类ID
function getChildsId ($cate, $pid) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['parentid'] == $pid) {
			$arr[] = $v['id'];
			$arr = array_merge($arr,getChildsId($cate, $v['id']));
		}
	}
	return $arr;
}
//传递一个父级分类ID返回所有子分类
function getChilds ($cate, $pid) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['parentid'] == $pid) {
			$arr[] = $v;
			$arr = array_merge($arr,getChilds($cate, $v['id']));
		}
	}
	return $arr;
}
/**
 * 构造返回的js代码
 * @param 文字 $text
 * @param 成功或者失败对应的英文 $code
 */
function returnJs($text="成功",$code="success",$location=""){
	if($code =='error'){
		$icon 	= 2;
	}else{
		$icon 	= 1;
	}
	if(empty($location)){
		$js 				= "layer.msg(\"$text\",{time:800})";
	}else{
		$js 				= "layer.msg(\"$text\",{time:800},function(){
									$location
								})";
	}
/* 	$js 				= "layer.msg(\"$text\",{icon:\"$icon\",time:800})";
	$js 				= "$.jBox.tip(\"$text\", \"$code\");";
	$js 				.= $location; */
	return $js;
}
/**
 * 导出excel(csv)
 * @data 导出数据
 * @headlist 第一行,列名
 * @fileName 输出Excel文件名
 */
function csv_export($data = array(), $headlist = array(), $fileName) {
	ini_set("output_buffering","On");
	@header('Content-Type: application/vnd.ms-excel');
	@header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
	@header('Cache-Control: max-age=0');
	//打开PHP文件句柄,php://output 表示直接输出到浏览器
	$fp = fopen('php://output', 'a');
	//$fp 	 = fopen("./".$, $mode)
	//输出Excel列名信息
	foreach ($headlist as $key => $value) {
		//CSV的Excel支持GBK编码，一定要转换，否则乱码
		$headlist[$key] = iconv('utf-8', 'gbk', $value);
	}
	//将数据通过fputcsv写到文件句柄
	fputcsv($fp, $headlist);
	//计数器
	$num = 0;
	//每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
	$limit = 100000;
	set_time_limit(0);
	//逐行取出数据，不浪费内存
	$count = count($data);
	for ($i = 0; $i < $count; $i++) {
		$num++;
		//刷新一下输出buffer，防止由于数据过多造成问题
		if ($limit == $num) {
			ob_flush();
			flush();
			$num = 0;
		}
		$row = $data[$i];
		foreach ($row as $key => $value) {
			if(!empty($value)){
				if($key == "orderno" || $key =="otime"){
					$row[$key] = "\"\t".iconv('utf-8', 'gbk', $value)."\"\t";
				}else{
					$row[$key] = iconv('utf-8', 'gbk', $value);
				}
			}
		}
		fputcsv($fp, $row);
	}
}
/**
 * excel  导出
 * @param unknown $headlist
 * @param unknown $array
 * @param unknown $fileName
 */
function excel_import($headlist,$array,$fileName){
	date_default_timezone_set('Europe/London');
	/** Include PHPExcel */
	//require_once '../Classes/PHPExcel.php';
	import("Vendor.PHPExcel.PHPExcel");
	// Create new PHPExcel object
	$objPHPExcel = new \PHPExcel();
	#创建人
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
	#最后修改人
	$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
	#标题
	$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
	#题目
	$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
	#描述
	$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
	#关键字
	$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
	#种类
	$objPHPExcel->getProperties()->setCategory("Test result file");
	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
	->setLastModifiedBy("Maarten Balliauw")
	->setTitle("Office 2007 XLSX Test Document")
	->setSubject("Office 2007 XLSX Test Document")
	->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	->setKeywords("office 2007 openxml php")
	->setCategory("Test result file");
	#合并单元格
	//$objPHPExcel->getActiveSheet()->mergeCells('A18:E22');
	#设置宽width
	// Set column widths
	#$objPHPExcel->getActiveSheet()->getColumnDimension($a[$j].$k)->setAutoSize(true);
	#$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
	// 设置单元格高度
	// 所有单元格默认高度
	$objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
	// 第一行的默认高度
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
	#设置font
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Candara');
	//$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20);
	/* $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
	$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
	$objPHPExcel->getActiveSheet()->getStyle('D13')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('E13')->getFont()->setBold(true); */
	#设置align
	/* $objPHPExcel->getActiveSheet()->getStyle('D11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$objPHPExcel->getActiveSheet()->getStyle('D12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$objPHPExcel->getActiveSheet()->getStyle('D13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY); */
	//垂直居中
	/* $objPHPExcel->getActiveSheet()->getStyle('A18')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
	#设置column的border
	/* $objPHPExcel->getActiveSheet()->getStyle('A4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$objPHPExcel->getActiveSheet()->getStyle('B4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$objPHPExcel->getActiveSheet()->getStyle('C4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$objPHPExcel->getActiveSheet()->getStyle('D4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	$objPHPExcel->getActiveSheet()->getStyle('E4')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	#设置border的color
	$objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getLeft()->getColor()->setARGB('FF993300');
	$objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getTop()->getColor()->setARGB('FF993300');
	$objPHPExcel->getActiveSheet()->getStyle('D13')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
	$objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getTop()->getColor()->setARGB('FF993300');
	$objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getBottom()->getColor()->setARGB('FF993300');
	$objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getRight()->getColor()->setARGB('FF993300'); */
	#设置填充颜色
	/* $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->getStartColor()->setARGB('FF808080'); */
	//$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	#设置所有列的宽
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth("26");
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth("20");
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth("25");
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth("15");
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth("9");
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth("18");
	$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth("15");
	$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth("36");
	$a = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	//头部信息
	$j = 0;
	$k = 1;
	foreach($headlist as $key=>$val){
		#设置头部水平居中
		//$objPHPExcel->getActiveSheet()->getStyle($a[$j])->getAlignment()>setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($a[$j].$k, $val);
		$j++;
	}
	$m = 2;//头部已经填充了第一行内容需要从第二行开始
	//内容开始
	for($i=0;$i<count($array);$i++){
		$data 	= $array[$i];
		$n = 0;
		foreach($data as $key=>$val){
			if($key =="orderno"){
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($a[$n].$m, "\"\t".$val."\"\t");
				$n++;
			}else{
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($a[$n].$m, $val);
				$n++;
			}
		}
		$m++;
	}
	//sheet 名字
	$objPHPExcel->getActiveSheet()->setTitle('MyGod');
	//保存至那个worksheet中
	$objPHPExcel->setActiveSheetIndex(0);
	//写入文件
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$fileName = $fileName.".xls";
	header('Content-Disposition: attachment;filename='.$fileName);
	// 启动浏览器下载功能
	header('Content-Type: application/vnd.ms-excel');
	/*header('Cache-Control: max-age=0');*/
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	//header('Content-Disposition:inline;filename="'.$filename.'"');
	header("Content-Transfer-Encoding: binary");
	header("Last-Modified: " .gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: no-cache");
	$objWriter->save('php://output');
	exit;
}
function convertUTF8($str)
{
	if(empty($str)) return '';
	return  iconv('gb2312', 'utf-8', $str);
}
?>
