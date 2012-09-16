<?php
include_once 'ModelCommon.php';
class WikiTagModel extends Model{
	public function getWikiTags($wid){
		$r = $this->where(array('wiki_id'=>intval($wid)))->field('tag')->select();
		return getValues($r,'tag');
	}
	public function setWikiTags($wid,$keys=false){
		$wid = intval($wid);
		$this->where(array('wiki_id'=>$wid))->delete();
		if($keys){
			$keys = preg_split('/(?:\s|\&nbsp\;)+/i',htmlspecialchars(strtolower(trim($keys))));
			print_r($keys);
			$data['wiki_id'] = $wid;
			foreach($keys as $f){
				$data['tag'] = $f;
				$this->add($data);
			}
		}
	}
	public function getTagWikis($tag){
		if(is_array($tag)){
			foreach($tag as $k=>$v){
				$tag[$k]=trim($v);
			}
			$map['tag']=array('IN',$tag);
		}else{
			$map['tag']=trim($tag);
		}
		$r = $this->where($map)->field('wiki_id')->select();
		return getValues($r,'wiki_id');
	}
	public function getTagWikisCount($tag){
		$r = $this->where(array('tag'=>strtolower(trim($tag))))->field('wiki_id')->count();
		return $r;
	}
	public function searchWikiByTag($tag) {
		$tag = preg_split('/(?:\s|\&nbsp\;)+/i',htmlspecialchars(strtolower(trim($tag))));
		$ids = $this->where(array('tag'=>array('IN',$tag)))->field('wiki_id')->select();
		$ids = getValues($ids,'wiki_id');
		if($ids)
			return M('wiki')->where(array('id'=>array('IN',$ids)))->field("id, keyword, description")->select();
		return array();
	}
}
