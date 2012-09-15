<?php
/**
 * "微博达人"勋章
 *
 * 连续3天发原创微博获得此勋章. 用户获得此勋章即为永久拥有.
 *
 * @author daniel <desheng.young@gmail.com>
 */
class TrueWeiboerMedal extends BaseMedal {

	protected $_next_level_time = 0;

	public function getMedalStatus($uid, $medal_id, $user_medal_data, $medal_data) {
		!empty($user_medal_data['data']) && $user_medal_data['data'] = unserialize($user_medal_data['data']);
		!empty($medal_data['data']) 	 && $medal_data['data']		 = unserialize($medal_data['data']);

		$result = array();
		if ( empty($user_medal_data) ) {
			// 添加用户的勋章信息
			if ( $this->__isTrueWeiboer($uid) ) {
				$data['received_time'] = time();
				$data['alert_message'] = '';
				$data['is_alert_on']   = 0;

				$this->__addUserCredit($uid);
			}else {
				$data['received_time'] = 0;
				$data['alert_message'] = $medal_data['data']['alert_message'];
				$data['is_alert_on']   = 1;
			}
			$this->dao->addUserMedal($uid, $medal_id, serialize($data));

			$result['received_time'] = $data['received_time'];
			$result['alert_message'] = $data['alert_message'];
			$result['is_alert_on']   = $data['is_alert_on'];

		}else {
			// 尚未获得勋章, 检查现在是否够条件. 如果是, 更新用户的勋章信息
			if ( intval($user_medal_data['data']['received_time']) <= 0 && $this->__isTrueWeiboer($uid) ) {
				$user_medal_data['data']['received_time'] = time();
				$user_medal_data['data']['alert_message'] = '';
				$user_medal_data['data']['is_alert_on']   = 0;
				$this->dao->updateUserMedal($user_medal_data['user_medal_id'], serialize($user_medal_data['data']));

				$this->__addUserCredit($uid);
			}

			$result['received_time'] = $user_medal_data['data']['received_time'];
			$result['alert_message'] = $user_medal_data['data']['alert_message'];
			$result['is_alert_on']   = $user_medal_data['data']['is_alert_on'];
		}

		$result['title']			= $medal_data['title'];
		//$result['icon_url']			= $medal_data['data']['icon_url'];
		//$result['big_icon_url']		= $medal_data['data']['big_icon_url'];
		$result['icon_url']			= SITE_URL . '/addons/plugins/Medal/lib/' . $medal_data['path_name'] . '/icon.gif';
		$result['big_icon_url']		= SITE_URL . '/addons/plugins/Medal/lib/' . $medal_data['path_name'] . '/big_icon.gif';
		$result['description']		= $medal_data['data']['description'];
		$result['next_level_time']	= $this->_next_level_time > 0 ? $this->_next_level_time . '天' : '0';
		return $result;
	}

	/**
	 * 关闭勋章的提示消息
	 *
	 * @param int $uid
	 * @param int $medal_id
	 */
	public function closeMedalAlert($uid, $medal_id) {
		$medal_data = $this->dao->getUserMedal($uid, $medal_id);
		$medal_data['data'] = unserialize($medal_data['data']);
		$medal_data['data']['is_alert_on'] = '0';
		$this->dao->updateUserMedal($medal_data['user_medal_id'], serialize($medal_data['data']));
	}

	/**
	 * 计算给定用户是否为微博控 ( 即: 连续3天原创微博 )
	 *
	 * 算法说明: 首先算出今天、昨天、前天的起止时间，然后使用UNION ALL作区间查询
	 *
	 * @param int $uid
	 * @return bool
	 */
	private function __isTrueWeiboer($uid, $days = 3) {
		if ($days <= 0) {
			return true;
		}

		$this->_next_level_time = intval($days);

		// 计算时间区间
		$time = array();
		for($i = 0; $i < $days; $i ++) {
			$time[$i] = array(
				'begin'	=> mktime(0,  0,  0,  date('m'), date('d') - $i, date('Y')),
				'end'	=> mktime(23, 59, 59, date('m'), date('d') - $i, date('Y')),
			);
		}

		// 作为示例, 这里简单处理.
		$db_prefix = C('DB_PREFIX');
		foreach ($time as $v) {
			$sql = "SELECT * FROM {$db_prefix}weibo WHERE `ctime` >= {$v['begin']} AND `ctime` <= {$v['end']} AND `uid` = {$uid} AND `transpond_id` = 0 LIMIT 1";
			$res = M('')->query($sql);
			if ( ! empty($res) ) {
				$this->_next_level_time --;
			}else {
				break;
			}
		}
		return $this->_next_level_time === 0;
	}

	private function __addUserCredit($uid) {
		X('Credit')->setUserCredit($uid,'add_medal');
	}
}