<?php
// +----------------------------------------------------------------------
// | ThinkSNS
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.thinksns.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: nonant <nonant@thinksns.com>
// +----------------------------------------------------------------------
//

/**
 * 动态服务
 */
class FeedService extends Service {
	public function __construct() {
	}
	
	/**
	 * 发布动态
	 * 
	 * @param string $type 动态类型, 必须与模版的类型相同, 使用下划线分割应用. 
	 * 					   如$type = "weibo_follow"定位至/apps/weibo/Language/cn/feed.php的"weibo_follow"
	 * @param array  $data 动态的数据数组, 该数组的key必须对应$type定位到数组的key
	 * @param int    $uid  发布动态的用户ID
	 * @return false|int   保存失败返回false, 否则返回该条数据在数据库的ID
	 */
	public function put($type, $data, $uid) {
		$data ['uid'] = ($uid) ? $uid : $_SESSION ['mid'];
		$data ['type'] = $type;
		$data ['data'] = serialize ( $data );
		$data ['ctime'] = time ();
		return M ( 'feed' )->data ( $data )->add ();
	}
	
	/**
	 * 获取给定用户看到的动态
	 * @param int    $uid   用户ID
	 * @param string $order 结果集顺序
	 * @return array
	 */
	public function get($uid, $order = 'feed_id DESC') {
		$prefix = C ( 'DB_PREFIX' );
		$list = M ( 'feed' )->where ( "uid IN (SELECT fid FROM {$prefix}weibo_follow where uid=$uid) OR uid=$uid" )->order ( $order )->findPage ( 20 );
		foreach ( $list ['data'] as $key => $value ) {
			$list ['data'] [$key] = array_merge ( $value, $this->_parseTemplate ( $value ) );
		}
		return $list;
	}
	
	
	/**
	 * 根据给定条件获取动态
	 * @param array|string $map   查询条件, 必须使用ThinkPHP的查询规则
	 * @param int          $limit 分页中每页的数据条数
	 * @param string       $order 结果集顺序
	 * @return array
	 */
	public function getFeedByMap($map, $limit = 20, $order = 'feed_id DESC') {
		$list = M ( 'feed' )->where ( $map )->order ( $order )->findPage ( $limit );
		foreach ( $list ['data'] as $key => $value ) {
			$list ['data'] [$key] = array_merge ( $value, $this->_parseTemplate ( $value ) );
		}
		return $list;
	}
	
	/**
	 * 枚举动态类型
	 * @return array
	 */
	public function enumerateType() {
		$sql = "SELECT `type` FROM " . C ( 'DB_PREFIX' ) . "feed GROUP BY `type`";
		$res = M ( '' )->query ( $sql );
		return getSubByKey ( $res, 'type' );
	}
	
	/**
	 * 删除单条微博
	 * @param int $uid     用户ID
	 * @param int $feed_id 动态ID
	 * @return boolean
	 */
	public function deleteOneFeed($uid, $feed_id) {
		$map ['uid'] = $uid;
		$map ['feed_id'] = $feed_id;
		return M ( 'feed' )->where ( $map )->delete ();
	}
	
	/**
	 * 删除多条微博
	 * @param int|array $ids 动态ID
	 */
	public function deleteFeed($ids) {
		$ids = is_array ( $ids ) ? $ids : explode ( ',', $ids );
		if (empty ( $ids ))
			return false;
		$map ['feed_id'] = array ('in', $ids );
		return M ( 'feed' )->where ( $map )->delete ();
	}
	
	/**
	 * 解析模板
	 */
	public function _parseTemplate($i_data) {
		
		if (false == $i_data ['data'] = unserialize ( $i_data ['data'] )) {
			$i_data ['data'] = unserialize ( stripslashes ( $i_data ['data'] ) );
		}
		
		$replace ["{actor}"] = '<a href="' . U ( "home/space/index", array ("uid" => $i_data ['uid'] ) ) . '" target="_blank">' . getUserName ( $i_data ['uid'] ) . '</a>';
		if ($i_data)
			extract ( $i_data ['data'], EXTR_OVERWRITE );
		unset ( $i_data ['data'] );
		extract ( $i_data, EXTR_OVERWRITE );
		$template_type = explode ( '_', $i_data ['type'] );
		$template = require (SITE_PATH . '/apps/' . $template_type [0] . '/Language/cn/feed.php');
		
		$return ['title'] = str_replace ( array_keys ( $replace ), array_values ( $replace ), $template [$type] ['title'] );
		$return ['body'] = str_replace ( array_keys ( $replace ), array_values ( $replace ), $template [$type] ['body'] );
		return $return;
	}
	
	//运行服务，系统服务自动运行
	public function run(){

	}
	
	//启动服务，未编码
	public function _start() {
		return true;
	}
	
	//停止服务，未编码
	public function _stop() {
		return true;
	}
	
	//安装服务，未编码
	public function _install() {
		return true;
	}
	
	//卸载服务，未编码
	public function _uninstall() {
		return true;
	}
}
?>