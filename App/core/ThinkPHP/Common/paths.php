<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2008 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
//[RUNTIME]
// 目录设置
define('TMPL_DIR',	'Theme');

// 路径设置
define('TMPL_PATH',		APP_PATH.'/Theme/');
define('COMMON_PATH',   APP_PATH.'/Common/'); // 项目公共目录
define('LIB_PATH',		APP_PATH.'/'); //
define('CONFIG_PATH',	APP_PATH.'/Conf/'); //
define('LANG_PATH',     APP_PATH.'/Lang/'); //


define('HTML_PATH',		RUNTIME_PATH.'/html/'); //
define('CACHE_PATH',	RUNTIME_PATH.'/cache/'); //
define('LOG_PATH',		RUNTIME_PATH.'/logs/'); //
define('TEMP_PATH',		RUNTIME_PATH.'/temp/'); //
define('DATA_PATH',		RUNTIME_PATH.'/data/'); //

define('VENDOR_PATH',	SITE_PATH.'/addons/');

//[/RUNTIME]
// 为了方便导入第三方类库 设置Vendor目录到include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);
?>