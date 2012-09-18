<?php
error_reporting(E_ALL);
class IndexAction extends Action{
	private $buy;
	private $buyComment;
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
		global $ts;
		$this->app_alias = $ts['app']['app_alias'];
		
		$this->buy = D('Buy');
		$this->buyComment = D('BuyComment');
		$_SESSION['language']='zh-cn';
		
	}
	// A basicly static page, provides searching, hot tags display
	function index(){
		$this->getRecentItem();
		$this->display();
	}

	function rightItems(){
		$list['hotItems'] = $this->buy->getHotItems(5);
		$list['goodItems'] = $this->buy->getGoodItems(5);
		$list['goodOwners'] = $this->buy->getGoodOwner(5);
		echo json_encode($list);
	}
	function usershop(){
		if(isset($_GET['uid']))
			$user=intval($_GET['uid']);
		else
			$user=$this->mid;
		if($user==$this->mid)
			$this->assign('title','我的商品');
		else{
			$r=M('user')->where('uid='.$user)->field('uname')->select();
			$this->assign('title','<a href="'.U('home/Space/index',array('uid'=>$user)).'">'.$r[0]['uname'].'</a> 的商品');
		}
		$this->getUserItems($user);
		$this->display('myshop');
	}
	function myshop(){
		$this->usershop();
	}
	function getUserItems($user) {
		$uid = $_POST['uid'];
		$page = isset($_GET['page'])?intval($_GET['page']):0;
		$ret = $this->buy->getUserItems($user,$page,20);
		$this->assign('pager',array('total'=>$ret['pages'],'current'=>$page));
		$this->assign('userItem', $ret['items']);
	}
	function getRecentItem() {
		$ret = $this->buy->getRecentItem(20);
		$this->assign('recentItem', $ret);
	}
	function search() {
		$key = $_GET['buy_key'];
		$page=isset($_GET['page'])?intval($_GET['page']):0;
		$ret = $this->buy->searchItemByName($key,$page,20);
		$this->assign('searchResult',$ret['items']);
		$this->assign('pager',array('total'=>$ret['pages'],'current'=>$page));
		$this->assign('searchKey',htmlspecialchars($key));
		$this->display();
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
	function showitem(){
		if(isset($_GET['id'])){
			$id=intval($_GET['id']);
			$this->assign('isOwner',$this->buy->getOwner($id)==$this->mid);
			$this->assign('item',$this->buy->getItem($id));
			$comments=$this->buyComment->getComments($id);
			$this->assign('comments',$comments?$comments:array());
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
