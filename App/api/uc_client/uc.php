<?php
error_reporting(0);
//为了更好的兼容UC,把uc.php挪到api根目录下. 原uc_client中的保留. 只是与这个文件的SITE_PATH路径有点差异而已
define('SITE_PATH', dirname(dirname(getcwd())));

// UCenter中使用的ThinkSNS核心基类
require_once SITE_PATH . '/api/uc_client/core.php';

// ThinkSNS与UCenter集成的必要方法
require_once SITE_PATH.'/api/uc_client/common.php';


define('IN_DISCUZ', TRUE);

define('UC_CLIENT_VERSION', '1.5.0');	//note UCenter 版本标识
define('UC_CLIENT_RELEASE', '20081031');

define('API_DELETEUSER', 1);			//note 用户删除 API 接口开关
define('API_RENAMEUSER', 1);			//note 用户改名 API 接口开关
define('API_GETTAG', 1);				//note 获取标签 API 接口开关
define('API_SYNLOGIN', 1);				//note 同步登录 API 接口开关
define('API_SYNLOGOUT', 1);				//note 同步登出 API 接口开关
define('API_UPDATEPW', 1);				//note 更改用户密码 开关
define('API_UPDATEBADWORDS', 1);		//note 更新关键字列表 开关
define('API_UPDATEHOSTS', 1);			//note 更新域名解析缓存 开关
define('API_UPDATEAPPS', 1);			//note 更新应用列表 开关
define('API_UPDATECLIENT', 1);			//note 更新客户端缓存 开关
define('API_UPDATECREDIT', 1);			//note 更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);		//note 向 UCenter 提供积分设置 开关
define('API_GETCREDIT', 1);				//note 获取用户的某项积分 开关
define('API_UPDATECREDITSETTINGS', 1);	//note 更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('DISCUZ_ROOT', SITE_PATH.'/api/');

//note 普通的 http 通知方式
if(!defined('IN_UC')) {
	error_reporting(0);
	set_magic_quotes_runtime(0);

	defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	require_once DISCUZ_ROOT.'./uc_client/uc_config.inc.php';

	$_DCACHE = $get = $post = array();

	$code = @$_GET['code'];
	parse_str(_authcode($code, 'DECODE', UC_KEY), $get);
	if(MAGIC_QUOTES_GPC) {
		$get = _stripslashes($get);
	}
	$timestamp = time();
	if($timestamp - $get['time'] > 3600) {
		exit('Authracation has expiried');
	}
	if(empty($get)) {
		exit('Invalid Request');
	}
	$action = $get['action'];
	require_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
	$post = xml_unserialize(file_get_contents('php://input'));

	if(in_array($get['action'], array('test','face','deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
		require_once DISCUZ_ROOT.'./uc_client/lib/db.class.php';
		$GLOBALS['db'] = new ucclient_db;
		$GLOBALS['db']->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCONNECT, true, UC_DBCHARSET);
		$GLOBALS['tablepre'] = UC_DBTABLEPRE;
		//unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
		$uc_note = new uc_note();
		exit($uc_note->$get['action']($get, $post));
	} else {
		exit(API_RETURN_FAILED);
	}

//note include 通知方式
} else {
	require_once DISCUZ_ROOT.'./uc_client/uc_config.inc.php';
	require_once DISCUZ_ROOT.'./uc_client/lib/db.class.php';
	$GLOBALS['db']->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCONNECT, true, UC_DBCHARSET);
	$GLOBALS['tablepre'] = UC_DBTABLEPRE;
	$GLOBALS['tablepre'] = $tablepre;
	//unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
}