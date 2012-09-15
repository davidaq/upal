<?php

class CacheService extends Service { //类定义开始
  private static $cacheLock = 5;
  private static $cacheCheckTime = 10;

  
	/**
     +----------------------------------------------------------
	 * 是否连接
     +----------------------------------------------------------
	 * @var string
	 * @access protected
     +----------------------------------------------------------
	 */
	protected $connected;
	
	/**
     +----------------------------------------------------------
	 * 操作句柄
     +----------------------------------------------------------
	 * @var string
	 * @access protected
     +----------------------------------------------------------
	 */
	public $handler;
	
	protected $prefix = '~@';
	
	/**
     +----------------------------------------------------------
	 * 缓存连接参数
     +----------------------------------------------------------
	 * @var integer
	 * @access protected
     +----------------------------------------------------------
	 */
	protected $options = array ();
	
	/**
     +----------------------------------------------------------
	 * 缓存类型
     +----------------------------------------------------------
	 * @var integer
	 * @access protected
     +----------------------------------------------------------
	 */
	protected $type;
	
	/**
     +----------------------------------------------------------
	 * 缓存过期时间
     +----------------------------------------------------------
	 * @var integer
	 * @access protected
     +----------------------------------------------------------
	 */
	protected $expire;
	
	public function __construct($options = array()) {
		$options ['options'] ['expire'] = isset ( $options ['options'] ['expire'] ) ? $options ['options'] ['expire'] : 86400;
		if (C ( 'MEMCACHED_ON' )) {
			//dump($options);
			$this->handler = Cache::getInstance ( "Memcache", $options ['options'] );
		} else {
			$this->handler = Cache::getInstance ( "File", $options ['options'] );
		}
	}
	
	//服务初始化
	public function init() {
	
	}

	//运行服务，系统服务自动运行
	public function run() {
	
	}
	public function __get($name) {
		return $this->handler->get ( $name );
	}
	
	public function setExpire($time) {
		$this->handler->setExpire ( $time );
	}
	
	public function __set($name, $value) {
		return $this->handler->set ( $name, $value );
	}
	
	public function __unset($name) {
		$this->handler->rm ( $name );
	}
	
	public function setOptions($name, $value) {
		$this->options [$name] = $value;
	}
	
	Public function setForumList($type,$userGroup,$fid,$limit,$data){
		$key = $this->creatForumList($type,$userGroup,$fid,$limit);
		$this->setExpire(300);
		return $this->handler->$key = serialize($data);
	}
	
	public function getForumList($type,$userGroup,$fid,$limit){
		$key = $this->creatForumList($type,$userGroup,$fid,$limit);
		$lockKey = $this->creatCacheLock($fid,'list');
		if( ($lock = $this->handler->$lockKey) && time() - $lock >= self::$cacheCheckTime){
		  unset($this->handler->$lockKey);
		  return false;
		}
		return unserialize($this->handler->$key);
	}
	
	public function cleanForumList($fid){
		$limit = array(20,40,60,80,100);
		$temp = $_GET['p'];
		$_GET['p'] = 1;
		$lockKey = $this->creatCacheLock($fid,"list");
		if($this->handler->$lockKey){
		  return false;
		}

		$this->setExpire(self::$cacheLock);
		$this->handler->$lockKey = time();
		
		foreach($limit as $value){
			$key = $this->creatForumList("default","0",$fid,$value);
			unset($this->handler->$key);
			$key = $this->creatForumList("default","1",$fid,$value);
			unset($this->handler->$key);
		}
		$_GET['p'] = $temp;
	}	
	public function cleanTopForumList(){
		$key = $this->creatForumList("top","0","0","none");
		unset($this->handler->$key);
		$key = $this->creatForumList("top","1","0","none");
		unset($this->handler->$key);
	}
	
	
	public function setUserSession($key,$data){
		return $this->handler->$key = serialize($data);
	}
	
	public function getUserSession($key){
		return unserialize($this->handler->$key);
	}
	
	public function cleanGetUserSession($key){
		unset($this->handler->$key);
	}
	
	
	public function setUserScore($uid,$data){
		$key = $this->creatUserScore($uid);
		return $this->handler->$key = serialize($data);
	}
	
	public function getUserScore($uid){
		$key = $this->creatUserScore($uid);
		return unserialize($this->handler->$key);
	}
	
	public function cleanUserScore($uid){
		$key = $this->creatUserScore($uid);
		unset($this->handler->$key);
	}
	
	
	
	public function setUserDist($uid,$data){
		$key = $this->creatUserDist($uid);
		return $this->handler->$key = serialize($data);
	}
	
	public function getUserDist($uid){
		$key = $this->creatUserDist($uid);
		return unserialize($this->handler->$key);
	}
	
	public function cleanDist($uid){
		$key = $this->creatUserDist($uid);
		unset($this->handler->$key);
	}
	
	public function setUserSetting($uid,$data){
		$key = $this->creatUserSetting($uid);
		return $this->handler->$key = serialize($data);
	}
	
	public function getUserSetting($uid){
		$key = $this->creatUserSetting($uid);
		return unserialize($this->handler->$key);
	}
	
	public function cleanSetting($uid){
		$key = $this->creatUserSetting($uid);
		unset($this->handler->$key);
	}
	
	
	public function setAttach($attachId,$data){
		$key = $this->creatAttachIndex($attachId);
		return $this->handler->$key = $data;
	}
	
	public function getAttach($attachId){
		$key = $this->creatAttachIndex($attachId);
		return $this->handler->$key;
	}
	
	public function cleanAttach($attachId){
		$key = $this->creatAttachIndex($attachId);
		unset($this->handler->$key);
	}
	
	
	public function setForumIcon($fid,$data){
		$key = $this->createForumIcon($fid);
		return $this->handler->$key = $data;
	}
	
	public function getForumIcon($fid){
		$key = $this->createForumIcon($fid);
		return $this->handler->$key;
	}
	
	public function cleanForumIcon($fid){
		$key = $this->createForumIcon($fid);
		unset($this->handler->$key);
	}
		
	
	
	public function setSite($key,$data){
		$key = $this->creatSiteData($key);
		return $this->handler->$key = $data;
	}
	
	public function getSite($key){
		$key = $this->creatSiteData($key);
		return $this->handler->$key;
	}
	
	public function cleanSite($key){
		$key = $this->creatSiteData($key);
		unset($this->handler->$key);
	}
	
	public function setTopNav($fid,$data){
		$key = $this->creatForumTopNav($fid);
		return $this->handler->$key = $data;
	}
	
	public function getTopNav($fid){
		$key = $this->creatForumTopNav($fid);
		return $this->handler->$key;
	}
	
	public function cleanTopNav($fid){
		$key = $this->creatForumTopNav($fid);
		unset($this->handler->$key);
	}
	
	
	
	public function setFilterWord($data){
		$key = $this->creatFilterWord();
		return $this->handler->$key = serialize($data);
	}
	
	
	public function getFilterWord(){
		$key = $this->creatFilterWord();
		return unserialize($this->handler->$key);
	}
	
	public function cleanFilterWord(){
		$key = $this->creatFilterWord();
		unset($this->handler->$key);
	}
	
	public function getTopicBorser($tid,$update = false) {
		$key = $this->creatTopicBorser ( $tid );
		$data = unserialize($this->handler->$key);
		if(!$data) return false;
		if($update){
			if(time()-$data['updateTime'] >=600 || $data['count'] % 50 == 0){
				//写入数据库
				$map['tid']  = $tid;
				$save['viewcount'] = $data['count'];
				D('Topic','forum')->where($map)->save($save);
			}
		}
		return $data['count'];
	}
	
	public function setTopicBorser($tid,$count = -1){
		$key = $this->creatTopicBorser ( $tid );
		if($count == -1){
			$count = $this->getTopicBorser($tid,true);
		}
		$data['count'] = $count + 1;
		$data['updateTime'] = time();
		return $this->handler->$key = serialize($data);
	}
	
	public function cleanTopicBorser($tid) {
		$key = $this->creatForumKey ( $tid );
		unset ( $this->handler->$key );
	}
	
	public function setForum($fid){
		$key = $this->creatForumKey ( $fid );
		return unserialize ( $this->handler->$key );
	}
	
	public function cleanForum($fid){
		$key = $this->creatForumKey ( $fid );
		unset ( $this->handler->$key );
	}
	
	
	

	public function getTopicData($tid) {
		$key = $this->createForumTopic ( $tid );
		return unserialize ( $this->handler->$key );
	}
	
	public function cleanTopicData($tid) {
		$key = $this->createForumTopic ( $tid );
		unset ( $this->handler->$key );
	}
	
	public function setTopicData($tid, $data) {
		$key = $this->createForumTopic ( $tid );
		$this->setExpire(86400);
		return $this->handler->$key = serialize ( $data );
	}
	
	
	public function getForumSetting($fid) {
		$key = $this->creatForumSettingKey ( $fid );
		return unserialize ( $this->handler->$key );
	}
	public function cleanForumSetting($fid) {
		$key = $this->creatForumSettingKey ( $fid );
		
		unset ( $this->handler->$key );
	}
	
	public function setForumSetting($fid, $data) {
		$key = $this->creatForumSettingKey ( $fid );
		return $this->handler->$key = serialize ( $data );
	}
	
	public function getDetail($pid, $limit) {
		$key = $this->creatPostKey ( $pid, $limit );
		$lockKey = $this->creatCacheLock($pid,"detail");
		if(($lock = $this->handler->$lockKey) && time() - $lock >=self::$cacheCheckTime){
		  unset($this->handler->$lockKey);
		  return false;
		}
		return unserialize ( $this->handler->$key );
	}
	
	public function setDetail($pid, $limit, $data) {
		$key = $this->creatPostKey ( $pid, $limit );
		$this->setExpire(172800);
		return $this->handler->$key = serialize ( $data );
	}
	
	public function cleanDetail($pid){
		$limit = array('20',"40","80","100");
		$lockKey = $this->creatCacheLock($pid,"detail");
		if($lock = $this->handler->$lockKey){
		  return false;
		}

		$this->setExpire(self::$cacheLock);
		$this->handler->$lockKey = time();

		foreach($limit as $value){
			$key = $this->creatPostKey ( $pid, $value );
			unset($this->$key);	
		}
	}
	
	public function getCategoryInfo($fid) {
		$key = $this->createCategoryInfo ( $fid );
		return unserialize ( $this->handler->$key );
	}
	
	public function setCategoryInfo($fid, $data) {
		$key = $this->createCategoryInfo ( $fid );
		return $this->handler->$key = serialize ( $data );
	}
	
	
	public function cleanCategoryInfo($fid) {
		$key = $this->createCategoryInfo ( $fid );
		unset ( $this->$key );
		return true;
	}
	
	
	public function getUserInfo($uid) {
		$key = $this->creatUserInfoKey ( $uid );
		return unserialize ( $this->handler->$key );
	}
	
	public function setUserInfo($uid, $data,$time = 0) {
		$key = $this->creatUserInfoKey ( $uid );
		if($time != 0){
			$this->setExpire($time);
		}
		return $this->handler->$key = serialize ( $data );
	}
	public function cleanUserInfo($uid) {
		$key = $this->creatUserInfoKey ( $uid );
		unset ( $this->$key );
		return true;
	}
	
	public function setForumRule($gid, $fid,$type, $data) {
		$key = $this->creatForumPopedomKey ( $gid, $fid ,$type);
		return $this->handler->$key = serialize ( $data );
	}
	public function getForumRule($gid,$fid,$type) {
		$key = $this->creatForumPopedomKey ( $gid, $fid,$type );
		return unserialize ( $this->handler->$key );
	}
	public function cleanForumRule($gid, $fid,$type) {
		$key = $this->creatForumPopedomKey ( $gid, $fid,$type );
		unset ( $this->$key );
	}
	
	public function setUserName($uid, $name, $language) {
		$key = $this->creatUidKey ( $uid, $language );
		$this->handler->$key = $name;
	}
	
	public function getUserName($uid, $language) {
		$key = $this->creatUidKey ( $uid, $language );
		return $this->handler->$key;
	}
	
	public function cleanUserName($uid,$language){
		$key = $this->creatUidKey($uid,$language);
		unset($this->$key);
	}
	
	public function getOptions($name) {
		return $this->options [$name];
	}
	
	public function clear(){
		return $this->handler->clear();
	}
	
	private function creatUidKey($uid, $language) {
		return "username_{$language}_{$uid}";
	}
	private function creatUserInfoKey($uid) {
		return "userinfo_{$uid}";
	}
	
	private function creatForumPopedomKey($gid, $fid,$type) {
		$result = "popedom_forum_{$type}_{$gid}_{$fid}";
		return $result;
	}
	
	private function creatForumSettingKey($fid) {
		return "forum_setting_{$fid}";
	}
	
	private function creatForumKey($fid) {
		return "forum_count_cahce_{$fid}";
	}
	
	private function creatPostKey($pid,$limit) {
		if(isset($_GET['p']) && $_GET['p'] != "last"){
			$page = intval($_GET['p']);
		}else{
			$page = 1;
		}
		return "detail_{$pid}_{$limit}_{$page}";
	}
	private function creatTopicBorser($pid) {
		return "topic_count_{$pid}";
	}
	private function creatFilterWord(){
		return "forum_filter_word";
	}
	
	private function creatForumTopNav($fid){
		return "forum_topnav_{$fid}";
	}
	
	private function creatAttachIndex($attachId){
		return "attach_{$attachId}";
	}
	
	private function creatSiteData($key){
		return "site_setting_{$key}";
	}
	
	private function creatUserSetting($uid){
		return "user_setting_{$uid}";
	}
	
	private function createForumIcon($fid){
		return "forum_Icon_{$fid}";
	}
	
	private function createForumTopic($tid){
		return "forum_topic_data_{$tid}";
	}
	
	private function createCategoryInfo($fid){
		return "forum_category_info_{$fid}";
	}
	
	private function creatUserDist($maskName){
		$maskName = crc32($maskName);
		return "user_dist_{$maskName}";
	}
	
	private function creatUserScore($maskName){
		return "user_score_{$maskName}";
	}

	private function creatCacheLock($id,$type){
	  return "cache_lock_{$type}_{$id}";
	}
	
	private function creatForumList($type,$userGroup,$fid,$limit){
		if(isset($_GET['p'])){
			$page = intval($_GET['p']);
		}else{
			$page = 1;
		}
		return "user_list_{$type}_{$userGroup}_{$fid}_{$limit}_{$page}";
	}
}
?>