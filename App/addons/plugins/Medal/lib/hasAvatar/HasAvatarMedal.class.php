<?php
/**
 * "有头有脸"勋章
 * 
 * 上传头像即可获得此勋章.
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class HasAvatarMedal extends BaseMedal {
	
	protected $_is_has_avatar;
	
	public function getMedalStatus($uid, $medal_id, $user_medal_data, $medal_data) {
		!empty($user_medal_data['data']) && $user_medal_data['data'] = unserialize($user_medal_data['data']);
		!empty($medal_data['data']) 	 && $medal_data['data']		 = unserialize($medal_data['data']);
		
		$result = array();
		if ( empty($user_medal_data) ) {
			// 添加用户的勋章信息
			if ( $this->__isHasAvatar($uid) ) {
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
			// 重新检查是否有头像, 如果与原有状态不符(如先上传,后取消), 更新用户勋章信息
			$user_medal_data['data']['received_time'] = intval($user_medal_data['data']['received_time']);
			if ( $user_medal_data['data']['received_time'] <= 0 && $this->__isHasAvatar($uid) ) {
				$user_medal_data['data']['received_time'] = time();
				$user_medal_data['data']['alert_message'] = '';
				$user_medal_data['data']['is_alert_on']   = 0;
				
				$this->__addUserCredit($uid);
				
			}else if ( $user_medal_data['data']['received_time'] > 0 && !$this->__isHasAvatar($uid) ) {
				$user_medal_data['data']['received_time'] = 0;
				$user_medal_data['data']['alert_message'] = $medal_data['data']['alert_message'];
				$user_medal_data['data']['is_alert_on']   = 1;
				
				$this->__deleteUserCredit($uid);
			}
			$this->dao->updateUserMedal($user_medal_data['user_medal_id'], serialize($user_medal_data['data']));
			
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
		$result['next_level_time']	= $this->__isHasAvatar($uid) ? '0' : '上传头像';
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
	
	private function __isHasAvatar($uid) {
		if (!isset($this->_is_has_avatar)) {
			$this->_is_has_avatar = false;
			$types = array('middle.jpg', 'small.jpg', 'big.jpg');
			foreach ($types as $type) {
				$userface = SITE_PATH.'/data/uploads/avatar/'.$uid.'/'.$type;
				if(is_file($userface)){
					$this->_is_has_avatar = true;
					break ;
				}
			}
		}
		
		return $this->_is_has_avatar;
	}
	
	private function __addUserCredit($uid) {
		X('Credit')->setUserCredit($uid,'add_medal');
	}
	
	private function __deleteUserCredit($uid) {
		X('Credit')->setUserCredit($uid,'delete_medal');
	}
}