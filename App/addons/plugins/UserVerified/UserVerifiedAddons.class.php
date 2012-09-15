<?php
class UserVerifiedAddons extends NormalAddons
{
	protected $version = '1.0';
	protected $author  = '陈伟川';
	protected $site    = 'http://weibo.com/cchhuuaann';
	protected $info    = '认证身份';
	protected $pluginName = '用户认证';
	protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('UserVerifiedHooks');
        return $hooks;
    }

    public function adminMenu(){
        $menu = array(
					'verifying' 	  => '待认证',
					'verified'  	  => '已认证',
					'addVerifiedUser' => '添加认证',
					'setVerifyRuler'  => '设置认证规则'
				);
        return $menu;
    }

    public function start()
    {

    }

	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_verified` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `uid` int(11) unsigned NOT NULL,
				  `realname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  `verified` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
				  `attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `uid` (`uid`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}user_verified`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
}