<?php
function getValues($arr,$key=false){
	if($arr==false)
		return false;
	$ret = arrray();
	foreach($arr as $f){
		$ret[]=($key===false)?$f:$f[$key];
	}
	return $ret;
}
