<?php
/**
 * 应用模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class AppModel extends Model {
	protected $tableName = 'app';
	static public $AllApp = array();

	/**
	 * 检测APP是否安装
	 * 
	 * @param string $app_name APP英文名字
	 * @param int    $app_id   APP ID, 当指定app_id时, 该app将被排除
	 * @return boolean
	 */
	public function isAppNameExist($app_name, $app_id = 0) {
		$appInfo = $this->getAppDetailByName($app_name);
		return $appInfo['app_id'] != $app_id ? true : false; 
	}

	/**
	 * 根据应用名检测应用是否开启
	 * 
	 * @param string $app_name
     * @return boolean true:开启 | false:关闭
	 */
	public function isAppNameActive($app_name) {
		$appInfo = $this->getAppDetailByName($app_name);
		return $appInfo['status'] != 0 ? true : false;
	}


    /**
     * 根据应用ID检测应用是否开启
     * 
     * @param int $app_id
     * @return boolean true:开启 | false:关闭
     */
	public function isAppIdActive($app_id) {
		$map['app_id'] = $app_id;
		$map['status'] = array('neq', 0);
		return $this->where($map)->find() ? true : false;
	}

	/**
	 * 检查给定节点是否为管理后台
	 * 
	 * @param string $app_name 应用名
	 * @param string $mod_name Action控制器名
	 * @return boolean
	 */
	public function isAppAdmin($app_name, $mod_name) {
		$mod_name = $mod_name ? $mod_name : 'Admin';
		$map['app_name'] 	= $app_name;
		$admin_entry = $this->where($map)->field('admin_entry')->find();
		$admin_entry = explode('/', $admin_entry['admin_entry']);
		return $admin_entry[0] == $mod_name;
	}
	
	/**
	 * 获取所有应用
	 * 
	 * @param string $field 字段名
	 * @return array
	 */
	public function getAllApp($field = '*') {
		if(!empty(self::$AllApp)){ return self::$AllApp;}
		if( ( $cache = F('Cache_App') )===false){
			$cache = $this->order('display_order ASC')->findAll();			
			F('Cache_App',$cache);
			self::$AllApp = $cache;
		}
		return $cache;
	}

	/**
	 * 获取应用列表
	 * 
	 * @param int    $limit 默认20
	 * @param string $field 默认*
	 * @param string $order 默认 展示顺序升序,应用ID升序
	 * @return array
	 */
	public function getAllAppByPage($limit = 20, $field = '*', $order = 'display_order ASC') {
		return $this->field($field)->order($order)->findPage($limit);
	}

	/**
	 * 获取非关闭的应用列表
	 * 
     * @param int    $limit 默认20
     * @param string $field 默认*
     * @param string $order 默认 展示顺序升序,应用ID升序
     * @return array
	 */
	public function getOpenAppByPage($limit = 20, $field = '*', $order = 'display_order ASC') {
		return $this->where('`status`<>0')->field($field)->order($order)->findPage($limit);
	}

	/**
	 * 获取有后台管理的应用
	 * 
	 * @param string $field 默认*
     * @param string $order 默认 展示顺序升序,应用ID升序
     * @return array
	 */
	public function getAdminApp($field = '*', $order = 'display_order ASC') {
		$map['admin_entry'] = array('neq', '');
		return $this->where($map)->field($field)->order($order)->findAll();
	}

	/**
	 * 获取应用的详细信息(根据ID)
	 * 
	 * @param int    $app_id 应用ID
	 * @param string $field  默认*
	 * @return array
	 */
	public function getAppDetailById($app_id, $field = '*') {
		$appList = $this->getAllApp();
		$app = array();
		$app_id = is_array($app_id) ? $app_id : explode(',', $app_id);
		foreach ($appList as $v){
			if(in_array($v['app_id'],$app_id)){
				$app[] = $v;
			}
		}
		return count($app_id) <= 1 ? $app[0] : $app;
	}

    /**
     * 获取应用的详细信息(根据应用名)
     * 
     * @param int    $app_name 应用名
     * @return array
     */	
	public function getAppDetailByName($app_name) {
		$appList = $this->getAllApp();
		foreach ($appList as $v){
			if($v['app_name'] == $app_name){
				return $v;
			}
		}
		return false;
	}

	/**
	 * 删除应用信息
	 * 
	 * @param int $app_id 应用ID
	 */
	public function deleteApp($app_id) {
		$map['app_id'] = intval($app_id);
		$this->unsetSiteDefaultApp();
		return $this->where($map)->delete();
	}

	/**
	 * 用户添加应用
	 * 
	 * @param int $uid    用户ID
	 * @param int $app_id 应用ID
	 * @return boolean
	 */
	public function addAppForUser($uid, $app_id) {
		$this->removeAppForUser($uid, $app_id);
		
		$data['app_id'] = $app_id;
		$data['uid']	= $uid;
		$data['ctime']	= time();
		return M('user_app')->add($data);
	}

    /**
     * 检测APP用户是否可用
     * 
     * @param int $uid    用户ID
     * @param int $app    应用ID或应用名
     * @return boolean
     */
    public function isAppExistForUser($uid, $app) {
    	if (is_numeric($app)) {
	        $map_app = " AND a.app_id={$app} ";
    	} else {
            $map_app  = ' AND a.app_name="' . t($app) . '" ';
    	}
        $map['uid']     = $uid;
        $sql = "SELECT u.user_app_id FROM {$this->tablePrefix}app AS a " .
               "LEFT JOIN {$this->tablePrefix}user_app AS u ON u.uid = '{$uid}' AND u.app_id = a.app_id " .
               "WHERE (a.status=1 OR a.status=2 AND u.app_id<>'') AND a.app_entry<>'' {$map_app}";
        return $this->query($sql) ? true : false;
    }

    /**
     * 用户删除应用
     * 
     * @param int $uid    用户ID
     * @param int $app_id 应用ID
     * @return boolean
     */
    public function removeAppForUser($uid, $app_id) {
        $map['uid']     = $uid;
        $map['app_id']  = $app_id;
        return M('user_app')->where($map)->delete();
    }
	
	/**
	 * 获取站点的默认应用 (注: 非home, weibo等核心应用)
	 */
	public function getSiteDefaultApp($hide_invisible_app = true)
	{
		$appList = $this->getAllApp();
		$app = array();
		foreach($appList as $v){
			if($v['status'] != 1){
				continue;
			}
			if($hide_invisible_app){
				$v['app_entry']!='' && $app[] = $v;
			}else{
				$app[] = $v;			
			}
		}
		return $app;
	}
	
	/**
	 * 重置站点的默认应用
	 */
	public function unsetSiteDefaultApp()
	{
		F('Cache_App', null);
	}
	
	/**
	 * 获取用户安装的可选应用列表
	 * 
	 * @param int $uid 用户ID
	 */
	public function getUserInstalledApp($uid)
	{
		$cache_id = 'installed_app_user_' . $uid;
		if (!isset($_SESSION[$cache_id])) {
			$sql = "SELECT a.*,u.display_order AS `user_display_order` FROM {$this->tablePrefix}user_app AS u " . 
				   "INNER JOIN {$this->tablePrefix}app AS a ON u.uid = '{$uid}' AND u.app_id = a.app_id " . 
				   "WHERE a.status = '2' AND a.app_entry <> '' " .
				   "ORDER BY u.display_order ASC,a.display_order ASC,a.app_id ASC";
			$_SESSION[$cache_id] = $this->query($sql);
		}
		return $_SESSION[$cache_id];
	}
	
	/**
	 * 重置用户安装的可选应用列表
	 * 
	 * @param int $uid 用户ID
	 */
	public function unsetUserInstalledApp($uid)
	{
		$cache_id = 'installed_app_user_' . $uid;
		unset($_SESSION[$cache_id]);
	}

	/**
	 * 根据appid重置appCache
	 *
	 */
	public function resetAppCache($app_id){
		$this->unsetSiteDefaultApp();
		return $this->getAppDetailById($app_id);
	}
}