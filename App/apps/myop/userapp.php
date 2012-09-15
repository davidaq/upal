<?php
require_once './common.php';
header('Content-type: text/html; charset=UTF-8');
 
//检查漫游是否开启
if ( !$_SITE_CONFIG['my_status'] ) {
	redirect(SITE_URL, 5, '抱歉：漫游已关闭。系统将在5秒后自动跳转至首页');
}

if ( empty($_GET['id']) ) {
	exit('请先选择应用');
}

$_GET['id'] = intval($_GET['id']);
$db_prefix	= getDbPrefix();
$app		= doQuery("SELECT * FROM {$db_prefix}myop_myapp WHERE `appid` = {$_GET['id']} LIMIT 1");
$app		= $app[0];
setTitle($app['appname']);

//漫游
$my_appId	= $_GET['id'];
$my_suffix	= base64_decode(urldecode($_GET['my_suffix']));
$my_prefix	= MYOP_URL . '/';

if (!$my_suffix) {
	redirect(SITE_URL . '/apps/myop/userapp.php?id=' . $my_appId . '&my_suffix=' . urlencode(base64_encode('/')));
    exit;
}

if (preg_match('/^\//', $my_suffix)) {
    $url = 'http://apps.manyou.com/'.$my_appId.$my_suffix;
} else {
    if ($my_suffix) {
        $url = 'http://apps.manyou.com/'.$my_appId.'/'.$my_suffix;
    } else {
        $url = 'http://apps.manyou.com/'.$my_appId;
    }
}

if (strpos($my_suffix, '?')) {
    $url = $url.'&my_uchId='.$_SITE_CONFIG['uid'].'&my_sId='.$_SITE_CONFIG['my_site_id'];
} else {
    $url = $url.'?my_uchId='.$_SITE_CONFIG['uid'].'&my_sId='.$_SITE_CONFIG['my_site_id'];
}

$url	.= '&my_prefix='.urlencode($my_prefix).'&my_suffix='.urlencode($my_suffix);

$current_url = MYOP_URL . '/userapp.php';
if ($_SERVER['QUERY_STRING']) {
    $current_url = $current_url.'?'.$_SERVER['QUERY_STRING'];
}

$extra 		 = $_GET['my_extra'];
$timestamp	 = $_MY_GLOBAL['timestamp'];
$url 		.= '&my_current='.urlencode($current_url);
$url 		.= '&my_extra='.urlencode($extra);
$url 		.= '&my_ts='.$timestamp;
$url 		.= '&my_appVersion='.$app['version'];
$hash 		 = $_SITE_CONFIG['my_site_id'] . '|' . $_SITE_CONFIG['uid'] . '|' . $_GET['id'] . '|' . $current_url . '|' . $extra . '|' . $timestamp . '|' . $_SITE_CONFIG['my_site_key'];
$hash		 = md5($hash);
$url		.= '&my_sig='.$hash;
$my_suffix   = urlencode($my_suffix);

include MYOP_THEME_PATH . '/header.html';
echo '<div class="content myop">';
//include MYOP_THEME_PATH . '/apps.html';
include MYOP_THEME_PATH . '/body.html';
echo '</div>';
include MYOP_THEME_PATH . '/footer.html';
?>