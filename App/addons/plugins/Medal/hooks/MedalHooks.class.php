<?php
class MedalHooks extends Hooks
{
	// 用户勋章设置
	public function home_account_tab(&$param)
	{
		$param['menu'][] = array('act' => 'medal', 'name' => '勋章管理','param'=>array('addon'=>'Medal','hook'=>'home_account_show'));
	}

	public function home_account_show($param)
	{
    	$_GET['type'] = $_GET['type'] == 'manage' ? 'manage' : 'my';

    	if ($_GET['type'] == 'my') {
    		$data = $this->model('Medal')->getMedalWidgetData($this->mid, false, false);
    	}else {
    		$data = $this->model('Medal')->getMedalWidgetData($this->mid, false, true);
    	}

    	$this->assign($data);
    	$this->assign('type', $_GET['type']);
    	$this->display('medal_setting');
    }

    public function home_account_do($param) {
    	// medal_manage主要是为了防止表单重复提交 :(
    	if ($_POST['medal_manage'] != '1') {
    		$this->error('参数错误');
    	}

    	$MedalDao = $this->model('Medal');
    	$_POST['show_ids'] = explode(',', t($_POST['show_ids']));

    	// 显示OR隐藏仅针对用户已获得的勋章, 用户未获得的勋章(即received_time<=0)不做变化
    	$show_ids = array();
    	$hide_ids = array();
    	$data = $MedalDao->getMedalWidgetData($this->mid, false, true);
    	foreach ($data['user_medal'] as $v) {
    		if (in_array($v['medal_id'], $_POST['show_ids'])) {
    			$show_ids[] = $v['medal_id'];
    		}else {
    			$hide_ids[] = $v['medal_id'];
    		}
    	}

    	if ( !empty($show_ids) ) {
	    	$MedalDao->setUserMedalStatus($this->mid, $show_ids, 1);
    	}
    	if ( !empty($hide_ids) ) {
	    	$MedalDao->setUserMedalStatus($this->mid, $hide_ids, 0);
    	}

    	$this->assign('jumpUrl', U('home/Account/medal', array('type'=>'manage','addon'=>'Medal','hook'=>'home_account_show')));
    	$this->success('操作成功');
    }

	// 勋章展示
	public function home_index_left_avatar_bottom()
	{
		$this->assign('uid', $this->mid);
		$this->display('medal_list_ajax');
	}

	public function home_space_right_middle($param)
	{
		$this->assign('uid', $param['uid']);
		$this->display('medal_list_ajax');
	}

	public function hook_ajax()
	{
		if (isset($_GET['do']) && 'medalCloseAlert' == $_GET['do']) { // 关闭提示消息
			$_POST['medal_id']	= intval($_POST['medal_id']);
			$medal_path_name	= M('medal')->where('`medal_id`='.$_POST['medal_id'])->getField('path_name');
			self::medal($medal_path_name)->closeMedalAlert($this->mid, $_POST['medal_id']);
		} else {
			$data['uid']		= $_POST['uid'] ? intval($_POST['uid']) : $this->mid;
			$data['show_alert']	= $this->mid == $data['uid'] ? 1 : 0;

			$medal_data = $this->model('Medal')->getMedalWidgetData($data['uid']);
			$data = array_merge($data, $medal_data);
			unset($medal_data);
			$this->assign($data);
			$this->display('medal_list');
		}
	}

	// 获取勋章类的一个实例
	static public function medal($name) {
		if ( empty($name) )
			return ;

		static $_medal = array();
		if ( isset($_medal[$name]) )
			return $_medal[$name];

		$classname	= ucfirst($name) . 'Medal';
		$filename	= $classname . '.class.php';
		$basepath	= SITE_PATH . '/addons/plugins/Medal/lib';
		$filepath	= $basepath . '/' . $name . '/' . $filename;

		// 加载基类
		if ( ! class_exists('BaseMedal') )
			require_cache($basepath . '/BaseMedal.class.php');
		if ( file_exists($filepath) )
			require_cache($filepath);

		if ( class_exists($classname) ) {
			$_medal[$name]	= new $classname();
			return $_medal[$name];
		}else {
			throw_exception(L('_CLASS_NOT_EXIST_').':'.$classname);
		}
	}

	/* 插件后台配置项 */
	public function medalAdmin() {
		$this->assign('medal', $this->model('Medal')->getInstalledMedal());
		$this->display('medalAdmin');
	}

	public function installMedal() {
		// 已安装的插件
		$installed		= $this->model('Medal')->getInstalledMedal();
		$installed		= getSubByKey($installed, 'path_name');

		// 全部插件
		require_once SITE_PATH . '/addons/libs/Io/Dir.class.php';
		$dirs	= new Dir($this->path . '/lib');
		$dirs	= $dirs->toArray();

		// 获取未安装的插件
		$uninstalled	= array();
		foreach($dirs as $v)
			if ( $v['isDir'] && !in_array($v['filename'], $installed) )
				if ( $info = $this->_getPluginInfo('/lib/'.$v['filename']) )
					$uninstalled[]	= $info;

		$this->assign('uninstalled', $uninstalled);
		$this->display('installMedal');
	}

	public function doInstallMadel() {
		$_GET['path_name'] = t($_GET['path_name']);

		$info = $this->_getPluginInfo('/lib/'.t($_GET['path_name']));
		if ( !$info ) {
			return false;
		}else {
			// 检查是否已安装
			$installed		= $this->model('Medal')->getInstalledMedal();
			$installed		= getSubByKey($installed, 'path_name');
			if ( in_array($_GET['path_name'], $installed) )
				return false;

			$info['is_active']	= 1;
			$info['ctime']		= time();
			if ( ( $medal_id = M('medal')->add($info) ) ) {
				// 为排序方便，设置 display_order = medal_id

				$_LOG['uid'] = $this->mid;
				$_LOG['type'] = '1';
				$data[] = '扩展 - 插件 - 勋章管理 - 安装勋章';
				$data[] = $_GET['path_name'];
				$_LOG['data'] = serialize($data);
				$_LOG['ctime'] = time();
				M('AdminLog')->add($_LOG);

				M('medal')->where('`medal_id`='.$medal_id)->setField('display_order', $medal_id);
				$this->assign('jumpUrl', Addons::adminPage('installMedal'));
				$this->success('安装成功');
			}else {
				$this->error('安装失败');
			}
		}
	}

	public function doSetMedalStatus($param)
	{
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '扩展 - 插件 - 勋章管理 - 设置勋章状态';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$param['result']['status'] = $this->model('Medal')->setMedalStatus(intval($_POST['id']), intval($_POST['status'])) ? '1' : '0';
	}

	public function doMedalOrder($param)
	{
		$_POST['id']	 = intval($_POST['id']);
		$_POST['baseid'] = intval($_POST['baseid']);
		if ( $_POST['id'] <= 0 || $_POST['baseid'] <= 0 ) {
			$param['result']['status'] = 0;
			return;
		}
		$dao = M('medal');
		$map['medal_id'] = array('in', array($_POST['id'], $_POST['baseid']));
		$res = $dao->where($map)->field('medal_id,display_order')->findAll();
		if ( count($res) != 2 ) {
			$param['result']['status'] = 0;
			return;
		}

		//转为结果集为array('id'=>'order')的格式
    	foreach($res as $v) {
    		$order[$v['medal_id']]	= intval($v['display_order']);
    	}
    	unset($res);

    	//交换order值
    	$res = 		   $dao->where('`medal_id`=' . $_POST['id'])->setField( 'display_order', $order[$_POST['baseid']] );
    	$res = $res && $dao->where('`medal_id`=' . $_POST['baseid'])->setField( 'display_order', $order[$_POST['id']] );

    	$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '扩展 - 插件 - 勋章管理 - 设置勋章排序';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

    	$param['result']['status'] = ($res ? 1 : 0);
	}

	public function uninstallMedal() {
		if ( ($medal_id = intval($_GET['medal_id'])) <= 0 )
			return false;

		$this->assign('jumpUrl', Addons::adminPage('medal'));
		if ( $this->model('Medal')->deleteMedal($medal_id) ){
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '2';
			$data[] = '扩展 - 插件 - 勋章管理 - 卸载勋章';
			$data[] = $this->model('Medal')->where( 'medal_id='.$_GET['medal_id'] )->field('path_name')->find();
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			$this->success('卸载成功');
		}else{
			$this->error('卸载失败');
		}
	}

	private function _getPluginInfo($path_name = '', $using_lowercase = true) {
		$filename = $this->path . $path_name . '/info.php';

		if ( is_file($filename) ) {
			$info = include_once $filename;
			return $using_lowercase ? array_change_key_case($info) : array_change_key_case($info,CASE_UPPER);
		}else {
			return null;
		}
	}
}