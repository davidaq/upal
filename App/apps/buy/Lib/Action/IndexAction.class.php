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
		
		$this->buy = D('Buy');
		$_SESSION['language']='zh-cn';
		
	}
	// A basicly static page, provides searching, hot tags display
	function index(){
		$this->getRecentItem();
		$this->display();
	}
	function getUserItems() {
		$uid = $_POST['uid'];
		$ret = $this->buy->getUserItems();
		print_r($ret);
	}
	function getRecentItem() {
		$num = $_POST['num'];
		$ret = $this->buy->getRecentItem($num);
		echo "aaaaasdgagag!";
		print_r($ret);
		$this->assign('recentItem', $res);
	}
	function search() {
		$name = $_GET['buy_title'];
		$ret = $this->buy->searchItemByName($name);
		print_r($ret);
	}
}
