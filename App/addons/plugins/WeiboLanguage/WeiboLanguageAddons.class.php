<?php
class WeiboLanguageAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = 'Num16';
	protected $site    = 'http://num16.com/';
	protected $info    = '允许选择语言发布、评论微博';
	protected $pluginName = '微博语言';
	protected $tsVersion  = "2.5";
	
	public function getHooksInfo()
	{
	}
	
	public function start()
	{
		return true;
	}
	
	public function install()
	{	
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_language` (
				  `weibo_id` int(11) unsigned NOT NULL COMMENT '微博ID',
				  `language` char(5) NOT NULL,
				  PRIMARY KEY (`weibo_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		if (false !== M()->execute($sql)) {
			return true;
		}else
			return false;
	}
	
	public function uninstall()
	{
	}

}
?>
