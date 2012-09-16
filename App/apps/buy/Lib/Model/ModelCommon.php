<?php
function getValues($arr, $key){
	if($arr==false)
		return false;
	$ret = array();
	foreach($arr as $f){
		$ret[]=$f[$key];
	}
	return $ret;
}
