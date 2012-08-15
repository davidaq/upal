<?php
//微博收藏
class FriendshipsApi extends Api{
	//关注某用户
	function create(){
		$result = D('Follow','weibo')->dofollow($this->mid,$this->user_id);
		if ($result == '00' || $result == '10') {
			$result = array('is_followed' => 'unfollow');
		}else if ($result == '13') {
			$result = array('is_followed' => 'eachfollow');
		}else {
			$result = array('is_followed' => 'havefollow');
		}
		return $result;
	}
	//取消关注
	function destroy(){
		$result = D('Follow','weibo')->unfollow($this->mid,$this->user_id);
		return array('is_followed' => $result ? 'unfollow' : 'havefollow');
	}
	// 获取关注详情
	function show(){
		return getFollowState($this->mid,$this->user_id);
	}
	// 拉入黑名单
	function addToBlackList()
	{
	   	return D('UserPrivacy', 'home')->setBlackList($this->mid, 'add', $this->user_id) ? 1 : 0;
	}
	// 删除黑名单
	function removeFromBlackList()
	{
	    return D('UserPrivacy', 'home')->setBlackList($this->mid, 'del', $this->user_id) ? 1 : 0;
	}
	
	//关注话题
	function followTopic(){
			$topicId = D('Topic','weibo')->getTopicId(t($this->data['topic']));
			$result = D('Follow','weibo')->dofollow($this->mid,$topicId,1);
			if ($result == '00' || $result == '10') {
				$result = array('is_followed' => 'unfollow');
			}else if ($result == '13') {
				$result = array('is_followed' => 'eachfollow');
			}else {
				$result = array('is_followed' => 'havefollow');
			}
			return $result;
	}	
	//取消关注话题
	
	function unfollowTopic(){
			$topicId = D('Topic','weibo')->getTopicId(t($this->data['topic']));
			$result = D('Follow','weibo')->unfollow($this->mid,$topicId,1);
			return array('is_followed' => $result ? 'unfollow' : 'havefollow');			
	}
	
	//话题状态
	function isFollowTopic(){
		if($this->user_id>0){
			return (int)D('Follow','weibo')->getTopicState($this->user_id,t($this->data['topic']));
		}else{
			return (int)D('Follow','weibo')->getTopicState($this->mid,t($this->data['topic']));
		}
	}
}