<?php 
class WeiboTopicModel extends Model{
	var $tableName = 'group_weibo_topic';
	
	//添加话题
	function addTopic($content, $gid){
		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is",$content,$arr);
		$arr = array_unique($arr[1]);
		foreach($arr as $v){
			$this->addKey($v, $gid);
		}
	}

	//添加话题
	private function addKey($key, $gid){
		$map['name'] = trim(t(mStr(preg_replace("/#/",'',trim($key)),150,'utf-8',false)));
		$map['gid']  = intval($gid);
		if( $this->where($map)->count() ){
			$this->setInc('count',$map);
		}else{
			$map['count'] = 1;
			$map['ctime'] = time();
			return $this->add($map);
		}
	}

	//获取话题ID
	function getTopicId($name, $gid){
		$map['name'] = trim(t(mStr(preg_replace("/#/",'',$name),150,'utf-8',false)));
		$map['gid']  = intval($gid);
		$info = $this->where($map)->find();
		if( $info['topic_id'] ){
			return $info['topic_id'];
		}else{
			if($map['name'] && $map['gid']){
				$map['count'] = 0;
				return $this->add($map);
			}
		}
	}

	function getHot($gid, $limit = 5){
		$hot_list = $this->where('gid=' . intval($gid) . ' AND `count`>0')->order('`count` DESC')->limit($limit)->findAll();		
		return $hot_list;
	}
	
	//最新话题
	function getNew($num, $gid){
		$gid = intval($gid);
		return $this->where("gid={$gid}")->order('cTime DESC')->limit($num)->findall();
	}
}
?>