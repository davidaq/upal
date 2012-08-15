<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 系统定义文件
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id$
 +------------------------------------------------------------------------------
 */
//[RUNTIME]
if (!defined('THINK_PATH')) exit();

// 系统信息
if(version_compare(PHP_VERSION,'6.0.0','<') ) {
    @set_magic_quotes_runtime (0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {

    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            // CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER["SCRIPT_NAME"],'/'));
        }
    }

    if(!defined('__ROOT__')) {
        // 网站URL根目录
        $_root = dirname(_PHP_FILE_);
        //define('__ROOT__',  'http://'.$_SERVER['HTTP_HOST']. (($_root=='/' || $_root=='\\')?'':$_root));
		define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':rtrim($_root,'/')));
    }

	if(!defined('SITE_DOMAIN'))		define('SITE_DOMAIN',$_SERVER['HTTP_HOST']);
	if(!defined('SITE_URL'))		define('SITE_URL', 'http://'.SITE_DOMAIN.__ROOT__);
	if(!defined('UPLOAD_URL'))		define('UPLOAD_URL', SITE_URL.'/data/uploads');
	if(!defined('__UPLOAD__'))		define('__UPLOAD__', UPLOAD_URL);

}

// 版本信息
define('THINK_VERSION', '2.0');
//[/RUNTIME]

// 记录内存初始使用
if(MEMORY_LIMIT_ON) {
     $GLOBALS['_startUseMems'] = memory_get_usage();
}
?>