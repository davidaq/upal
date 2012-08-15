<?php
class UserModel extends Model {
	protected	$tableName	=	'user';
	public static $nameHash = array();
    var $uid;
	/**
	 * 根据查询条件查询用户
	 *
	 * @param array|string $map          查询条件
	 * @param string       $field		   字段
	 * @param int 		   $limit		   限制条数
	 * @param string 	   $order		   结果排序
	 * @param boolean 	   $is_find_page 是否分页
	 * @return array
	 */
	public function getUserByMap($map = array(), $field = '*', $limit = '', $order = '', $is_find_page = true) {
		if ($is_find_page) {
			return $this->where($map)->field($field)->order($order)->findPage($limit);
		}else {
			return $this->where($map)->field($field)->order($order)->limit($limit)->findAll();
		}
	}
	/**
	 * 获取用户列表
	 *
	 * @param array|string $map             查询条件
	 * @param boolean	   $show_dept		是否显示部门信息
	 * @param boolean 	   $show_user_group 是否显示用户组
	 * @param string       $field		           字段
	 * @param string 	   $order		           结果排序
	 * @param int 		   $limit		 	限制条数
	 * @return array
	 */
    public function getUserList($map = '', $show_dept = false, $show_user_group = false, $field = '*', $order = 'uid ASC', $limit = 30) {
    	$res  = $this->where($map)->field($field)->order($order)->findPage($limit);
    	$uids = getSubByKey($res['data'], 'uid');
    	//部门信息
    	if ($show_dept) {
    	}
    	//用户组
    	if ($show_user_group) {
    		$temp_user_group = model('UserGroup')->getUserGroupByUid($uids);
    		//转换成array($uid => $user_group)的格式
    		$user_group = array();
    		foreach($temp_user_group as $v) {
    			$user_group[$v['uid']][] = $v;
    		}
    		unset($temp_user_group);
    		//将用户组信息添加至结果集
    		foreach($res['data'] as $k => $v) {
				$res['data'][$k]['user_group'] = isset($user_group[$v['uid']]) ? $user_group[$v['uid']] : array();
    		}
    	}
    	return $res;
    }
    /**
     * 删除用户
     *
     * @param array|string $uids
     * @return boolean
     */
    public function deleteUser($uids) {
		set_time_limit(0);
    	//防止误删
    	$uids = is_array($uids) ? $uids : explode(',', $uids);
    	foreach($uids as $k => $v) {
    		if (!is_numeric($v) || $v == $GLOBALS['ts']['user']['uid']) {
    			unset($uids[$k]);
    		}
    	}
    	if ( empty($uids) ) return false;
    	$map['uid'] = array('in', $uids);
    	$map['admin_level'] = 0;
    	//user
    	$res = M('user')->where($map)->delete();
    	unset($map['admin_level']);
		$res = true;
		if ($res) {
			//删除用户资料相关信息
			service('Comment')->where($map)->delete();
			M('credit_user')->where($map)->delete();
			M('feed')->where($map)->delete();
			M('invitecode')->where($map)->delete();
			M('invite_record')->where(array('uid'=>array('IN',$uids),'fid'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('login')->where($map)->delete();
			M('login_record')->where($map)->delete();
			model('Message')->where(array('from_uid'=>array('IN',$uids),'to_uid'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('notify')->where(array('from'=>array('IN',$uids),'receive'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('ucenter_user_link')->where($map)->delete();
			M('user_app')->where($map)->delete();
			M('user_blacklist')->where($map)->delete();
			M('user_count')->where($map)->delete();
			M('user_department')->where($map)->delete();
			M('user_group_link')->where($map)->delete();
			M('user_medal')->where($map)->delete();
			M('user_online')->where($map)->delete();
			M('user_privacy')->where($map)->delete();
			M('user_profile')->where($map)->delete();
			M('user_tag')->where($map)->delete();
			M('user_verified')->where($map)->delete();
			M('user_visited')->where($map)->delete();
			//删除用户应用信息
			M('admin_log')->where($map)->delete();
			M('blog')->where($map)->delete();
			M('blog_category')->where($map)->delete();
			M('blog_mention')->where($map)->delete();
			M('blog_outline')->where($map)->delete();
			M('blog_subscribe')->where($map)->delete();
			M('credit_user')->where($map)->delete();
			M('denounce')->where($map)->delete();
			M('event')->where($map)->delete();
			M('event_photo')->where($map)->delete();
			M('event_user')->where($map)->delete();
			M('friend')->where(array('uid'=>array('IN',$uids),'friend_uid'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('gift_user')->where(array('fromUserId'=>array('IN',$uids),'toUserId'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('group_invite_verify')->where($map)->delete();
			M('group_member')->where($map)->delete();
			M('group_photo')->where(array('userId'=>array('IN',$uids)))->delete();
			M('group_post')->where($map)->delete();
			M('group_topic')->where($map)->delete();
			M('group_user_count')->where($map)->delete();
			M('group_weibo')->where($map)->delete();
			M('group_weibo_atme')->where($map)->delete();
			M('group_weibo_comment')->where($map)->delete();
			M('group_weibo_favorite')->where($map)->delete();
			M('login')->where($map)->delete();
			M('login_record')->where($map)->delete();
			M('photo')->where(array('userId'=>array('IN',$uids)))->delete();
			M('photo_album')->where(array('userId'=>array('IN',$uids)))->delete();
			M('photo_index')->where(array('userId'=>array('IN',$uids)))->delete();
			M('poster')->where($map)->delete();
			M('space')->where($map)->delete();
			M('vote')->where($map)->delete();
			M('vote_user')->where($map)->delete();
			D('Weibo', 'weibo')->where($map)->delete();
			D('Atme', 'weibo')->where($map)->delete();
			D('Comment', 'weibo')->where($map)->delete();
			D('Favorite', 'weibo')->where($map)->delete();
			D('Follow', 'weibo')->where(array('uid'=>array('IN',$uids),'fid'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			D('FollowGroup', 'weibo')->where(array('uid'=>array('IN',$uids),'follow_id'=>array('IN',$uids),'_logic'=>'OR'))->delete();
			M('follow_group_link')->where($map)->delete();
			D('Star', 'weibo')->where($map)->delete();
			//删除用户附件
			$all_attach	=	M('Attach')->where(array('userId'=>array('IN',$uids)))->findAll();
			foreach($all_attach as $v){
				unlink(UPLOAD_PATH.'/'.$v['savepath'].$v['savename']);
			}
			$result = M('attach')->where(array('userId'=>array('IN',$uids)))->delete();
			if($result){
				echo 'delete succesful!';
			}
		}
    	return $res;
    }
    /**
     * 更新操作
     *
     * @param string $type 操作
     * @return boolean
     */
	function upDate($type){
	    return $this->$type();
	}
	/**
	 * 更新基本信息
	 *
	 * @return array
	 */
	private function upbase( ){
		$nickname = t($_POST['nickname']);
		if(!$nickname){
			$data['message'] = L('nickname_nonull');
			$data['boolen']  = 0;
			return $data;
		}
	
		if( !isLegalUsername($nickname) ){
			$data['message'] = L('nickname_format_error');
			$data['boolen']  = 0;
			return $data;
		}
		if( checkKeyWord($nickname) ){
			$data['message'] = '昵称含有敏感词';
			$data['boolen']  = 0;
			return $data;
		}
		if( M('user')->where("uname='{$nickname}' AND uid!={$this->uid}")->find() ){
			$data['message'] = L('nickname_used');
			$data['boolen']  = 0;
			return $data;
		}
	    $data['province'] = intval( $_POST['area_province'] );
	    $data['uname']    = $nickname;
	    $data['city']     = intval( $_POST['area_city'] );
	    $data['location'] =  getLocation($data['province'],$data['city']);
	    $data['sex']      = intval( $_POST['sex'] );
	    M('user')->where("uid={$this->uid}")->data($data)->save();
	    //修改登录用户缓存信息--名称
	    $userLoginInfo = S('S_userInfo_'.$this->uid);
	    if(!empty($userLoginInfo)) {
	    	$userLoginInfo['uname'] = $data['uname'];
	    	S('S_userInfo_'.$this->uid, $userLoginInfo);
	    }
	    $_SESSION['userInfo'] = D('User', 'home')->find($this->uid);
	   	$data['message'] = L('update_done');
		$data['boolen']  = 1;
		return $data;
	}
	/**
	 * 获取用户基本信息字段
	 *
	 * @param string $module 字段类别,contact联系的字段、inro基本介绍的字段
	 * @return array
	 */
	protected function data_field($module = '',$space=false){
       if($space){
	        $list = $this->table(C('DB_PREFIX').'user_set')->where("status=1 and showspace=1")->findAll();
	    }else{
	        $list = $this->table(C('DB_PREFIX').'user_set')->where("status=1")->findAll();
	    }
        foreach ($list as $value){
            $data[$value['module']][$value['fieldkey']] = $value['fieldname'];
        }
	    return ($module)?$data[$module]:$data;
	}
	/**
	 * 根据标示符(uid或uname或email或domain)获取用户信息
	 *
	 * 首先检查缓存(缓存ID: user_用户uid / user_用户uname), 然后查询数据库(并设置缓存).
	 *
	 * @param string|int $identifier      标示符内容
	 * @param string     $identifier_type 标示符类型. (uid, uname, email, domain之一)
	 */
	public function getUserByIdentifier($identifier, $identifier_type = 'uid')
	{
		if ($identifier_type == 'uid' && !is_numeric($identifier))
			return false;
		else if (!in_array($identifier_type, array('uid','uname','email','domain')))
			return false;
		$user = $this->getUserInfoCache($identifier, $identifier_type);
		return $user;
	}
	/**
     * 缓存用户列表
     *
     * 缓存key的格式为: user_用户uid 和 user_用户昵称
     *
     * @param array $user_list 用户ID列表, 或者用户详情列表. 如果为用户ID列表时, 本方法会首先获取用户详情列表, 然后缓存.
     * @return boolean true:缓存成功 false:缓存失败
     */
	public function setUserObjectCache($user_list)
	{
		if (!is_array($user_list))
			return false;
		if (!is_array($user_list[0]) && !is_numeric($user_list[0]))
			return false;
		if (is_numeric($user_list[0])) {
			foreach($user_list as $val) {
				$user = $this->getUserInfoCache($val);
			}
		} else {
			foreach($user_list as $val) {
				$this->getUserInfoCache($val['uid']);
			}
		}
		return true;
	}
    public function isEmailAvailable($email,$uid)
    {
        if (!isValidEmail($email)) // Email格式错误
            return false;
        else if (($res = $this->getUserByIdentifier($email, 'email'))) { // 在TS已存在
            if($uid){
                if($res['uid'] != intval($uid)) return false;
            }else{
                return false;
            }
        } else if (UC_SYNC && uc_user_checkemail($email) < 0) // 在UCenter已存在或非法
            return false;
        return true;
    }
    public function isUnameAvailable($uname)
    {
        if (!isValidUname($uname)) // 格式错误
            return false;
        else if ($this->getUserByIdentifier($uname, 'uname')) // 在TS已存在
            return false;
        else if (UC_SYNC && uc_user_checkname($uname) < 0) // 在UCenter已存在或非法
            return false;
        return true;
    }
	/**
	 * 获取用户基本信息缓存
	 *
	 * @param int $uid 用户UID，string $type 查询类型(uid,uname,email,domain)
	 * @return array 用户基本信息
	 */
	public function getUserInfoCache($uid, $type = 'uid') {
		//如果为空，则直接退出
		$defaultValue = $uid;
		if(empty($uid)) {
			return false;
		}
		if(!in_array($type, array('uid','uname','email','domain'))) {
			return false;
		}
		//获取用户的UID
		if(!is_numeric($uid) && $type != 'uid') {
			if(isset(self::$nameHash[$type][$defaultValue])){
				$uid = self::$nameHash[$type][$defaultValue];
			} else {
				$map[$type] = $uid;
				$uid = $this->where($map)->getField('uid');
				self::$nameHash[$type][$defaultValue] = $uid;
			}
			if(empty($uid)) {
				return false;
			}
		}
		$userInfo = S('S_userInfo_'.$uid);
		//获取用户基本信息缓存
		if(empty($userInfo)) {
			//姓名
			$userInfo = $this->where('uid='.$uid)->find();
			//积分与经验
			$userCredit = X('Credit')->getUserCredit($uid);
			$userInfo['credit'] = $userCredit;
			//关注数
			$count['following'] = M('weibo_follow')->where("uid={$uid} AND type=0")->count();
			$userInfo['following'] = $count['following'];
			//粉丝数
			$count['follower']  = M('weibo_follow')->where("fid={$uid} AND type=0")->count();
			$userInfo['follower'] = $count['follower'];
			//微博数
			$count['miniNum'] = M('weibo')->where("uid={$uid} AND isdel=0")->count();
			$userInfo['miniNum'] = $count['miniNum'];
			S('S_userInfo_'.$uid, $userInfo);
		}
		return $userInfo;
	}
}