<?php
/**
 * TranslateShareAddons
 * 转发翻译版
 */
class TranslateShareAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = 'Num16';
	protected $site    = 'http://num16.com/';
	protected $info    = '允许转发翻译版，同时允许其他人为翻译投票';
	protected $pluginName = '翻译转发';
	protected $tsVersion  = "2.5";
	
	private $languages=array(
		'en'=>'English',
		'zh_cn'=>'中文',
		'sp'=>'español',
		'fr'=>'française',
		'jp'=>'日本語',
		'kr'=>'한국의'
	);

	public function getHooksInfo(){
		$this->apply('transpond_header','shareLanguageChoice');
		$this->apply('transpond_post_preprocess','');
	}
	
	public function shareLanguageChoice()
	{
		if(isset($_SESSION['language'])&&$_SESSION['language']=='en'){
			$_lang['o']='Original';
			$_lang['hint_o']='Content will be quoted just as it is';
			$_lang['hint_t']='Input you version of translation below';
		}else{
			$_lang['o']='原文';
			$_lang['hint_o']='将按照原文转发';
			$_lang['hint_t']='在下面输入译文';
		}
		
		$lang='';
		foreach($this->languages as $k=>$v){
			$lang.='<option value="'.$k.'">'.$v.'</option>';
		}
		echo '<select name="shareTranslate" onchange="shareTranslateChange(this)"><option value="o">'.$_lang['o'].'</option>'.$lang.'</select>';
		echo<<<JS
<label id="shareTranslateHint">{$_lang['hint_o']}</label>
<script type="text/javascript">
function shareTranslateChange(me){
	if($(me).val()=='o'){
		$('#shareTranslateHint').html('{$_lang['hint_o']}');
	}else{
		$('#shareTranslateHint').html('{$_lang['hint_t']}');
	}
}
</script>
JS;
	}

	public function start()
	{
		
	}
	
	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_translation` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `weibo_id` int(11) unsigned NOT NULL,
				  `uid` int(11) unsigned NOT NULL,
				  `language` char(5) NOT NULL,
				  `content` text NOT NULL,
				  `votes` int(11) NOT NULL COMMENT '顶数',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		if (false !== M()->execute($sql)) {
			return true;
		}else
			return false;
	}

	public function uninstall()
	{
		return true;
	}


}
