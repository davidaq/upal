<?php
error_reporting(E_ALL);
class IndexAction extends Action{
	private $buy;
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
		global $ts;
		$this->app_alias = $ts['app']['app_alias'];
		
		$this->wiki = D('Buy');
		$_SESSION['language']='zh-cn';
		
	}
	// A basicly static page, provides searching, hot tags display
	function index(){
		$this->display();
	}
	function getUserItems() {
		$uid = $_POST['uid'];
		$ret = $buy->getUserItems();
		print_r($ret);
	}
	function getRecentItem() {
		$num = $_POST['num'];
		$ret = $buy->getRecentItem($num);
		print_r($ret);
	}
}
