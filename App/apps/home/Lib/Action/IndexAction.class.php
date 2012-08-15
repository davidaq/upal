<?php
class IndexAction extends Action{
	
	public function index() {
		if (service('Passport')->isLogged())
			redirect(U('home/User/index'));
		else
			$this->showIndex();
	}

	private function showIndex(){
		
		unset($_SESSION['sina'], $_SESSION['key'], $_SESSION['douban'], $_SESSION['qq'],$_SESSION['open_platform_type']);

		//验证码
		$opt_verify = $this->_isVerifyOn('login');
		if ($opt_verify) {
			$this->assign('login_verify_on', $opt_verify);
		}

		$data['email'] = t($_REQUEST['email']);
		$data['uid']   = t($_REQUEST['uid']);
		$uids = array();

		// 正在热议 1小时缓存
		$data['hot_topic'] = D('Topic', 'weibo')->getHot();

		// 人气推荐
		$data['hot_user']  = D('Follow', 'weibo')->getTopFollowerUser();
		$data['hot_user'] = array_slice($data['hot_user'], 0, 10);
		$uids = array_merge($uids, getSubByKey($data['hot_user'], 'uid'));

		// 正在发生 (原创的文字微博)
		$data['lastest_weibo'] = D('Operate', 'weibo')->getLastWeibo();
		$data['lastest_weibo'] = array_slice($data['lastest_weibo'], 0, 6);
		$uids = array_merge($uids, getSubByKey($data['lastest_weibo'], 'uid'));
		$this->assign('since_id', empty($data['lastest_weibo']) ? 0 : $data['lastest_weibo'][0]['weibo_id'] );

		// 原创的图片微博
		$data['pic_weibo'] = S('S_login_pic_weibo');
		if(empty($data['pic_weibo'])) {
			$map['transpond_id'] = 0;
			$map['type']		 = 1;
			$map['isdel'] 		 = 0;
			$data['pic_weibo'] = D('Operate', 'weibo')->where($map)->order('weibo_id DESC')->limit(3)->findAll();
			S('S_login_pic_weibo', $data['pic_weibo'], 3600);
		}

		$uids = array_merge($uids, getSubByKey($data['pic_weibo'], 'uid'));
		foreach ($data['pic_weibo'] as $k => $v){
			$imageData = unserialize($v['type_data']);
			if(isset($imageData[0])) {
				$data['pic_weibo'][$k]['type_data'] = $imageData[0];
			} else {
				$data['pic_weibo'][$k]['type_data'] = $imageData;
			}
		}

		D('User', 'home')->setUserObjectCache(array_flip(array_flip($uids)));
		
		$this->assign($data);
		$this->assign('regInfo',model('Xdata')->lget('register'));
		$this->display();
	}

	private function _isVerifyOn($type='login'){
		// 检查验证码
		if($type!='login' && $type!='register') return false;
		$opt_verify = $GLOBALS['ts']['site']['site_verify'];
		return in_array($type, $opt_verify);
	}

	/**  前台 应用管理  **/
	
	public function addapp() {
		$dao = model('App');
		$all_apps  = $dao->getOpenAppByPage();
		$installed = isset($_SESSION['installed_app_user_'.$this->mid]) ? $_SESSION['installed_app_user_'.$this->mid] :M('user_app')->where('`uid`='.$this->mid)->field('app_id')->findAll();
		$installed = getSubByKey($installed, 'app_id');
		$this->assign($all_apps);
		$this->assign('installed', $installed);
		$this->setTitle(L('add_apps'));
		$this->display();
	}
	
	public function editapp() {
		// 重置用户的漫游应用的缓存
		global $ts;
		if ($ts['site']['my_status'])
			model('Myop')->unsetAllInstalledByUser($this->mid);
		
		$this->assign('has_order', array('local_app', 'myop_app'));
		$this->setTitle(L('manage_apps'));
		$this->display();
	}
	
	public function install() {
		$app = isset($_GET['app_name']) ? 
			   model('App')->getAppDetailByName(t($_GET['app_name'])) :
			   model('App')->getAppDetailById(intval($_GET['app_id']));
		if (!$app || $app['status'] == 0)
			$this->error(L('app_notexist'));
			
		$this->assign($app);
		$this->setTitle(''.L('install').'"' . $app['app_alias'] . '"'.L('app'));
		$this->display();
	}
	
	public function doInstall() {
		$_GET['app_id'] = intval($_GET['app_id']);
		$app = model('App')->getAppDetailById($_GET['app_id']);
		if (!app || $app['status'] == 0)
			$this->error(L(app_notexist));
			
		if (model('App')->addAppForUser($this->mid, $_GET['app_id'])) {
			model('App')->unsetUserInstalledApp($this->mid);
			$this->assign('jumpUrl', U($app['app_name'].'/'.$app['app_entry']));
			$this->success(L('install_success'));
		} else {
			$this->error(L('install_error'));
		}
	}
	
	public function uninstall() {
		$_GET['app_id'] = intval($_GET['app_id']);
		if (model('App')->where('`app_id`='.$_GET['app_id'])->getField('status') == '1')
			$this->error(L('default_app'));
		
		if (model('App')->removeAppForUser($this->mid, $_GET['app_id'])) {
			model('App')->unsetUserInstalledApp($this->mid);
			$this->assign('jumpUrl', U('home/Index/editapp'));
			$this->success(L('uninstall_success'));
		} else {
			$this->error(L('uninstall_error'));
		}
	}
	
	public function doOrder() {
		global $ts;
		$has_order  = array('local_app', 'myop_app');
		$table_name = array('local_app'=>'user_app', 'myop_app'=>'myop_userapp');
		$order_field_name = array('local_app'=>'display_order', 'myop_app'=>'displayorder');
		$app_id_name	  = array('local_app'=>'app_id', 'myop_app'=>'appid');
		
		// 现在的顺序 array('app_id'=>'order')
		$now_order = array();
		foreach ($has_order as $v)
			foreach ($ts['user_app'][$v] as $app)
				$now_order[$v][$app['app_id']] = $app['display_order'];
		
		$has_changed = false;
		foreach ($_POST as $field => $v) {
			if ( !in_array($field, $has_order) )
				continue ;
			foreach ($v as $order => $app_id) {
				$order  = intval($order);
				$app_id = intval($app_id);

				// 只更新有变化的顺序号
				if ($order == $now_order[$field][$app_id])
					continue ;
				// 提交修改
				if ( M($table_name[$field])->where("`{$app_id_name[$field]}`='$app_id' AND `uid`='{$this->mid}'")->setField($order_field_name[$field], $order) )
					$has_changed = true;
				else
					$this->error(L('save_error'));
			}
		}
		
		// 重置缓存
		model('App')->unsetUserInstalledApp($this->mid);
		global $ts;
		if ($ts['site']['my_status'])
			model('Myop')->unsetAllInstalledByUser($this->mid);
		
 		if ($has_changed)
			$this->success(L('save_success'));
		else
			$this->error(L('order_nochange'));
	}
}
