<?php
require_once(SITE_PATH.'/apps/weibo/Lib/Model/WeiboModel.class.php');
class WeiboApiModel extends WeiboModel
{
	var $tableName = 'weibo';

	//获取最新更新的公共微博消息
	public function public_timeline($since_id, $max_id, $count = 20, $page = 1)
	{
		$limit = ($page-1)*$count.','.$count;
		$map['type'] = array('IN',array(0,1));
		if($since_id){
			$map['weibo_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['weibo_id'] = array('lt',$max_id);
		}
		$map['isdel'] = 0;
		$list = $this->where($map)->limit($limit)->order('weibo_id DESC')->findAll();

		$this->_doWeiboAndUserCache($list);

		$weibo_ids = getSubByKey($list, 'weibo_id');
		foreach ($list as $k => $v)
			$result[$k] = $this->getOneApi('', $v);

    	return $result;
	}

	//获取当前用户所关注用户的最新微博信息
	public function friends_timeline($uid, $since_id, $max_id, $count = 20, $page = 1)
	{
		$limit = ($page-1) * $count . ',' . $count;
		$now_following_sql = D('Follow', 'weibo')->getNowFollowingSql($uid);
		$map['type'] = array('IN', array('1','0'));
		if ($since_id) {
			$map['weibo_id'] = array('gt', $since_id);
		} else if ($max_id) {
			$map['weibo_id'] = array('lt', $max_id);
		}
		$map['isdel'] = 0;
		//没有关注任何人时,不进行查询
		$followCount = D('Follow','weibo')->getUserFollowCount($uid);
		if($followCount)
			$map['uid']   = array('exp', "IN ( {$now_following_sql} ) OR `uid` = '{$uid}'");

		$list = $this->where($map)->limit($limit)->order('weibo_id DESC')->findAll();
		$this->_doWeiboAndUserCache($list);
		$weibo_ids = getSubByKey($list, 'weibo_id');
		foreach($list as $k => $v) {
			$v['favorited'] = D('Favorite','weibo')->isFavorited($v['weibo_id'], $uid, $weibo_ids);
			$result[$k]     = $this->getOneApi('', $v);
	    }
    	return $result;
	}

	//获取用户发布的微博信息列表
	public function user_timeline($uid, $uname, $since_id, $max_id, $count = 20, $page = 1)
	{
		if (!$uid) {
			$user = M('user')->where("uname='$uname'")->field('uid')->find();
			$uid = $user['uid'];
		}
		$limit = ($page-1)*$count.','.$count;
		$map['type'] = array('IN',array('1','0'));
		if ($since_id) {
			$map['weibo_id'] = array('gt',$since_id) ;
		} else if ($max_id) {
			$map['weibo_id'] = array('lt',$max_id);
		}
		$map['isdel'] = 0;
		$map['uid']   = $uid;
		$list = $this->where($map)->limit($limit)->order('weibo_id DESC')->findAll();

		$this->_doWeiboAndUserCache($list);

		$weibo_ids = getSubByKey($list, 'weibo_id');
		foreach($list as $k => $v) {
			$v['favorited'] = D('Favorite','weibo')->isFavorited($v['weibo_id'], $uid, $weibo_ids);
			$result[$k]     = $this->getOneApi('', $v);
	    }
    	return $result;
	}

	//获取@当前用户的微博列表
	public function mentions($uid, $since_id, $max_id, $count = 20, $page = 1)
	{
		$limit = ($page-1)*$count.','.$count;
		$map = "(type=1 OR type=0) AND isdel=0";
		if($since_id){
			$map.= " AND weibo_id > $since_id";
		}elseif ($max_id){
			$map.= " AND weibo_id < $max_id";
		}
		$list = $this->where("$map AND (weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_atme WHERE uid=$uid))")->order('weibo_id DESC')->limit($limit)->findAll();

		$this->_doWeiboAndUserCache($list);

		$weibo_ids = getSubByKey($list, 'weibo_id');
		foreach($list as $k=>$v){
			$v['favorited'] = D('Favorite','weibo')->isFavorited($v['weibo_id'], $uid, $weibo_ids);
			$result[$k]     = $this->getOneApi('', $v);
	    }
    	return $result;
	}

	//获取评论列表
    public function getCommentList($uid, $type = 'all', $since_id, $max_id, $count = 20, $page = 1)
    {
    	$limit = ($page-1)*$count.','.$count;
		if($since_id){
			$map['comment_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['comment_id'] = array('lt',$max_id);
		}
    	if($type=='all'){
    		$map['_string'] = "reply_uid=$uid OR uid=$uid";
    	}elseif($type=='send'){ // 发出的评论
    		$map['reply_uid'] = array('neq',$uid);
    		$map['uid']       = $uid;
    	}else{ // 收到的评论
    		$map['reply_uid'] = $uid;
    		$map['uid']       = array('neq',$uid);
    	}
		$map['isdel'] = 0;
    	$list = M('weibo_comment')->where($map)->order('comment_id DESC')->limit($limit)->findall();
    	foreach ($list as $key=>$value){
    		$list[$key]['status']       = $this->getOneApi($value['weibo_id']);
    		if ($type=='receive') { // 查看收到的评论时, 展示发送者的用户信息
	    		$list[$key]['user']     = getUserInfo($value['uid'],'',$uid,false);
	    		$list[$key]['uname'] = $list[$key]['user']['uname'];
	    	}
    		if( $value['reply_comment_id'] && $value['reply_uid'] ){
    			$list[$key]['type']    = 'comment';
    			$list[$key]['comment']   = $this->where('comment_id='.$value['reply_comment_id'].' AND isdel=0')->find();
    		}else{
    			$list[$key]['type']  = 'weibo';
    		}
    		//$list[$key]['reply_user']   = getUserInfo($value['reply_uid'],'',$uid,false);
    		$list[$key]['timestamp'] = $value['ctime'];
    		$list[$key]['ctime'] = date('Y-m-d H:i', $value['ctime']);
    	}
    	return $list;
    }

    //获取API评论
    function comments($id,$since_id,$max_id,$count=20,$page=1){
        $limit = ($page-1)*$count.','.$count;
		if($since_id){
			$map['comment_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['comment_id'] = array('lt',$max_id);
		}
		$map['weibo_id'] = $id;
		$map['isdel'] = 0;
    	$list = M('weibo_comment')->where($map)->order('comment_id DESC')->field('comment_id,uid,content,ctime')->limit($limit)->findall();
    	foreach($list as $key=>$value){
    		$list[$key]['uname'] = getUserName($value['uid']);
    		$list[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);;
    		$list[$key]['timestamp'] = $value['ctime'];
    	}
    	return $list;
    }

    //获取用户关注的列表
    function following($uid,$uname,$since_id,$max_id,$count=20,$page=1){
    	$limit = ($page-1)*$count.','.$count;
       	if(!$uid){
			$user = M('user')->where("uname='$uname'")->field('uid')->find();
			$uid = $user['uid'];
		}
    	if($since_id){
			$map['follow_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['follow_id'] = array('lt',$max_id);
		}
		$map['uid']=$uid;
		$list = M('weibo_follow')->where($map)->limit($limit)->field('fid as uid,follow_id as id')->order('follow_id DESC')->findAll();

		$uids = getSubByKey($list, 'uid');
		D('User', 'home')->setUserObjectCache($uids);
		model('UserCount')->setUserFollowerCount($uids);
		model('UserCount')->setUserFollowingCount($uids);
		model('UserCount')->setUserWeiboCount($uids);
		foreach ($list as $k=>$v){
			if(isset($_SESSION['mid']) && $_SESSION['mid'] > 0){
				$list[$k]['user'] = getUserInfo($v['uid'], '', $_SESSION['mid']);
			}else{
				$list[$k]['user'] = getUserInfo($v['uid'], '', $uid);
			}

			$mini = $this->where('uid='.$v['uid'].' AND isdel=0')->order('weibo_id DESC')->find();
			$list[$k]['weibo'] = $this->getOneApi('', $mini);
		}
		return $list;
    }

    //获取用户粉丝列表
    function followers($uid,$uname,$since_id,$max_id,$count=20,$page=1){
    	$limit = ($page-1)*$count.','.$count;
       	if(!$uid){
			$user = M('user')->where("uname='$uname'")->field('uid')->find();
			$uid = $user['uid'];
		}
    	if($since_id){
			$map['follow_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['follow_id'] = array('lt',$max_id);
		}
		$map['fid']  = $uid;
		$map['type'] = 0;
		$list = M('weibo_follow')->where($map)->limit($limit)->field('uid,follow_id as id')->order('follow_id DESC')->findAll();

		$uids = getSubByKey($list, 'uid');
		D('User', 'home')->setUserObjectCache($uids);
		model('UserCount')->setUserFollowerCount($uids);
		model('UserCount')->setUserFollowingCount($uids);
		model('UserCount')->setUserWeiboCount($uids);
		foreach ($list as $k=>$v){
			if(isset($_SESSION['mid']) && $_SESSION['mid'] > 0){
				$list[$k]['user'] = getUserInfo($v['uid'], '', $_SESSION['mid']);
			}else{
				$list[$k]['user'] = getUserInfo($v['uid'], '', $uid);
			}
			$mini = $this->where('uid='.$v['uid'].' AND isdel=0')->order('weibo_id DESC')->find();
			$list[$k]['weibo'] = $this->getOneApi('', $mini);
		}
		return $list;
    }

    //搜索微博（话题)
    function search($key,$since_id,$max_id,$count=20,$page=1){
    	$key=t($key);
    	if(!$key) return false;
   	    $limit = ($page-1)*$count.','.$count;
		$map = "(type=1 OR type=0) AND isdel=0";
		if($since_id){
			$map.= " AND weibo_id > $since_id";
		}elseif ($max_id){
			$map.= " AND weibo_id < $max_id";
		}
		$list = $this->where($map." AND content LIKE '%{$key}%'")->limit($limit)->order('weibo_id DESC')->findAll();
		$weibo_ids = getSubByKey($list, 'weibo_id');
		foreach($list as $k => $v) {
			$result[$k] = $this->getOneApi('', $v);
	    }
	    unset($list);
		return $result;
    }


    //搜索用户
    function searchUser($key,$mid,$since_id,$max_id,$count=20,$page=1){
    	$key=t($key);
    	if(!$key) return false;
       	$limit = ($page-1)*$count.','.$count;
       	$map = 'uid>0';
		if($since_id){
			$map.= " AND uid > $since_id";
		}elseif ($max_id){
			$map.= " AND uid < $max_id";
		}
    	$list = $this->table(C('DB_PREFIX').'user')->where($map." AND uname LIKE '%{$key}%'")->limit($limit)->findall();
    	foreach ($list as $k=>$v){
			$list[$k]['mini'] = M('weibo')->where('uid='.$v['uid'].' AND type=0')->order('weibo_id DESC')->find();
			$list[$k]['followed_count'] 	= M('weibo_follow')->where('uid='.$v['uid'])->count();
			$list[$k]['followers_count']	= M('weibo_follow')->where('fid='.$v['uid'])->count();
			$list[$k]['is_followed']  		= getFollowState($mid, $v['uid']);
			$list[$k]['area']         		= $v['location'];
			$list[$k]['face']				= getUserFace($v['uid']);
    	}
    	return $list;
    }

}

?>
