<?php
/**
 * 邀请模型
 * 
 * @author nonant
 */
class InviteModel extends Model{
	var $tableName = 'invitecode';
	/**
	 * 获未邻取的邀请码数量
	 * 
	 * @param int $uid 用户ID
	 * @return int
	 */
	function getReceiveCount( $uid ){
		return $this->where("uid=$uid AND is_received=0 AND is_used=0")->count();
	}
	/**
	 * 领取邀请码，将未邻取的邀请码标记为已领取
	 * 
	 * @param int $uid 用户ID
	 * @return boolean
	 */
	function getReceiveCode( $uid ){
		return $this->setField('is_received',1,"uid=$uid AND is_received=0 AND is_used=0");
	}
	/**
	 * 获取邀请码列表
	 * 
     * @param int $uid 用户ID
     * @return array
	 */
	function getInviteCode($uid){
		$inviteSite = $this->getSet();
		if($inviteSite['invite_set']=='invitecode'){
			return $this->where("uid=$uid AND is_used=0 AND is_received=1")->findall();
		}else if($inviteSite['invite_set']=='common'){
			$data[0]['code'] = $uid;
			return $data;
		}else{
			return '';
		}
		
	}
	/**
	 * 生成邀请码
	 * 
     * @param int $uid 用户ID
	 * @param int $num 邀请码数量
	 * @return boolean
	 */
   function sendcode($uid,$num){
   		if(!$uid) return '';
		for ($i=1;$i<=$num;$i++){
			$invitecode = md5($uid.time()."^@*&@*HF*@&#&@*(*@#".$i);
			$code[] = "($uid,'$invitecode',0)";
		}
		if($code){
			$this->query("INSERT INTO ".C('DB_PREFIX')."invitecode (uid,code,is_used) VALUES ".implode(',',$code));
		}
			//$sql = "INSERT INTO ".C('DB_PREFIX')."invitecode "
	}
	/**
	 * 返回邀请设置
	 * 
	 * @return array invite_set三种状态:close-关闭邀请,invitecode-使用邀请码,common-普通邀请
	 */
	function getSet(){
		$inviteset = model('Xdata')->lget('inviteset');
		$data['invite_set']      = ($inviteset['invite_set']) ? $inviteset['invite_set'] : 'common';
		return $data;
	}
	/**
	 * 获取邀请码信息
	 * 
	 * @param string $invitecode 邀请码
	 * @return array
	 */
	function checkInviteCode($invitecode){
		return $this->where("code='{$invitecode}'")->find();
	}
	/**
	 * 设置邀请码失效,即设置为已使用
	 * 
	 * @param string $invitecode 邀请码
	 * @return boolean
	 */
	function setInviteCodeUsed($invitecode){
		return $this->setField('is_used',1,"code='{$invitecode}'");
	}
}
?>