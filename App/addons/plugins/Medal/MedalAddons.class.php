<?php
class MedalAddons extends NormalAddons
{
	protected $version = "1.0";
	protected $author  = "杨德升";
	protected $site    = "desheng.me";
	protected $info    = "给用户添加勋章，彰显活跃度";
	protected $pluginName = "勋章";
	protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('MedalHooks');
        return $hooks;
    }

    public function adminMenu(){
    	$menu = array(
    		'medalAdmin' => '勋章管理'
    	);
        return $menu;
    }

    public function start()
    {
        return true;
    }

	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = array(
			// group数据
			"CREATE TABLE IF NOT EXISTS `{$db_prefix}medal` (
			  `medal_id` int(11) NOT NULL AUTO_INCREMENT,
			  `path_name` varchar(255) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `data` text,
			  `is_active` tinyint(1) NOT NULL DEFAULT '1',
			  `display_order` smallint(4) NOT NULL DEFAULT '0',
			  `ctime` int(11) DEFAULT NULL,
			  PRIMARY KEY (`medal_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

		    "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_medal` (
			  `user_medal_id` int(11) NOT NULL AUTO_INCREMENT,
			  `uid` int(11) NOT NULL,
			  `medal_id` int(11) NOT NULL,
			  `is_active` tinyint(1) NOT NULL DEFAULT '1',
			  `data` text,
			  PRIMARY KEY (`user_medal_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;",

		    "INSERT INTO ".C('DB_PREFIX')."credit_setting (`name`,`alias`,`type`,`info`,`score`,`experience`)
			VALUES
			    ('add_medal','获得勋章','medal','{action}{sign}了{score}{typecn}',5,5),
			    ('delete_medal','丢失勋章','medal','{action}{sign}了{score}{typecn}',-5,0);",
		);

		foreach ($sql as $v)
			$res = M()->execute($v);
		if (false !== $res) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = array(
			// group数据
			"DROP TABLE IF EXISTS `{$db_prefix}medal`;",
		    "DROP TABLE IF EXISTS `{$db_prefix}user_medal;",
			// 积分规则
			"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'medal';",
		);

		foreach ($sql as $v)
			$res = M()->execute($v);

		if (false !== $res) {
			return true;
		}
	}
}