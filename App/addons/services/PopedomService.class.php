<?php
class PopedomService extends Service {
	//所有设置的值
	private $popedom;
	private $fid;
	private $userInfo = array();
	private $mid;
	private $status = false;
	private static $cache;
	private $banzhu = false;
	private static $forumSetting;
	private static $checkTime;
	
	public function getStatus() {
	  	return $this->status;
	}
	
	public function __construct($data) {
		if (self::$cache == null) {
		  self::$cache = service("Cache", $data);
		}
		$this->mid = intval($_SESSION['mid']);
		if(!isset($this->userInfo[$this->mid])){
		    $userGroupInfo = $this->getUserInfo($_SESSION ['mid'], self::$cache);
		    $this->setUserInfo($userGroupInfo);
	 	} 
	 	if($data[2] != true) {
	 		$this->setFid($data[0], $data[1]); 
	 		$this->run();
	 	}
	 }
	 
	 public function reload($data) {
	 	if (self::$cache == null) {
	 		self::$cache = service ( "Cache" );
	 	}
	 	$this->mid = intval ( $_SESSION ['mid'] );
		if(!isset($userInfo[$mid])){
	 	  $userGroupInfo = $this->getUserInfo ( $this->mid,self::$cache); 
	 	  $this->setUserInfo ( $userGroupInfo );	   
		}
	
		$this->setFid ( $data [0], $data [1] );
		$this->run ();
	}
	
	public function getCheckTime($fid) {
		return self::$checkTime [$fid];
	}
	
	public function setFid($fid, $setting) {
		$this->fid = $fid;
		self::$forumSetting [$fid] = $setting;
		$isAdmin = model("UserGroup")->isAdmin($this->mid);
		$this->init();
		//时间判断
		$userGroup = $this->userInfo[$this->mid]['userGroup'];

		if (!$userGroup){
		  $this->setUserInfo ( $this->getUserInfo ( $this->mid, self::$cache ) );		  
		}
		$userGroup = $this->userInfo[$this->mid] ['userGroup'];
		$forumTime = unserialize ( self::$forumSetting [$fid] ['timeSetting'] );
		
		$timeSettingTime = self::$forumSetting[$fid]['site']['timeSetting'];
		
		$forumTime = $forumTime ? $forumTime : $timeSettingTime;
		if ($forumTime) {
			$userGid = array ();
			foreach ( $userGroup as $value ) {
				$userGid = array_merge ( $userGid, getSubByKey ( $value, 'gid' ) );
			}
			$now = getdate ();
			$result = array ();
			$checked = true;
			//检查当天是否需要进行判断
			$dateMap ['data'] = $now ['year'] . '-' . $now ['mon'] . '-' . $now ['mday'];
			$date = D ( 'ForumDate' )->where ( $dateMap )->find ();
			if ($date != false && $date ['login'] == 1) {
				foreach ( $forumTime as $value ) {
					$start = mktime ( intval ( $value ['s'] [0] ), intval ( $value ['s'] [1] ) );
					$end = mktime ( intval ( $value ['e'] [0] ), intval ( $value ['e'] [1] ) );
					$check = ($now [0] > $start && $now [0] < $end);
					if ($check) {
						$extGid = false;
						if (! isset ( $value ['userGroup'] )) {
							$extGid = true;
						} else {
							$extGid = array_intersect ( $userGid, $value ['userGroup'] ['id'] );
							if (! empty ( $extGid )) {
								$extGid = true;
							} else {
								$extGid = false;
							}
						}
						if (in_array ( $this->mid, $setting ['manager'] ) || $isAdmin) {
							$extGid = false;
						}
						if ($extGid) {
							$checked = false;
						}
					}
				
				}
				self::$checkTime [$fid] = $checked;
				return;
			}
		}
		self::$checkTime [$fid] = true;
	}
	
	public function coverPopedom($newPopedom) {
		if (empty ( $newPopedom ) || $newPopedom === false)
			return false;
		$userGroup = $this->userInfo[$this->mid] ['userGroup'];
		if (!$userGroup)
			$this->setUserInfo ( $this->getUserInfo ( $this->mid, self::$cache ) );
		$userGroup = $this->userInfo[$this->mid] ['userGroup'];
		
		foreach ( $userGroup as $value ) {
			foreach ( $value as $gid ) {
				$userGid [] = $gid ['gid'];
			}
		}
		if (empty ( $userGid ))
			return false;
		$rule = array ();
		$manager = self::$forumSetting [$this->fid] ['manager'];
		
		foreach ( $userGroup as $key => $value ) {
			foreach ( $value as $gid ) {
				if ($gid ['gid'] == 2 && !in_array ( $this->mid, $manager )) continue;
				$temp = $newPopedom [$gid ['gid']];
				if (empty ( $keyed ))
					$keyed = array_keys ( $temp );
				$rule [$gid ['gid']] = implode ( "", $temp );
			}
		}
		$temp = array_reduce ( $rule, array ($this, "reduce" ), false );
		$count = count ( $keyed );
		for($i = 0; $i < $count; $i ++) {
			$this->popedom [$keyed [$i]] = $temp {$i};
		}
	}
	
	public function setUid($uid) {
		$this->mid = $uid;
		$userInfo = $this->getUserInfo ( $uid, self::$cache );
		$this->setUserInfo (  $userInfo,$uid ); 
	} 
	
	public function setUserInfo($userInfo,$uid = 0) { 
	  $uid = $uid ? $uid:$this->mid;

	   $this->userInfo[$uid] = $userInfo;

	}
	
	public function check($action, $checkTime = true) {
		$fid = $this->fid;
//		if ($checkTime && ! self::$checkTime [$this->fid]) {
//			return false;
//		} else {
			$result = isset ( $this->popedom [$action] ) ? (intval ( $this->popedom [$action] ) == 1 ? true : false) : false;
			return $result;
//		}
	}
	
	public function getEditSpecial() {
		return $this->popedom ["allow_edit_special_thread"];
	}
	
	public function getPostSpecial() {
		return $this->popedom ["allow_edit_special_thread"];
	}
	
	public function getSignSpecial() {
		return $this->popedom ["allow_edit_special_sign"];
	}
	
	public function getPopedom($type) {
		return $this->popedom;
	}
	
	//服务初始化
	public function init() {
		$userGroup = $this->userInfo[$this->mid] ['userGroup'];
		if(!$userGroup){
		  $this->setUserInfo($this->getUserInfo($this->mid,self::$cache));
		}
		$userGroup = $this->userInfo[$this->mid]['userGroup'];
		$adminModel = D('AdminRule', 'forum');
		$userModel = D('UserRule', 'forum');
		$manager = self::$forumSetting[$this->fid]['manager'];
		foreach($userGroup as $value) {
			foreach($value as $gid) {
				$userGid[] = $gid['gid'];
			}
		}
		if(empty($userGid)) {
			return false;
		}
		$admin = array ();
		$user = array ();
		$rule = array ();
		$keyed = array ();
		$special_edit = array ();
		$special_post = array ();
		$special_sign = array ();
		foreach ( $userGroup as $key => $value ) {
			foreach ( $value as $gid ) {
				$admin = false;
				if ($gid ['gid'] == 2) {
					if (in_array ( $this->mid, $manager )) {
						$admin = $adminModel->getPopedomOneGroup ( $this->fid, $gid ['gid'] );
						$user = $userModel->getPopedomOneGroup ( $this->fid, $gid ['gid'] );
					} else {
						$user = $userModel->getPopedomOneGroup ( $this->fid, 1 );
					}
				} else {
					//查看管理权限
					$admin = $adminModel->getPopedomOneGroup ( $this->fid, $gid ['gid'] );
					$user = $userModel->getPopedomOneGroup ( $this->fid, $gid ['gid'] );
				}
				if (! $admin) {
					$adminField = $adminModel->getDbFields ();
					unset ( $adminField ['_autoinc'] );
					$admin = array_flip ( $adminField );
					foreach ( $admin as &$value ) {
						$value = 0;
					}
				}
				
				//去重以及不权限并集处理
				if ($user) {
					unset ( $user ['usergid'] );
					unset ( $user ['fid'] );
					foreach ( $user ['allow_edit_special_thread'] as $e ) {
						$special_edit [$e ['id']] = $e;
					}
					foreach ( $user ['allow_post_special_thread'] as $p ) {
						$special_post [$p ['id']] = $p;
					}
					unset ( $user ['allow_edit_special_thread'] );
					unset ( $user ['allow_post_special_thread'] );
				}
				if ($admin) {
					unset ( $admin ['admingid'] );
					unset ( $admin ['fid'] );
					
					foreach ( $admin ['allow_edit_special_thread'] as $e ) {
						$special_edit [$e ['id']] = $e;
					}
					foreach ( $admin ['allow_edit_special_sign'] as $s ) {
						$special_sign [$s ['id']] = $s;
					}
					unset ( $admin ['allow_edit_special_thread'] );
					unset ( $admin ['allow_edit_special_sign'] );
				}
				$temp = array_merge ( ( array ) $admin, ( array ) $user );
				if (empty ( $keyed ))
					$keyed = array_keys ( $temp );
				$rule [$gid ['gid']] = implode ( "", $temp );
			}
		}
		
		array_filter($special_edit);
		array_filter($special_sign);
		array_filter($special_post);
		$temp = array_reduce ( $rule, array ($this, "reduce" ), false );
		$count = count ( $keyed );
		for($i = 0; $i < $count; $i ++) {
			$this->popedom [$keyed [$i]] = $temp {$i};
		}
		$this->popedom ["allow_edit_special_thread"] = $special_edit;
		$this->popedom ["allow_edit_special_sign"] = $special_sign;
		$this->popedom ["allow_post_special_thread"] = $special_post;
	}
	
	public function checkStanding($action = "user") {
		if ($action == "admin" && (isset ( $this->userInfo[$this->mid] ['userGroup'] [1] ) || isset ( $this->userInfo[$this->mid] ['userGroup'] [2] )))
			return true;
		if ($action == "admin" && isset ( $this->userInfo[$this->mid] ['userGroup'] [0] ))
			return false;
		if ($action == "user" && isset ( $this->userInfo[$this->mid] ['userGroup'] [0] ))
			return true;
		return false;
	}
	
	private function getUserInfo($uid, $cacheObjcet) {
		if($uid == -1){
			$guess = array();
			$guess['userGroup'][0][0] = array ("gid"=>29,"name"=>"游客","type"=>0);
			return $guess;
		}
	  	if(isset($this->userInfo[$uid])){
	      	return $this->userInfo[$uid];
	    }
		$sql = 'SELECT u.* FROM ' . C ( 'DB_PREFIX' ) . 'user as u 
					where u.uid = ' . $uid;
		$userInfo = M ()->query ( $sql );
		$userInfo = $userInfo ? $userInfo [0] : false;
		if($userInfo) {
			$userGroup = model('UserGroup')->getUserGroups($uid, true);
			$userInfo ['userGroup'] = group ( $userGroup, "type" );
		}
		
		if(!$userInfo || !isset($userInfo['userGroup']) || empty($userInfo['userGroup'])){
			$userInfo['userGroup'][0][0] = array ("gid"=>1,"name"=>"普通用户","type"=>0);
		}
		
		//添加版主用户组
		$allManager = M('forum')->field('forum_manager')->findAll();
		foreach($allManager as $value) {
			if(empty($value['forum_manager'])) {
				continue;
			}
			$manager = explode(',', $value['forum_manager']);
			if(in_array($this->mid, $manager)) {
				array_push($userInfo['userGroup'][0], array ("gid"=>2,"name"=>"版主","type"=>1));
				break;
			}
		}
		
		return $userInfo;
	}
	
	//运行服务，系统服务自动运行
	public function run() {
	
	}
	
	/* 后台管理相关方法 */
	
	//启动服务，未编码
	public function _start() {
		return true;
	}
	
	//停止服务，未编码
	public function _stop() {
		return true;
	}
	
	//卸载服务，未编码
	public function _install() {
		return true;
	}
	
	//卸载服务，未编码
	public function _uninstall() {
		return true;
	}
	
	public function reduce($a, $b) {
		$result = ( string ) $a | ( string ) $b;
		return "$result";
	}

}
?>