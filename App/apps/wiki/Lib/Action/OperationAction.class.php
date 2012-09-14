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
		$title = $_POST['wiki_title'];
		$des = $_POST['wiki_description'];
		$uid = $_POST['creator_id'];
		$wiki->createWiki($title, $des, $uid);
	}
	function addPost() {
		$data['title'] = $_POST['post_title'];
		$data['author'] = $_POST['post_author'];
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$wikipost->add($data);
	}
	function editPost() {
		$data['title'] = $_POST['post_title'];
		$data['author'] = $_POST['post_author'];
		$data['content'] = $_POST['post_content'];
		$data['wiki_id'] = $_POST['wiki_id'];
		$wikipost->save($data);
	}
	function editWikiDescription() {
		$id = $_POST['wiki_id'];
		$des = $_POST['wiki_des'];
		$wiki->setWikiDescription($id, $des);
	}
	function deleteWiki() {
		$wid = $_POST['wid'];
		$wiki->removeWiki($wid);
	}
	function deletePost($pid) {
		$pid = $_POST['pid'];
		$wikipost->remove($pid);
	}
	function setTag() {
		$tag = $_POST['tags'];
		$wid = $_POST['wid'];
		$tag = explode(",", $tag);
		foreach ($tag as $k => $v) 
			$tag[$k] = trim($v);
		$wikitag->setWikiTags($wid, $tag);
	}
}
