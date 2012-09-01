<?php
class WeiboLanguageAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = 'Num16';
	protected $site    = 'http://num16.com/';
	protected $info    = '允许选择语言发布微博';
	protected $pluginName = '微博语言';
	protected $tsVersion  = "2.5";
	
	private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';
	private $languages= array();
	
	public function WeiboLanguageAddons()
	{
		$f=file($this->cfgFile);
		unset($f[0]);
		$this->languages=json_decode(implode('',$f),true);
	}
	
	public function getHooksInfo()
	{
		$this->apply('home_index_weibo_func','postSelectLang');
		$this->apply('weibo_publish_after','publish');
		$this->apply('weibo_delete','del');
		$this->apply('Weibo_getHomeList','getList');
		$this->apply('weibo_feedbox','feedbox');
	}
	
	private function getFilter()
	{
		$filter='/';
		$key='weiboFilter'.$_SESSION['mid'];
		$m=M('db_session');
		if(isset($_GET['weiboFilterLangSet']))
		{
			$filter=$_GET['weiboFilterLangSet'];
			trace('setFilter::'.$filter);
			$m->where(array('key'=>$key))->delete();
			$m->add(array('key'=>$key,'value'=>$filter));
		}else
		{
			$r=$m->where(array('key'=>$key))->field('value')->select();
			if($r){
				$filter=$r[0]['value'];
			}
			trace('gotFilter::'.$filter);
		}
		trace($filter);
		return $filter;
	}
	
	public function feedbox()
	{
		$filter=$this->getFilter();
		if(isset($_SESSION['language'])&&'en'==$_SESSION['language']){
			$_LANG['useLang']='Language filter';
			$_LANG['allLang']='Display all';
		}else{
			$_LANG['useLang']='显示语言';
			$_LANG['allLang']='显示所有语言';
		}
		$mid=$_SESSION['mid'];
		echo '<div style="text-align:right">';
		echo $_LANG['useLang'].': <select id="weibo_lang_set_filter">';
		echo '<option value="/">'.$_LANG['allLang'].'</option>';
		foreach($this->languages as $k=>$v){
			echo '<option value="'.$k.'"'.($k==$filter?' selected':'').'>'.$v.'</option>';
		}
		echo '</select>';
		echo '</div>';
		$URI=$_SERVER['REQUEST_URI'];
		$url=parse_url($URI);
		$tmp=array();
		parse_str($url['query'],$tmp);
		if(isset($tmp['weiboFilterLangSet']))
			unset($tmp['weiboFilterLangSet']);
		//$url['query']=http_build_query($tmp);
		$URI=$url['path'].'?'.http_build_query($tmp);
		echo<<<JS
		<script type="text/javascript">
		$('#weibo_lang_set_filter').change(function(){
			document.location.href='{$URI}&weiboFilterLangSet='+$(this).val();
		});
		</script>
JS;
	}
	
	public function getList($param)
	{
		$filter=$this->getFilter();
		trace($param);
		trace($filter);
		if($filter&&$filter!='/')
		{
			$param['map'].=' AND (weibo_id IN (SELECT weibo_id FROM '.C('DB_PREFIX').'weibo_language WHERE language="'.$filter.'"))';
		}
	}
	
	public function del($wid)
	{
		$m=M('weibo_language');
		$m->where(array('weibo_id'=>$wid))->delete();
	}
	
	public function publish($param)
	{
		$m=M('weibo_language');
		$data['weibo_id']=$param['weibo_id'];
		$data['language']=$_POST['lang'];
		$m->add($data);
	}
	
	public function postSelectLang()
	{
		if(isset($_SESSION['language'])&&'en'==$_SESSION['language']){
			$_LANG['useLang']='Use language';
		}else{
			$_LANG['useLang']='使用语言';
		}
		$mid=$_SESSION['mid'];
		$m = M('weibo_default_language');
		$r=$m->where(array('uid'=>$mid))->field('language')->select();
		if($r)
			$r=$r[0]['language'];
		echo $_LANG['useLang'].': <select name="lang">';
		foreach($this->languages as $k=>$v){
			echo '<option value="'.$k.'"'.($k==$r?' selected':'').'>'.$v.'</option>';
		}
		echo '</select>';
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

		if (false === M()->execute($sql))
			return false;
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}db_session` (
				  `key` char(25) NOT NULL,
				  `value` char(25) NOT NULL,
				  PRIMARY KEY (`key`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
		if (false === M()->execute($sql))
			return false;
		return true;
	}
	
	public function uninstall()
	{
	}

}
?>
