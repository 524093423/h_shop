<?php
// 后台成功首页
namespace Home\Controller;
use Think\Controller;
class AdminController extends CheckController {
	/*
	 * 登陆界面
	 * 2016.12.16
	 */
    public function index(){
    	$this->display("admin");
    }
    /*
     * 管理中心
     * 2016.12.16
     */
    public function Center(){
    	$this->display("center");
    }
    /*
     * 退出系统
     * 2016.12.31
     */
    public function adminOut(){
    	session(null);
    }
}