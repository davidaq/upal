<?php
class OperationAction extends Action{
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
		$this->wiki = D('Wiki');
		$this->wikiTag = D('WikiTag');
		$this->wikiPost = D('WikiPost');
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
		$data['author'] = $_POST['post_author'];
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$this->wikipost->add($data);
	}
	function editPost() {
		$data['title'] = $_POST['post_title'];
		$data['author'] = $_POST['post_author'];
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$this->wikipost->save($data);
	}
	function editWikiDescription() {
		$id = $_POST['wiki_id'];
		$des = $_POST['wiki_des'];
		$this->setTag();
		$this->wiki->setWikiDescription($id, $des);
	}
	function deleteWiki() {
		$wid = $_POST['wid'];
		$this->wiki->removeWiki($wid);
	}
	function deletePost($pid) {
		$pid = $_POST['pid'];
		$this->wikipost->remove($pid);
	}
	function setTag($wid=false) {
		$tag = $_POST['tags'];
		$wid = ($wid)?$wid:$_POST['wiki_id'];
		$this->wikiTag->setWikiTags($wid, $tag);
	}
}
