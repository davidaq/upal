<?php
class photoStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('photo');
		$photoDao     = M('photo');
		$albumDao = M('photo_album');
		$albumCount     = $albumDao->where(' photoCount>0 ')->count();
		$photoCount     = $photoDao->count();
		$storageCount   = $photoDao->sum('size');
		$storageCount   = byte_format($storageCount);
		return array(
			"非空{$app_alias}总数"	=>	$albumCount,
			'图片数量'            	=>	$photoCount,
			'占用空间'				=>  "{$storageCount}"
		);
	}
}