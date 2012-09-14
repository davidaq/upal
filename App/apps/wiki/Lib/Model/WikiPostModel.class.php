<?php
include_once 'ModelCommon.php';
class WikiPostModel extends Model{
	public function add($rawdata){
		$data['title'] = trim($rawdata['title']);
		$data['author'] = intval($rawdata['author']);
		$data['wiki_id'] = intval($rawdata['wiki_id']);
		$data['content'] = intval($rawdata['content']);
		$data['cTime'] = time();
		Model::add($data);
	}
	public function save($rawdata){
		$data['id'] = intval($rawdata['id']);
		$data['title'] = trim($rawdata['title']);
		$data['author'] = intval($rawdata['author']);
		$data['wiki_id'] = intval($rawdata['wiki_id']);
		$data['content'] = intval($rawdata['content']);
		$data['cTime'] = time();
		Model::save($data);
	}
	public function setOrder($id,$order){
		$data['id'] = intval($id);
		$data['order'] = intval($order);
		Model::save($data);
	}
	public function remove($id){
		$this->where(array('id'=>intval($id)))->delete();
	}
	public function listOfWIki($wid){
		$wid = intval($wid);
		$r = $this->where(array('wiki_id'=>$wid))->field('id,title')->select();
		return getValues($r);
	}
}
