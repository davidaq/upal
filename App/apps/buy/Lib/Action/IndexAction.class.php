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
	function rightItems(){
		#$list['hotItems'] = $this->buy->getHotItems(5);
		$list['goodItems'] = $this->buy->getGoodItems(5);
		echo json_encode($list);
	}
	function getUserItems() {
		$uid = $_POST['uid'];
		$ret = $this->buy->getUserItems();
		print_r($ret);
	}
	function getRecentItem() {
		$num = $_POST['num'];
		$ret = $this->buy->getRecentItem($num);
		print_r($ret);
		$this->assign('recentItem', $res);
	}
	function search() {
		$name = $_GET['buy_title'];
		$ret = $this->buy->searchItemByName($name);
		print_r($ret);
	}

	function getHotItems() {
		if (isset($_POST['num']))
			$num = $_POST['num'];
		else
			$num = 10;
		$ret = $this->buy->getHotItems($num);
		print_r($ret);
	}
	function getGoodItems() {
		if (isset($_POST['num']))
			$num = $_POST['num'];
		else
			$num = 10;
		$ret = $this->buy->getGoodItems($num);
		print_r($ret);
	}
}
