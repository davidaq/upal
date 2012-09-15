<?php
set_time_limit(0);
header("Content-type: text/html; charset=utf-8");
$basedir = '.';

$auto = 1;

checkdir($basedir);
echo '<br />验证完毕，如果没有红色文件证明一切正常！';
function checkdir($basedir){
	if($dh = opendir($basedir)) {
		while(($file = readdir($dh)) !== false) {
			if($file != '.' && $file != '..' && $file != '.svn'){
				if( substr($file,-4,4) == '.php' ){
					if(!is_dir($basedir."/".$file)) {
						if(checkBR("$basedir/$file")==1){
							echo "filename: $basedir/$file <font color=red>存在，自动删除.</font> <br>";
						}
					}
				}else{
						$dirname = $basedir."/".$file;
						checkdir($dirname);
				}
			}
		}
		closedir($dh);
	}
}

function checkBR($filename) {
	$s = file_get_contents($filename);
	$s = preg_replace("/(?<!\\n)\\r+(?!\\n)/", "\n", $s); //replace just CR with CRLF  mac to linux
	//$s = preg_replace("/(?<!\\r)\\n+(?!\\r)/", "\n", $s); //replace just LF with CRLF 
	//$s = preg_replace("/(?<!\\r)\\n\\r+(?!\\n)/", "\n", $s); //replace misordered LFCR with CRLF windows to linux
	@file_put_contents($filename, $s, LOCK_EX);
	return 1;
}
?>