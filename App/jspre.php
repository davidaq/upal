<?php
	session_start();
	$lanSet=(isset($_SESSION['language'])&&$_SESSION['language']='en')?'en':'zh_cn';
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
			$arr2=$arr=array();
			if(file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php'))
				$arr=include('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php');
			if(file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php'))
				$arr2=include('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php');
			function call_bbb($arg){
				global $arr;
				global $arr2;
				if(isset($arr2[$arg[1]])){
					return $arr2[$arg[1]];
				}elseif(isset($arr[$arg[1]])){
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
