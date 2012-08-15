<?php
/**
 * 用户组模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class UserGroupModel extends Model {
	protected	$tableName	=	'user_group';

	/**
	 * 添加用户组
	 * 
	 * @param string $title 用户组名称
	 * @param string $icon  用户组图标 范例“v_01.gif”
	 * @return boolean
	 */
	public function addUserGroup($title,$icon) {
		$data['title']		= $title;
		$data['icon']		= $icon;
		$data['ctime']		= time();
		return $this->add($data);
	}

	/**
	 * 删除用户组
	 * 
	 * @param string $gids 用户组ID
	 * @return boolean
	 */
	public function deleteUserGroup($gids) {
		//防误操作
		if (empty($gids)) return false;
		
		$map['user_group_id']	= array('in', $gids);
		M('user_group')		->where($map)->delete();
		M('user_group_link')->where($map)->delete();
		return true;
	}
	
	/**
	 * 获取所有的用户组配置信息
	 * 
	 * 本方法按照运行时缓存->文件缓存->数据库的顺序查询
	 * 
	 * @param $do_format 是否将结果集格式化为array($user_group_id => $user_group)
	 */
	public function getAllUserGroup($do_format = true) {
		$cache_id = '_model_user_group' . ($do_format ? '_1' : '_0');
		
		if (($res = object_cache_get($cache_id)) === false) {
			if (($res = F($cache_id)) === false) {
				$temp = $this->findAll();
				if ($do_format) {
					foreach ($temp as $v)
						$res[$v['user_group_id']] = $v;
				}else {
					$res = $temp;
				}
				unset($temp);
				
				F($cache_id, $res);
			}
			
			object_cache_set($cache_id, $res);
		}
		return $res;
	}
	
	/**
	 * 获取所有的"用户-用户组"关联关系
	 * 
	 * 本方法按照运行时缓存->文件缓存->数据库的顺序查询
	 * @param $do_format 是否将结果集格式化为array($uid => $user_group_link)
	 */
	public function getAllUserGroupLink($do_format = true) {
		$cache_id = '_model_user_group_link' . ($do_format ? '_1' : '_0');
		if (($res = object_cache_get($cache_id)) === false) {
			if (($res = F($cache_id)) === false) {
				$temp = M('user_group_link')->findAll();
				if ($do_format) {
					foreach ($temp as $v)
						$res[$v['uid']][] = $v;
				}else {
					$res = $temp;
				}
				unset($temp);
				
				F($cache_id, $res);
			}
			object_cache_set($cache_id, $res);
		}
		return $res;
	}

	/**
	 * 按照查询条件获取用户组
	 * 
	 * @param array  $map   查询条件
	 * @param string $field 字段 默认*
	 * @param string $order 排序 默认 以用户组ID升序排列
	 * @return array 用户组信息
	 */
	public function getUserGroupByMap($map = '', $field = '*', $order = 'user_group_id ASC') {
		return $this->field($field)->where($map)->order($order)->findAll();
	}

	/**
	 * 根据IDs获取用户组信息
	 * 
     * @param array  $gids  用户组ID
     * @param string $field 字段 默认*
     * @param string $order 排序 默认空
     * @return array 用户组信息
	 */
	public function getUserGroupById($gids, $field = '*', $order = '') {
		$map['user_group_id']	= array('in', $gids);
		return $this->getUserGroupByMap($map, $field, $order);
	}

	/**
	 * 根据用户ID获取用户组
	 * 
	 * @param array $uids 用户ID
	 * @return array 用户和用户组关系信息
	 */
	public function getUserGroupByUid($uids) {
		$map['uid']	= array('in', $uids);
		return M('user_group_link')->where($map)->order('user_group_id ASC')->findAll();
	}



	/**
	 * 获取制定用户组内的用户
	 * 
	 * @param array $gids 用户组ID
	 * @return array 用户和用户组关系信息,数组的键替换为用户ID
	 */
	public function getUidByUserGroup($gids) {
		$map['user_group_id']	= array('in', $gids);
		return getSubByKey( M('user_group_link')->where($map)->findAll(), 'uid' );
	}

	/**
	 * 将用户添加至用户组
	 * 
	 * @param array|string $uids 多个ID可为数组也可用“,”分隔
	 * @param array|string $gids 多个ID可为数组也可用“,”分隔
	 * @return boolean
	 */
    public function addUserToUserGroup($uids, $gids) {
    	$gids = is_array($gids) ? $gids : explode(',', $gids);
    	$uids = is_array($uids) ? $uids : explode(',', $uids);
    	
    	//用户信息
        $map['uid'] = array('in', $uids);
        $users = D('User', 'home')->getUserList($map, false, false, 'uid', '', count($uids));
        unset($map);
        if (!$users)
            return false;
        $users = $users['data'];
    	
        //删除旧数据
        $map['uid'] = array('in', $uids);
        M('user_group_link')->where($map)->delete();
        unset($map);
    	
    	//用户组信息
    	$groups = $this->getUserGroupById($gids);
    	if (!$groups) 
    		return false;
    	
    	//组装SQL，插入新数据
    	$sql = "INSERT INTO `" . C('DB_PREFIX') . "user_group_link` (`user_group_id`,`user_group_title`,`uid`) VALUES ";
    	foreach($groups as $group) {
    		foreach($users as $user) {
    			$sql .= "('{$group['user_group_id']}', '{$group['title']}', '{$user['uid']}'),";
    		}
    	}
    	$sql = rtrim($sql, ',');
    	return $this->execute($sql);
    }

    /**
     * 检测用户组是否存在
     * 
     * @param unknown_type $title 用户组名称
     * @param unknown_type $gid   用户组ID 该函数里为非该用户组ID
     * @return boolean
     */
	public function isUserGroupExist($title, $gid = 0) {
		$map['user_group_id']	= array('neq', $gid);
		$map['title']			= $title;
    	return M('user_group')->where($map)->find();
    }

    /**
     * 指定用户组下是否存在用户
     * 
     * @param array $gids 用户组ID
     * @return boolean
     */
    public function isUserGroupEmpty($gids) {
    	$map['user_group_id']	= array('in', $gids);
    	return ! M('user_group_link')->where($map)->find();
    }

    /**
     * 检测指定用户是否属于指定用户组
     * 
     * @param int   $uid  用户ID
     * @param array $gids 用户组ID
     * @return boolean
     */
    public function isUserInUserGroup($uid, $gids) {
    	$map['uid']			  	= $uid;
    	$map['user_group_id']	= array('in', $gids);
    	return M('user_group_link')->where($map)->find();
    }
    
    /**
     * 根据用户ID获取该用户所在用户组的ID
     *
     * @param unknown_type $uid
     * @return array $gid
     */
    public function getUserGroupId($uid){
    	if(($gid = S('UserGroupIds_'.$uid)) === false){
	    	$map['uid']	= $uid;
	    	$gid = array();
	    	if($list = M('user_group_link')->where($map)->field('user_group_id')->findAll()){
	    		foreach($list as $v){
	    			$gid[] = $v['user_group_id'];
	    		}
	    	}
	    	S('UserGroupIds_'.$uid,$gid);
    	}
    	return $gid;
    }

    /**
     * 获取指定用户的用户组图标
     * 
     * @param int $uid 用户ID
     * @return string  返回用户组图标的img标签
     */
    public function getUserGroupIcon($uid) {
    	$user_group      = $this->getAllUserGroup();
    	$user_group_link = $this->getAllUserGroupLink();
    	$user_group_link = $user_group_link[$uid];
    	
    	$html = '';

    	foreach ($user_group_link as $v) {
    		if ($user_group[$v['user_group_id']]['icon'])
    			$html .= "<img class='ts_icon' src=".THEME_URL."/images/".$user_group[$v['user_group_id']]['icon']." title=".$user_group[$v['user_group_id']]['title'].">";
    	}
    	
    	return $html;
    }
    
    public function isAdmin($uid) {
    	return service('SystemPopedom')->hasPopedom($uid, 'admin/Index/index', false);
    }
    
	//获取一个用户的组
	public function getUserGroups($uid, $showDetail = false) {
		$uid = intval($uid);
		if(!$uid) {
			return false;
		}
		$sql = "SELECT l.user_group_id AS gid, g.`name`, g.type FROM `ts_user_group_link` AS l LEFT JOIN `ts_forum_user_group` AS g ON l.user_group_id = g.gid WHERE l.uid = {$uid}";
		$result = $this->query($sql);
		if(!$result) {
			return null;
		} else {
			if($showDetail) {
				return $result;			//输出详细信息
			} else {
				foreach($result as $v) {
					$group[] = $v['gid'];
				}
				return $group;			//只输出GID
			}
		}
	}
}