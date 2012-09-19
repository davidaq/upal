<?php
function renderStar($val,$total=5){
	$ret='';
	while($val>0.75){
		$val--;
		$total--;
		$ret.='<img src="'.SITE_URL.'/apps/buy/star_all.png"/>';
	}
	if($val>=0.25){
		$total--;
		$ret.='<img src="'.SITE_URL.'/apps/buy/star_half.png"/>';
	}
	while($total>0){
		$total--;
		$ret.='<img src="'.SITE_URL.'/apps/buy/star_none.png"/>';
	}
	return $ret;
}
?>
