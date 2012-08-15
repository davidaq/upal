<?php
//error_reporting(E_ALL); //调试、找错时请弃用这一行配置，注释下一行配置
error_reporting(E_ERROR | E_PARSE | E_STRICT);

//安装检查开始：如果您已安装过ThinkSNS，可以删除本段代码
if(is_dir('install/') && !file_exists('install/install.lock')){
	header("Content-type: text/html; charset=utf-8");
	die ("<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;'>"
		."<h1>系统检测到您尚未安装ThinkSNS系统，<a href='install/install.php'>请点击进入安装页面</a></h1>"
		."</div> <br /><br />");
}
//安装检查结束

//网站根路径设置
define('SITE_PATH', getcwd());

define('RUNTIME_ALLINONE', false);	// 是否开启AllInOne模式 (开启时, NO_CACHE_RUNTIME 和 APP_DEBUG将失效)
define('NO_CACHE_RUNTIME', true);	// 是否关闭核心文件的编译缓存 (开启AllInOne模式时设置无效, 将自动置为false)

//载入核心文件
require(SITE_PATH.'/core/sociax.php');

//实例化一个网站应用实例
$App = new App();
$App->run();