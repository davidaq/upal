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
	function myshop(){
		$this->getUserItems();
		$this->display();
	}
	function getUserItems() {
		$uid = $_POST['uid'];
		$page = isset($_GET['page'])?intval($_GET['page']):0;
		$ret = $this->buy->getUserItems($this->mid,$page,20);
		$this->assign('pager',array('total'=>$ret['pages'],'current'=>$page));
		$this->assign('userItem', $ret['items']);
	}
	function getRecentItem() {
		$ret = $this->buy->getRecentItem(20);
		$this->assign('recentItem', $ret);
	}
	function search() {
		$name = $_GET['buy_title'];
		$ret = $this->buy->searchItemByName($name);
		print_r($ret);
	}
	function showitem(){
		if(isset($_GET['id'])){
			$id=intval($_GET['id']);
			$this->assign('isOwner',$this->buy->getOwner($id)==$this->mid);
			$this->assign('item',$this->buy->getItem($id));
			
			$this->display();
		}
	}
	function edititem(){
		$ditem['name']='';
		$ditem['count']=1;
		$ditem['description']='';
		$ditem['id']=0;
		if(isset($_GET['id'])){
			$item=$this->buy->getItem($_GET['id']);
			if($item){
				$ditem=$item;
			}
		}
		$this->assign('item',$ditem);
		$this->display();
	}
}
