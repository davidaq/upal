<?php
class AlbumListWidget extends Widget{
	//type:select
	public function render( $data ){
		//初始化参数
		if(empty($data['type']))		$data['type']		= 'select';
		if(empty($data['form_name']))	$data['form_name']	= 'albumlist';
		if(empty($data['form_id']))		$data['form_id']	= 'albumlist';
		if(empty($data['uid']))			$data['uid']		= $_SESSION['mid'];

		//创建默认相册
		$pre	=	C('DB_PREFIX');
		if(D()->table("{$pre}photo_album")->where("isDel=0 AND userId='".$data['uid']."'")->count()==0){
			$album['cTime']		=	time();
			$album['mTime']		=	time();
			$album['userId']	=	$data['uid'];
			$album['name']		=	getShort(getUserName($data['uid']),5).'的相册';
			$album['privacy']	=	1;
			D()->table("{$pre}photo_album")->add($album);
		}

		//获取相册列表数据
		$data['data']	=	M('photo_album')->where("isDel=0 AND userId='".$data['uid']."'")->findAll();
		return $this->renderFile( $data );
	}
	/*
		Widget模版在第一次初始化的时候，写不进数据，故先改成如下形式。
	*/
	protected function renderFile( $data ){
		$out	=	'<select name="'.$data['form_name'].'" id="'.$data['form_id'].'">';
		foreach($data['data'] as $vo){
			if( $vo['id'] == intval($data['selected']) ){
				$out	.=	'<option value="'.$vo['id'].'" selected="selected">'.getShort($vo['name'],13).'</option>';
			}else{
				$out	.=	'<option value="'.$vo['id'].'">'.getShort($vo['name'],13).'</option>';
			}
		}
		$out	.=	'</select>';

		return	$out;
	}
}