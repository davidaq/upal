<?php
class LoginModel extends Model {
	protected	$tableName	=	'login_record';
	public static $nameHash = array();
    var $uid;

	/**
	 * ���ݲ�ѯ������ѯ��־
	 *
	 * @param array|string $map          ��ѯ����
	 * @param string       $field		   �ֶ�
	 * @param int 		   $limit		   ��������
	 * @param string 	   $order		   �������
	 * @param boolean 	   $is_find_page �Ƿ��ҳ
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
	 * ��ȡ�û���־�б�
	 *
	 * @param array|string $map             ��ѯ����
	 * @param boolean	   $show_dept		�Ƿ���ʾ������Ϣ
	 * @param boolean 	   $show_user_group �Ƿ���ʾ�û���
	 * @param string       $field		           �ֶ�
	 * @param string 	   $order		           �������
	 * @param int 		   $limit		 	��������
	 * @return array
	 */
    public function getLoginList($map = '', $show_dept = false, $show_user_group = false, $field = '*', $order = 'ctime ASC', $limit = 40) {
    	$res  = $this->where($map)->field($field)->order($order)->findPage($limit);
    	$uids = getSubByKey($res['data'], 'ctime');
		return $res;
    }


/**
     * ɾ����־
     *
     * @param array|string $uids
     * @return boolean
     */
    public function deleteLog($uids) {
    	//��ֹ��ɾ
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