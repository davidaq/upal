<?php
	class DefaultLanguageAddons extends SimpleAddons
	{
		protected $version = '1.0';
		protected $author  = 'Num16';
		protected $site    = 'http://num16.com/';
		protected $info    = '设置用户用于Upal活动的语言';
		protected $pluginName = '默认语言设置';
		protected $tsVersion  = "2.5";
		
		private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';

		private $languages=array();
		
		public function DefaultLanguageAddons()
		{
			$f = file($this->cfgFile);
			unset($f[0]);
			$this->languages = json_decode(implode('', $f), true);
		}
		public function getHooksInfo()
		{
			$this->apply('default_language_setting', 'display');
			$this->apply('update_default_language', 'update');
		}
		public function display($param)
		{
			$m = M('weibo_default_language');
			if (($d = $m->find($param)) == false) {
				echo "[".$m->find($param)."]";
				$data['uid'] = $param;
				$d_lang = $data['language'] = 'zh_cn';
				$m->add($data);
			} else {
				$d_lang = $d['language'];
			}
			if(isset($_SESSION['language'])&&'en'==$_SESSION['language']){
				$_LANG['defLang']='Default language';
			}else{
				$_LANG['defLang']='默认使用语言';
			}
			echo<<<HTML
				<dd>
					<label>{$_LANG['defLang']}: </label>
					<select name = 'default_language'>
HTML;
			$lang='';
			foreach($this->languages as $k=>$v) {
				if ($d_lang == $k)
					$lang.='<option value="'.$k.'" selected = "selected">'.$v.'</option>';
				else
					$lang.='<option value="'.$k.'">'.$v.'</option>';
			}
			echo $lang;
			echo<<<HTML
					</select>
				</dd>
HTML;
			$this->InsertJS();
		}
		private function InsertJS()
		{
			echo<<<HTML
			<script type="text/javascript">
				$(function() {
					$(".btn_b").click(function(){
						
					});
				});
			</script>
HTML;
		}
		public function update($param)
		{
			$m = M('weibo_default_language');
			if (false != $m->find($param['uid'])) {
				$m->data($param)->save();			
			} else {
				return;
			}
		}
		public function install()
		{
			$db_prefix = C('DB_PREFIX');
			$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_default_language` (
					  `uid` int(11) unsigned NOT NULL COMMENT '设置用户ID的',
					  `language` char(5) NOT NULL,
					  PRIMARY KEY (`uid`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			if (false !== M()->execute($sql)) {
				return true;
			} else
				return false;
		}
		public function uninstall()
		{

		}
	}
?>
