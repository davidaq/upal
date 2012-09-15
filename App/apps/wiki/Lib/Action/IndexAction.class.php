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
		}
		if (isset($_POST['wiki_tag']))
		{
			$key = $_POST['wiki_tag'];
			$tag = $_POST['wiki_tag'];
			$ret_tag = $this->wikiTag->searchWikiByTag($tag);
		}
		

		$this->assign('searchkey',$key);
		$this->assign('searchresult',array_merge($ret_wiki,$ret_tag));
		$this->display();
	}
	// Create/modify a wiki post to a wiki
	function edit(){		
	}
}
