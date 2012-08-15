<?php
/**
 * 站内信模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class _MessageModel extends Model {
	protected $tableName = 'message';
	
	public function _initialize() {
		
	}

	/**
	 * 获取消息列表
	 * 
	 * @param string|array $map   查询条件
	 * @param string       $field 默认'*'
	 * @param string       $order 默认'message_id DESC'
	 * @param int          $limit 默认20
	 * @return array
	 */
	public function getMessageByMap($map = array(), $field = '*', $order = 'message_id DESC', $limit = 20) {
		return $this->where($map)->field($field)->order($order)->findPage($limit);
	}

	/**
	 * 收件箱消息列表
	 * 
	 * @param int    $uid  用户ID
	 * @param string $type all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
	 * @return array
	 */
	public function getInboxByUid($uid, $type = 'all') {
		$map['to_uid']		= $uid;
		$map['is_lastest']	= 1;
		$map['deleted_by']	= array('neq', $uid);
		if ($type == 'is_read') {
			$map['is_read'] = 1;
		}else if ($type == 'is_unread') {
			$map['is_read']	= 0;
		}
		return $this->getMessageByMap($map);
	}

	/**
	 * 收件箱消息列表（API专用）
	 * 
	 * @param int    $uid      用户ID
	 * @param string $type     all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
	 * @param int    $since_id 范围起始ID 默认0
	 * @param int    $max_id   范围结束ID 默认0
	 * @param int    $count    单页读取条数  默认20
	 * @param int    $page     页码  默认1
	 * @param string $order    排序  默认以消息ID倒叙排列
	 * @return array
	 */
	public function getInboxByUidFromApi($uid, $type = 'all', $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = 'message_id DESC') {
		$map['to_uid']		= $uid;
		$map['is_lastest']	= 1;
		$map['deleted_by']	= array('neq', $uid);
		if ($type == 'is_read') {
			$map['is_read'] = 1;
		}else if ($type == 'is_unread') {
			$map['is_read']	= 0;
		}
		if ($since_id) {
			$map['message_id'][] = array('gt', $since_id);
		}
		if ($max_id) {
			$map['message_id'][] = array('lt', $max_id);
		}
		$limit = ($page-1) * $count . ',' . $count;
		return $this->where($map)->order($order)->limit($limit)->findAll();
	}

	/**
	 * 获取发件箱消息列表
	 * 
	 * @param int $uid 用户列表
	 * @return array
	 */
	public function getOutboxByUid($uid) {
		$map['from_uid']			= $uid;
		$map['is_lastest']			= 1;
		$map['deleted_by']			= array('neq', $uid);
		return $this->getMessageByMap($map);
	}

	/**
	 * 获取发件箱消息列表（API专用）
	 * 
     * @param int    $uid      用户ID
     * @param int    $since_id 范围起始ID 默认0
     * @param int    $max_id   范围结束ID 默认0
     * @param int    $count    单页读取条数  默认20
     * @param int    $page     页码  默认1
     * @param string $order    排序  默认'message_id DESC'
     * @return array
	 */
	public function getOutboxByUidFromApi($uid, $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = 'message_id DESC') {
		$map['from_uid']			= $uid;
		$map['is_lastest']			= 1;
		$map['deleted_by']			= array('neq', $uid);
		if ($since_id) {
			$map['message_id'][] = array('gt', $since_id);
		}
		if ($max_id) {
			$map['message_id'][] = array('lt', $max_id);
		}
		$limit = ($page-1) * $count . ',' . $count;
		return $this->where($map)->order($order)->limit($limit)->findAll();
	}

	/**
	 * 私信详细信息
	 * 
	 * @param int     $uid          用户ID
	 * @param int     $message_id   私信ID
	 * @param boolean $show_cascade 是否获取回话内容
	 * @return array
	 */
	public function getDetailById($uid, $message_id, $show_cascade = true) {
		$uid = intval($uid);
		$message_id = intval($message_id);
		$map['from_uid'] = array('exp', "={$uid} OR `to_uid`={$uid}");
		if ($show_cascade) {
			$source_msg_id = $this->where('`message_id`='.$message_id)->field('source_message_id')->find();
			$map['source_message_id']	= $source_msg_id['source_message_id'];
			$map['deleted_by']			= array('neq', $uid);
			return $this->where($map)->order('message_id DESC')->findAll();
		}else {
			$map['message_id']			= $message_id;
			$map['deleted_by']			= array('neq', $uid);
			return $this->where($map)->find();
		}
	}

	/**
	 * 用户未读私信数
	 * 
	 * @param int $uid 用户ID
	 * @return array
	 */
	public function getUnreadMessageCount($uid) {
		$map['to_uid']		= $uid;
		$map['is_read']		= 0;
		$map['deleted_by']	= array('neq', $uid);
		return $this->where($map)->count();
	}

	/**
	 * 获取私信会话数，可同时返回多条私信的该状态
	 * 
	 * @param int          $uid
	 * @param string|array $message_ids 多个私信可以组装成数组，也可以用“,”分隔
	 * @return array
	 */
	public function getSessionCount($uid, $message_ids = 0) {
		$message_ids	= is_array($message_ids) ? implode(',', $message_ids) : $message_ids;
		$prefix = C('DB_PREFIX');
		$where	= "`source_message_id` IN ( $message_ids ) AND `deleted_by` <> $uid";
		$sql 	= "SELECT `source_message_id`, count(*) AS count FROM {$prefix}message WHERE $where GROUP BY `source_message_id`";
		$res	= $this->query($sql);
		
		//格式化为array($message_id => $count)的形式
		foreach ($res as $v) {
			$session_count[$v['source_message_id']]	= $v['count'];
		}
		return $session_count;
	}

	/**
	 * 发送私信
	 * 
	 * @param array $data     私信信息,包括to接受对象、title私信标题、content私信正文
	 * @param int   $from_uid 发送私信的用户ID
	 * @return array          返回新添加的私信的ID
	 */
	public function postMessage($data, $from_uid) {
		$message_ids = array();
		$data['to']	 = is_array($data['to']) ? $data['to'] : explode(',', $data['to']);
		
		//添加新记录
		$message['from_uid']	= $from_uid;
        $message['content']		= t($data['content']);
        $message['title']       = getShort($message['content'],15);
		$message['ctime']		= time();
		foreach ($data['to'] as $to_uid) {
			if ( !is_numeric($to_uid) ) continue;
			$message['to_uid']	= intval($to_uid);
			$res = $this->add($message);
			if ($res)
				$message_ids[] = $res;
		}
		
		//设置source_message_id
		$sql = 'UPDATE `' . C('DB_PREFIX') . 'message` SET `source_message_id`=`message_id` WHERE `message_id` IN ( ' . implode(',', $message_ids) . ' )';
		$this->execute($sql);
		return $message_ids;
	}

	/**
	 * 回复私信
	 * 
	 * @param int    $message_id 回复的站内信的ID
	 * @param string $content    内容
	 * @param int    $from_uid   回复者
	 * @return mixed 回复失败返回false，回复成功返回本条新回复的ID
	 */
	public function replyMessage($message_id, $content, $from_uid) {
		$source_message = $this->getDetailById( $from_uid, $message_id, false );
		if ( empty($source_message) ) {
			return false;
		}
		
		// 添加新记录
		$new_message['from_uid']			= $from_uid;
		$new_message['to_uid']				= $source_message['from_uid'] == $from_uid ? $source_message['to_uid'] : $source_message['from_uid'];
		$new_message['title']				= substr($source_message['title'], 0, 8) == '回复: ' ? $source_message['title']  : '回复: ' . $source_message['title'];
		$new_message['content']				= t($content);
		$new_message['source_message_id']	= $source_message['source_message_id'];
		$new_message['ctime']				= time();
		$new_message_id = $this->add($new_message);
		unset($new_message);
		
		if ( !$new_message_id ) {
			return false;
		}else {
			// 重置最新记录
			$map['message_id']			= array('neq', $new_message_id);
			$map['from_uid']			= $from_uid;
			$map['source_message_id']	= $source_message['message_id'];
			$this->where($map)->setField('is_lastest', 0);
		}
		return $new_message_id;
	}

    /**
     * 设置站内信为已读
     * 
     * @param array|string $message_ids 多个站内信ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */	
	public function setMessageIsRead($message_ids = 0) {
		$map['source_message_id'] = array('in', $message_ids);
		return $this->where($map)->setField('is_read', '1');
	}

    /**
     * 设置站内信为未读
     * 
     * @param array|string $message_ids 多个站内信ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */ 
	public function setMessageIsUnread($message_ids = 0) {
		$map['source_message_id'] = array('in', $message_ids);
		return $this->where($map)->setField('is_read', '0');
	}

	/**
	 * 删除站内信
	 * 
	 * @param int          $uid         用户ID
     * @param array|string $message_ids 多个站内信ID可以组成数组，也可以用“,”分隔
     * @return boolean
	 */
	public function deleteMessageById($uid, $message_ids) {
		if ( empty($message_ids) )
			return false;
		
		// 首先获取信息详情，检查是否已被对方删除
		$map['source_message_id'] = array('in', $message_ids);
		$map['deleted_by']		  = array('neq', $uid);
		$map['from_uid']		  = array('exp', " =$uid OR `to_uid`=$uid");
		$message_list = $this->getMessageByMap($map);
		$message_list = $message_list['data'];
		unset($map);
		if ( empty($message_list) )
			return false;

		$setfield_ids		= array(); //设置字段的站内信ID（对方未删除站内信）
		$delete_ids 		= array(); //直接删除的站内信ID（对方已删除站内信）
		foreach ($message_list as $v) {
			if ( $v['deleted_by'] == 0 )
				$setfield_ids[] = $v['source_message_id'];
			else
				$delete_ids[]	= $v['source_message_id'];
		}
		unset($message_list);
		
		if ( empty($setfield_ids) && empty($delete_ids) ) {
			return false;
		}
		
		$res = true;
		if ( !empty($setfield_ids) ) {
			$map['source_message_id'] = array('in', $setfield_ids);
			$res = $this->where($map)->setField('deleted_by', $uid);
			unset($map);
		}
		if ( !empty($delete_ids) ) {
			$map['source_message_id'] = array('in', $delete_ids);
			$res = $res && $this->where($map)->delete();
			unset($map);
		}
		return $res;
	}
	
	public function setAllIsRead($uid) {
		$uid = intval($uid);
		if ($uid <= 0)
			return false;
			
		$map['to_uid']     = $uid;
		$map['is_read']    = 0;
		$map['deleted_by'] = array('neq', $uid);
		return $this->where($map)->setField('is_read', 1);
	}

	/**
	 * 直接删除（管理员）
	 * 
     * @param array|string $message_ids 多个站内信ID可以组成数组，也可以用“,”分隔
     * @return boolean
	 */
	public function deleteMessage($message_ids) {
		$message_ids = is_array($message_ids) ? $message_ids : explode(',', $message_ids);
		if ( empty($message_ids) )
			return false;
		$map['message_id'] = array('in', $message_ids);
		return $this->where($map)->delete();
	}
}
