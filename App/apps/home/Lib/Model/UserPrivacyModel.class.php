<?php
class UserPrivacyModel extends Model {
	protected	$tableName	=	'user_privacy';

	//获取用户设置
	function getUserSet($mid) 	{
		$userPrivacy = $this->where("uid=$mid")->field('`key`,`value`')->findall();
		if($userPrivacy){
			foreach ($userPrivacy as $k=>$v){
				$r[$v['key']] = $v['value'];
			}
			return $r;
		}else{
			return $this->defaultSet();
		}
	}

	//保存用户设置  我勒个去啊 谁写的
	function dosave($data,$uid){
		if(empty($uid)){
			return false;
		}
		$map = array();
		$map['uid'] = $uid;
		$this->where($map)->delete();
		foreach ($data as $key=>$value){
			$sql[] = "($uid,'{$key}',{$value})";
		}
		$this->query("INSERT INTO {$this->tablePrefix}user_privacy (uid,`key`,`value`) VALUES ".implode(',', $sql));
		return false;

	}

	//默认配置
	private function defaultSet(){
		return array(
			'weibo_comment' => 0, //(所有人，除黑名单)
			'message' => 0,	//(所有人，除黑名单)
		);
	}

	function getPrivacy($mid,$uid){
		if($mid==$uid) {
			$data['weibo_comment'] = true;
			$data['message']       = true;
			$data['follow']        = true;
			return $data;
		}

		$isBackList  = isBlackList($uid, $mid);
		$followState = getFollowState($uid, $mid) != 'unfollow';
		$userset     = $this->getUserSet($uid);
		if ($isBackList) {
			$data['weibo_comment'] = false;
			$data['message']       = false;
			$data['follow']        = false;
			$data['blacklist']     = true;
		}else {
			$data['weibo_comment'] = ( $userset['weibo_comment'] )? $followState : true;
			$data['message']       = ( $userset['message'] )? $followState : true;
			$data['follow']        = true;
			$data['blacklist']     = false;
		}

		return $data;
	}

	//设置黑名单
	function setBlackList($mid,$type,$fid){
		if($type=='add'){
			$map['uid'] = $mid;
			$map['fid'] = $fid;
			if( M('user_blacklist')->where($map)->count()==0 ){
				$map['ctime'] = time();
				M('user_blacklist')->add($map);  //添加黑名单
				M('weibo_follow')->where("(uid=$mid AND fid=$fid) OR (uid=$fid AND fid=$mid)")->delete(); //自动解除关系
				return true;
			}else{
				return false;
			}
		}else{
			$map['uid'] = $mid;
			$map['fid'] = $fid;
			return M('user_blacklist')->where($map)->delete();
		}
	}

	//获取黑名单列表
	function getBlackList($mid){
		return M('user_blacklist')->where("uid=$mid")->findall();
	}

	//判断用户是否是黑名单关系
	function isInBlackList($uid,$mid){
		$uid = intval($uid);
		$mid = intval($mid);
		$result = M('user_blacklist')->where("uid=$mid AND fid=$uid")->find();
		return	$result;
	}
}
?>