<?php
require_once './common.php';
header('Content-type: text/html; charset=UTF-8');

// 检查漫游是否开启
if ( !$_SITE_CONFIG['my_status'] ) {
	redirect(SITE_URL, 5, '抱歉：漫游已关闭。系统将在5秒后自动跳转至首页');
}

//站点地址
$uchUrl			= MYOP_URL . '/cp.php?ac=userapp';

//manyou
$my_prefix		= $_MY_GLOBAL['my_uchome_url']; /* http://uchome.manyou.com */
if(empty($_GET['my_suffix'])) {
	$appId		= intval($_GET['appid']);
	if ($appId) {
		// 应用简介
		$mode 	= $_GET['mode'];
		if ($mode == 'about') {
			setTitle('应用简介');
			$my_suffix = '/userapp/about?appId='.$appId;
		} else {
			setTitle('隐私设置');
			$my_suffix = '/userapp/privacy?appId='.$appId;
		}
	} else {
		// 管理应用
		$my_suffix = '/userapp/list';
	}
} else {
	// 添加应用 OR 删除应用
	$my_suffix	= $_GET['my_suffix'];
}

$my_extra		= isset($_GET['my_extra']) ? $_GET['my_extra'] : '';
$delimiter		= strrpos($my_suffix, '?') ? '&' : '?';
$myUrl			= $my_prefix . urldecode($my_suffix . $delimiter . 'my_extra=' . $my_extra);

// 本地列表
if($my_suffix == '/userapp/list') {
	// 由home统一管理应用
	redirect(U('home/Index/editapp'));

	// 管理应用
	$_GET['op'] = 'menu';//模板
	$max_order = 0;

	$myop_default		= getDefaultApp();
  	$myop_default_id	= getSubByKey($myop_default['data'], 'appid');
  	$myop_userapp     	= getInstalledByUser($_SITE_CONFIG['uid']);
  	foreach ($myop_userapp['data'] as $k => $v) {
      	// 默认应用不再出现在个人应用中
      	if ( in_array($v['appid'], $myop_default_id) ) {
         	unset($myop_userapp['data'][$k]);
      	}
  	}
}

$hash			= $_SITE_CONFIG['my_site_id'] . '|' . $_SITE_CONFIG['uid'] . '|' . $_SITE_CONFIG['my_site_key'] . '|' . $_MY_GLOBAL['timestamp'];
$hash 			= md5($hash);
$delimiter		= strrpos($myUrl, '?') ? '&' : '?';

$url			= $myUrl . $delimiter
				. 's_id=' . $_SITE_CONFIG['my_site_id']
				. '&uch_id=' . $_SITE_CONFIG['uid']
				. '&uch_url=' . urlencode($uchUrl)
				. '&my_suffix=' . urlencode($my_suffix)
				. '&timestamp=' . $_MY_GLOBAL['timestamp']
				. '&my_sign=' . $hash;

if ( $_GET['op'] == 'deleteapp' ) {
	include MYOP_THEME_PATH . '/body.html';
}else {
	include MYOP_THEME_PATH . '/header.html';
	echo '<div class="content">';
	//include MYOP_THEME_PATH . '/apps.html';
	include MYOP_THEME_PATH . '/body.html';
	echo '</div>';
	include MYOP_THEME_PATH . '/footer.html';
}
?>