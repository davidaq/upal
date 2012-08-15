<?php 
class UserTagModel extends Model{
	var $tableName = 'user_tag';
	
	//查找指定签标的用户
	function doSearchTag($k) {
		global $ts;
		$keyinfo = M('tag')->where("tag_name='{$k}'")->find();
		if ($keyinfo && $k ) {
			$list = $this->where("tag_id=".$keyinfo['tag_id'])->field('uid')->findPage();
			$uids = getSubByKey($list['data'], 'uid');
			
			/*
			 * 缓存用户的资料, 粉丝数, 关注数, Tag列表
			 */
			$user_model 	  = D('User', 'home');
			$user_count_model = model('UserCount');
			$user_model->setUserObjectCache($uids);
			$user_count_model->setUserFollowerCount($uids);
			$user_count_model->setUserFollowingCount($uids);
			$this->setUserTagObjectCache($uids);
			
			foreach ($list['data'] as $k => $v) {
				$list['data'][$k]['user']        = $user_model->getUserByIdentifier($v['uid']);
				$list['data'][$k]['taglist'] 	 = $this->getUserTagList($v['uid']);
				$list['data'][$k]['following']   = $user_count_model->getUserFollowingCount($v['uid']);
				$list['data'][$k]['follower']    = $user_count_model->getUserFollowerCount($v['uid']);
				$list['data'][$k]['followState'] = getFollowState( $ts['user']['uid'] , $v['uid'] );
			}
		}else {
			$list['count'] = 0;
		}
		
		return $list;
	}
	
	//获取感兴趣的Tag列表
	function getFavTageList($uid){
		$db_prefix = C('DB_PREFIX');
		$sql = "SELECT a.* FROM {$db_prefix}tag a 
			LEFT JOIN {$db_prefix}user_tag b  ON a.tag_id = b.tag_id  
			WHERE b.uid != {$uid} AND a.tag_id >= ((SELECT MAX(tag_id) FROM {$db_prefix}tag)-(SELECT MIN(tag_id) FROM {$db_prefix}tag)) * RAND() + (SELECT MIN(tag_id) FROM {$db_prefix}tag) 			
			LIMIT 15";
		
		$list = $this->query($sql);
		
		$tags = $return =  array();
		if(count($list)>0){
			foreach($list as $v){
				if(count($return) == 10 ){
					break;
				}
				if(!in_array($v['tag_name'],$tags)){
					$return[] = $v;
					$tags[] = $v['tag_name'];
				}
			}
		}
		return $return;

	
	}
	
	//添加Tag by Id
	function addUserTagById($tagid,$uid){
		$tagInfo = M('tag')->where('tag_id='.$tagid)->find();
		if($tagInfo){
			$userTagInfo = $this->where("uid=$uid AND tag_id=".$tagInfo['tag_id'])->find();
			if(!$userTagInfo){
				$data['uid'] = $uid;
				$data['tag_id'] = $tagInfo['tag_id'];
				$data['user_tag_id'] = $this->add($data);
				$data['tag_name'] = $tagInfo['tag_name'];
				$return['code'] = '1';
				$return['data'] = $data;
			}else{
				$return['code'] =  '0' ;
			}
		}else{
			$return['code'] = '0';
		}
		
		return json_encode( $return );
	}
	
	//添加用户tag
	function addUserTagByName($tagname,$uid,$nowcount){
		$tagInfo = $this->addTags($tagname,$nowcount);
		if($tagInfo){
			foreach ($tagInfo as $k=>$v){
				$userTagInfo = $this->where("uid=$uid AND tag_id=".$v['tag_id'])->find();
				if(!$userTagInfo){
					$data['uid'] = $uid;
					$data['tag_id'] = $v['tag_id'];
					if( $v['user_tag_id'] = $this->add($data) ){
						$tagdata[] = $v;
					}
				}
			}
			if($tagdata){
		    	$return['code'] =  '1' ;
		    	$return['data'] =  $tagdata ;
			}else{
				$return['code'] =  '0' ;
			}
		}else{
			$return['code'] =  '-1';
		}
		
		return json_encode( $return );
	}
	
	function doDel($tagid,$uid){
		if( $this->where("user_tag_id=$tagid AND uid=$uid")->delete() ){
			return '1';
		}
	}
	
	//添加全局tag
	private function addTags( $tagname,$nowcount ){
		if(!$tagname) return false;
		$tagname = explode(',', $tagname);
		foreach($tagname as $k=>$v){
			if( mb_strlen($v, 'UTF-8') > '10' || $v == '')continue;
			$result[] = $this->addOneTag($v);
			$addcount = $addcount+1;
			if( $addcount+$nowcount >= '10' )break;
		}
		return $result;
	}
	
	private function addOneTag($tagname){
		$map['tag_name'] = keyWordFilter(t($tagname));
		if( $info = M('tag')->where($map)->find() ){
			return $info;
		}else{
			$map['tag_id'] = M('tag')->add($map);
			return $map;
		}
	}
	
	public function setUserTagObjectCache(array $uids)
	{
		if (!is_numeric($uids[0]))
			return false;
			
		$base_cache_id = 'user_tag_';
		$uids = implode(',', $uids);
		$sql  = "SELECT a.*,b.tag_name FROM {$this->tablePrefix}user_tag a LEFT JOIN {$this->tablePrefix}tag b ON b.tag_id=a.tag_id WHERE a.uid IN ( {$uids} ) ORDER BY a.user_tag_id ASC";
		$res  = $this->query($sql);
		
		// 格式化为: array($uid => $tags_array)
		// 注: 每人最多含有10个标签
		$user_tags = array();
		foreach ($res as $v) {
			if (count($user_tags[$v['uid']]) >= 10)
				continue;
			else
				$user_tags[$v['uid']][] = $v;
		}
		
		foreach ($user_tags as $k => $v)
			object_cache_set($base_cache_id . $k, $v);
		
		return $res;
	}
	
	// 获取用户Tag列表
	public function getUserTagList($uid)
	{
		$base_cache_id = 'user_tag_';
		
		if (($res = object_cache_get($base_cache_id . $uid)) === false) {
			$this->setUserTagObjectCache(array($uid));
			$res  = object_cache_get($base_cache_id . $uid);
		}
			
		return $res;
	}
}
?>