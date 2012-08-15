<?php
class LoginModel extends Model {
	protected	$tableName	=	'login_record';
	public static $nameHash = array();
    var $uid;

	/**
	 * 根据查询条件查询日志
	 *
	 * @param array|string $map          查询条件
	 * @param string       $field		   字段
	 * @param int 		   $limit		   限制条数
	 * @param string 	   $order		   结果排序
	 * @param boolean 	   $is_find_page 是否分页
	 * @return array
	 */
	public function getLoginByMap($map = array(), $field = '*', $limit = '', $order = '', $is_find_page = true) {
		if ($is_find_page) {
			return $this->where($map)->field($field)->order($order)->findPage($limit);
		}else {
			return $this->where($map)->field($field)->order($order)->limit($limit)->findAll();
		}
	}

	/**
	 * 获取用户日志列表
	 *
	 * @param array|string $map             查询条件
	 * @param boolean	   $show_dept		是否显示部门信息
	 * @param boolean 	   $show_user_group 是否显示用户组
	 * @param string       $field		           字段
	 * @param string 	   $order		           结果排序
	 * @param int 		   $limit		 	限制条数
	 * @return array
	 */
    public function getLoginList($map = '', $show_dept = false, $show_user_group = false, $field = '*', $order = 'ctime ASC', $limit = 40) {
    	$res  = $this->where($map)->field($field)->order($order)->findPage($limit);
    	$uids = getSubByKey($res['data'], 'ctime');
		return $res;
    }


/**
     * 删除日志
     *
     * @param array|string $uids
     * @return boolean
     */
    public function deleteLog($uids) {
    	//防止误删
    	$uids = is_array($uids) ? $uids : explode(',', $uids);
    	foreach($uids as $k => $v) {
    		if ( !is_numeric($v) ) unset($uids[$k]);
    	}
    	if ( empty($uids) ) return false;

    	$map['login_record_id'] = array('in', $uids);
    	//user
    	M('login_record')->where($map)->delete();
    	//user_group_link
    	//user_group_popedom
    	//user_popedom
    	return true;
    }


}