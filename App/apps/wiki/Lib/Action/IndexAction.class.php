<?php
error_reporting(E_ALL);
class IndexAction extends Action{
	private $wiki;
	private $wikiTag;
	private $wikiPost;
	private $wikiMember;
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
		$this->wikiMember = D('wikiMember');
		$_SESSION['language']='zh-cn';
		
	}
	// A basicly static page, provides searching, hot tags display
	function index(){
		$this->assign('hot',$this->wikiTag->getPopularTags());
		$this->display();
	}
	// Display the wiki words that one created or joinedin

	function mywiki(){
		$list['create']=$this->wiki->getUserCreatedWiki($this->mid);
		$list['join']=$this->wiki->getUserJoinedWiki($this->mid);
		echo json_encode($list);
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
			$ret_tag = $this->wikiTag->searchWikiByTag($key);
			if($ret_tag)
				$res = array_merge($res,$ret_tag);
		}
		
		$this->assign('searchkey',htmlspecialchars($key));
		$this->assign('searchresult',$res);
		$this->display();
	}
	
	function editPost(){
		if(isset($_GET['pid'])&&$_GET['pid']>0){
			$pid=intval($_GET['pid']);
			$this->assign('post',$this->wikiPost->get($pid));
		}elseif(isset($_GET['wid'])){
			$this->assign('post',array('id'=>0,'wiki_id'=>intval($_GET['wid']),'title'=>'','content'=>''));
		}else
			return;
		$this->show();
	}
	// Create/modify a wiki word
	function edit(){
		if(isset($_GET['id'])){
			$r = $this->wiki->where(array('id'=>intval($_GET['id'])))->select();
			if($r){
				$r=$r[0];
				$this->assign('keyword',$r['keyword']);
				$tags = $this->wikiTag->getWikiTags($r['id']);
				if($tags)
					$tags = implode(' ',$tags);
				else
					$tags = '';
				$this->assign('tags',$tags);
				$this->assign('desc',$r['description']);
				$this->assign('id',$r['id']);
			}
		}else{
			$this->assign('keyword',$_GET['keyword']);
			$this->assign('tags','');
			$this->assign('desc','');
			$this->assign('id',0);			
		}
		$this->display();
	}
	
	public function show(){
		if(isset($_GET['wid'])){
			$wid=intval($_GET['wid']);
			$r = $this->wiki->where(array('id'=>$wid))->select();
			if($r){
				$r=$r[0];
				$this->assign('wiki',$r);
				$editable=false;
				if($r['creator']==$this->mid)
					$editable=true;
				else{
					$editable=0<$this->wikiMember->where(array('wiki_id'=>$wid,'user_id'=>$this->mid))->count();
				}
				$this->assign('tags',$this->wikiTag->getWikiTags($wid));
				$this->assign('editable',($this->mid==1)||$editable);
				$this->assign('isCreator',$r['creator']==$this->mid);
				$this->assign('posts',$this->wikiPost->listOfWIki($wid,true));
				$member = $this->wiki->wikiMember($wid);
				$r = M('user')->where(array('uid'=>array('IN',$member)))->field('uid,uname')->select();
				$this->assign('member',$r);
				$this->display();
			}
		}
	}
	function getUserJoinedWiki()
	{
		$id = $_POST['id'];
		$ret = $wiki->getUserJoinedWiki($id);
		print_r($ret);
	}
	function getUserCreatedWiki()
	{
		$id = $_POST['id'];
		$ret = $wiki->getUserCreatedWiki($id);
		print_r($ret);
	}
	function getPopularTags() {
		$ret = $wikiTaggetPupularTags();
		print_r($ret);
	}
}
