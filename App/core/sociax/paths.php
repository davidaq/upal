<?php
//[RUNTIME]
// 目录名设置
define('CACHE_DIR',		'cache');
define('HTML_DIR',		'html');
define('LOG_DIR',		'logs');
define('TEMP_DIR',		'temp');

define('TMPL_DIR',		'Tpl');
define('CONF_DIR',		'Conf');
define('LIB_DIR',		'Lib');
define('LANG_DIR',		'Lang');

// 应用路径设置
define('TMPL_PATH',		APP_PATH.'/'.TMPL_DIR.'/');
define('COMMON_PATH',	APP_PATH.'/Common/'); // 项目公共目录
define('LIB_PATH',		APP_PATH.'/'.LIB_DIR.'/'); //
define('CONFIG_PATH',	APP_PATH.'/'.CONF_DIR.'/'); //
define('LANG_PATH',     APP_PATH.'/'.LANG_DIR.'/'); //

//web访问路径
define('HTML_PATH',		RUNTIME_PATH.'/'.HTML_DIR.'/'); //

//运行时路径
define('LOG_PATH',		RUNTIME_PATH.'/'.LOG_DIR.'/'); //
define('CACHE_PATH',	RUNTIME_PATH.'/'.CACHE_DIR.'/'); //
define('TEMP_PATH',		SITE_PATH.'/_runtime/~temp/'); // 为了让多个app公用缓存
define('DATA_PATH',		RUNTIME_PATH.'/'.'data/'); //

//插件扩展路径
define('VENDOR_PATH',	SITE_PATH.'/addons/');
//[/RUNTIME]
// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);
?>