<?php
require_cache(SITE_PATH . '/addons/plugins/Medal/MedalModel.class.php');
abstract class BaseMedal {

	protected $dao;

	public function __construct() {
		$this->dao = model('Medal');
	}

	/**
	 * 获取用户勋章详情
	 *
	 * 返回数组的格式为：
	 * <code>
	 * array(
	 *   'title'			=> '',	//
	 *   'icon_url'  		=> '',	//
	 *   'big_icon_url'		=> '',	//
	 *   'description'		=> '',	//
	 *   'received_time'	=> '',	// int
	 *   'next_level_time'	=> '',	// string
	 *   'alert_message'	=> '',
	 *   'is_alert_on'		=> '1',
	 * )
	 * </code>
	 *
	 * @param int $uid
	 * @return array
	 */
	abstract public function getMedalStatus($uid, $medal_id, $user_medal_data, $medal_data);

	/**
	 * 关闭勋章的提示消息
	 *
	 * @param int $uid
	 * @param int $medal_id
	 */
	abstract public function closeMedalAlert($uid, $medal_id);
}