<?php 
// 格式化内容
function wapFormatContent($content, $url = false, $from_url = '') {
	if($url){
		$content = preg_replace('/((?:https?|mailto).*?)(\s|　|&nbsp;|<br|\'|\"|$)/', '<a href="'.U('w3g/Index/urlalert').'&from_url='.$from_url.'&url=\1">\1</a>\2', $content);
	}
	$content = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$content);
//	$content = preg_replace_callback("/#(.+?)#/is",wapFormatTopic,$content);
//	$content = preg_replace_callback("/@([\w\\x80-\\xff-]+?)([\s|:]|$)/is",wapFormatUser,$content);
	return $content;
}

// 格式化评论
function wapFormatComment($content,$url=false, $from_url = '') {
	if($url){
		$content = preg_replace('/((?:https?|mailto).*?)(\s|　|&nbsp;|<br|\'|\"|$)/', '<a href="'.U('w3g/Index/urlalert').'&from_url='.$from_url.'&url=\1">\1</a>\2', $content);
	}
    $content = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$content);
    $content = preg_replace_callback("/@(.+?)([\s|:]|$)/is",wapFormatUser,$content);
    return $content;
}

// 话题格式化回调
function wapFormatTopic($data) {
	return "<a href=".U('w3g/Index/doSearch',array('key'=>t($data[1]))).">".$data[0]."</a>";
}

// 用户连接格式化回调
function wapFormatUser($name) {
	$info = D('User', 'home')->where("uname='{$name[1]}'")->find();
	if( $info ){
		return "<a href=".U('w3g/Index/weibo',array('uid'=>$info['uid'])).">".$name[0]."</a>";
	}else{
		return "$name[0]";
	}
}

// 短地址
function getContentUrl($url) {
	return getShortUrl( $url[1] ).' ';
}

function is_iphone() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("iphone","ipad","ipod");
    $is_iphone = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_iphone = true;
            break;
        }
    }
    return $is_iphone;
}

function is_android() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("android");
    $is_android = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_android = true;
            break;
        }
    }
    return $is_android;
}
?>