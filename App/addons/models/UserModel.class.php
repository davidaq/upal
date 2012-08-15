<?php
/**
 * 用户Model [即将废弃, 请使用home下的UserModel ]
 *
 * 即将废弃, 请使用home下的UserModel
 *
 * @author daniel <desheng.young@gmail.com>
 * @deprecated
 */
class UserModel extends Model {
	protected	$tableName	=	'user';
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
	 * 获取日志列表
	 *
	 * @param array|string $map             查询条件
	 * @param boolean	   $show_loginip		是否显示登陆IP信息
	 * @param boolean 	   $show_loginurl 是否显示登陆地点
	 * @param string       $field		           字段
	 * @param string 	   $order		           结果排序
	 * @param int 		   $limit		 	限制条数
	 * @return array
	 */
    public function getUserList($map = '', $show_loginip = true, $show_loginurl = true, $field = '*', $order = 'uid ASC', $limit = 30) {
    	$res  = $this->where($map)->field($field)->order($order)->findPage($limit);
    	$uids = getSubByKey($res['data'], 'uid');

    




    }



    /**
     * 删除用户
     *
     * @param array|string $uids
     * @return boolean
     */
    public function deleteUser($uids) {
    	//防止误删
    	$uids = is_array($uids) ? $uids : explode(',', $uids);
    	foreach($uids as $k => $v) {
    		if ( !is_numeric($v) ) unset($uids[$k]);
    	}
    	if ( empty($uids) ) return false;

    	$map['uid'] = array('in', $uids);
    	//user
    	M('user')->where($map)->delete();
    	//user_group_link
    	//user_group_popedom
    	//user_popedom
    	return true;
    }

    /**
     * 更新操作
     *
     * @param string $type
     * @return boolean
     */
	function upDate($type){
	    return $this->$type();
	}

	/**
	 * 更新基本信息
	 *
	 */
	private function upbase( ){
		$nickname = t($_POST['nickname']);
		if(!$nickname) return '昵称不能为空';
		if( 0!=M('user')->where("uname='{$nickname}' AND uid!={$this->uid}")->count() ){
			return '昵称已有人使用';
		}
	    $data['province'] = intval( $_POST['area_province'] );
	    $data['uname']    = $nickname;
	    $data['city']     = intval( $_POST['area_city'] );
	    $data['location'] =  getLocation($data['province'],$data['city']);
	    $data['sex']      = intval( $_POST['sex'] );
	    M('user')->where("uid={$this->uid}")->data($data)->save();
	    	return '更新完成';
	}

	/**
	 * 获取用户基本信息字段
	 *
	 * @param string $module 字段类别,contact联系的字段、inro基本介绍的字段
	 * @return array
	 */
	protected function data_field($module,$space=false){
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
}
?>