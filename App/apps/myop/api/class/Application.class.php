<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Application.php 12591 2009-07-09 06:35:06Z zhengqingpeng $
*/

if(!defined('IN_MYOP')) {
	exit('Access Denied');
}

class Application extends MyBase {

	public function update($appId, $appName, $version, $displayMethod, $displayOrder = null) {
		$db_prefix	= getDbPrefix();

		global $_SGLOBAL;
		$fields	= array('appname'	=> $appName);
		$where	= array('appid'		=> $appId);
		$result = updatetable('myop_myapp', $fields, $where);
		$result = updatetable('myop_userapp', $fields, $where) || $result;

		$displayMethod = ($displayMethod == 'iframe') ? 1 : 0;
		$this->refreshApplication($appId, $appName, $version, $displayMethod, null, null, $displayOrder);
		return new APIResponse($result);
	}

	public function setFlag($applications, $flag) {
		global $_SGLOBAL;

		$flag = ($flag == 'disabled') ? -1 : ($flag == 'default' ? 1 : 0);
		$appIds = array();
		if ($applications && is_array($applications)) {
			foreach($applications as $application) {
				$this->refreshApplication($application['appId'], $application['appName'], null, null, null, $flag, null);
				$appIds[] = $application['appId'];
			}
		}

		if ($flag == -1) {
			$db_prefix	= getDbPrefix();
			$appIds		= "'" . implode("','", $appIds) . "'";
			doQuery("DELETE FROM {$db_prefix}myop_userapp WHERE `appid` IN ( $appIds )");
			doQuery("DELETE FROM {$db_prefix}myop_userappfield WHERE `appid` IN ( $appIds )");
			//TODO: Feed
			//TODO: Notification
			//TODO: myinvite

//			$sql = sprintf('DELETE FROM %s WHERE icon IN (%s)', tname('feed'), simplode($appIds));
//			$_SGLOBAL['db']->query($sql);
//
//			$sql = sprintf('DELETE FROM %s WHERE appid IN (%s)', tname('myinvite'), simplode($appIds));
//			$_SGLOBAL['db']->query($sql);
//
//			$sql = sprintf('DELETE FROM %s WHERE type IN (%s)', tname('notification'), simplode($appIds));
//			$_SGLOBAL['db']->query($sql);
		}

		$result = true;
		return new APIResponse($result);
	}

	function remove($appIds) {
		$db_prefix	= getDbPrefix();
		$appIds		= "'" . implode("','", $appIds) . "'";
		$result		= doQuery("DELETE FROM {$db_prefix}myop_userapp WHERE `appid` IN ( $appIds )");
		$result		= doQuery("DELETE FROM {$db_prefix}myop_userappfield WHERE `appid` IN ( $appIds )")	|| $result;
		$result		= doQuery("DELETE FROM {$db_prefix}myop_myapp WHERE `appid` IN ( $appIds )")		|| $result;

		//TODO: update cache

		return new APIResponse($result);
	}

}
?>
