<?php
/**
 * 用户统计模型
 *
 * @author nonant
 */
class UserCountModel extends Model {
	protected $tableName = 'user_count';
	/**
	 * 添加统计数据
	 *
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目
	 * @param int		$IncNum 变化值  默认1
	 * @return void
	 */
	function addCount($uid,$type,$IncNum=1){
		global $ts;
		if($uid==$ts['user']['uid']){
			return false;
		}
		if( is_array($uid) ){
			foreach ($uid as $k=>$v){
				$this->addCount($v,$type);
			}
		}else{
			if(!$uid) return false;
			if( $this->where('uid='.$uid)->find() ){
				$this->setInc($type,'uid='.$uid);
			}else{
				$data['uid'] = $uid;
				$data[$type] = 1;
				$this->add($data);
			}
		}
	}
	/**
	 * 归0
	 *
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目
	 * @return void
	 */
	function setZero($uid,$type){
		return $this->setField($type,0,'uid='.$uid);
	}
	/**
	 * 获取统计值
	 *
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目，为空将返回所有统计项目结果
	 * @return mixed
	 */
	public function getUnreadCount($uid, $type = '') {
		$map['uid'] = $uid;
		$res = $this->where($map)->find();
		return empty($type) ? intval($res) : intval($res[$type]);
	}
	/**
	 * 批量预设置用户的微博数
	 *
	 * @param array $uids
	 */
	public function setUserWeiboCount($uids, $type = null)
	{
		if (!is_array($uids) || !is_numeric($uids[0]))
			return false;
		$base_cache_id = 'user_weibo_count_';
		$uids = implode(',', $uids);
		if ('original' == $type) {
			$type = ' AND transpond_id=0';
		} else if (is_numeric($type) && $type > 0) {
			$type = ' AND type=' . $type;
		}
		$sql  = "SELECT count(*) AS `count`, `uid` FROM {$this->tablePrefix}weibo WHERE `uid` IN ( {$uids} ) {$type} AND `isdel` = 0 GROUP BY `uid`";
		$res = M('weibo')->query($sql);
		// 转换成array($uid => $count)的形式
		$count = array();
		foreach ($res as $v)
			$count[$v['uid']] = $v['count'];
		$uids = explode(',', $uids);
		foreach ($uids as $v)
			object_cache_set($base_cache_id . $v, intval($count[$v]));
		return intval($res);
	}
	/**
	 * 获取用户的微博数
	 *
	 * 本方法首先读取运行时缓存, 如果不存在则查询数据库, 并设置运行时缓存.
	 * 迭代获取用户微博数时, 预设置用户的微博数(setUserWeiboCount)能显著减少数据库查询次数
	 *
	 * @param int $uid
	 */
	public function getUserWeiboCount($uid, $type = null)
	{
		if ($type) {
			$base_cache_id = 'user_weibo_' . h($type) . '_count_';
		} else {
			$base_cache_id = 'user_weibo_count_';
		}
		if (($res = object_cache_get($base_cache_id . $uid)) === false) {
			$this->setUserWeiboCount(array($uid), $type);
			$res  = object_cache_get($base_cache_id . $uid);
		}
		return intval($res);
	}
	/**
	 * 批量预设置用户的关注数的运行时缓存
	 */
	public function setUserFollowingCount($uids)
	{
		if (!is_array($uids) || !is_numeric($uids[0]))
			return false;
		$base_cache_id = 'user_following_count_';
		$uids = implode(',', $uids);
		$sql = "SELECT count(*) AS `count`, `uid` FROM {$this->tablePrefix}weibo_follow WHERE `uid` IN ( {$uids} ) AND `type` = 0 GROUP BY `uid`";
		$res = M('weibo_follow')->query($sql);
		foreach ($res as $v)
			object_cache_set($base_cache_id . $v['uid'], $v['count']);
		return intval($res);
	}
	/**
	 * 获取用户的关注数
	 */
	public function getUserFollowingCount($uid)
	{
		$base_cache_id = 'user_following_count_';
		if (($res = object_cache_get($base_cache_id . $uid)) === false) {
			$this->setUserFollowingCount(array($uid));
			$res  = object_cache_get($base_cache_id . $uid);
		}
		return intval($res);
	}
	/**
	 * 批量预设置用户的粉丝数的运行时缓存
	 */
	public function setUserFollowerCount($uids)
	{
		if (!is_array($uids) || !is_numeric($uids[0]))
			return false;
		$base_cache_id = 'user_follower_count_';
		$uids = implode(',', $uids);
		$sql = "SELECT count(*) AS `count`, `fid` FROM {$this->tablePrefix}weibo_follow WHERE `fid` IN ( {$uids} ) AND `type` = 0 GROUP BY `fid`";
		$res = M('weibo_follow')->query($sql);
		foreach ($res as $v)
			object_cache_set($base_cache_id . $v['fid'], $v['count']);
		return intval($res);
	}
	public function getUserFollowerCount($uid)
	{
		$base_cache_id = 'user_follower_count_';
		if (($res = object_cache_get($base_cache_id . $uid)) === false) {
			$this->setUserFollowerCount(array($uid));
			$res  = object_cache_get($base_cache_id . $uid);
		}
		return intval($res);
	}
}