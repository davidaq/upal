<?php
	class LanguagePrefAddons extends SimpleAddons 
	{
		protected $version = '1.0';
		protected $author  = 'Num16';
		protected $site    = 'http://num16.com/';
		protected $info    = '增加资料中的多语言偏好设置';
		protected $pluginName = '个人资料中多语言选项';
		protected $tsVersion  = "2.5";
		
		private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';
		private $languages=array();

		public function LanguagePrefAddons()
		{
			$f = file($this->cfgFile);
			unset($f[0]);
			$this->languages = json_decode(implode('', $f), true);
		}
		public function getHooksInfo()
		{
			$this->apply('pref_language_setting', 'displaySetting');
			$this->apply('update_pref_language', 'update');
			$this->apply('userinfo_language', 'displayUserInfo');
		}
		public function displaySetting($param)
		{
			$m = M('weibo_pref_language');
			if (($d = $m->find($param)) == false) {
				//echo "[".$m->find($param)."]";
				$data['uid'] = $param;
				$d_lang = $data['mother_language'] = 'zh_cn';
				$i_lang = $data['learn_language'] = 0;
				$m_lang = $data['master_language'] = 0;
				$m->add($data);
			} else {
				$d_lang = $d['mother_language'];
				$i_lang = $d['learn_language'];
				$m_lang = $d['master_language'];
			}
			/**
				Display mother lang choice.
			*/
			echo<<<HTML
				<dd>
					<label>母语/Mother Lang: </label>		
					<select name="mother_language">
HTML;
			$lang='';
			foreach($this->languages as $k=>$v) {
				if ($d_lang == $k)
					$lang.='<option value="'.$k.'" selected = "selected">'.$v.'</option>';
				else
					$lang.='<option value="'.$k.'">'.$v.'</option>';
			}
			echo $lang;
			//echo L('shit happened')；
			echo<<<HTML
					</select>
				</dd>
HTML;
			/**
				Display master lang checkboxes.
			*/
			echo <<< HTML
			<dd>
				<label>
					掌握/ Master:
				</label>
HTML;
			$lang='';
			$i = 1;
			foreach($this->languages as $k=>$v) {
				if (($i & $m_lang) == true) {
					$lang.='<input type = "checkbox" value="yes" name = "mas_'.$k.'" checked = "checked">'.$v.'</input>';
				} else
					$lang.='<input type = "checkbox" value="yes" name = "mas_'.$k.'">'.$v.'</input>';
				$i <<= 1;
			}
			echo $lang;
			echo <<< HTML
			</dd>
HTML;
			/**
				Display interested lang checkboxes.
			*/
			echo <<< HTML
			<dd>
				<label>
					想学/Learning:
				</label>
HTML;
			$lang='';
			$i = 1;
			foreach($this->languages as $k=>$v) {
				if (($i & $i_lang) == true)
					$lang.='<input type = "checkbox" value="yes" name = "lrn_'.$k.'" checked = "checked">'.$v.'</input>';
				else
					$lang.='<input type = "checkbox" value="yes" name = "lrn_'.$k.'">'.$v.'</input>';
				$i <<= 1;
			}
			echo $lang;
			echo <<< HTML
			</dd>
HTML;
		}
		public function update($param)
		{
			$m = M('weibo_pref_language');
			if (($d = $m -> find($param['uid'])) == false)
				return;
			$d['mother_language'] = $_POST['mother_language'];
			$i = 1;
			foreach($this->languages as $k=>$v) {
				if ($_POST['mas_'.$k] == "yes")
					$d['master_language'] |= $i;
				if ($_POST['lrn_'.$k] == 'yes')
					$d['learn_language'] |= $i;
				$i <<= 1;
			}
			$m->data($d)->save();
		}
		public function displayUserInfo($param)
		{
			$m = M('weibo_pref_language');
			if (($d = $m -> find($param['uid'])) == false)
				return;
			$param['user_info']['母语 / Mum language'] = $this->languages[$d['mother_language']];
			$i = 1;
			$mas_lang = "";
			$lrn_lang = "";
			foreach($this->languages as $k=>$v) {
				if ( ($d['master_language'] & $i) == true)
					$mas_lang.= " ".$v;
				if ( ($d['learn_language'] & $i) == true)
					$lrn_lang.= " ".$v;
				$i <<= 1;
			}
			$param['user_info']['掌握 / Master'] = $mas_lang;
			$param['user_info']['想学 / Learning'] = $lrn_lang;
		}
		public function install()
		{
			$db_prefix = C('DB_PREFIX');
			$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_pref_language` (
					  `uid` int(11) unsigned NOT NULL COMMENT '用户ID',
					  `mother_language` char(5) NOT NULL,
					  `master_language` int(11) unsigned,
					  `learn_language` int(11) unsigned,
					  PRIMARY KEY (`uid`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

			if (false !== M()->execute($sql)) {
				return true;
			} else
				return false;
		}
	}
?>