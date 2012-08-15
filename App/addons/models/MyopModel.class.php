<?php
/**
 * 漫游应用模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class MyopModel extends Model {

	/**
	 * 获取指定用户已安装的Myop应用列表(分页)
	 * 
	 * @param int    $uid   用户ID
	 * @param int    $limit 每页显示条数 默认20
	 * @param string $order 排序,默认 展示顺序升序,应用ID升序
	 * @return array
	 */
	public function getInstalledByUser($uid, $limit = '20', $order = 'displayorder ASC, appid ASC') {
		$map['uid']	= $uid;
		return M('myop_userapp')->where($map)->order($order)->findPage($limit);
	}

    /**
     * 获取指定用户已安装的Myop应用列表(不分页)
     * 
     * 缓存的清理: 用户编辑应用 + 应用排序 + 用户添加漫游应用
     * 
     * @param int    $uid   用户ID
     * @param string $order 排序,默认 展示顺序升序,应用ID升序
     * @return array
     */
	public function getAllInstalledByUser($uid, $order = 'displayorder ASC, appid ASC')
	{
		$cache_id = 'myop_app_user_' . $uid;
		if (!isset($_SESSION[$cache_id])) {
			$map['uid'] = $uid;
			$res = M('myop_userapp')->where($map)->order($order)->findAll();
			$_SESSION[$cache_id] = $res ? $res : array();
		}
		return $_SESSION[$cache_id];
	}
	
	/**
	 * 重置用户已安装的Myop应用列表
	 * 
	 * @param int $uid
	 */
	public function unsetAllInstalledByUser($uid)
	{
		$cache_id = 'myop_app_user_' . $uid;
		unset($_SESSION[$cache_id]);
	}

	/**
	 * 获取默认应用列表(分页)
	 * 
	 * @param int    $limit 每页显示条数 默认20
     * @param string $order 排序,默认 展示顺序升序,应用ID升序
     * @return array
	 */
	public function getDefaultApp($limit = '20', $order = 'displayorder ASC, appid ASC') {
		$map['flag']	= 1;
		return M('myop_myapp')->where($map)->order($order)->findPage($limit);
	}

    /**
     * 获取默认应用列表(不分页)
     * 
     * @param string $order 排序,默认 展示顺序升序,应用ID升序
     * @return array
     */
	public function getAllDefaultApp($order = 'displayorder ASC, appid ASC')
	{
		$cache_id = '_model_myop_default_apps';
		if (($default_app = F($cache_id)) === false) {
			$map['flag'] = 1;
			$default_app = M('myop_myapp')->where($map)->order($order)->findAll();
			$default_app = $default_app ? $default_app : array();
			
			F($cache_id, $default_app);
		}
		
		return $default_app;
	}
}