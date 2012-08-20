<?php
	session_start();
	$lanSet=(isset($_SESSION['language'])&&$_SESSION['language']=='en')?'en':'zh_cn';
	$f=$_SERVER['REQUEST_URI'];	
	$f=strstr($f,'jspre.php?');
	$f=substr($f,strlen('jspre.php?'));
	$file=@file($f);
	if(!$file)
		header('location: '.$key);
	else{
		header("Content-type: text/javascript; charset=utf-8");
		$file=implode('',$file);
		if(preg_match('/\/\/.?@language=(.+)/i',$file,$match)){
			$p=explode('/',strtolower(trim($match[1])));
			$arr2=$arr=array();
			if(file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php'))
				$arr=include('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php');
			if(isset($p[1])&&file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php'))
				$arr=array_merge($arr,include('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php'));
			function call_bbb($arg){
				global $arr;
				if(isset($arr[$arg[1]])){
					return $arr	[$arg[1]];
				}else{
					return $arg[1];
				}
			}
			$file=preg_replace_callback('/\{:L\([\'"](.+?)[\'"]\)\}/','call_bbb',$file);
		}elseif(preg_match('/\/\/.?#language=(.+)/i',$file,$match)){
			$p=explode('/',strtolower(trim($match[1])));
			$arr2=$arr=array();
			if(file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php'))
				$arr=include('./apps/'.$p[0].'/Lang/'.$lanSet.'/common.php');
			if(isset($p[1])&&file_exists('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php'))
				$arr=array_merge($arr,include('./apps/'.$p[0].'/Lang/'.$lanSet.'/'.$p[1].'.php'));
			echo <<<JS
if(_LANG==undefined){
	var _LANG=[];
	function L(xxx){
		if(_LANG[xxx]!=undefined){
			return _LANG[xxx];
		}else
			return xxx;
	}
}
JS;
			foreach($arr as $k=>$v){
				echo '_LANG["'.addslashes($k).'"]="'.addslashes($v).'";';
			}
		}
		echo $file;
	}
?>
