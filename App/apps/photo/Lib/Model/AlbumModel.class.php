<?php
class AlbumModel extends Model{
	var $tableName	=	'photo_album';

	//为新用户创建默认数据
	public function createNewData($uid=0) {
		//创建默认相册
		if( intval($uid) <= 0 ){
			$uid	=	$this->mid;
		}
		$count	=	$this->where("userId='$uid' AND isDel=0")->count();
		if($count==0){
			$name	=	getShort(getUserName($uid),5).'的相册';	//默认的相册名
			$album['cTime']		=	time();
			$album['mTime']		=	time();
			$album['userId']	=	$uid;
			$album['name']		=	$name;
			$album['privacy']	=	1;
			$this->add($album);
		}
	}

	//更新相册图片数量
	function updateAlbumPhotoCount($aid) {
		$count	=	D('Photo')->where("albumId='$aid' AND isDel=0")->count();
		$map['photoCount']	=	$count;
		return $this->where("id='$aid'")->save($map);
	}

	//设置相册封面
	function setAlbumCover($albumId,$cover=0) {
		//插入图片封面
		$cover_info	=	D('Photo')->where("id='$cover'")->find();
		if($cover>0 && $cover_info){
			$map['coverImageId']	=	$cover_info['id'];
			$map['coverImagePath']	=	$cover_info['savepath'];
		}
		$map['mTime']	=	time();
		//更新相册信息
		$result	=	$this->where("id='$albumId'")->save($map);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//通过相册ID 获取图片ID集
/*	function getPhotoIds($uid,$albumId,$type) {
		$photos	=	$this->getPhotos($uid,$albumId,$type);
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	$v['photoId'];
			}
			return $photoIds;
		}else{
			return false;
		}
	}*/

	//通过相册ID 获取图片集
	function getPhotos($uid,$albumId,$type,$order='id ASC',$shownum=5) {
		//某个人的全部图片
		if($type=='mAll'){
			$map['userId']	=	$uid;
		}else{
		//某个专辑的全部图片(无type下默认)
			$map['albumId']	=	$albumId;
			$map['userId']	=	$uid;
		}
		$map['isDel']	=	0;
		$result	=	 D('Photo')->order($order)->where($map)->findAll();
		return $result;
	}

	//删除相册
	function deleteAlbum($aids,$uid,$isAdmin=0) {
		//解析ID成数组
		if(!is_array($aids)){
			$aids	=	explode(',',$aids);
		}

		//非管理员只能删除自己的图片
		if(!$isAdmin){
			$map['userId']	=	$uid;
		}
		
		//同步删除图片及附件
		$album['albumId']	=	array('in',$aids);
		$photos		=	D('Photo')->field('id')->where($album)->findAll();
		foreach($photos as $v){
			$photoIds[]	=	$v['id'];
		}
		//处理图片及附件
		$this->deletePhoto($photoIds,$uid,$isAdmin,$delFile);

		//删除相册		
		$map['id']		=	array('in',$aids);
		//$save['isDel']	=	1;
		$result	=	$this->where($map)->delete();			
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//删除图片
	function deletePhoto($pids,$uid,$isAdmin=0) {
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		//非管理员只能删除自己的图片
		if(!$isAdmin){
			$map['userId']	=	$uid;
		}

		//获取图片信息
		$photoDao  = D('Photo');
		$map['id'] = array('in',$pids);
		$photos	   = $photoDao->where($map)->findAll();
		//删除封面
		foreach ($photos as $key => $value){
			$id = $value['albumId'];
			$data['coverImageId'] = '';
			$data['coverImagePath'] = '';
			D('Album')->where(array('id'=>$id))->save($data);
		}
		///删除图片
		//$save['isDel']	=	1;
		$result	   = $photoDao->where($map)->delete();

		if($result){
			foreach($photos as $v){
				$attachIds[]	=	$v['attachId'];
				//重置相册图片数
				$this->updateAlbumPhotoCount($v['albumId']);
			}
			//处理附件			
			model('Attach')->deleteAttach($attachIds, true);
			return true;
		}else{
			return false;
		}
	}
}
?>