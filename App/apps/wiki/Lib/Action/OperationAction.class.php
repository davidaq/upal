<?php
class OperationAction extends Action{
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
		$this->wiki = D('Wiki');
		$this->wikiTag = D('WikiTag');
		$this->wikiPost = D('WikiPost');
		$this->wikiMember = D('wikiMember');
	}
	function createWiki(){
		if(isset($_POST['wiki_title'])&&isset($_POST['wiki_description'])){
			$title = $_POST['wiki_title'];
			$des = $_POST['wiki_description'];
			$uid = $this->mid;
			$wid = $this->wiki->createWiki($title, $des, $uid);
			$this->setTag($wid);
			die('ok');
		}else{
			die('bad post');
		}
	}
	function addPost() {
		$data['title'] = $_POST['post_title'];
		$data['author'] = $this->mid;
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$this->wikiPost->add($data);
		die('ok');
	}
	function editPost() {
		$data['id'] = intval($_POST['pid']);
		$data['title'] = $_POST['post_title'];
		$data['author'] = $this->mid;
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$this->wikiPost->save($data);
		die('ok');
	}
	function editWikiDescription() {
		$id = $_POST['wiki_id'];
		$des = $_POST['wiki_des'];
		$this->setTag();
		$this->wiki->setWikiDescription($id, $des);
		die('ok');
	}
	function deleteWiki() {
		$wid = $_GET['wid'];
		$this->wiki->removeWiki($wid,$this->mid);
		$this->redirect('wiki/Index/index');
	}
	function deletePost($pid) {
		$pid = $_POST['pid'];
		$this->wikipost->remove($pid);
	}
	private function setTag($wid=false) {
		$tag = $_POST['tags'];
		$wid = ($wid)?$wid:$_POST['wiki_id'];
		$this->wikiTag->setWikiTags($wid, $tag);
	}
	private function editable($wid,$strict=false){
		if($this->mid==1)
			return true;
		$editable=false;
		$r = $this->wiki->where(array('id'=>$wid))->field('creator')->select();
		if($r){
			$r=$r[0];
			if($strict)
				return $r['creator']==$this->mid;
			if($r['creator']==$this->mid)
				$editable=true;
			else{
				$editable=0<$this->wikiMember->where(array('wiki_id'=>$wid,'user_id'=>$this->mid))->count();
			}
		}
		return $editable;
	}
	public function saveOpr(){
		if(!isset($_POST['wid']))
			return;
		$wid = intval($_POST['wid']);
		if($this->editable($wid)){
			$del['wiki_id']=$wid;
			$del['id']=array('NOT IN',$_POST['ids']);
			$this->wikiPost->where($del)->delete();
			$order=1;
			foreach($_POST['ids'] as $f){
				$this->wikiPost->setOrder($f,$order);
				$order++;
			}
			die('ok');
		}
	}
	public function vote(){
		$wid=intval($_POST['wid']);
		if(isset($_SESSION['voted'][$wid]))
			die('voted');
		$_SESSION['voted'][$wid]=1;
		$vote=intval($_POST['vote']);
		if($vote>0)
			$this->wiki->where(array('id'=>$wid))->setInc('vote',1);
		else
			$this->wiki->where(array('id'=>$wid))->setDec('vote',1);
		die('ok');
	}
	public function removeEditor(){
		$wid=intval($_GET['wid']);
		if($this->editable($wid,true)){
			$uid=intval($_GET['uid']);
			$this->wiki->leaveWiki($uid,$wid);
		}
		$this->redirect('wiki/Index/show',array('wid'=>$wid));
	}
	public function addEditor(){
		$wid=intval($_POST['wid']);
		if($this->editable($wid,true)){
		$m=M('user');
			$uid=$m->where(array('uname'=>$_POST['uname']))->field('uid')->select();
			if($uid&&$uid[0]['uid']!=$this->mid){
				$this->wiki->joinWiki($uid[0]['uid'],$wid);
			}
		}
		$this->redirect('wiki/Index/show',array('wid'=>$wid));
	}
}
