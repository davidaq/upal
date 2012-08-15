<?php
/**
 * 排行榜
 */
class TopListAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '李俊红';
	protected $site    = 'http://www.thinksns.com';
	protected $info    = '排行榜插件';
	protected $pluginName = '排行榜插件';
    protected $tsVersion  = "2.5";
    protected $cacheTime = 600;
	protected $topType = array(
							'topTopic'=>'热门话题',
							'topTopicMonth'=>'月度话题榜',
							'topFans'=>'粉丝榜',
							'topMoods'=>'微博人气榜',
							'topWeiboMusic'=>'热门音乐',
							'topWeiboVideo'=>'热门视频',
							'topWeiboPic'=>'热门图片',
							'topVote'=>'热门投票',
							'topActivity'=>'热门活动',
							'topGroup'=>'热门群组',
							'topBlog'=>'热门日志',
							'topBlogMen'=>'博客人气榜',
							'topPhoto'=>'热门相册',
							'topPhotoMen'=>'相册人气榜',
							);

	public function getHooksInfo()
	{
		//给模版调用
		$this->apply("toprank","topRank");
		$this->apply("showdefinedrank","showDefinedRank");
	}

	//排行榜列表
	public function topRank(){
		$this->assign('topType', array_flip(array_map('strtolower', array_flip($this->topType))));
		$data = model('AddonData')->lget('top_list');
		$topname = array_flip($data['open']);
		//热门话题topTopic
		if (array_key_exists('topTopic',$topname)){
			if (($topic=S('TOP_TOPTOPIC_CACHE')) == FALSE){
				$topic = M('WeiboTopic')->order('count DESC')->limit(10)->select();
				S('TOP_TOPTOPIC_CACHE', $topic, $this->cacheTime);
			}
			if ($topic == NULL){
				$this->assign('topic',0);
			}else {			
				$this->assign('topic',1);
			}
			$this->assign('toptopic',$topic);
		}

		//月度话题榜topTopicMonth
		if (array_key_exists('topTopicMonth',$topname)){
			if (($topicauto=S('TOP_TOPICMONTH_CACHE')) == FALSE){
				$month = strtotime('last month');
				$topicauto = M('WeiboTopic')->where("ctime > $month")->order("count DESC")->limit(10)->select();
				//取热度 - 按照当前值与最大值的比例计算
				$hot_counts = max(getSubByKey($topicauto, 'count'));
				foreach($topicauto as $k=>$v){
					
					$topicauto[$k]['name']	=	htmlspecialchars($v['name']);
					$topicauto[$k]['rating'] = ceil(($v['count']/$hot_counts)*100);
				}
				S('TOP_TOPICMONTH_CACHE',$topicauto, $this->cacheTime);
			}
			if ($topicauto == NULL ){
				$this->assign('topicmonth',0);
			}else {
				$this->assign('topicmonth',1);
			}
			$this->assign('toptopicmonth',$topicauto);
		}
		//投票topVote
		if(array_key_exists('topVote',$topname)){
			if (($vote =S('TOP_VOTE_CACHE')) == FALSE){
				$vote = M('Vote')->order('vote_num DESC')->limit(10)->select();
				S('TOP_VOTE_CACHE',$vote, $this->cacheTime);
			}
			if ($vote == NULL){
				$this->assign('vote',0);
			}else {
				$this->assign('vote',1);
			}
			$this->assign('topvote',$vote);
		}
		//活动topActivity
		if (array_key_exists('topActivity',$topname)){
			if (($activity=S('TOP_ACTIVITY_CACHE')) == FALSE){
				$activity = M('Event')->order('joinCount DESC')->limit(10)->select();
				S('TOP_ACTIVITY_CACHE',$activity, $this->cacheTime);
			}
			if ($activity == NULL){
				$this->assign('activity',0);
			}else {
				$this->assign('activity',1);
			}
			$this->assign('topactivity',$activity);
		}
		//群组topGroup
		if (array_key_exists('topGroup',$topname)){
			if (($group=S('TOP_GROUP_CACHE')) == FALSE){
				$group = M('Group')->where(array('type'=>'open'))->order('membercount DESC')->limit(10)->select();
				S('TOP_GROUP_CACHE',$group, $this->cacheTime);
			}
			if ($group == NULL){
				$this->assign('group',0);
			}else {
				$this->assign('group',1);
			}
			$this->assign('topgroup',$group);
		}
		//日志topBlog
		if (array_key_exists('topBlog',$topname)){
			if (($blog=S('TOP_BLOG_CACHE')) == FALSE){
				$blog = M('Blog')->where(array('status'=>'1'))->order('readCount DESC')->limit(10)->select();
				S('TOP_BLOG_CACHE',$blog, $this->cacheTime);
			}
			if ($blog == NULL){
				$this->assign('blog',0);
			}else {
				$this->assign('blog',1);
			}
			$this->assign('topblog',$blog);
		}
		//发布日志最多的人topBlogMen
		if (array_key_exists('topBlogMen',$topname)){
			if (($blogmen=S('TOP_BLOGMEN_CACHE')) == FALSE){
				$blogmen = M()->query("SELECT count(*) as count ,uid FROM ts_blog GROUP BY uid ORDER BY count DESC LIMIT 10 ");
				S('TOP_BLOGMEN_CACHE',$blogmen, $this->cacheTime);
			}
			if ($blogmen == NULL){
				$this->assign('blogmen',0);
			}else {
				$this->assign('blogmen',1);
			}
			$this->assign('topblogmen',$blogmen);
		}
		//相册topPhoto
		if (array_key_exists('topPhoto',$topname)){
			if (($photo=S('TOP_PHOTO_CACHE')) == FALSE){
				$photo = M('PhotoAlbum')->order('readCount DESC')->limit(10)->select();
				S('TOP_PHOTO_CACHE',$photo, $this->cacheTime);
			}
			if ($photo == NULL){
				$this->assign('photo',0);
			}else {
				$this->assign('photo',1);
			}
			$this->assign('topphoto',$photo);
		}
		//发布照片最多的人topPhotoMen
		if (array_key_exists('topPhotoMen',$topname)){
			if (($photomen=S('TOP_PHOTOMEN_CACHE')) == false){
				$photomen = M()->query("SELECT count(*) as count ,userId FROM ts_photo GROUP BY userId ORDER BY count DESC LIMIT 10");
				S('TOP_PHOTOMEN_CACHE',$photomen, $this->cacheTime);
			}
			if ($photomen == NULL){
				$this->assign('photomen',0);
			}else {
				$this->assign('photomen',1);
			}
			$this->assign('topphotomen',$photomen);
		}
		//微博上传文档最多的人topWeiboDoc type=5
		if (array_key_exists('topWeiboDoc',$topname)){
			if (($weibodoc=S('TOP_WEIBODOC_CACHE')) == FALSE){
				$weibodoc = M()->query(" SELECT count(*) as count ,uid FROM ts_weibo WHERE ( `isdel` = 0) AND (`type` = 5 ) GROUP BY uid ORDER BY count DESC LIMIT 10");
				S('TOP_WEIBODOC_CACHE',$weibodoc, $this->cacheTime);
			}
			if ($weibodoc){
				$this->assign('weibodoc',0);
			}else {
				$this->assign('weibodoc',1);
			}
			$this->assign('topweibodoc',$weibodoc);
		}
		//微博上传音乐最多的人topWeiboMusic type=4
		if (array_key_exists('topWeiboMusic',$topname)){
			if (($weibomusic=S('TOP_WEIBOMUSIC_CACHE')) == FALSE){
				$weibomusic = M()->query(" SELECT count(*) as count ,uid FROM ts_weibo WHERE ( `isdel` = 0) AND (`type` = 4 ) GROUP BY uid ORDER BY count DESC LIMIT 10");
				S('TOP_WEIBOMUSIC_CACHE',$weibomusic, $this->cacheTime);
			}
			if ($weibomusic == NULL){
				$this->assign('weibomusic',0);
			}else {
				$this->assign('weibomusic',1);
			}
			$this->assign('topweibomusic',$weibomusic);
		}
		//微博上传视频最多的人topWeiboVideo type=3
		if (array_key_exists('topWeiboVideo',$topname)){
			if (($weibovideo=S('TOP_WEIBOVIDEO_CACHE')) == FALSE){
				$weibovideo = M()->query(" SELECT count(*) as count ,uid FROM ts_weibo WHERE ( `isdel` = 0) AND (`type` = 3 ) GROUP BY uid ORDER BY count DESC LIMIT 10");
				S('TOP_WEIBOVIDEO_CACHE',$weibovideo, $this->cacheTime);
			}
			if ($weibovideo == NULL){
				$this->assign('weibovideo',0);
			}else{
				$this->assign('weibovideo',1);
			}
			$this->assign('topweibovideo',$weibovideo);
		}
		//微博上传图片最多的人topWeiboPic type=1
		if (array_key_exists('topWeiboPic',$topname)){
			if (($weibopic=S('TOP_WEIBOPIC_CACHE')) == FALSE){
				$weibopic = M()->query(" SELECT count(*) as count ,uid FROM ts_weibo WHERE ( `isdel` = 0) AND (`type` = 1 ) GROUP BY uid ORDER BY count DESC LIMIT 10");
				S('TOP_WEIBOPIC_CACHE',$weibopic, $this->cacheTime);
			}
			if ($weibopic == NULL){
				$this->assign('weibopic',0);
			}else {
				$this->assign('weibopic',1);
			}
			$this->assign('topweibopic',$weibopic);
		}
		
		//微博粉丝榜topFans
		if (array_key_exists('topFans',$topname)){
			if (($topfans=S('TOP_TOPFANS_CACHE')) == FALSE){
				$topfans = M()->query("SELECT `fid` AS `uid`, count(`uid`) AS `count` FROM ts_weibo_follow WHERE `type` = 0 AND `fid` NOT IN ( 27788,10000,10001,10002,10003,10004,10006,10007,10008,10045,10046,10054,10212,10336,10034,10315,10381 ) GROUP BY `fid` ORDER BY `count` DESC LIMIT 10");
				S('TOP_TOPFANS_CACHE',$topfans, $this->cacheTime);
			}
			if ($topfans == NULL){
				$this->assign('fans',0);
			}else {
				$this->assign('fans',1);
			}
			$this->assign('topfans',$topfans);
		}
		//微博人气榜topMoods
		if (array_key_exists('topMoods',$topname)){
			if (($toppeople=S('TOP_TOPMOODS_CACHE')) == FALSE){
				$toppeople = M()->query("SELECT uid,sum(comment*5+transpond*5) as rating FROM `ts_weibo` WHERE ( `ctime` > 1329206157 ) GROUP BY uid ORDER BY rating desc LIMIT 10");
				S('TOP_TOPMOODS_CACHE',$toppeople, $this->cacheTime);
			}
			if ($toppeople == NULL){
				$this->assign('moods',0);
			}else {
				$this->assign('moods',1);
			}
			$this->assign('topmoods',$toppeople);
		}
		$this->display('toprank');
	}
	/* 后台管理 */
    public function adminMenu()
	{
        return array('config' => '系统排行榜','definedrank' =>'自定义排行榜','adddefine'=>'添加自定义排行榜');
    }
	/* 插件后台配置项 -系统排行榜*/
	public function config()
	{
		$config = model('AddonData')->lget('top_list');
		$this->assign('topType',$this->topType);
		$this->assign('config',$config);
		$this->display('config');
	}
	/* 插件后台配置项 -用户自定义排行榜*/
	public function definedrank(){
		$list = M('Toprank')->select();
		$this->assign('list',$list);
		$this->display('definedrank');
	}
	//添加自定义排行榜
	public function addDefine() {
		$this->display('adddefine');
	}
	public function doAddRank() {
		$data['name'] = $_POST['name'];
		$data['title'] = serialize($_POST['title']);
		$content = $_POST['content'];
		$list = array();
		foreach ($content['key'] as $k => $v){
			$list[] = array('key'=>$v,'value'=>$content['value'][$k]);
		}
		$data['content'] = serialize($list);
		$data['cTime'] = time();
		$data['status'] = $_POST['status'];
		$addtop = M('Toprank')->add($data);
		if ($addtop !== NULL){
			$this->success('添加成功');
		}else {
			$this->error('添加失败');
		}
		exit();
	}
	//编辑自定义排行榜
	public function editDefine(){
		$id = intval($_GET['id']);
		$date = M('Toprank')->where(array('id'=>$id))->find();
		$this->assign('date',$date);
		$this->display('eidtdefine');
		exit();
	}
	//开启自定义排行榜多选
	public function upStatus(){
		$top['id'] = array( 'in',explode(',',$_REQUEST['id']));
		$date['status'] = 1;
		$result = M('Toprank')->where($top)->save($date);
		if( $result !== '' ){
        	if ( !strpos($_REQUEST['id'],",") ){
            	echo 2;exit();            //说明只是开启一个
        	}else{
            	echo 1;exit();            //开启多个
        	}
        }else{
        	echo -1;exit();               //开启失败
        }
	}
	//关闭自定义排行榜多选
	public function stopStatus(){
		$top['id'] = array( 'in',explode(',',$_REQUEST['id']));
		$date['status'] = 0;
		$result = M('Toprank')->where($top)->save($date);
		if($result !== ''){
        	if ( !strpos($_REQUEST['id'],",") ){
            	echo 2;exit();            //说明只是关闭一个
        	}else{
            	echo 1;exit();           //关闭多个
        	}
        }else{
        	echo -1;exit();               //关闭失败
        }
	}
	//更新自定义排行榜
	public function updateRank(){
		$id = intval($_POST['id']);
		$data['name'] = $_POST['name'];
		$data['title'] = serialize($_POST['title']);
		$content = $_POST['content'];
		$list = array();
		foreach ($content['key'] as $k => $v){
			$list[] = array('key'=>$v,'value'=>$content['value'][$k]);
		}
		$data['content'] = serialize($list);
		$data['cTime'] = time();
		$data['status'] = $_POST['status'];
		$uptop = M('Toprank')->where(array('id'=>$id))->limit(1)->save($data);
		if ($uptop !== NULL){
			$this->success('更新成功');
		}else {
			$this->error('更新失败');
		}
	}
	//删除自定义排行榜
	public function doDelete() {
		$id = intval($_POST['id']);
		$deldate = M('Toprank')->where(array('id'=>$id))->limit(1)->delete();
		if ($deldate !== NULL){
			echo 1;
		}else {
			echo 0;
		}
	}
	//显示-自定义排行榜
	public function showDefinedRank(){
		if (($toppeople=S('TOP_DEFINED_CACHE')) == FALSE){
			$list = M('Toprank')->where(array('status'=>'1'))->select();
			S('TOP_DEFINED_CACHE',$list, $this->cacheTime);
		}
		$this->assign('list',$list);
		$this->display('showdefinedrank');
	}
	//保存配置-系统排行榜
	public function saveConfig(){
	    if(empty($_POST)) return;
	    if(empty($_POST['open'])) $_POST['open'] = array();
	    $data = $_POST;
	    $res = model('AddonData')->lput('top_list', $data);
	    if ($res) {
	        $this->assign('jumpUrl', Addons::adminPage('config'));
	        $this->success();
	    } else {
	        $this->error();
	    }
	    exit;
	}
	public function install(){
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE `{$db_prefix}toprank` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL COMMENT '中文名称',
			  `title` varchar(255) NOT NULL COMMENT '排行榜名称',
			  `content` text COMMENT '排行榜内容',
			  `cTime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
			  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='自定义排行榜';";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
	public function uninstall(){
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}toprank`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
	
}