<?php
class WeiboPluginsAction extends Action{
	function init(){
		if(extension_loaded('zlib')){//检查服务器是否开启了zlib拓展
	    	ob_start('ob_gzhandler');
	  	}
	  	header ("content-type: text/css; charset: UTF-8");//注意修改到你的编码
	  	header ("cache-control: must-revalidate");
	  	$offset = 60 * 60 * 24;//css文件的距离现在的过期时间，这里设置为一天
	  	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	  	header ($expire);
	  	ob_start("compress");
		//包含你的全部css和js文档
	  	include SITE_PATH.'/apps/group/Tpl/default/Public/Js/weibo.js';
	  	include SITE_PATH.'/apps/weibo/Lib/Plugin/video/control.js';
		include SITE_PATH.'/apps/weibo/Lib/Plugin/music/control.js';
		include SITE_PATH.'/apps/weibo/Lib/Plugin/image/control.js';
	  	if(extension_loaded('zlib')){
		    ob_end_flush();//输出buffer中的内容，即压缩后的css文件
	  	}
	}
}