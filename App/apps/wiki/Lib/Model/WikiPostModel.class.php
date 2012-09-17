<?php
include_once 'ModelCommon.php';
class WikiPostModel extends Model{
	public function add($rawdata){
		$data['title'] = htmlspecialchars(trim($rawdata['title']));
		$data['author'] = intval($rawdata['author']);
		$data['wiki_id'] = intval($rawdata['wiki_id']);
		$data['content'] = htmlspecialchars($rawdata['content']);
		$data['cTime'] = time();
		$data['order'] = time();
		Model::add($data);
	}
	public function save($rawdata){
		$data['id'] = intval($rawdata['id']);
		$data['title'] = htmlspecialchars(trim($rawdata['title']));
		$data['author'] = intval($rawdata['author']);
		$data['wiki_id'] = intval($rawdata['wiki_id']);
		$data['content'] = htmlspecialchars($rawdata['content']);
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
	public function listOfWIki($wid,$wantBody=false){
		$wid = intval($wid);
		$r = $this->where(array('wiki_id'=>$wid))->order('`order`')->field($wantBody?'id,title,author,content':'id,title')->select();
		return $r;
	}
	public function get($pid){
		$r = $this->where(array('id'=>intval($pid)))->select();
		if($r)
			return $r[0];
		else
			return false;
	}
}
