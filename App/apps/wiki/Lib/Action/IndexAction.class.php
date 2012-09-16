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
		if (isset($_POST['wiki_key']))
		{
			$key = $_POST['wiki_key'];
			$ret_wiki_acc = $this->wiki->searchWikiByTitleAccurate($key);
			$ret_wiki_sim = $this->wiki->searchWikiByTitleSimilar($key);
			$ret_tag = $this->wikiTag->searchWikiByTag($key);
			$this->assign('searchresultacc',$ret_wiki_acc);
		}
		if (isset($_POST['wiki_tag']))
		{
			$key = $_POST['wiki_tag'];
			$tag = $_POST['wiki_tag'];
			$ret_tag = $this->wikiTag->searchWikiByTag($tag);
		}
		

		$this->assign('searchkey',htmlspecialchars($key));
		$this->assign('searchresult',array_merge($ret_wiki_sim,$ret_tag));
		$this->display();
	}
	// Create/modify a wiki word
	function edit(){
		if(isset($_GET['id'])){
		}else{
			$this->assign('keyword',$_GET['keyword']);
			$this->assign('id',0);			
		}
		$this->display();
	}
}
