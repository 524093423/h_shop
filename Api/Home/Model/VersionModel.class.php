<?php
/**
 * 版本检测
 * 20170528
 */
namespace Home\Model;
use Think\Model;
header("Content-Type: text/html; charset=UTF-8");
class VersionModel extends Model
{
	public $AdminUrl = 'http://192.168.0.186/h_shop';
	/**
	 * 版本接口
	 * @version_no 版本号
	 */
	public function GetCheck($version_no){
		$info = M('app_config')->field("edition_no as version_no,downloadaddress as version_url")->find();
		$check = version_compare($version_no, $info['version_no'],'<');
		$check_new = version_compare($version_no, $info['version_no'],'=');
		$check_old = version_compare($version_no, $info['version_no'],'>');
		   	if ($check) {
            	$returnData['code'] = 200;
			    $returnData['result'] ="版本不是最新版，请升级";
			    $returnData['info']['upload_url'] = $info['version_url'];
			    return $returnData;
        	}
        	if ($check_new) {
            	$returnData['code'] = 204;
			    $returnData['result'] ="版本是最新版";
			    return $returnData;
        	}
        	if ($check_old) {
            	$returnData['code'] = 200;
			    $returnData['result'] ="当前版本有误，请使用最新的";
			    $returnData['info']['upload_url'] = $info['version_url'];
			    return $returnData;
        	}
	}
}