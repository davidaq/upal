<?php
/**
 * VisitorAddons
 * 来访的人插件
 * @uses VisitorAddons
 * @package
 * @version 1.0
 * @copyright 2001-2013 小川
 * @author 小川 <chenweichuan@zhishisoft.com>
 * @license PHP Version 5.2
 */
class VisitorAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '陈伟川';
	protected $site    = 'http://weibo.com/cchhuuaann';
	protected $info    = '显示空间来访者，提升互动';
	protected $pluginName = '最近来访';
	protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子,钩子和本类的方法是怎样的映射关系
     * @access public
     * @return void
     */
	public function getHooksInfo(){
		$this->apply("home_space_middle","home_space_middle");
		$this->apply("home_index_right_top","home_index_right_top");
		$this->apply("home_space_right_bottom","home_space_right_bottom");
	}

	public function home_space_middle($param)
	{
	    $uid = $param['uid'];
		if ($this->mid > 0 && $uid != $this->mid) {
	        // 记录访问时间
            M()->execute("replace into ".C('DB_PREFIX')."user_visited (`uid`,`fid`,`ctime`) VALUES ('".$this->mid."','".$uid."',".time().")");
			// 空间被访问积分
			X('Credit')->setUserCredit($uid, 'space_visited');
		}
	}

	// 首页 - 最近来访的人
	public function home_index_right_top()
	{
	    $config= model('AddonData')->lget('visitor');
	    if(in_array('home',$config['open'])){
	        $this->assign($this->__getVisitorData($this->mid,$config));
	        $this->display('visitor');
	    }
	}

	// 个人空间 - 最近来访的人
	public function home_space_right_bottom($param)
	{
	    $config= model('AddonData')->lget('visitor');
	    if(in_array('space',$config['open'])){
	        $uid = $param['uid'];
	        $data = $this->__getVisitorData($uid,$config);
	        $data['visitor_title'] = '这些人也刚刚来过...';
	        $this->assign($data);
	        $this->display('visitor');
	    }
	}

	private function __getVisitorData($uid,$config)
	{
		$data['visitor_title'] = '最近来访的人';
		$data['visitor_list']  = M('user_visited')->field('uid')
						->where("fid={$uid} AND ctime>0")
						->order('ctime DESC')->limit($config['limit'])->findAll();
		return $data;
	}

	/* 后台管理 */
	public function adminMenu()
	{
	    return array('config' => '全局配置');
	}

	public function config(){
	    $config= model('AddonData')->lget('visitor');
	    $this->assign('config',$config);
	    $this->display('config');
	}

	public function saveConfig(){
	    if(empty($_POST)) return;
	    if(empty($_POST['open'])) $_POST['open'] = array();
	    $data = $_POST;
	    $res = model('AddonData')->lput('visitor', $data);
	    if ($res) {
	        $this->assign('jumpUrl', Addons::adminPage('config'));
	        $this->success();
	    } else {
	        $this->error();
	    }
	    exit;
	}

	public function install()
	{
	    $data['open']=array('home','space');
	    $data['limit'] = 6;
	    model('AddonData')->lput('visitor', $data)?true:false;

		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_visited` (
			  `visited_id` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` int(11) NOT NULL,
			  `fid` int(11) NOT NULL,
			  `ctime` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY (`visited_id`),
			  UNIQUE KEY `uid_2` (`uid`,`fid`),
			  KEY `uid` (`uid`),
			  KEY `fid` (`fid`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

		return M()->execute($sql) !== false;
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}user_visited`;";

		return M()->execute($sql);
	}


}