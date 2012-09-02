<?php
	class BlogLanguageAddons extends SimpleAddons 
	{
		protected $version = '1.0';
		protected $author  = 'Num16';
		protected $site    = 'http://num16.com/';
		protected $info    = '为日志添加多语言支持';
		protected $pluginName = '日志多语种插件';
		protected $tsVersion  = "2.5";
		
		private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';
		private $languages=array();

		public function BlogLanguageAddons()
		{
			$f = file($this->cfgFile);
			unset($f[0]);
			$this->languages = json_decode(implode('', $f), true);
		}
		public function getHooksInfo()
		{
			$this->apply('blog_precontent','setBlogLanguageShow');
			$this->apply('blog_do_add','addBlog');
			$this->apply('blog_do_save','saveBlog');
			$this->apply('blog_delete','deleteIdle');
		}
		public function deleteIdle()
		{
			$m = M('blog_language');
			$map='blog_id NOT IN (SELECT id FROM '.C('DB_PREFIX').'blog)';
			$m->where($map)->delete();
			trace($m->getLastSql());
		}
		public function saveBlog($id)
		{
			$m = M('blog_language');
			$data['blog_id']=$id;
			$data['language']=$_POST['lang'];
			$m->save($data);
		}
		public function addBlog($id)
		{
			$m = M('blog_language');
			$data['blog_id']=$id;
			$data['language']=$_POST['lang'];
			$m->add($data);
		}
		public function setBlogLanguageShow($id)
		{
			if(isset($_SESSION['language'])&&'en'==$_SESSION['language']){
				$_LANG['useLang']='Use language';
			}else{
				$_LANG['useLang']='使用语言';
			}
			$r=false;
			if($id){
				$m = M('blog_language');
				$r=$m->where(array('blog_id'=>$id))->field('language')->select();
				if($r)
					$r=$r[0]['language'];
			}else
			{
				$mid=$_SESSION['mid'];
				$m = M('weibo_default_language');
				$r=$m->where(array('uid'=>$mid))->field('language')->select();
				if($r)
					$r=$r[0]['language'];
			}
			echo '<li style="padding:0;">';
			echo '<label>'.$_LANG['useLang'].': &nbsp;</label>';
			echo '<div class="c1">';
			echo '<select name="lang">';
			foreach($this->languages as $k=>$v){
				echo '<option value="'.$k.'"'.($k==$r?' selected':'').'>'.$v.'</option>';
			}
			echo '</select>';
			echo '</div></li>';
		}
		public function install()
		{
			$db_prefix = C('DB_PREFIX');
			$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}blog_language` (
					  `blog_id` int(11) unsigned NOT NULL COMMENT '日志的ID',
					  `language` char(5) NOT NULL,
					  PRIMARY KEY (`blog_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			if (false !== M()->execute($sql)) {
				return true;
			} else
				return false;
		}
		public function start()
		{
			return true;
		}
	}
?>
