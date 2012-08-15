<?php
class SpaceAppShowAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '陈伟川';
	protected $site    = 'http://weibo.com/cchhuuaann';
	protected $info    = '个人主页应用展示';
	protected $pluginName = '个人主页应用展示';
	protected $tsVersion  = "2.5";                               // ts核心版本号


	private static $validApps = array(
	            'blog'=>'日志',
	            'vote'=>'投票',
	            'group'=>'群组',
	            'photo'=>'相册'
	        );

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
		$this->apply("home_space_tab", "home_space_tab");
		$this->apply("home_space_list", "home_space_list");
		$this->apply("home_space_middle", "home_space_middle");
    }

	/* 日志、投票、群组 */
	public function home_space_tab($param)
	{
	    $config= model('AddonData')->lget('space_app_show');
		$uid  = $param['uid'];
		$menu = & $param['menu'];
		$apps = array(
			'blog'  => '日志',
			'vote'  => '投票',
			'group' => '群组',
		);
        foreach ($apps as $key => $value) {
	        if (model('App')->isAppExistForUser($uid, $key) && in_array($key,$config['open'])) {
        		$menu[$key] = $value;
	        }
	        unset($apps[$key]);
        }
	}

	public function home_space_list($param)
	{
	    $config= model('AddonData')->lget('space_app_show');
		$uid = $param['uid'];
		$app = $param['type'];
		if(!in_array($app,$config['open'])) return;
		$function_name = '_' . $app;
		if (method_exists($this, $function_name)) {
			$this->$function_name($uid);
		}
	}

	private function _blog($uid)
	{
		// 日志列表
		$field = '*';
        $map            = array();
        if ($uid != $this->mid) {
        	$map['private'] = array('neq',2);
        }
        $map['status']  = 1;
        $map['uid']     = $uid;
		$data = M('blog')->field( $field )->where( $map )->order('id DESC')->findPage(20);
		$this->assign($data);
		$this->display('blog');
	}

	private function _vote($uid)
	{
		// 投票列表
		$field = '*';
        $map['uid']= $uid;
		$data = M('vote')->field($field)->where($map)->order('id DESC')->findPage(20) ;//选项
        $optDao = M('vote_opt');
        foreach($data['data'] as $k=>$v) {
            $opts = $optDao->where("vote_id = {$v['id']}")->order("id asc")->field("*")->limit( '0,2' )->findAll();
            $data['data'][$k]['opts'] = $opts;
        }
		$this->assign($data);
		$this->display('vote');
	}

	private function _group($uid)
	{
		require_once SITE_PATH . '/apps/group/Common/common.php';
		// 群组列表
        $data['grouplist'] = D('Group', 'group')->getAllMyGroup($uid, 1);
        $isLogin = empty($_SESSION['mid']) ? false : true;
        $this->assign('isLogin', $isLogin);
		$this->assign($data);
		$this->display('group');
	}

	/* 相册 */
	public function home_space_middle($param)
	{
	    $config= model('AddonData')->lget('space_app_show');

		$uid = $param['uid'];
		if (model('App')->isAppExistForUser($uid, 'photo')  && in_array('photo',$config['open'])) {
    		if ($uid == $this->mid) {
    		} else if ('unfollow' == getFollowState($uid, $this->mid)) {
                $photo_map['privacy'] = 1;
    		} else {
    			$photo_map['privacy'] = array('IN', '1,2');
    		}
    		$photo_map['userId'] = $uid;
    		$data['photo_list'] = D('Photo', 'photo')->where($photo_map)->order('id DESC')->limit(4)->findAll();
    		$data['photo_preview'] = model('Xdata')->get('photo:photo_preview');
        }
		if ($data['photo_list']) {
			$this->assign($data);
			$this->display('photo');
		}
	}

	public function install(){
	    $data['open']=array('photo','blog','group','vote');
	    return model('AddonData')->lput('space_app_show', $data)?true:false;
	}

	/* 后台管理 */
	public function adminMenu()
	{
	    return array('config' => '全局配置');
	}
	public function config(){
	    $config= model('AddonData')->lget('space_app_show');
	    $this->assign('valid',self::$validApps);
	    $this->assign('config',$config);
	    $this->display('config');
	}

	public function saveConfig(){
	    if(empty($_POST)) 
	    	$this->error('最少开启一个应用类型');
	    if(empty($_POST['open'])) $_POST['open'] = array();
	    $data = $_POST;
	    $res = model('AddonData')->lput('space_app_show', $data);
	    if ($res) {
	        $this->assign('jumpUrl', Addons::adminPage('config'));
	        $this->success();
	    } else {
	        $this->error();
	    }
	    exit;
	}

}