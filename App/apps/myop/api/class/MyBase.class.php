<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: MyBase.class.php 13209 2009-08-20 06:37:28Z zhouguoqiang $
*/

class MyBase {
	
	/**
	 * getUsers
	 *
	 * @param array $uIds
	 * @param array $spaces space表中的信息
	 * @param boolean $isReturnSpaceField 是否返回spacefield表中的信息
	 * @param boolean $isReturnSpaceInfo 是否返回spaceinfo表中的信息
	 * @param boolean $isReturnFriends 是否返回好友信息
	 * @param integer $friendNum 好友数目
	 * @param boolean $isOnlyReturnFriendId 是否仅返回好友id
	 * @param boolean $isFriendIdKey 是否friendId作为数组的key
	 * @access public
	 * @return array
	 */
	function getUsers($uIds, $spaces = array(), $isReturnSpaceField = true, $isReturnSpaceInfo = false, $isReturnFriends = false, $friendNum = MY_FRIEND_NUM_LIMIT, $isOnlyReturnFriendId = false, $isFriendIdKey = false) {
		if (!$uIds) {
			return array();
		}
		
		$spaces			= array();
		$db_prefix		= getDbPrefix();
		$res = doQuery("SELECT * FROM {$db_prefix}user WHERE `is_active`=1 AND `uid` IN ( " . implode(',', $uIds) . " )");
		foreach ($res as $k => $v) {
			$spaces[$v['uid']]	= $v;
		}
		
		//TODO: 确定好友为联系人 OR 关注的人？暂时使用关注的人。
		$friends = array();
		if ($isReturnFriends) {
			$res = doQuery("SELECT * FROM {$db_prefix}weibo_follow WHERE `uid` IN ( " . implode(',', $uIds) . " ) AND `type` = 0");
			foreach ($res as $v) {
				$friends[$v['uid']][]	= $v['fid'];
			}
		}
		
		//TODO: extra信息
		
		$users			= array();
		foreach ($uIds as $uId) {
			$user		= $this->_formatUser($spaces[$uId]);
			
			if ($isReturnSpaceInfo) {
				$user['extra'] = $this->_formatExtra($spaceInfos[$uId]);
			}
			
			if ($isReturnFriends) {
				$user['friends'] = $this->_formatFriend($friends[$uId], $friendNum, $isOnlyReturnFriendId, $isFriendIdKey);
			}
			
			$users[]	= $user;
		}
		return $users;
	}
	
	public function getFriends($uId, $num = null) {
		$db_prefix		= getDbPrefix();
		$sql = "SELECT friend_uid FROM {$db_prefix}friend WHERE `uid` = $uId AND `status` = 1 ORDER BY friend_uid ";
		$sql = $num ? $sql . " LIMIT 0,$num" : $sql;
		$res = doQuery($sql);
		$res = getSubByKey($res, 'friend_uid');
		return $res;
	}
	
	public function refreshApplication($appId, $appName, $version, $displayMethod, $narrow, $flag, $displayOrder) {
		global $_SGLOBAL;
		$fields = array();
		if ($appName !== null && strlen($appName)>1) {
			$fields['appname'] = $appName;
		}
		if ($version !== null) {
			$fields['version'] = $version;
		}
		if ($displayMethod !== null) {
			// todo: remove
			$fields['displaymethod'] = $displayMethod;
		}
		if ($narrow !== null) {
			$fields['narrow'] = $narrow;
		}
		if ($flag !== null) {
			$fields['flag'] = $flag;
		}
		if ($displayOrder !== null) {
			$fields['displayorder'] = $displayOrder;
		}
		$db_prefix		= getDbPrefix();
		$is_installed	= doQuery("SELECT * FROM {$db_prefix}myop_myapp WHERE `appid` = $appId");
		
		if ($is_installed) {
			$where = sprintf('appid = %d', $appId);
			updatetable('myop_myapp', $fields, $where);
		}else {
			$fields['appid'] = $appId;
			$result = inserttable('myop_myapp', $fields, 1);
		}
		
		//TODO: update cache
	}
	
	protected function _formatUser($space, $extra = array()) {
		$user = array(
			'uId'					=> $space['uid'],
			'handle'				=> $space['uname'],
			'action'				=> null,
			'realName'				=> $space['uname'],
			'realNameChecked' 		=> false,
			'gender'				=> $space['sex'] == 1 ? 'male' : 'female',
			'email'					=> $space['email'],
			'qq'					=> '',
			'msn'					=> '',
			'birthday'				=> '2000-01-01',
			'bloodType'				=> empty($space['blood_type']) ? 'unknown' : $space['blood_type'],
			'relationshipStatus' 	=> 'unknown', /* single / notSingle */
			'birthProvince' 		=> $space['current_province'],
			'birthCity'				=> $space['current_province'],
			'resideProvince' 		=> $space['current_province'],
			'resideCity'			=> $space['current_province'],
			'viewNum'				=> '0',
			'friendNum'				=> '0',
			'myStatus'				=> '',
			'lastActivity' 			=> time(),
			'created'				=> $space['ctime'],
			'credit'				=> $space['score'],
			'isUploadAvatar'		=> true,
			'adminLevel'			=> $space['admin_level'],
			'homepagePrivacy'		=> 'public', // $privacy['view']['index'] == 1 ? 'friends' : ($privacy['view']['index'] == 2 ? 'me' : 'public'),
			'profilePrivacyList'	=> array(),
			'friendListPrivacy'		=> 'public', // $privacy['view']['friend'] == 1 ? 'friends' : ($privacy['view']['friend'] == 2 ? 'me' : 'public')
		);
		return $user;
	}
	
	protected function _formatFriend($friends , $num, $isOnlyReturnId = false, $isFriendIdKey = false) {
		$i = 1;
		$res = array();
		foreach($friends as $friend) {
			if ($num) {
				if ($i > $num) {
					continue;
				}
			}
			if ($isOnlyReturnId) {
				$row  = $friend;
			} else {
				$row = array('uId'	  => $friend,
							 'handle' => getUserName($friend),
							);
			}
			if ($isFriendIdKey) {
				$res[$friend] = $row;
			} else {
				$res[] = $row;
			}
			$i++;
		}
		return $res;
	}
	
	protected function _formatExtra($rows) {
		return array();
	}
	
	/* ========================================================================================================== */

	function getExtraByUsers($uIds) {
		return array();
		
		
		global $_SGLOBAL;

		if (!$uIds) {
			return array();
		}
		$spaceInfos = array();
		$sql = sprintf('SELECT * FROM %s WHERE uid IN (%s)', tname('spaceinfo'), implode(', ', $uIds));
		$query = $_SGLOBAL['db']->query($sql);
		$spaceInfos = array();
		while($row = $_SGLOBAL['db']->fetch_array($query)) {
			$spaceInfos[$row['uid']][] = $row;
		}

		$users = array();
		foreach($uIds as $uId) {
			$user = array('uId' => $uId,
						  'extra' => $this->_spaceInfo2Extra($spaceInfos[$uId]),
						  );
			$users[] = $user;
		}
		return $users;
	}
}