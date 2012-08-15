<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: Feed.class.php 12545 2009-07-07 07:43:29Z liguode $
*/

if(!defined('IN_MYOP')) {
	exit('Access Denied');
}

class Feed extends MyBase {

	function publishTemplatizedAction($uId, $appId, $titleTemplate, $titleData, $bodyTemplate, $bodyData, $bodyGeneral = '', $image1 = '', $image1Link = '', $image2 = '', $image2Link = '', $image3 = '', $image3Link = '', $image4 = '', $image4Link = '', $targetIds = '', $privacy = '', $hashTemplate = '', $hashData = '', $specialAppid=0) {
		global $_SITE_CONFIG;
		$db_prefix	= getDbPrefix();
		$site_userapp_url	= SITE_URL.'/apps/myop/userapp.php';
		$site_cp_url		= SITE_URL.'/apps/myop/cp.php';
		
		if ( strpos($titleTemplate, MYOP_URL) === false ) {
			$titleTemplate	= str_replace('userapp.php', $site_userapp_url, $titleTemplate);
			$titleTemplate	= str_replace('cp.php', $site_cp_url, $titleTemplate);
		}
		foreach ($titleData as $k => $v) {
			if ( strpos($titleTemplate, MYOP_URL) === false ) {
				$v	= str_replace('userapp.php', $site_userapp_url, $v);
				$v	= str_replace('cp.php', $site_cp_url, $v);
			}
			$titleTemplate	= str_replace('{'.$k.'}', $v, $titleTemplate);
		}
		
		if ( strpos($bodyTemplate, MYOP_URL) === false ) {
			$bodyTemplate	= str_replace('userapp.php', $site_userapp_url, $bodyTemplate);
			$bodyTemplate	= str_replace('cp.php', $site_cp_url, $bodyTemplate);
		}
		foreach ($bodyData as $k => $v) {
			if ( strpos($bodyTemplate, MYOP_URL) === false ) {
				$v	= str_replace('userapp.php', $site_userapp_url, $v);
				$v	= str_replace('cp.php', $site_cp_url, $v);
			}
			$bodyTemplate	= str_replace('{'.$k.'}', $v, $bodyTemplate);
		}

		$titleTemplate	= str_replace('{actor}', '', $titleTemplate);
		$bodyTemplate	= str_replace('{actor}', '<a href="'.U('home/Space/index',array('uid'=>$uId)).'">'.getUserName($uId).'</a>', $bodyTemplate);
		
		$content	= array(
						'title'			=> stripslashes($titleTemplate),
						'content'		=> stripslashes($bodyTemplate),
						'image1'		=> $image1,
						'image1Link'	=> $image1Link,
						'image2'		=> $image2,
						'image2Link'	=> $image2Link,
						'image3'		=> $image3,
						'image3Link'	=> $image3Link,
						'image4'		=> $image4,
						'image4Link'	=> $image4Link,
						);
		
		doLog($content, 'ContetArray');
		$content	= serialize($content);
		
		$ctime		= time();
		$sql		= "INSERT INTO {$db_prefix}feed (`uid`,`data`,`type`,`ctime`) VALUES 
					   ({$_SITE_CONFIG['uid']}, '$content', 'myop_feed','$ctime')";
		
		$result		= doQuery($sql);
		
		return new APIResponse($result);
	}
}

?>
