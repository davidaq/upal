<?php 
include_once 'function.php';
switch ($do_type){
	case 'before_publish':
			$link = t($_POST['url']);
			$parseLink = parse_url($link);
			if(preg_match("/(youku.com|youtube.com|5show.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com)$/i", $parseLink['host'], $hosts)) {
				$return['boolen'] = 1;
				$return['data']   = getShortUrl($link);
			}else{
				$return['boolen'] = 0;
				$return['message'] = '仅支持youku、youtube、5show、ku6、sohu、mofile、sina、tudou等视频发布';
			}
			$flashinfo = video_getflashinfo($link, $hosts[1]);
			
			/*if (!$flashinfo['flashvar'] || !$flashinfo['img'] || !$flashinfo['title']) {
				$return['boolen'] = 0;
				$return['message'] = '未成功获取视频信息，请检查地址是否正确';
				//$return['message'] = 'flashvar:'.$flashinfo['flashvar'].';img:'.$flashinfo['img'].';title:'.$flashinfo['title'];
			}else{*/
				$return['data'] = $flashinfo['title'].$return['data'];
			/*}*/
			exit( json_encode($return) );
		break;
		
	case 'publish':
	        	$link = $type_data;
				$parseLink = parse_url($link);
				if(preg_match("/(youku.com|youtube.com|5show.com|ku6.com|sohu.com|mofile.com|sina.com.cn|tudou.com)$/i", $parseLink['host'], $hosts)) {
					$flashinfo = video_getflashinfo($link, $hosts[1]);
				}        	
				if ($flashinfo['flashvar']) {
		        	$typedata['flashvar']  = $flashinfo['flashvar'];
		        	$typedata['flashimg']  = $flashinfo['img'];
		        	$typedata['host']      = $hosts[1];
		        	$typedata['source']    = $type_data;
		        	$typedata['title']     = $flashinfo['title'];
				}
		break;	
}