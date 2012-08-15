<?php
header("Content-type: text/html; charset=utf-8");
define('SITE_PATH',dirname(__FILE__));
$config = include 'config.inc.php';

//清memcached缓存
if( isset($config['DATA_CACHE_TYPE']) && strtolower($config['DATA_CACHE_TYPE']) == 'memcache'){
	if(!isset($config['MEMCACHE_HOST'])){
		$config['MEMCACHE_HOST'] = '127.0.0.1:11211';
	}
	$result = flushServer($config['MEMCACHE_HOST']);
	if($result){
		echo "<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;'> Memcache缓存已清除! </div> <br /><br />";
	}
}

function flushServer($server){
    list($host,$port) = explode(':',$server);
    $resp = sendMemcacheCommand($host,$port,'flush_all');
    return $resp;
}

function sendMemcacheCommand($server,$port,$command){
	$s = @fsockopen($server,$port);
	if (!$s){
		die("Cant connect to:".$server.':'.$port);
	}
	fwrite($s, $command."\r\n");
	$buf='';
	while ((!feof($s))) {
		$buf .= fgets($s, 256);
		if (strpos($buf,"END\r\n")!==false){ // stat says end
		    break;
		}
		if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
		    break;
		}
		if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
		    break;
		}
	}
    fclose($s);
	if(trim($buf)=='OK'){
		return true;
	}else{
		return false;
	}
}

//清文件缓存
$dirs	=	array('./_runtime/');

//清理缓存
foreach($dirs as $value) {
	rmdirr($value);

	echo "<div style='border:2px solid green; background:#f1f1f1; padding:20px;margin:20px;width:800px;font-weight:bold;color:green;text-align:center;'>  文件缓存已清除！ </div> <br /><br />";

}

@mkdir('_runtime',0777,true);

function rmdirr($dirname) {
	if (!file_exists($dirname)) {
		return false;
	}
	if (is_file($dirname) || is_link($dirname)) {
		return unlink($dirname);
	}
	$dir = dir($dirname);
	if($dir){
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
		}
	}
	$dir->close();
	return rmdir($dirname);
}
function U(){
	return false;
}
?>