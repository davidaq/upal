<?php
/**
 * 系统权限服务
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class SystemPopedomService extends Service {

	/**
	 * 检查给定用户是否拥有给定节点的权限
	 * 
	 * @param int	 $uid               用户ID(默认当前用户)
	 * @param string $node              节点, 格式为"APP_NAME/MOD_NAME/ACT_NAME"(默认当前节点)
	 * @param bool	 $has_admin_popedom 当没有设置admin节点权限时的是否默认拥有admin权限 ( true:有权限 false:没有权限 )
	 */
	public function hasPopedom($uid = null, $node = null, $has_admin_popedom = true) {
		global $ts;
		
		$uid  	= isset($uid) ? intval($uid) : $_SESSION['mid'];
		
		// 超级管理员拥有所有权限
		if ( $uid == $_SESSION['mid'] && $_SESSION['userInfo']['admin_level'] == '1' )
			return true;
		
		$node 	= isset($node) ? explode('/', $node) : array($ts['_app'], $ts['_mod'], $ts['_act']);
		
		$gid = $this->getGidByNode($node);
		
		if ( empty($gid) ) {
			return $has_admin_popedom ? true : $app != 'admin';
		}else {
			$userGid = model('UserGroup')->getUserGroupId($uid);
			return count(array_intersect($gid,$userGid))>0  ? true : false;
		}
	}
	
	/**
	 * 获取某个节点权限的用户组
	 *
	 * @param unknown_type $node 节点
	 * @return unknown $gid 用户组ID
	 */
	public function getGidByNode($node){
		if( ($cache = F('Cache_Node')) === false){
			$prefix = C('DB_PREFIX');
			$sql = "select  a.*,b.user_group_id from {$prefix}node a left join {$prefix}user_group_popedom b on  a.node_id = b.node_id";
			$cache = M('')->query($sql);
			F('Cache_Node',$cache);
		}
		$gid = array();
		foreach ($cache as $v){
			if(empty($v['user_group_id'])) continue;
			if($v['app_name'] == $node[0]){
				if($v['mod_name'] == '*'){
					$gid[] = $v['user_group_id']; 
					continue;
				}
				if($v['mod_name'] == $node[1]){
					if($v['act_name'] == $node[2] || $v['act_name'] == '*')
					$gid[] =  $v['user_group_id'];
				}
			}
		}
		return $gid;
	}
	
	/**
	 * 清楚节点权限缓存
	 *
	 * @return unknown
	 */
	
	public function delNodeCache(){
		return F('Cache_Node',null);
	}
	
	public function run() {

	}

	public function _start() {

	}

	public function _stop() {

	}

	public function _install() {

	}

	public function _uninstall() {

	}
}