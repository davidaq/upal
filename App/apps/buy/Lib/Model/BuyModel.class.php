<?php
include_once 'ModelCommon.php';
class BuyModel extends Model{
	public function getItem($id){
		$r = $this->where(array('id'=>$id))->select();
		if($r)
			return $r[0];
		else
			return false;
	}
	public function getUserItems($uid,$page,$num){
		$uid = intval($uid);
		$c = $this->where(array('owner'=>$uid))->count();
		$pages=ceil($c/$num);
		$r = $this->where(array('owner'=>$uid))->limit($page*$num,$num)->select();
		return array('pages'=>$pages,'items'=>$r);
	}
	public function getOwner($id){
		$r = $this->where(array('id'=>intval($id)))->field('owner')->select();
		if($r){
			return $r[0]['owner'];
		}
		return 0;
	}
	public function createItem($title,$description,$uid,$count){
		$title=trim($title);
		$data['name']=$title;
		$data['owner']=intval($uid);
		$data['description']=$description;
		$data['img'] = '[]';
		$data['cTime']=time();
		$data['count']=intval($count);
		return $this->add($data);
	}
	public function modifyItem($id,$title,$description,$count) {
		$title=trim($title);
		$data['id'] = intval($id);
		$data['name']=$title;
		$data['description']=$description;
		$data['count']=intval($count);
		$this->save($data);
	}

	public function addImage($id,$path){
		$id = intval($id);
		$path = trim($path);
		$r = $this->where(array('id'=>$id))->field('img')->select();
		if($r){
			$r=json_decode($r[0]['img'],true);
			if(!in_array($path,$r)){
				$r[]=$path;
			}
			$r=json_encode($r);
			$data['id']=intval($id);
			$data['img']=$r;
			$r = $this->save($data);
		}
	}
	public function removeImage($id,$index){
		$id = intval($id);
		$r = $this->where(array('id'=>$id))->field('img')->select();
		if($r){
			$r=json_decode($r[0]['img'],true);
			unlink($r[$index]);
			unset($r[$index]);
			$r=json_encode($r);
			$data['id']=intval($id);
			$data['img']=$r;
			$r = $this->save($data);
		}
	}
	public function removeItem($id){
		$id = intval($id);
		$r = $this->where(array('id'=>$id))->field('img')->select();
		if($r){
			$r=json_decode($r[0]['img'],true);
			foreach($r as $f)
				unlink($f);
		}
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
		$r = M('BuyComment')->order(array('total' => 'desc'))->limit($num)->group("bid")->field("bid, count(*) as total") -> select();
		$ret = array();
		foreach ($r as $v) {
			//return $this->where(array('id' => intval($v['bid'])))->select();
			$ret = array_merge($ret, $this->where(array('id' => $v['bid']))->select());
		}
		return $ret;
	}
	public function getGoodOwner($num) {
		$r = $this->order(array('tmp' => 'desc')) -> limit($num) -> group('owner')->field("owner, avg(vote) as tmp") -> select();
		#return $r;
		$ret = array();
		foreach ($r as $v) {
			#return intval($v['owner']);
			$ret = array_merge($ret, M('User')->where(array('uid' => intval($v['owner'])))->select());
		}
		return $ret;
	}
}
