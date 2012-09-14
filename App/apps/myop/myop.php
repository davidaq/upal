<?php
//初始化
require_once './common.php';
header("Content-type: text/html; charset=UTF-8");

if ($_GET['my_suffix'] == '/appadmin/list') {
	$is_admin	= true;
	// 检查管理员权限（即：是否有“后台/应用”的权限）
	if ( ! hasPopedom($_SITE_CONFIG['uid'], 'admin/Apps/*', false) )
		redirect(SITE_URL, 5, '您无权限查看');
}else {
	$is_admin	= false;
}

if ( empty($_SITE_CONFIG['my_site_id']) || empty($_SITE_CONFIG['my_site_key']) ) {
	$_SITE_CONFIG['my_status']	= 0;
}

if(submitcheck('mysubmit')) {
	//开启漫游 OR 同步漫游信息
	$_SITE_CONFIG['site_key']		= trim($_SITE_CONFIG['site_key']);
	if ( empty($_SITE_CONFIG['site_key']) ) {
		$_SITE_CONFIG['site_key']	= mksitekey();
		$db_prefix					= getDbPrefix();
		doQuery("REPLACE INTO {$db_prefix}system_data (`list`, `key`, `value`) VALUES ('myop', 'site_key', '" . serialize($_SITE_CONFIG['site_key']) . "')");
	}

	//如果漫游关闭再开启则直接调用更新接口
	if ( empty($_SITE_CONFIG['my_status']) && !empty($_SITE_CONFIG['my_site_id']) && !empty($_SITE_CONFIG['my_site_key']) ) {
		$_SITE_CONFIG['my_status']	= 1;
	}

	$is_register 	 = 0;
	if ( empty($_SITE_CONFIG['my_status']) ) {
		$is_register = 1;
		$res = my_site_register($_SITE_CONFIG['site_key'], $_SITE_CONFIG['site_name'], MYOP_URL,
								UC_URL, $_SITE_CONFIG['charset'], $_SITE_CONFIG['timeoffset'], 0, 0,
								$_SITE_CONFIG['language'], SOCIAX_VER, MY_VER);
	}else {
		$res = my_site_refresh($_SITE_CONFIG['my_site_id'], $_SITE_CONFIG['site_name'], MYOP_URL,
							   UC_URL, $_SITE_CONFIG['charset'], $_SITE_CONFIG['timeoffset'], 0, 0,
							   $_SITE_CONFIG['my_site_key'], $_SITE_CONFIG['site_key'], $_SITE_CONFIG['language'],
							   SOCIAX_VER, MY_VER);
	}

	if ($res['errCode']) {
		//注册失败 OR 更新失败
		echo $is_register ? '<h1>漫游注册失败</h1>' : '<h1>漫游更新失败</h1>';
		echo $res['errMessage'];
		exit();
	}else {
		$db_prefix	= getDbPrefix();
		if ($is_register) {
			//注册成功
			echo '注册成功，请更新缓存';
			$res['result']['mySiteId']	= serialize($res['result']['mySiteId']);
			$res['result']['mySiteKey']	= serialize($res['result']['mySiteKey']);
			$my_status					= serialize('1');
			$site_key					= serialize($_SITE_CONFIG['site_key']);
			doQuery("REPLACE INTO {$db_prefix}system_data (`list`, `key`, `value`) VALUES ('myop', 'my_site_id', '{$res['result']['mySiteId']}'), ('myop', 'my_site_key', '{$res['result']['mySiteKey']}'), ('myop','my_status', '{$my_status}'), ('myop','site_key', '{$site_key}')");
			refreshConfig();
		}else {
			//更新成功
			echo '更新成功，请更新缓存';
			$my_status					= serialize('1');
			doQuery("REPLACE INTO {$db_prefix}system_data (`list`, `key`, `value`) VALUES ('myop','my_status', '{$my_status}')");
			refreshConfig();
		}

	}
}else if(submitcheck('closemysubmit')) {
	$res		= my_site_close($_SITE_CONFIG['my_site_id'], $_SITE_CONFIG['my_site_key']);

	//无论漫游服务器端是否成功关闭，都在本站点关闭漫游
	$db_prefix	= getDbPrefix();
	$my_status	= serialize('0');
	doQuery("REPLACE INTO {$db_prefix}system_data (`list`, `key`, `value`) VALUES ('myop','my_status', '{$my_status}')");
	refreshConfig();

	if($res['errCode']) {
		//关闭失败
		dump($res);
		exit('漫游关闭失败');
	} else {
		exit('漫游关闭成功，请更新缓存');
	}

}
 
$uch_prefix	= MYOP_URL . '/myop.php?';
$uch_suffix	= '';
$uchUrl    	= $uch_prefix . $uch_suffix;

//manyou
$my_prefix 	= $_MY_GLOBAL['my_uchome_url'];	/* http://uchome.manyou.com */
$my_suffix 	= urlencode($_GET['my_suffix']);

if (!$my_suffix) {
	redirect(SITE_URL . '/apps/myop/myop.php?my_suffix=' . urlencode('/appadmin/list'));
    exit;
}

$tmp_suffix	= isset($_GET['my_suffix']) ? urldecode($_GET['my_suffix']) : '/appadmin/list';
$myUrl     	= $my_prefix . $tmp_suffix;

$hash 		= md5($_SITE_CONFIG['my_site_id'] . '|' . $_SITE_CONFIG['uid'] . '|' . $_SITE_CONFIG['my_site_key'] . '|' . $_MY_GLOBAL['timestamp']);

$delimiter	= strrpos($myUrl, '?') ? '&' : '?';

$url 		= $myUrl . $delimiter
			 . 's_id=' . $_SITE_CONFIG['my_site_id']
			 . '&uch_id=' . $_SITE_CONFIG['uid']
			 . '&uch_url=' . urlencode($uchUrl)
			 . '&my_suffix=' . $my_suffix
			 . '&timestamp=' . $_MY_GLOBAL['timestamp']
			 . '&my_sign=' . $hash;

if ( $_GET['my_suffix'] == '/app/list') { // 前台
	$is_app_manage = 1;
	setTitle('添加应用');

	include MYOP_THEME_PATH . '/header.html';
	echo '<div class="content myop">';
	//include MYOP_THEME_PATH . '/apps.html';
	echo '<div class="main no_l"><div class="mainbox"><div class="mainbox_appC no_r">';
	include MYOP_THEME_PATH . '/body.html';
	echo '</div></div></div></div>';
	include MYOP_THEME_PATH . '/footer.html';

}else { // 后台管理员
	include MYOP_THEME_PATH . '/body.html';
}