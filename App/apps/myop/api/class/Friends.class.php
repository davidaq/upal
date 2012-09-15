<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Friends.php 12766 2009-07-20 04:26:21Z liguode $
*/

if(!defined('IN_MYOP')) {
	exit('Access Denied');
}

class Friends extends MyBase {
	
	public function areFriends($uId1, $uId2) {
		$db_prefix	= getDbPrefix();
		$sql		= "SELECT * FROM {$db_prefix}friend WHERE `uid` = '$uId1' AND `friend_uid` = '$uId2' AND `status` = '1' LIMIT 1";
		$result		= doQuery($sql) ? true : false;
		return new APIResponse($result);
	}

	public function get($uIds, $friendNum = MY_FRIEND_NUM_LIMIT) {
		$result = array();
		if ($uIds) {
			foreach($uIds as $uId) {
				$result[$uId] = $this->getFriends($uId, $friendNum);
			}
		}
		return new APIResponse($result);
	}
}

?>
