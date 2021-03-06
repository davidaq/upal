<?php
include_once 'ModelCommon.php';
class WikiModel extends Model{
	public function getUserCreatedWiki($uid){
		$uid = intval($uid);
		$r = $this->where(array('creator'=>$uid))->field('id,keyword')->select();
		return $r;
	}
	public function setWikiVerified($wid,$v=true){
		$data['id'] = intval($wid);
		$data['verified'] = $v?1:0;
		$this->save($data);
	}
	public function getUserJoinedWiki($uid){
		$uid=intval($uid);
		$r = M('wiki_member')->where(array('user_id'=>$uid))->field('wiki_id')->select();
		$ids = getValues($r,'wiki_id');
		if(!$ids)
			return array();
		return $this->where(array('id'=>array('IN',$ids)))->field('id,keyword')->select();
	}
	public function wikiMember($wid){
		$wid = intval($wid);
		$r = $this->where(array('id'=>$wid))->field('creator')->select();
		if($r==false)
			return false;
		$ret = array($r[0]['creator']);
		$r = M('wiki_member')->where(array('wiki_id'=>$wid))->field('user_id')->select();
		$r = getValues($r,'user_id');
		if($r==false)
			return $ret;
		$ret = array_merge($ret,$r);
		return $ret;
		
	}
	public function createWiki($title,$description,$uid){
		$title=trim($title);
		$data['keyword']=$title;
		$data['creator']=$uid;
		$data['description']=$description;
		$data['cTime']=time();
		return $this->add($data);
	}
	public function removeWiki($wid,$uid){
		$this->where(array('id'=>intval($wid),'creator'=>$uid))->delete();
		$P = C('DB_PREFIX');
		M('wiki_member')->where("wiki_id NOT IN (SELECT id FROM {$P}wiki)")->delete();
	}
	public function joinWiki($uid,$wid){
		$data['user_id']=intval($uid);
		$data['wiki_id']=intval($wid);
		M('wiki_member')->add($data);
	}
	public function leaveWiki($uid,$wid){
		$data['user_id']=intval($uid);
		$data['wiki_id']=intval($wid);
		M('wiki_member')->where($data)->delete();
	}
	public function searchWikiByTitleSimilar($title) {
		$title=htmlspecialchars($title);
		$name=preg_split('/(\&nbsp\;|[\s,;])+/i',$title);
		$map = '`keyword` <> "'.$title.'" AND ( 1=2';
		foreach($name as $f){
			$map.=' OR `keyword` like "%'.$f.'%" OR `description` like "%'.$f.'%"';
		}
		$map.=' )';
		$r = $this->where($map)->limit(50)->order(array('id'=>'DESC'))->field('id, keyword, description')->select();
		return $r;
	}
	public function searchWikiByTitleAccurate($title) {
		$r = $this->where(array("keyword"=>$title))->field('id, keyword, description')->select();
		return $r;
	}
	public function setWikiDescription($wid, $v) {
		$data['id'] = intval($wid);
		$data['description'] = $v;
		$this->save($data);
	}
}
