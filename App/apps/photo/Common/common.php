<?php
//读取推荐列表
function IsHotList(){
	$lists = M('photo_album')->where(' isHot="1" ')->order( 'rTime DESC' )->limit(5)->findAll();
	return $lists;
}
//获取相册封面
function get_album_cover($albumId,$album='',$width=140,$height=140) {

	//获取相册详细信息
	if(empty($album) || $albumId!=$album['id']){
		$album	=	D('Album')->find($albumId);
	}
	
	//根据隐私情况，判断相册封面
	if($album['privacy']==4&&(md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'])!=$_COOKIE['album_password_'.$album['id']])){
	//密码可见
		$cover		=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/photo/Tpl/default/Public/images/photo_mima.gif";
	}elseif($album['privacy']==3){
	//主人可见
		$cover		=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/photo/Tpl/default/Public/images/photo_zrkj.gif";
	}elseif($album['privacy']==2){
	//显示相册只有他关注的人可见
		$cover		=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/photo/Tpl/default/Public/images/photo_hykj.gif";
	}else{
		//图片封面
		if(intval($album['photoCount'])>0 && !empty($album['coverImagePath'])){
			$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=".get_photo_url($album['coverImagePath']);
		}elseif(intval($album['photoCount'])==0){
			$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/photo/Tpl/default/Public/images/photo_zwzp.gif";
		}else{//无设置封面 且有照片 则默认最新一张为封面
			$firstImg = M('photo')->field('savepath')->where("albumId={$album['id']}")->order('`order` DESC,id DESC')->find();
			$cover	  = SITE_URL."/thumb.php?w=$width&h=$height&url=".get_photo_url($firstImg['savepath']);
		}			
	}
	return $cover;
}
//根据存储路径，获取图片真实URL
function get_photo_url($savepath) {
	return SITE_URL . '/data/uploads/' . $savepath;
}

//获取照隐私
function get_privacy($privacy) {
	//根据隐私情况，显示相册隐私
	if($privacy==4){
		//持密码可见
		return '持密码可见';
	}elseif($privacy==3){
		//仅主人可见
		return '仅主人可见';
	}elseif($privacy==2){
		//仅朋友可见
		return '仅主人关注的人可见';
	}else{
		//任何人都可见
		return '任何人都可见';
	}
}

//获取照隐私
function get_privacy_code($privacy) {
	//根据隐私情况，显示相册隐私
	if($privacy==4){
		//持密码可见
		return 'password';
	}elseif($privacy==3){
		//仅主人可见
		return 'self';
	}elseif($privacy==2){
		//仅我关注的人可见
		return 'following';
	}else{
		//任何人都可见
		return 'everyone';
	}
}
//获取应用配置参数
function getConfig($key=NULL){
	$config = model('Xdata')->lget('photo');
	$config['album_raws'] || $config['album_raws']=6;
	$config['photo_raws'] || $config['photo_raws']=8;
	$config['photo_preview']==0 || $config['photo_preview']=1;
	($config['photo_max_size']=floatval($config['photo_max_size'])*1024*1024) || $config['photo_max_size']=-1;
	$config['photo_file_ext'] || $config['photo_file_ext']='jpeg,gif,jpg,png';
	$config['max_flash_upload_num'] || $config['max_flash_upload_num']=10;
	//$config['max_storage_size'] || $config['max_storage_size']=0;
	//$config['max_album_num'] || $config['max_album_num']=0;
	//$config['max_photo_num'] || $config['max_photo_num']=0;
	$config['open_watermark']==0 || $config['open_watermark']=1;
	$config['watermark_file'] || $config['watermark_file']='public/images/watermark.png';
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];	
	}
}
/*function type_weibo($type_data){
	$filename = basename($type_data);
	preg_match('|\.(\w+)$|', $filename, $ext);
	if(in_array($ext[1],array('jpeg','jpg','gif','png'))){
		return 1;
	}else{
		return NULL;
	}
}*/
/*
function getTagName($tagId) {
    $dao = D ( 'Tag' );
    $list = $dao->where('id='.$tagId)->find ();
    return $list['name'];
}
function getTagModule($tagId) {
    $dao = D ( 'Tag' );
    $list = $dao->where('id='.$tagId)->find ();
    return $list['module'];
}
function getTagId($tagName, $module = 'artifacts') {
    $dao = D ( 'Tag' );
    $map['name'] = 'tagName';
    $map[$module] = $module;
    $list = $dao->where($map)->find();
    return $list['id'];
}
function getTagNames($tags) {
    $dao = D ( 'Tag' );
    $map['id'] = array('in',$tags);
    $list = $dao->where($map)->findAll();
    foreach ( $list as $v ) {
        $names [] = " <a href='" . __APP__ . "/Index/tags/module/{$v['module']}/tagId/{$v['id']}'>{$v['name']}</a> ";
    }
    return implode ( ',', $names );
}*/
?>