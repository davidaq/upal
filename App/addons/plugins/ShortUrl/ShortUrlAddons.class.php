<?php
/**
 +------------------------------------------------------------------------------
 * ShortUrl 实现了基于网络API的短网址插件
 * 以goo.gl t.cn作为实例，其他方式大家可以仿照来写
 * 只支持mysql
 +------------------------------------------------------------------------------
 */
class ShortUrlAddons extends SimpleAddons
{
	protected $version		= '1.0';
	protected $author		= '海虾';
	protected $site			= 'http://www.thinksns.com';
	protected $info			= '短网址插件';
	protected $pluginName	= '短网址';
	protected $tsVersion	= "2.5"; // ts核心版本号

	public function getHooksInfo()
	{
		return $this->apply('get_short_url','getShortUrl');
	}

	// 替换短网址
	public function getShortUrl($param)
	{
		$data	=	model('AddonData')->lget('short_url');
		//是否开启短网址服务
		if(!$data['shorturlapi']){
			return ;
		}
		//使用新浪t.cn短网址
		if($data['shorturlapi']=='sina'){
			include_once(dirname(__FILE__).'/hooks/SinaShortUrlHooks.class.php');
			$shorten = new SinaShortUrlHooks;
			(false != $short = $shorten->getShortUrl($param['url'],$data['sinakey'])) && $param['url'] = $short;
			return ;
		}

		//使用goo.gl短网址
		if($data['shorturlapi']=='google'){
			include_once(dirname(__FILE__).'/hooks/GoogleShortUrlHooks.class.php');
			$shorten = new GoogleShortUrlHooks;
			(false != $short = $shorten->getShortUrl($param['url'],$data['googlekey'])) && $param['url'] = $short;
			return ;
		}

		//使用百度dwz.cn短网址
		if($data['shorturlapi']=='baidu'){
			include_once(dirname(__FILE__).'/hooks/BaiduShortUrlHooks.class.php');
			$shorten = new BaiduShortUrlHooks;
			(false != $short = $shorten->getShortUrl($param['url'])) && $param['url'] = $short;
			return ;
		}

		//使用网易126.am短网址
		if($data['shorturlapi']=='netease'){
			include_once(dirname(__FILE__).'/hooks/NeteaseShortUrlHooks.class.php');
			$shorten = new NeteaseShortUrlHooks;
			(false != $short = $shorten->getShortUrl($param['url'],$data['neteasekey'])) && $param['url'] = $short;
			return ;
		}

		//使用本地短网址
		if($data['shorturlapi']=='local'){
			include_once(dirname(__FILE__).'/hooks/LocalShortUrlHooks.class.php');
			$shorten = new LocalShortUrlHooks;
			(false != $short = $shorten->getShortUrl($param['url'],$data['localurl'])) && $param['url'] = $short;
			return ;
		}

		return ;
	}

	/* 后台管理 */

    public function adminMenu()
	{
        return array('config' => '配置');
    }

	public function config()
	{
		$data	=	model('AddonData')->lget('short_url');
		$this->assign($data);
		$this->display('config');
	}

	public function saveConfig($param)
	{
		unset($_POST['__hash__']);

		foreach($_POST as $k=>$v){
			$_POST[$k] = h($v);
		}

		$res = model('AddonData')->lput('short_url', $_POST);

		if ($res) {
			$this->assign('jumpUrl', Addons::adminPage('config'));
    		$this->success();
		} else {
    		$this->error();
		}
	}

    public function start()
    {
        return true;
    }

	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}url` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `url` tinytext NOT NULL COMMENT '原网址',
				  `hash` varchar(32) DEFAULT NULL COMMENT '长网址的hash,便于查询已存在地址',
				  `hits` int(11) NOT NULL COMMENT '点击量',
				  `status` tinyint(1) NOT NULL COMMENT '=1可用,=0不可用',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}url`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
}