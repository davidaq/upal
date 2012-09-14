<?php
//相册应用 - indexaction 图片和专辑的列表
class IndexAction extends BaseAction{
	public function _initialize() {
		parent::_initialize();
		$IsHotList = IsHotList();
		$this->assign('IsHotList',$IsHotList);
	}

	public function index(){
		$this->all_albums();
    }
	//大家的图片
	public function all_photos() {
		$order  = NULL;
        switch( $_GET['order'] ) {
        	case 'new':    //最新排行
       			$order = 'cTime DESC';
       			$this->setTitle('最新图片');
                break;
            case 'following':    //关注的人的图片
				$in_arr = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
				$in_arr = $this->_getInArr($in_arr);
                $map['userId'] = array('in',$in_arr);
                $this->setTitle('我关注的人的图片');
                break;
            default:      //默认热门排行
                $order = 'readCount DESC';
                $this->setTitle('热门图片');
        }
		$map['privacy']	=	1; //所有人公开的图片

		//获取配置参数
		$config = getConfig();
		$photos	=	D('Photo')->where($map)->order($order)->findPage($config['photo_raws']);
		$this->assign('photo_preview',$config['photo_preview']);
		$this->assign('photos',$photos);
		$this->display('all_photos');
	}
	//大家的专辑
	public function all_albums() {
		$order = NULL;
		$map   = '';
        switch($_GET['order']) {
        	case 'new':    //最新排行
       			$order = 'mTime DESC';
				$map  .=' photoCount>0 AND privacy=1 ';
				$this->setTitle('最新' . $this->appName);
                break;
            case 'following':    //关注的人的相册
				$in_arr = M('weibo_follow')->field('fid')->where("uid={$this->mid} AND type=0")->findAll();
				$in_arr = $this->_getInArr($in_arr);
                $map['userId'] = array('in',$in_arr);
                $this->setTitle('我关注的人的' . $this->appName);
                break;
            default:      //默认热门排行
                //$order = 'readCount DESC';
                $order = 'readCount DESC';
				$map  .=' photoCount>0 AND privacy=1 ';
				$this->setTitle('热门' . $this->appName);
        }
		//获取相册数据
		$data	=	D('Album')->order($order)->where($map)->findPage(getConfig('album_raws'));
		$this->assign('data',$data);
		$this->display('all_albums');
	}
	function _getInArr($in_arr) {
		$in_str = array();
		foreach($in_arr as $key=>$v) {
			$in_str[] = $v['fid'];
		}
		return $in_str;
	}
	//某人的全部图片
	public function photos() {
		//隐私控制
		if($this->mid!=$this->uid){
			$relationship	=	getFollowState($this->uid,$this->mid);
			if($relationship=='eachfollow'||$relationship=='havefollow'){
				$map['privacy']	= array('in',array(1,2));
			}else{
				$map['privacy']	= 1;
			}
		}
		//获取配置参数
		$config = getConfig();
		//获取图片数据
		$order	=	'albumId DESC,`order` DESC';
		$map['userId']	=	$this->uid;

		$photos	=	D('Photo')->order($order)->where($map)->findPage($config['photo_raws']);
		$this->assign('photo_preview',$config['photo_preview']);
		$this->assign('type','mAll');
		$this->assign('photos',$photos);
		$this->display();
	}

	//某人的全部专辑
	public function albums() {
		//获取相册数据
		$map['userId']	=	$this->uid;
		$map['isDel']	=	0;

		$data	=	D('Album')->order("mTime DESC")->where($map)->findPage(getConfig('album_raws'));

		//获取微博相册
		$weibo  =  D('WeiboAttach','weibo')->getWeiboAlbum($this->uid);

		$this->assign('weibo',$weibo);
		$this->assign('data',$data);

		$this->display();
	}

	//显示一个图片专辑
	public function album() {

		$id		=	intval($_REQUEST['id']);

		//获取相册信息
		$albumDao = D('Album');
		$album	  =	$albumDao->where("id={$id}")->find();

		if(!$album){
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}

		//隐私控制
		if($this->mid!=$album['userId']){
			$relationship	=	getFollowState($this->mid,$this->uid);
			if($album['privacy']==3){
				$this->error('这个'.$this->appName.'，只有主人自己可见。');
			}elseif($album['privacy']==2 && $relationship=='unfollow'){
				$this->error('这个'.$this->appName.'，只有关注自己的人可见。');
			}elseif($album['privacy']==4){;
				$cookie_password	=	cookie('album_password_'.$album['id']);
				//如果密码不正确，则需要输入密码
				if($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)){
					$this->need_password($album);
					exit;
				}
			}
		}

		//获取图片数据
		//$raws	=	$this->setting['photo_raws'];
		//$order	=	$this->setting['album_default_order'];
		$order	=	'`order` DESC,id DESC';
		$map['albumId']	=	$id;
		$map['userId']	=	$this->uid;
		$map['isDel']	=	0;

		$config = getConfig();
		$photos	= D('Photo')->order($order)->where($map)->findPage($config['photo_raws']);
		$this->assign('photos',$photos);

		//获取标记数据
		//D('PhotoMarks')->where($map)->findAll();

		//点击率加1
		$albumDao->execute('UPDATE '.C('DB_PREFIX').$albumDao->tableName." SET readCount=readCount+1 WHERE id={$id} AND userId={$this->uid} LIMIT 1");

		$this->setTitle(getUserName($this->uid).'的'.$this->appName.'：'.$album['name']);

		$this->assign('photo_preview',$config['photo_preview']);
		$this->assign('album',$album);
		$this->display();
	}

	//显示一张图片
	public function photo() {
		$uid  = intval($_REQUEST['uid']);
		$aid  =	intval($_REQUEST['aid']);
		$id   = intval($_REQUEST['id']);
		$type =	t($_REQUEST['type']);	//图片来源类型，来自某相册，还是其它的

		//判断来源类型
		if(!empty($type) && $type!='mAll'){
			$this->error('错误的链接！');
		}
		$this->assign('type',$type);

		//获取所在相册信息
		$albumDao = D('Album');
		$album = $albumDao->find($aid);
		if(!$album){
			$this->assign('jumpUrl', U('photo/Index/index'));
			$this->error('专辑不存在或已被删除！');
		}

		//获取图片信息
		$photoDao = D('Photo');
		$photo	  =	$photoDao->where(" albumId={$aid} AND `id`={$id} AND userId={$uid} ")->find();
		$this->assign('photo',$photo);

		//验证图片信息是否正确
		if(!$photo){
			$this->assign('jumpUrl', U('photo/Index/album', array('uid'=>$this->uid,'id'=>$aid)));
			$this->error('图片不存在或已被删除！');
		}

		//隐私控制
		if($this->mid!=$album['userId']){
			$relationship	=	getFollowState($this->mid,$this->uid);
			if($album['privacy']==3){
				$this->error('这个'.$this->appName.'的图片，只有主人自己可见。');
			}elseif($album['privacy']==2 && $relationship=='unfollow'){
				$this->error('这个'.$this->appName.'的图片，只有主人关注的人可见。');
			}elseif($album['privacy']==4){;
				$cookie_password	=	cookie('album_password_'.$album['id']);
				//如果密码不正确，则需要输入密码
				if($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)){
					$this->need_password($album,$id);
					exit;
				}
			}
		}
		
		$this->assign('album',$album);
		//$order	=	$this->setting['album_default_order'];

		//获取所有图片数据
		$photos	=	$albumDao->getPhotos($this->uid,$aid,$type,$order,5);
		//$this->assign('photos',$photos);

		//获取上一页 下一页 和 预览图
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	intval($v['id']);
			}
			$photoCount	=	count($photoIds);

			//颠倒数组，取索引
			$pindex		=	array_flip($photoIds);

			//当前位置索引
			$now_index	=	$pindex[$id];

			//上一张
			$pre_index	=	$now_index-1;
			if( $now_index <= 0 )	{
				$pre_index	=	$photoCount-1;
			}
			$pre_photo	=	$photos[$pre_index];

			//下一张
			$next_index	=	$now_index+1;
			if( $now_index >= $photoCount-1 ) {
				$next_index	=	0;
			}
			$next_photo	=	$photos[$next_index];

			//预览图的位置索引
			$start_index	=	$now_index - 2;
			if($photoCount-$start_index<5){
				$start_index	=	($photoCount-5);
			}
			if($start_index<0){
				$start_index	=	0;
			}

			//取出预览图列表 最多5个
			$preview_photos	=	array_slice($photos,$start_index,5);
		}else{
			$this->error('图片列表数据错误！');
		}
		//点击率加1
		$photoDao->execute('UPDATE '.C('DB_PREFIX').$photoDao->tableName." SET readCount=readCount+1 WHERE id={$id} AND albumId={$aid} AND userId={$this->uid} LIMIT 1");

		$this->assign('photoCount',$photoCount);
		$this->assign('now',$now_index+1);
		$this->assign('pre',$pre_photo);
		$this->assign('next',$next_photo);
		$this->assign('previews',$preview_photos);

		unset($pindex);
		unset($photos);
		unset($album);
		unset($preview_photos);

		$this->setTitle(getUserName($this->uid).'的图片：'.$photo['name']);

		$this->display();
	}

	//输入相册密码
	public function need_password($album,$pid='') {

		//$aid	=	intval($_REQUEST['aid']);
		//$pid	=	intval($_REQUEST['pid']);
		//$uid	=	intval($_REQUEST['uid']);

		//获取相册信息
		/*$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();

		if(!$album){
			$this->error('专辑不存在或已被删除！');
		}*/

		$this->assign('username',getUserName($album['userId']));
		$this->assign('pid',$pid);
		$this->assign('album',$album);
		$this->display('need_password');
	}

	//验证相册密码
	public function check_password() {

		$aid	=	intval($_REQUEST['aid']);
		$uid	=	intval($_REQUEST['uid']);
		$password	=	t($_REQUEST['password']);
		$_REQUEST['pid'] && $pid = intval($_REQUEST['pid']);
		//获取相册信息
		$album	=	D('Album')->where(" id='$aid' AND userId='$uid' ")->find();
		$id = $album['id'];
		if($album['isDel'] != 0){
			$this->error('专辑不存在或已被删除！');
		}
		if($password == $album['privacy_data']){
		// 	//跳转到图片页面
		// 	$url	=	U('/Index/photo',array('uid'=>$album['userId'],'aid'=>$album['id']));
		// }else{
			//跳转到相册页面
			$url	=	U('/Index/album',array('uid'=>$album['userId'],'id'=>$album['id']));
		}
		//验证密码
		if( $password == $album['privacy_data'] ){

			//加密保存密码
			$cookie_password	=	md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid);
			//密码保存7天
			cookie( 'album_password_'.$album['id'] , $cookie_password , 3600*24*7 );
			$this->assign('jumpUrl',$url);
			$this->success('密码验证成功，将自动保存7天。马上跳转到'.$this->appName.'页面！');

		}else{
			$this->assign('jumpUrl',$url);
			$this->error('密码验证失败！');
		}
	}

	//幻灯播放
/*	public function autoplayer(){

		$id		=	intval($_REQUEST['id']);
		$aid	=	intval($_REQUEST['aid']);
		$type	=	t($_REQUEST['type']);	//图片来源类型，来自某相册，还是其它的

		//判断来源类型
		if(!empty($type) && !in_array($type,array('album','mAll','fAll'))){
			$this->error('错误的链接！');
		}

		//获取图片信息
		$photo	=	D('Photo')->where(" id={$id} AND albumId={$aid} AND userId={$this->uid} ")->find();
		$this->assign('photo',$photo);

		//验证图片信息是否正确
		if(!$photo){
			$this->error('图片不存在或已被删除！');
		}

		//获取所在相册信息
		$album	=	D('Album')->find($aid);
		$this->assign('album',$album);

		//隐私控制
		if($this->mid!=$photo['userId']){
			$relationship	=	getFollowState($this->mid,$this->uid);
			if($album['privacy']==3){
				$this->error('这张图片，只有主人自己可见。');
			}elseif($album['privacy']==2 && $relationship=='unfollow'){
				$this->error('这张图片，只有主人关注的人可见。');
			}elseif($album['privacy']==4){
				$cookie_password	=	Cookie::get('album_password_'.$aid);
				if($cookie_password	!= md5($album['privacy_data'].'_'.$aid.'_'.$this->uid)){
					$this->redirect('/Index/need_password/uid/'.$this->uid.'/aid/'.$aid.'/pid/'.$id);
				}
			}
		}

		$this->display("autoplayer");
	}*/
	//相册数据输出
/*	public function photo_xml() {

		$id		=	intval($_REQUEST['id']);
		$aid	=	intval($_REQUEST['aid']);
		$uid	=	intval($_REQUEST['uid']);
		$type	=	t($_REQUEST['type']);	//图片来源类型，来自某相册，还是其它的

		//判断来源类型
		if(!empty($type) && !in_array($type,array('album','mAll','fAll'))){
			echo "0";exit();
		}

		//获取所在相册信息
		$album	=	D('Album')->find($aid);
		$this->assign('album',$album);

		//验证隐私信息
		if($this->mid!=$album['userId']){
			$album_privacy	=	get_privacy_code($album['privacy']);
			$relationship	=	check_relationship($uid);

			if($album_privacy=='self' && $relationship!='self'){
				echo "0";exit();
			}else
			if($album_privacy=='friend' && $relationship=='stranger'){
				echo "0";exit();
			}else
			if($album_privacy=='password'){
				$cookie_password	=	Cookie::get('album_password_'.$aid);
				if($cookie_password	!= md5($album['privacy_data'].'_'.$aid.'_'.$uid)){
					echo "0";exit();
				}
			}
		}

		//$order	=	$this->setting['album_default_order'];

		//获取所有图片数据
		$photos	=	D('Album')->getPhotos($uid,$aid,$type,$order,5);
		$this->assign('photos',$photos);
		header('Content-type: application/xml');
		$this->display("flash_autoplayer");
	}

	//标记某人的图片
	public function marked() {

		//获取相片数据
		$map['userId']	=	$this->uid;
		$map['isDel']	=	0;
		$data	=	D('Photo')->order($order)->where($map)->findPage(getConfig('photo_raws'));
		$this->assign('data',$data);

		$this->display();
	}
*/

	public function weiboalbum(){

		//微博相册 ID = 0
		if( $id==0 && $this->uid > 0 ){
			$weibo  =  D('WeiboAttach','weibo')->getWeiboAlbum($this->uid);
		}
		//获取微博图片数据
		$config = getConfig();

		// $photos = D('WeiboAttach','weibo')->getUserAttachData($this->uid,1,$config['photo_raws']);
		$photos = D('WeiboAttach','weibo')->getUserAttachDataNew($this->uid,1,$config['photo_raws']);

		$this->assign('photos',$photos);
		$this->setTitle(getUserName($this->uid).'的微博相册');

		$this->assign('album',$weibo);
		$this->display();
	}


	//显示一张图片
	public function weibophoto() {

		$id		=	intval($_REQUEST['id']);
		$uid	=	intval($_REQUEST['uid']);

		//获取所有图片数据
		$photos = D('WeiboAttach','weibo')->getUserAttachData($this->uid,1);
		$photos = $photos['data'];

		//验证图片信息是否正确
		if(!$photos){
			$this->error('图片不存在或已被删除！');
		}

		//获取当前照片信息
		foreach($photos as $v){
			if($v['id']==$id){
				$photo	=	$v;
			}
		}

		//获取图片微博信息
		$weibo	=	D('Weibo','weibo')->getOne($photo['weibo_id']);
		$_REQUEST['p'] = $_GET['p'];
		$comment=	D('Comment','weibo')->getComment($photo['weibo_id']);
		$privacy=	D('UserPrivacy','home')->getPrivacy($this->mid,$photo['userId']);

		$this->assign('weibo',$weibo);
		$this->assign('comment',$comment);
		$this->assign('privacy',$privacy);
		$this->assign('photo',$photo);
		$this->assign('photos',$photos);

		//获取上一页 下一页 和 预览图
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	intval($v['id']);
			}

			$photoCount	=	count($photoIds);

			//颠倒数组，取索引
			$pindex		=	array_flip($photoIds);

			//当前位置索引
			$now_index	=	$pindex[$id];

			//上一张
			$pre_index	=	$now_index-1;
			if( $now_index <= 0 )	{
				$pre_index	=	$photoCount-1;
			}
			$pre_photo	=	$photos[$pre_index];

			//下一张
			$next_index	=	$now_index+1;
			if( $now_index >= $photoCount-1 ) {
				$next_index	=	0;
			}
			$next_photo	=	$photos[$next_index];

			//预览图的位置索引
			$start_index	=	$now_index - 2;
			if($photoCount-$start_index<5){
				$start_index	=	($photoCount-5);
			}
			if($start_index<0){
				$start_index	=	0;
			}

			//取出预览图列表 最多5个
			$preview_photos	=	array_slice($photos,$start_index,5);
		}else{
			$this->error('图片列表数据错误！');
		}
		//点击率加1
		//$photoDao->execute('UPDATE '.C('DB_PREFIX').$photoDao->tableName." SET readCount=readCount+1 WHERE id={$id} AND albumId={$aid} AND userId={$this->uid} LIMIT 1");

		$this->assign('photoCount',$photoCount);
		$this->assign('now',$now_index+1);
		$this->assign('pre',$pre_photo);
		$this->assign('next',$next_photo);
		$this->assign('previews',$preview_photos);

		unset($pindex,$photos,$album,$preview_photos);

		$this->setTitle(getUserName($this->uid).'的微博图片：'.$photo['name']);

		$this->display();
	}

	//将微博中的老照片全部导入到微博相册中.
	public function pickWeiboPhotos(){

	}
}
?>