<?php
/**
 * 验证服务
 *
 * 验证服务（ValidationService）主要用于邀请注册、修改帐号等需要用户验证的操作。
 *
 * @author daniel <desheng.young@gmail.com>
 * @category sociax
 * @package sociax
 * @subpackage service
 * @version $Id$
 */

/**
 * 验证服务
 *
 * 验证服务（ValidationService）主要用于邀请注册、修改帐号等需要用户验证的操作。
 *
 * @author daniel <desheng.young@gmail.com>
 * @category sociax
 * @package sociax
 * @subpackage service
 * @version $Id$
 */
class ValidationService extends Service{

	/**
	 * 添加验证
	 *
	 * @access public
	 * @param int    $from_uid   验证的来源
	 * @param string $to_user    验证的目的地
	 * @param string $target_url 进行验证的url地址
	 * @param string $type       验证类型
	 * @param array  $data       附加数据
	 * @return boolean|string Return the target_url if saved successfully, or return false
	 */
	public function addValidation($from_uid, $to_user, $target_url, $type = '', $data = '') {
		$validation['type']			= $type;
		$validation['from_uid']		= $from_uid;
		$validation['to_user']		= $to_user;
		$validation['data']			= $data;
		$validation['is_active']	= 1;
		$validation['ctime']		= time();
		$vid = model('Validation')->add($validation);

		if ($vid) {
			$validation_code = $this->__generateCode($vid);
			$target_url	 = $target_url . "&validationid=$vid&validationcode=$validation_code";
			$res = model('Validation')->where("`validation_id`=$vid")->setField(array('code','target_url'), array($validation_code, $target_url));
			if ($res) {
				return $target_url;
			}else {
				return false;
			}
		}else {
			return $vid;
		}
	}

	/**
	 * 分发验证
	 *
	 * @access public
	 * @param int    $id              验证的ID（为0或留空时，自动从$_REQUEST获取）
	 * @param string $validation_code 验证码（为0或留空时，自动从$_REQUEST获取）
	 * @return void
	 */
	public function dispatchValidation($id = 0, $validation_code = 0) {
		if ( ! $this->getValidation($id, $validation_code) ) {
			redirect(SITE_URL, 5, '邀请码错误或已失效～');
		}
	}

	/**
	 * 取消邀请（即设置验证为失效）
	 *
	 * @access public
	 * @param int $id 验证的ID（为0或留空时，自动从$_REQUEST获取）
	 * @return boolean
	 */
	public function unsetValidation($id = 0) {
		$where = $id != 0 ? "`validation_id`=$id" : '`validation_id`=' . intval($_REQUEST['validationid']) . ' AND `code`="' . h($_REQUEST['validationcode']) . '"';
		return model('Validation')->where($where)->setField('is_active',0);
	}

	/**
	 * 获取邀请详情
	 *
	 * @access public
	 * @param int    $id   验证的ID（为0或留空时，自动从$_REQUEST获取）
	 * @param string $code 验证码（为0或留空时，自动从$_REQUEST获取）
	 * @return array|boolean
	 */
	public function getValidation($id = 0, $code = 0) {
		if ( $id == 0 && $code == 0 && !empty($_REQUEST['validationid']) && !empty($_REQUEST['validationcode']) ) {
			$where = '`validation_id`=' . intval($_REQUEST['validationid']) . ' AND `code`="' . h($_REQUEST['validationcode']) . '" AND `is_active`=1';
		}else if ($id != 0) {
			$id	   = intval($id);
			$where = $code == 0 ? "`validation_id`=$id AND `is_active`=1" : "`validation_id`=$id AND `is_active`=1 AND `code`='$code'";
		}else if ($code != 0) {
			$where = $id == 0 ? '`code`="' . h($code) . '" AND `is_active`=1'  : "`validation_id`=$id AND `is_active`=1 AND `code`='$code'";
		}else {
			return false;
		}

		return model('Validation')->field('validation_id AS validationid, type,from_uid,to_user,data,code,target_url,is_active,ctime')->where($where)->find();
	}

	/**
	 * 判断给定的验证ID和验证码是否合法
	 *
	 * @access public
	 * @param int    $id   验证的ID（为0或留空时，自动从$_REQUEST获取）
	 * @param string $code 验证码（为0或留空时，自动从$_REQUEST获取）
	 * @return bool
	 */
	public function isValidValidationCode($id = 0, $code = 0) {
		if ( ($id == 0 && $code != 0) || ($id != 0 && $code == 0) ) return false;

		if ($id == 0 && $code == 0) {
			$id	  = intval($_REQUEST['validationid']);
			$code = h($_REQUEST['validationcode']);
		}
		return model('Valiation')->where("`validation_id`=$id AND `code`='$code' AND `is_active`=1")->find();
	}

	/**
	 * 生成唯一的验证码
	 *
	 * @access private
	 * @param string $id 验证ID
	 * @return string
	 */
	private function __generateCode($id) {
		return md5($id.'thinksns#^!@*#%^!@#');
	}

 	/**
 	 * 运行服务，系统服务自动运行
 	 * @access public
 	 * @return void
 	 */
	public function run(){

	}

	/**
 	 * 启动服务，未编码
 	 * @access public
 	 * @return void
 	 */
	public function _start(){
		return true;
	}

	/**
 	 * 停止服务，未编码
 	 * @access public
 	 * @return void
 	 */
	public function _stop(){
		return true;
	}

	/**
 	 * 安装服务，未编码
 	 * @access public
 	 * @return void
 	 */
	public function _install(){
		return true;
	}

	/**
 	 * 卸载服务，未编码
 	 * @access public
 	 * @return void
 	 */
	public function _uninstall(){
		return true;
	}
}
?>