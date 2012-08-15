<?php
/**
 * 勋章模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class MedalModel extends Model
{
	protected $tableName = 'user_medal';

	/**
	 * 已经安装的勋章列表
	 * 
	 * @param string $order
	 * @return array
	 */
	public function getInstalledMedal($order = 'display_order ASC') {
		return M('medal')->order($order)->findAll();
	}

	/**
     * 开启状态的勋章列表
     * 
     * @param string $order
     */
	public function getActiveMedal($order = 'display_order ASC') {
		return M('medal')->where('`is_active`=1')->order($order)->findAll();
	}

	/**
	 * 设置勋章开启/关闭状态(管理员)
	 * 
	 * @param int $medal_id 勋章ID
	 * @param int $status   状态,1：开启,0：关闭
	 * @return boolean
	 */
	public function setMedalStatus($medal_id, $status = 1) {
		$status = ($status == '0') ? '0' : '1';
		return M('medal')->where('`medal_id`='.$medal_id)->setField('is_active', $status);
	}

    /**
     * 设置勋章显示/隐藏状态(用户)
     * 
     * @param int $medal_id 勋章ID 可以同时操作通过
     * @param int $status   状态,1：显示,0：隐藏
     * @return boolean
     */	
	public function setUserMedalStatus($uid, $medal_ids, $status = 1) {
		$map['uid']			= $uid;
		$map['medal_id']	= array('in', $medal_ids);
		$status = ($status == '0') ? '0' : '1';
		return M('user_medal')->where($map)->setField('is_active', $status);
	}

	/**
	 * 获取用户的勋章
	 * 
	 * @param int $uid      用户ID
	 * @param int $medal_id 勋章ID
	 * @return array 返回该用户某个勋章的信息，或所有勋章的信息
	 */
	public function getUserMedal($uid, $medal_id = 0) {
		$map['uid']	= $uid;
		
		if ($medal_id) {
			$map['medal_id'] = $medal_id;
			return $this->where($map)->find();
		}else {
			return $this->where($map)->findAll();
		}
	}

	/**
	 * 卸载勋章
	 * 
	 * @param array|string $medal_ids 勋章ID 可以操作多个
	 * @return boolean
	 */
	public function deleteMedal($medal_ids) {
		$medal_ids = is_array($medal_ids) ? $medal_ids : explode(',', t($medal_ids));
		if ( empty($medal_ids) ) 
			return false;
			
		$map['medal_id'] = array('in', $medal_ids);
		M('user_medal')->where($map)->delete();
		return M('medal')->where($map)->delete();
	}

	/**
	 * 获得勋章
	 * 
	 * @param int $uid      获得勋章的用户ID
	 * @param int $medal_id 获得的勋章
	 * @param string $data  获得信息
	 * @return int 操作成功则返回新增用户勋章记录的ID
	 */
	public function addUserMedal($uid, $medal_id, string $data) {
		$map['uid']			= intval($uid);
		$map['medal_id']	= intval($medal_id);
		$map['data']		= $data;
		return $this->add($map);
	}

	/**
	 * 更新用户勋章信息
	 * 
	 * @param int    $user_medal_id 用户勋章记录的ID
	 * @param string $data          用户勋章获的信息
	 * @return boolean
	 */
	public function updateUserMedal($user_medal_id, string $data) {
		$map['data']		= $data;
		return $this->where("`user_medal_id`={$user_medal_id}")->save($map);
	}

	/**
	 * 存储用户勋章信息
	 * 
	 * @param int    $uid      用户ID
	 * @param int    $medal_id 勋章ID
	 * @param string $data     相关信息
	 * @return boolean
	 */
	public function saveUserMedal($uid, $medal_id, string $data) {
		$medal = $this->getUserMedal($uid, $medal_id);
		
		if ( empty($medal) ) {
			return $this->addUserMedal($uid, $medal_id, $data);
		}else {
			return $this->updateUserMedal($medal['user_medal_id'], $data);
		}
	}
	
	/**
	 * 获取用户勋章的widget数据
	 * 
	 * @param int  $uid
	 * @param bool $hide_inactive	是否隐藏用户禁用的勋章
	 * @param bool $hide_unreceived 是否隐藏用户未获得的勋章
	 * @return array
	 */
	public function getMedalWidgetData($uid, $hide_inactive = true, $hide_unreceived = true) {
		$data['uid'] = $uid;

		// 获取勋章数据，并将数组转换为 array($medal_id => $array) 的形式
		$medal		= $this->__changeArrayKey( $this->getActiveMedal() );
		$user_medal	= $this->__changeArrayKey( $this->getUserMedal($data['uid']) );

		// 轮询获取勋章详情
		$data['user_medal']	= array();
		foreach ($medal as $k => $v) {
			// 检查用户是否禁用勋章
			if ( $hide_inactive && ! empty($user_medal[$k]) && ! $user_medal[$k]['is_active'] )
				continue ;

			$temp_medal_data = MedalHooks::medal($v['path_name'])->getMedalStatus($data['uid'], $k, $user_medal[$k], $v);

			if ( empty($data['alert']) && $temp_medal_data['is_alert_on'] && !empty($temp_medal_data['alert_message']) ) {
				$data['alert']	= array('medal_id' => $k, 'content' => $temp_medal_data['alert_message']);
			}

			// 勋章的获取时间为0时表示用户未获得勋章, 不显示该勋章(注: 该勋章的alert_message仍然可以有效)
			if ( $hide_unreceived && (intval($temp_medal_data['received_time']) <= 0) ) {
				unset($temp_medal_data);
				continue ;
			}else {
				$temp_medal_data['received_time']	= $temp_medal_data['received_time'] <= 0 ? 0 : date('Y-m-d H:i', $temp_medal_data['received_time']);
				$temp_medal_data['is_active']		= isset($user_medal[$k]['is_active']) ? $user_medal[$k]['is_active'] : '1';
				$temp_medal_data['medal_id']		= $v['medal_id'];
				$temp_medal_data['path_name']		= $v['path_name'];
				$data['user_medal'][$k] = $temp_medal_data;
				unset($temp_medal_data);
			}
		}
		unset($medal, $user_medal);
		return $data;
	}

	/**
	 * 将数组的键值替换为勋章id
	 * 
	 * @param array  $input 数据
	 * @param string $key   默认'medal_id'
	 * @return array
	 */
	private function __changeArrayKey($input, $key = 'medal_id') {
		$output = array();
		foreach ($input as $v) {
			$output[$v[$key]] = $v;
		}
		return $output;
	}
}