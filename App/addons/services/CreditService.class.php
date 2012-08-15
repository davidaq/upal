<?php
/**
 * 积分服务
 *
 * 提供积分获取、积分设置等服务
 *
 * @author thinksns
 *
 */
class CreditService extends Service {
	//所有设置的值
	var $info;
	var $creditType;

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @author melec制作
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
    	if (($this->creditType = F('_service_credit_type')) === false) {
			$this->creditType = M('credit_type')->order('id ASC')->findAll();
			F('_service_credit_type', $this->creditType);
    	}
    }

	//服务初始化
	public function init() {
	}

	//运行服务，系统服务自动运行
	public function run() {
	}

	/**
	 * 获取所有积分类型
	 *
	 * @return array
	 */
	public function getCreditType() {
		return $this->creditType;
	}

	/**
	 * 获取用户积分
	 *
	 * 返回积分值的数据结构
	 * <code>
	 * array(
	 * 	'score'     =>array(
	 * 		'credit'=>'1',
	 * 		'alias' =>'积分',
	 * 	),
	 * 	'experience'=>array(
	 * 		'credit'=>'2',
	 * 		'alias' =>'经验',
	 * 	),
	 * 	'类型'      =>array(
	 * 		'credit'=>'值',
	 * 		'alias' =>'名称',
	 * 	),
	 * )
	 * </code>
	 *
	 * @param int $uid
	 * @return boolean|array 用户的所有积分
	 */
	public function getUserCredit($uid) {
		if(empty($uid))
			return false;

		$userCreditInfo = M('credit_user')->where("uid={$uid}")->find();// 用户积分
		foreach($this->creditType as $v){
			$userCredit[$v['name']] = array('credit'=>intval($userCreditInfo[$v['name']]),'alias'=>$v['alias']);
		}
		return $userCredit;
	}

	/**
	 * 操作用户积分
	 *
	 * @param int          $uid    用户ID
	 * @param array|string $action 系统设定的积分规则的名称
	 * 							   或临时定义的一个积分规则数组，例如array('score'=>-4,'experience'=>3)即socre减4点，experience加三点
	 * @param string|int   $type   reset:按照操作的值直接重设积分值，整型：作为操作的系数，-1可实现增减倒置
	 * @return Object
	 */
	public function setUserCredit($uid,$action,$type=1) {
		if(!$uid){
			$this->info = false;
			return $this;
		}
		if(is_array($action)) {
			$creditSet = $action;
		}else {
			// 获取配置规则
			$credit_ruls = $this->getCreditRules();
			foreach ($credit_ruls as $v)
				if ($v['name'] == $action)
					$creditSet = $v;
		}
		if(!$creditSet){
			$this->info = '积分规则不存在';
			return $this;
		}
		$creditUserDao = M('credit_user');
		$creditUser    = $creditUserDao->where("uid={$uid}")->find(); // 用户积分
		//计算
		if($type=='reset') {
			foreach($this->creditType as $v){
				$creditUser[$v['name']] = $creditSet[$v['name']];
			}
		}else{
			$type = intval($type);
			foreach($this->creditType as $v){
				$creditUser[$v['name']] = $creditUser[$v['name']]+($type*$creditSet[$v['name']]);
			}
		}
		$creditUser['uid'] || $creditUser['uid'] = $uid;
		$res = $creditUserDao->save($creditUser) || $res = $creditUserDao->add($creditUser);//首次进行积分计算的用户则为插入积分信息

		//用户进行积分操作后，登录用户的缓存将修改
		 S('S_userInfo_'.$uid,null);
		//$userLoginInfo = S('S_userInfo_'.$uid);
		//if(!empty($userLoginInfo)) {
		//	$userLoginInfo['credit']['score']['credit'] = $creditUser['score'];
		//	$userLoginInfo['credit']['experience']['credit'] = $creditUser['experience'];
		//	S('S_userInfo_'.$uid, $userLoginInfo);
		//}

		if($res){
			$this->info = $creditSet['info'];
			return $this;
		}else{
			$this->info = false;
			return $this;
		}
	}

	/**
	 * 获取积分操作结果
	 *
	 * return string
	 */
	public function getInfo(){
		return $this->info;
	}

	/**
	 * 获取所有系统积分规则
	 *
	 */
	public function  getCreditRules() {
		if (($res = F('_service_credit_rules')) === false) {
			$res  = M('credit_setting')->order('type ASC')->findAll();
			F('_service_credit_rules', $res);
		}
		return $res;
	}

	/* 后台管理相关方法 */

	//启动服务，未编码
	public function _start(){
		return true;
	}

	//停止服务，未编码
	public function _stop(){
		return true;
	}

	//卸载服务，未编码
	public function _install(){
		return true;
	}

	//卸载服务，未编码
	public function _uninstall(){
		return true;
	}
}
?>