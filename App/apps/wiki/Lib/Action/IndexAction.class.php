<?php
error_reporting(E_ALL);
class IndexAction extends Action{
	private $wiki;
	private $wikiTag;
	private $wikiPost;
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
		global $ts;
		$this->app_alias = $ts['app']['app_alias'];
		
		$this->wiki = D('Wiki');
		$this->wikiTag = D('WikiTag');
		$this->wikiPost = D('WikiPost');
		$_SESSION['language']='zh-cn';
		
	}
	// A basicly static page, provides searching, hot tags display
	function index(){
		$this->display();
	}
	// Display the wiki words that one created or joinedin

	function mywiki(){
		$this->display();
	}
	// Display search result
	function search(){
		$res=array();
		if (isset($_GET['wiki_key']))
		{
			$key = $_GET['wiki_key'];
			$ret_wiki_acc = $this->wiki->searchWikiByTitleAccurate($key);
			$ret_wiki_sim = $this->wiki->searchWikiByTitleSimilar($key);
			$ret_tag = $this->wikiTag->searchWikiByTag($key);
			if($ret_wiki_sim)
				$res = array_merge($res,$ret_wiki_sim);
			if($ret_tag)
				$res = array_merge($res,$ret_tag);
			$this->assign('searchresultacc',$ret_wiki_acc);
		}
		if (isset($_GET['wiki_tag']))
		{
			$key = $_GET['wiki_tag'];
			$tag = $_GET['wiki_tag'];
			$ret_tag = $this->wikiTag->searchWikiByTag($tag);
			if($ret_tag)
				$res = array_merge($res,$ret_tag);
		}
		
		$this->assign('searchkey',htmlspecialchars($key));
		$this->assign('searchresult',$res);
		$this->display();
	}
	// Create/modify a wiki word
	function edit(){
		if(isset($_GET['id'])){
		}else{
			$this->assign('keyword',$_GET['keyword']);
			$this->assign('tags','');
			$this->assign('id',0);			
		}
		$this->display();
	}
	public function show(){
		if(isset($_GET['id'])){
			$wid=intval($_GET['id']);
			$this->wiki->where(array('id'=>$wid))->select();
			$this->display();
		}
	}
}
