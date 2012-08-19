<?php
	$f=$_SERVER['REQUEST_URI'];	
	$f=strstr($f,'jspre.php?');
	$f=substr($f,strlen('jspre.php?'));
	$file=@file($f);
	if(!$file)
		header('location: '.$key);
	else{
		$file=implode('',$file);
		if(preg_match('/\/\/.?@language=(.+)/i',$file,$match)){
			$p=explode('/',strtolower(trim($match[1])));
			if(file_exists('./apps/'.$p[0].'/Lang/en/common.php'))
				$arr=include('./apps/'.$p[0].'/Lang/en/common.php');
			if(file_exists('./apps/'.$p[0].'/Lang/zh_cn/common.php'))
				array_push($arr,include('./apps/'.$p[0].'/Lang/zh_cn/common.php'));
			if(file_exists('./apps/'.$p[0].'/Lang/en/'.$p[1].'.php'))
				array_push($arr,include('./apps/'.$p[0].'/Lang/en/'.$p[1].'.php'));
			if(file_exists('./apps/'.$p[0].'/Lang/zh_cn/'.$p[1].'.php'))
				array_push($arr,include('./apps/'.$p[0].'/Lang/zh_cn/'.$p[1].'.php'));
			function call_bbb($arg){
				global $arr;
				if(isset($arr[$arg[1]])){
					return $arr[$arg[1]];
				}else{
					return $arg[1];
				}
			}
			$file=preg_replace_callback('/\{:L\([\'"](.+?)[\'"]\)\}/','call_bbb',$file);
		}
		echo $file;
	}
?>
