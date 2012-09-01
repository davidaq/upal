<?php
	class CommentLanguageAddons extends SimpleAddons 
	{
		protected $version = '1.0';
		protected $author  = 'Num16';
		protected $site    = 'http://num16.com/';
		protected $info    = '使评论变为多语种';
		protected $pluginName = '评论多语种插件';
		protected $tsVersion  = "2.5";
		
		private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';
		private $languages=array();

		public function CommentLanguageAddons()
		{
			$f = file($this->cfgFile);
			unset($f[0]);
			$this->languages = json_decode(implode('', $f), true);
		}
		public function getHooksInfo()
		{
			$this->apply('set_comment_language', 'display');
		}
		public function display()
		{
			//die("OK");
			echo "Hello World!";
		}
		public function install()
		{
			$db_prefix = C('DB_PREFIX');
			$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_comment_language` (
					  `comment_id` int(11) unsigned NOT NULL COMMENT '评论的ID',
					  `language` char(5) NOT NULL,
					  PRIMARY KEY (`comment_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			if (false !== M()->execute($sql)) {
				return true;
			} else
				return false;
		}
	}
?>