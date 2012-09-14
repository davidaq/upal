<?php
/**
 * 用户统计模型
 * 
 * @author nonant
 */
class GroupUserCountModel extends Model {
	protected $tableName = 'group_user_count';
	/**
	 * 添加统计数据
	 * 
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目
	 * @param int		$IncNum 变化值  默认1
	 * @return void
	 */
	function addCount($uid, $type, $IncNum=1){
		global $ts;
		if($uid==$ts['user']['uid']){
			//return false;
		}
		if( is_array($uid) ){
			foreach ($uid as $k=>$v){
				$this->addCount($v,$type);
			}
		}else{
			if(!$uid) return false;
			if( $this->where('uid='.$uid)->find() ){
				$this->setInc($type,'uid='.$uid);
			}else{
				$data['uid'] = $uid;
				$data[$type] = 1;
				$this->add($data);
			}
		}
	}
	/**
	 * 归0
	 * 
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目
	 * @return void
	 */
	function setZero($uid,$type){
		return $this->setField($type,0,'uid='.$uid);
	}
	/**
	 * 获取统计值
	 * 
	 * @param int|array $uid    用户ID
	 * @param string 	$type	统计的项目，为空将返回所有统计项目结果
	 * @return mixed
	 */
	public function getUnreadCount($uid, $type = '') {
		$map['uid'] = $uid;
		$res = $this->field($type)->where($map)->find();
		return empty($type) ? $res : $res[$type];
	}
}