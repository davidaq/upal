<?php
//相册应用 - UploadAction 上传图片 及 处理
class UploadAction extends BaseAction{
	public function _initialize() {
		parent::_initialize();
		$IsHotList = IsHotList();
		$this->assign('IsHotList',$IsHotList);
	}
	//普通上传
	public function index() {
		$this->setTitle('普通上传');
		$this->display();
	}

	//flash上传
	public function flash() {
		$config = getConfig();
		$this->assign($config);	
		$this->setTitle('批量上传');	
		$this->display();
	}

	//执行单张图片上传
    public function upload_single_pic(){

		$albumId	=	intval($_REQUEST['albumId']);
		$albumDao   =   D('Album');
		$albumInfo	=	$albumDao->field('id')->find($albumId);
		if(!$albumInfo)echo "0";
		$config     =   getConfig();
		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	$config['photo_file_ext'];
		$options['max_size']    =   $config['photo_max_size'];
		//$options['save_photo']['albumId']	=	$albumId;

		$info	=	X('Xattach')->upload('photo',$options);
		if($info['status']){			
			//保存图片信息
			$info['info'] = $this->save_photo($albumId,$info['info']);
			//启用session记录flash上传的图片数，也可以防止意外提交。
			//$upload_count	=	intval($_SESSION['upload_count']);
			//$_SESSION['upload_count']	=	$upload_count + 1;

			//重置相册图片数
			$albumDao->updateAlbumPhotoCount($albumId);

			//上传成功
			echo json_encode($info);
		}else{
			//上传出错
			echo json_encode($info);
		}
    }
	//执行多张图片上传
	public function upload_muti_pic() {

		$albumId	=	intval($_REQUEST['albumId']);
		$albumDao   =   D('Album');
		$albumInfo	=	$albumDao->field('id')->find($albumId);
		if(!$albumInfo)$this->error('不存在的相册ID');
		$config     =   getConfig();
 		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	$config['photo_file_ext'];
		$options['max_size']    =   $config['photo_max_size'];
		//$options['save_photo']['albumId']	=	$albumId;

		//$info		=	$this->api->attach_upload('photo',$options);
		$info	=	X('Xattach')->upload('photo',$options);
		if($info['status']){
			$info['info'] = $this->save_photo($albumId,$info['info']);
			//记录上传的图片数量
			$upnum	=	count($info['info']);
			//重置相册图片数
			$albumDao->updateAlbumPhotoCount($albumId);

			U('/Upload/muti_edit_photos',array(albumId=>$albumId,upnum=>$upnum),true);
		}else{
			$this->error('上传出错：'.$info['info']);
		}
	}
	//保存照片信息
	public function save_photo($albumId,$attachInfos) {		
		//获取相册隐私
		$albumInfo	=	D('Album')->field('privacy')->find($albumId);
		//保存图片附件进入相册 并进行积分操作
		foreach($attachInfos as $k=>$v){
			$photo['attachId']	=	$v['id'];
			$photo['albumId']	=	$albumId;
			$photo['userId']	=	$v['userId'];
			$photo['cTime']		=	time();
			$photo['mTime']		=	time();
			$photo['name']		=	substr($v['name'],'0',strpos($v['name'],'.'));	//去掉后缀名
			$photo['size']		=	$v['size'];
			$photo['savepath']	=	$v['savepath'].$v['savename'];
			$photo['privacy']	=	$albumInfo['privacy'];
			$photo['order']		=	10000;

			$photoid            =   D('Photo')->add($photo);
			//dump($this->getLastSql());
			$attachInfos[$k]['photoId']		=	$photoid;
			$attachInfos[$k]['albumId']		=	$albumId;
		}

	 	//计算积分
		X('Credit')->setUserCredit($v['userId'],'add_photo');

		return $attachInfos;
	}

	//上传后执行编辑操作
	public function muti_edit_photos() {

		//判断session,防止意外提交
//		if( intval($_SESSION['upload_count']) > 0 ){
//			$upnum	=	intval($_SESSION['upload_count']);
//			unset($_SESSION['upload_count']);
//		}else{
//			$this->error('上传错误，请正常提交！不要多次点击 "保存图片信息" 按钮！');
//		}
		$upnum		=	intval($_REQUEST['upnum']);
		if($upnum==0)
			$this->error('请至少上传一张图片！');
		$albumId	=	intval($_REQUEST['albumId']);
		$albumDao   =   D('Album');
		$albumInfo	=	$albumDao->find($albumId);

		if(!$albumInfo){
			$this->error('请上传到指定的相册！');
		}

		//公开的相册发布微薄
		if($albumInfo['privacy']<=2){
			$this->assign('publish_weibo',1);
		}

		if( $upnum > 0 ) {

			$photos		=	D('Photo')->limit($upnum)->order("id DESC")->where("userId='$this->mid'")->findAll();
			$this->assign('photos',$photos);
			$this->assign('album',$albumInfo);
			$this->assign('upnum',$upnum);
			
			$albumlist	=	$albumDao->where(" userId={$this->uid} ")->findAll();
			$this->assign('albumlist',$albumlist);
		
			$this->display();

		}else{

			$this->error('上传出错：没有上传任何图片！');
		}
	}

	//保存上传的图片
	public function save_upload_photos() {

		//相册信息
		$albumId		=	intval($_POST['albumId']);
		$album_cover	=	intval($_POST['album_cover']);
		$upnum			=	intval($_POST['upnum']);

		$albumDao       =   D('Album');
		$albumInfo		=	$albumDao->find($albumId);

		if(!$albumInfo){
			$this->error('请先正确选择相册，再上传图片！');
		}
			/*处理图片信息*/
			$photoDao       =   D('Photo');
			
			//解析图片数据
			foreach($_POST['name'] as $k=>$v){
				$new_photos[$k]['name']		=	$v;
				$new_photoids[]	=	$k;
			}
			foreach($_POST['move_to'] as $k=>$v){
				$new_photos[$k]['albumId']	=	$v;
			}


			//对比原始数据，筛选出需要更新的图片
			$photo_ids['id']	=	array('in',$new_photoids);
			$old_photos			=	$photoDao ->where($photo_ids)->findAll();
			foreach($old_photos as $k=>$v){
				//如果相册ID和名称都没变化，不需要保存
				$photoid	=	$v['id'];
				if($v['albumId']==$new_photos[$photoid]['albumId'] && $v['name']==$new_photos[$photoid]['name'] ){
					unset($new_photos[$photoid]);
				}
			}

			//保存图片信息并统计新图片数
			foreach($new_photos as $k=>$v){
				unset($map);
				$map['userId']		=	$this->mid;
				$map['albumId']		=	$v['albumId'];
				$map['name']		=	$v['name'];
				$map['privacy']		=	$album_privacy;
				//相册信息更新
				$photoDao->limit(1)->where("id='$k'")->save($map);
				//重置相册图片数
				$albumDao->updateAlbumPhotoCount($map['albumId']);
			}

		/*   处理相册信息  */

			//重置相册图片数
			$albumDao->updateAlbumPhotoCount($albumId);
	
			//如果相册封
			if($album_cover){
				$album['coverImageId']	=	$album_cover;
				if($coverInfo	=	$photoDao->field('id,savepath')->find($album_cover)){
					$album['coverImagePath']=	$coverInfo['savepath'];
					$albumDao->where("id='$albumId'")->save($album);
				}
			}

		//保存相册数据
		//D('Album')->setAlbumCover($albumId,$album_cover);

		if(intval($_POST['publish_weibo'])==1){
			$newphotoCount = count($new_photoids);
			$photo_ids['albumId'] = $albumId;
			$photoInfo = $photoDao->where($photo_ids)->order('id ASC')->find();
			if(!$photoInfo)$photoInfo = $photoDao->where(array('id'=>array('in',$new_photoids)))->order('id ASC')->find();
			$_SESSION['publish_weibo']=urlencode(serialize(array('count'=>$newphotoCount,'author'=>getUserName($photoInfo['userId']),'title'=>$photoInfo['name'],'url'=>U('photo/Index/photo',array('id'=>$photoInfo['id'],'uid'=>$photoInfo['userId'],'aid'=>$photoInfo['albumId'])),'type'=>1,'type_data'=>$photoInfo['savepath'])));
		}
		//跳转到相册页面
		//$this->redirect('/Index/album/id/'.$albumId.'/uid/'.$this->mid);
		$this->assign('jumpUrl',U('/Index/album',array(id=>$albumId,uid=>$this->mid)));
		$this->success('图片上传保存成功！');
	}
}
?>