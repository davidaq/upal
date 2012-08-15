<?php
/**
 +------------------------------------------------------------------------------
 * RelatedUserAddons 可能认识的人插件
 * $data接受的参数:
 * array(
 * 	'uid'(可选)		=> $uid,	// 用户ID(默认当前用户)
 * 	'limit'(可选)	=> $limit,	// 展示的数量(默认为3x3=9个), 用户点击"关注"后自动补全
 * 	'max'(可选)		=> $max,	// 一次搜索获取的最大数结果数(默认50)
 * 	'title'(可选)	=> $title,	// 标题(默认"可能感兴趣的人")
 * )
 */
class RelatedUserAddons extends SimpleAddons
{
	protected $version		= '1.0';
	protected $author		= '海虾';
	protected $site			= 'http://www.thinksns.com';
	protected $info			= '可能认识的人';
	protected $pluginName	= '可能认识的人';
	protected $tsVersion	= "2.8"; // ts核心版本号

	public function getHooksInfo()
	{
		return $this->apply('home_index_right_top','showRelatedUser');
	}

	// 替换短网址
	public function showRelatedUser($param)
	{
		$data	=	model('AddonData')->lget('related_user');
		$data['uid']	 = isset($data['uid'])	? intval($data['uid'])	 : intval($_SESSION['mid']);
		$data['limit']	 = isset($data['limit']) ? intval($data['limit']) : 3;
		$data['max']	 = isset($data['max'])	? intval($data['max'])	 : 50;
		$data['title']	 = isset($data['title']) ? $data['title'] 		 : L('may_interest');
		$data['user'] 	 = $this->getRelatedUser($data['uid'], $data['max']);
		$data['oldUser'] = $data['user'];
		$data['async']   = 0;
		$_SESSION['_widget_related_user'] = serialize($data['oldUser']);
		$this->assign($data);
		$this->display('relatedusers');
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

	/* 后台管理 */

    public function adminMenu()
	{
        return array('config' => '配置');
    }

	public function config()
	{
		$data	=	model('AddonData')->lget('related_user');
		$this->assign($data);
		$this->display('config');
	}

	public function saveConfig($param)
	{
		unset($_POST['__hash__']);
		foreach($_POST as $k=>$v){
			$_POST[$k] = h($v);
		}
		$_POST['total_weight'] = intval($_POST['tag_weight'] + $_POST['city_weight'] + $_POST['friend_weight'] + $_POST['follower_weight']);
		$res = model('AddonData')->lput('related_user', $_POST);

		if ($res) {
			$this->assign('jumpUrl', Addons::adminPage('config'));
    		$this->success();
		} else {
    		$this->error();
		}
	}

    public function start()
    {
        return true;
    }

	public function install()
	{
		
		return true;
	}

	public function uninstall()
	{
		return true;
	}
}