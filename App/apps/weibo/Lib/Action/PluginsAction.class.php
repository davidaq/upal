<?php
class PluginsAction extends Action{

	function init(){

//		if(extension_loaded('zlib')){//检查服务器是否开启了zlib拓展
//	    	ob_start('ob_gzhandler');
//	  	}
	  	header ("content-type: text/css; charset: UTF-8");//注意修改到你的编码
	  	header ("cache-control: must-revalidate");
	  	$offset = 60 * 60 * 24;//css文件的距离现在的过期时间，这里设置为一天
	  	$expire = "expires: " . gmdate ("D, d M Y H:i:s", time() + $offset) . " GMT";
	  	header ($expire);
	  	ob_start("compress");

//	  	$weibo_option = model('Xdata')->lget('weibo');
//	  	if( $weibo_option['openAutoDenounceText'] ){
//	  		echo '<script>var success_publish = '.$weibo_option['openAutoDenounceText'].'</script>';
//	  	}

		//包含你的全部css和js文档
	  	include SITE_PATH.'/apps/weibo/Tpl/default/Public/weibo.js';

//	  	if(extension_loaded('zlib')){
//		    ob_end_flush();//输出buffer中的内容，即压缩后的css文件
//	  	}
	}
}
?>
