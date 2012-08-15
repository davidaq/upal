<?php
/**
 * 好友模型
 *
 * @author daniel <desheng.young@gmail.com>
 */
class FriendModel extends Model
{
	protected $tableName = 'friend';

	private   $_error = null;

	const FRIEND_STATUS_PEDDING  = 0;
	const FRIEND_STATUS_ACCEPTED = 1;

	const NOT_FRIENDS  = 0;
	const ARE_FRIENDS  = 1;
	const HAVE_APPLIED = 2;
	const BE_APPLIED   = 3;

	public function getLastError()
	{
		return $this->_error;
	}

	public function getFriendList($map = null, $field = null, $order = 'ctime DESC', $limit = 20, $page = true)
	{
		$this->where($map)->field($field)->order($order);
		if ($page) {
			$list = $this->findPage($limit);
			$this->_formatList( $list['data']); // 引用
		} else {
			$list = $this->limit($limit)->findAll();
			$this->_formatList( $list); // 引用
		}
		return $list;
	}

	// [引用] 格式化朋友列表数据，追加与关注列表相同信息
	private function _formatList(& $list)
	{
		$fids = $_list = array();
		foreach ($list as $l_v) { // 引用
			$fids[]     = $l_v['friend_uid'];
			$l_v['fid'] = $l_v['friend_uid'];
			$_list[$l_v['fid']] = $l_v;
		}
		$list = $_list;
		unset($_list);

		// 追加信息
		//用户地址
		$location = M('User')->where("uid in (". implode(",",$fids).")")->field('location,uid')->findAll();

		//关注和粉丝、朋友
		$following = D('Follow', 'weibo')->field('count(1) as count,uid')->where("uid in (". implode(",",$fids).") AND type={$type}")->group('uid')->findAll();
		$follower  = D('Follow', 'weibo')->field('count(1) as count,fid')->where("fid in (". implode(",",$fids).") AND type={$type}")->group('fid')->findAll();
		$friend    = $this->field('count(1) as count,friend_uid')->where("friend_uid in (". implode(",",$fids).") AND status=" . FriendModel::FRIEND_STATUS_ACCEPTED)->group('friend_uid')->findAll();

		//关注状态
		$followState = D('Follow', 'weibo')->getStateByArr($GLOBALS['ts']['user']['uid'], $fids);

		foreach($followState as $k => $v){
			$list[$k]['followState'] = $v != 3 ? $v != 1 ? 'unfollow' : 'havefollow' : 'eachfollow';
		}
		foreach ($location as $v){
			$list[$v['uid']]['user']['location'] = $v['location'];
		}
		foreach ($following as $v){
			$list[$v['uid']]['following'] =$v['count'];
		}
		foreach ($follower as $v){
			$list[$v['fid']]['follower'] =$v['count'];
		}
		foreach ($friend as $v){
			$list[$v['friend_uid']]['friend'] =$v['count'];
		}
	}

	public function applyFriend($uid, $friend_uid, $message)
	{
		$res = $this->_addFriend($uid, $friend_uid, 0, $message);

		/* 通知 */
		if ($res) {
			$notify_data['content'] = $message;
			X('Notify')->send($friend_uid, 'friend_apply', $notify_data, $uid);
		}

		return $res;
	}

	public function responseToFriendApplication($uid, $friend_uid, $status)
	{
		if ($status) {
			$res = $this->acceptFriend($uid, $friend_uid);
		} else {
			$res = $this->rejectFriend($uid, $friend_uid);
		}
		return $res;
	}

    public function acceptFriend($uid, $friend_uid)
	{
		$res = $this->_editFriend($friend_uid, $uid, 1);
		if ($res) {
			$res = $this->_addFriend($uid, $friend_uid, 1, '');
			!$res && $res = $this->_editFriend($uid, $friend_uid, 1); // 二者已互相发出邀请的情况
		}

		/* 通知 互粉 */
		if ($res) {
			// 通知
			X('Notify')->send($friend_uid, 'friend_accept', '', $uid);
			// 自动互粉
			D('Follow', 'weibo')->dofollow($uid, $friend_uid, 0);
			D('Follow', 'weibo')->dofollow($friend_uid, $uid, 0);
		}

		return $res;
	}

    public function rejectFriend($uid, $friend_uid)
	{
		$res = $this->_deleteFriend($uid, $friend_uid);

		/* 通知 */
		if ($res) {
			X('Notify')->send($friend_uid, 'friend_reject', '', $uid);
		}

		return $res;
	}

	public function cancelFriendApplicaition($uid, $friend_uid)
	{
		$res = $this->_deleteFriend($uid, $friend_uid);

		/* 通知 */
		if ($res) {
			X('Notify')->send($friend_uid, 'friend_cancel_apply', '', $uid);
		}

		return $res;
	}

    public function deleteFriend($uid, $friend_uid)
	{
		$res = $this->_deleteFriend($uid, $friend_uid);

		/* 通知 */
		if ($res) {
			X('Notify')->send($friend_uid, 'friend_cancel', '', $uid);
		}

		return $res;
	}

    public function identifyFriend($uid, $friend_uid)
	{
		static $_friend_status = array();

		$cache_key = "{$uid}_{$friend_uid}";
		if (isset($_friend_status[$cache_key])) {
			return $_friend_status[$cache_key];
		}
		// Friend 数据
		$friend = $this->where("(`uid`={$uid} AND `friend_uid`={$friend_uid}) OR (`uid`={$friend_uid} AND `friend_uid`={$uid})")
					   ->findAll();
		// 格式化数据
		$_friend = array();
		if (is_array($friend)) {
			foreach ($friend as $f_v) {
				$_friend[$f_v['uid']] = $f_v;
			}
			$friend = $_friend;
			unset($_friend);
		}
		if (!$friend) {
			$res = self::NOT_FRIENDS;
		} else if ($friend[$uid] && self::FRIEND_STATUS_ACCEPTED == $friend[$uid]['status']) {
			$res = self::ARE_FRIENDS;
		} else if ($friend[$friend_uid] && self::FRIEND_STATUS_PEDDING == $friend[$friend_uid]['status']) {
			$res = self::BE_APPLIED;
		} else if ($friend[$uid] && self::FRIEND_STATUS_PEDDING == $friend[$uid]['status']) {
			$res = self::HAVE_APPLIED;
		}
		$_friend_status[$cache_key] = $res;
		return $res;
	}

	/* ------ */
	private function _addFriend($uid, $friend_uid, $status, $message)
	{
		$data['uid'] 			= intval($uid);
		$data['friend_uid'] 	= intval($friend_uid);
		$data['status'] 		= 1 == $status ? 1 : 0;
		$data['message']		= t($message);
		$data['ctime']			= time();
		if ($data['uid'] <= 0 || $data['friend_uid'] <= 0 || $data['uid'] == $data['friend_uid']) {
			return false;
		}
		return $this->add($data);
	}

	private function _editFriend($uid, $friend_uid, $status)
	{
		$map['uid']        = intval($uid);
		$map['friend_uid'] = intval($friend_uid);
		$data['status']    = $status;
		if ($map['uid'] <= 0 || $map['friend_uid'] <= 0 || $map['uid'] == $map['friend_uid']) {
			return false;
		}
		return $this->where($map)->save($data);
	}

	private function _deleteFriend($uid, $friend_uid)
	{
		$uid        = intval($uid);
		$friend_uid = intval($friend_uid);
		if ($uid <= 0 || $friend_uid <= 0 || $uid == $friend_uid) {
			return false;
		}
		return $this->where("(`uid`={$uid} AND `friend_uid`={$friend_uid}) OR (`uid`={$friend_uid} AND `friend_uid`={$uid})")
					->delete();
	}

	/**
	 * 可能认识的人 (可能认识的人 = 有相同tag的用户 || 所在城市相同的用户 || 好友的好友 || 我的粉丝 || 随机推荐)
	 *
	 * 注意: 因为头像信息未保存数据库, 所以当开启"隐藏无头像的用户"时, 结果集数量可能小于$max
	 *
	 * @param int 	  $uid 		  用户ID
	 * @param int 	  $max 		  获取的最大人数
	 * @param boolean $do_shuffle 是否随机次序 (默认:true)
	 * @return boolean|array 用户ID的数组
	 */
	public function getRelatedUser($uid, $max = 100, $do_shuffle = true)
	{
		if (($uid = intval($uid)) <= 0)
			return false;

		// 权重设置
		$config = model('Xdata')->lget('related_user');
		$tag_weight      = isset($config['tag_weight'])      ? intval($config['tag_weight'])      : 4; // 拥有相同Tag
    	$city_weight     = isset($config['city_weight'])     ? intval($config['city_weight'])     : 3; // 设置的城市相同
    	$friend_weight   = isset($config['friend_weight'])   ? intval($config['friend_weight'])   : 2; // 好友的好友
    	$follower_weight = isset($config['follower_weight']) ? intval($config['follower_weight']) : 1; // 我的粉丝
		$total_weight    = $tag_weight + $city_weight + $friend_weight + $follower_weight;

		// 是否隐藏无头像的用户
		$hide_no_avatar  = $config['hide_no_avatar'];

		// 权重对应的数量
		$tag_count 		 = intval($tag_weight      / $total_weight * $max);
		$city_count 	 = intval($city_weight     / $total_weight * $max);
		$friend_count    = intval($friend_weight   / $total_weight * $max);
		$follower_count  = intval($follower_weight / $total_weight * $max);

		$related_uids = array();

		// 按Tag
		if ($tag_count > 0) {
			$tag_uids      = $this->_getRelatedUserFromTag($uid, $related_uids, $tag_count);
			$related_uids  = array_merge($related_uids, $tag_uids);
		}

		// 按设置的城市
		if ($city_count > 0) {
			$limit         = $city_count + ($tag_count - count($related_uids));
			$city_uids     = $this->_getRelatedUserFromCity($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $city_uids);
		}

		// 按好友的好友
		if ($friend_count > 0) {
			$limit 		   = $friend_count + ($tag_count + $city_count - count($related_uids));
			$friend_uids   = $this->_getRelatedUserFromFriend($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $friend_uids);
		}

		// 按粉丝
		if ($follower_count > 0) {
			$limit 		   = $follower_count + ($tag_count + $city_count + $friend_count - count($related_uids));
			$follower_uids = $this->_getRelatedUserFromFollower($uid, $related_uids, $limit);
			$related_uids  = array_merge($related_uids, $follower_uids);
		}

		// 随机推荐
		$limit         = $max - count($related_uids);
		$random_uids   = $this->_getRandomRelatedUser($uid, $related_uids, $limit);
		$related_uids  = array_merge($related_uids, $random_uids);

		// 按"好友的好友"推荐时, 可能会产生重复用户
		$related_uids  = array_unique($related_uids);

		// 添加推荐原因
		foreach ($related_uids as $k => $v) {
			if ($hide_no_avatar && !hasUserFace($v)) {
				unset($related_uids[$k]);
				continue ;
			}

			if (in_array($v, $tag_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => 'Tag相同');
			else if (in_array($v, $city_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '城市相同');
			else if (in_array($v, $friend_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '好友的好友');
			else if (in_array($v, $follower_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '您的粉丝');
			else if (in_array($v, $random_uids))
				$related_uids[$k] = array('uid' => $v, 'reason' => '随机推荐');
		}

		if ($do_shuffle)
			shuffle($related_uids);

		return $related_uids;
	}

	/**
	 * 根据Tag推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromTag($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$model    = D('UserTag', 'home');
		$tag_list = $model->getUserTagList($uid);
		$tag_ids  = getSubByKey($tag_list, 'tag_id');
		$tag_ids  = implode(',', $tag_ids);
		$now_uids = implode(',', array_merge($related_uids, array($uid)));
		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$sql = "SELECT `uid` FROM {$this->tablePrefix}user_tag WHERE `uid` NOT IN ( {$now_following} )  AND `uid` NOT IN ( {$now_uids} ) AND `tag_id` IN ( {$tag_ids} ) LIMIT {$limit}";

		if ($res = $model->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 根据用户设置的城市推荐用户. 如果当前用户没有设置城市, 则返回空数组
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromCity($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$model = D('User', 'home');
		$user  = $model->getUserByIdentifier($uid, 'uid');
		if (empty($user['location']))
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));

		$map['uid']		  = array('exp', " NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} )");
		$map['location']  = $user['location'];
		$map['is_active'] = '1';
		$map['is_init']   = '1';
		if ($res = $model->where($map)->field('uid')->limit($limit)->findAll())
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 根据"好友的好友"推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 * @todo 还需要优化效率 (用户总数8000: 粉丝和关注各7000+时, 执行时间约500ms; 粉丝和互粉各2000+时, 执行时间约3ms)
	 */
	protected function _getRelatedUserFromFriend($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		// DISTINCT在大数据量时对性能影响太大, 所以不加
		$sql = "SELECT `fid` FROM {$this->tablePrefix}weibo_follow " .
			   "WHERE `fid` NOT IN ( {$now_following} ) AND `fid` NOT IN ( {$now_uids} ) AND `uid` IN ( {$now_following} ) AND `type` = '0' " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'fid');
		else
			return array();
	}

	/**
	 * 根据粉丝推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRelatedUserFromFollower($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		$sql = "SELECT `uid` FROM {$this->tablePrefix}weibo_follow WHERE " .
			   "`fid` = {$uid} AND `uid` NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} ) " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}

	/**
	 * 随机推荐用户
	 *
	 * @param int   $uid		  当前用户ID
	 * @param array $related_uids 已推荐用户的uid数组
	 * @param int   $limit        推荐的人数
	 * @return array 被推荐用户的uid数组
	 */
	protected function _getRandomRelatedUser($uid, $related_uids, $limit = 20)
	{
		if ($limit <= 0)
			return array();

		$now_following = D('Follow' ,'weibo')->getNowFollowingSql($uid);
		$now_uids 	   = implode(',', array_merge($related_uids, array($uid)));
		$sql = "SELECT `uid` FROM {$this->tablePrefix}user WHERE " .
			   "`uid` NOT IN ( {$now_following} ) AND `uid` NOT IN ( {$now_uids} ) " .
			   "LIMIT {$limit}";

		if ($res = M()->query($sql))
			return getSubByKey($res, 'uid');
		else
			return array();
	}
}