<?php
include_once 'ModelCommon.php';
class BuyModel extends Model{
	public function getUserItems($uid){
		$uid = intval($uid);
		$r = $this->where(array('owner'=>$uid))->select();
		return $r;
	}
	public function setItemVerified($id,$v=true){
		$data['id'] = intval($id);
		$data['verified'] = $v?1:0;
		$this->save($data);
	}
	public function createItem($title,$description,$uid, $imgpath){
		$title=trim($title);
		$data['name']=$title;
		$data['owner']=intval($uid);
		$data['description']=$description;
		$data['img'] = $imgpath;
		$data['cTime']=time();
		return $this->add($data);
	}
	public function modifyItem($id, $title,$description,$uid, $imgpath) {
		$title=trim($title);
		$data['id'] = intval($id);
		$data['name']=$title;
		$data['owner']=intval($uid);
		$data['description']=$description;
		$data['img'] = $imgpath;
		$this->save($data);
	}
	public function removeItem($id) {
		$this->where(array('id'=>intval($id)))->delete();
	}
	public function searchItemByName($name) {
		$r = $this->where(array("name"=>array('like','%'.$name.'%')))->field('id, name, description, img, owner')->select();
		return $r;
	}
	public function getRecentItem($num) {
		$r = $this->order(array('cTime' => 'desc'))->limit($num)->select();
		return $r;
	}
	public function getGoodItems($num) {
		$r = $this->order(array('vote' => 'desc')) -> limit($num) -> select();
		return $r;
	}
	public function getHotItems($num) {
		$r = $M('BuyComment')->order(array('total' => 'desc'))->limit($num)->group("bid")-> select('bid, count(*) as total');
		return $r;
	}
	public function getGoodOwner($num) {

	}
}
