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
	
	private $cfgFile	= './addons/plugins/TranslateShare/cfg.php';
	
	private $languages=array();
	
	public function TranslateShareAddons(){
		$f=file($this->cfgFile);
		unset($f[0]);
		$this->languages=json_decode(implode('',$f),true);
	}

	public function getHooksInfo(){
		$this->apply('transpond_header','shareLanguageChoice');
		$this->apply('transpond_post_done','_sharePost');
		$this->apply('weibo_list_item','displayLanguage');
		$this->apply('weibo_list_start','weiboListStart');
	}
	
	public function _sharePost($param){
		if(isset($_POST['shareTranslate'])&&$_POST['shareTranslate']!='o'){
			$m=M('weibo_translation');
			if(false!=($d=$m->where(array('tid'=>$param['origin_id']))->field('origin_id')->select())){
				$data['origin_id']	= $d[0]['origin_id'];
			}else{
				$data['origin_id']	= $param['origin_id'];
			}
			$data['tid']			= $param['weibo_id'];
			$data['language']		= $_POST['shareTranslate'];
			$data['votes']			= 0;
			$m->add($data);
		}
	}
	
	public function getWeiboTranslation(){
		$m=M('weibo_translation');
		$d=$m->join(C('DB_PREFIX').'weibo as wb ON tid=wb.weibo_id')
				->join(C('DB_PREFIX').'user as us ON wb.uid=us.uid')
				->where(array('origin_id'=>intval($_GET['weibo']),'language'=>$_GET['language']))
				->order(array('votes'=>'desc'))
				->field('tid,content,us.uid,uname,votes')->select();
		echo json_encode($d);
	}
	
	public function voteTranslation(){
		$tid=intval($_GET['tid']);
		if(isset($_SESSION['language'])&&$_SESSION['language']=='en'){
			$_LANG['voted']='You already voted for this';
			$_LANG['db_error']='Database error';
		}else{
			$_LANG['voted']='您已投过票了';
			$_LANG['db_error']='数据库错误';
		}
		if(isset($_SESSION['vote'.$tid])){
			die($_LANG['voted']);
		}
		$_SESSION['vote'.$tid]=true;
		$m=M('weibo_translation');
		if(false!=$m->where(array('tid'=>$tid))->setInc('votes'))
			echo 'ok';
		else
			echo $_LANG['db_error'];
	}
	
	public function weiboListStart(){
		$url=Addons::createAddonUrl('TranslateShare','getWeiboTranslation');
		$space=U('home/space/index',array('uid'=>''));
		$vote=Addons::createAddonUrl('TranslateShare','voteTranslation');
		
		if(isset($_SESSION['language'])&&$_SESSION['language']=='en'){
			$_LANG['translate']	='translates';
			$_LANG['up']			='Up';
			$_LANG['vote-done']	='Voting submited';
			$_LANG['show-all']	='There are more, display all';
			$_LANG['hide-below']	='Display recommended only';
		}else{
			$_LANG['translate']	='翻译';
			$_LANG['up']			='顶';
			$_LANG['vote-done']	='投票成功';
			$_LANG['show-all']	='还有更多，显示全部';
			$_LANG['hide-below']	='只显示推荐';
		}
		echo<<<HTML
<style type="text/css">
.weibo-content{
}
ul.translate-versions{
	display:inline;
	margin:0;
	padding:0;
	color:#777;
}
ul.translate-versions li{
	display:inline;
	padding:2px 5px;
	cursor:pointer;
}
ul.translate-versions li:hover{
	color:#000;
}
.translation-content{
	background:#F7F7F7;
	margin:5px;
	padding:10px;
	border-radius:10px;
	border:1px solid #DDD;
	
}
.translation-content .vote-up{
	text-align:right;
}
.showMore{
	display:none;
}
</style>
<script type="text/javascript">
var loadedTranslates=[];
function voteTranslate(me,tid){
	$.get('{$vote}',{'tid':tid},function(result){
		if(result=='ok'){
			ui.success('{$_LANG['vote-done']}');
			$(me).find('span').html($(me).find('span').html()*1+1);
		}else{
			ui.error(result);
		}
	});
}
function showMore(me){
	$(me).next('.showMore').toggle();
	if('none'==$(me).next('.showMore').css('display')){
		$(me).html('{$_LANG['show-all']}');
	}else{
		$(me).html('{$_LANG['hide-below']}');
	}
}
$(function(){
	$('ul.translate-versions li').click(function(){
		$(this).parent().find('li').css('background','transparent');
		$(this).css('background','#EEE');
		var wid=$(this).parent().attr('weibo');
		var lang=$(this).attr('language');
		if(loadedTranslates[wid+'o']==undefined){
			loadedTranslates[wid+'o']=[{'content':$(this).parent().parent().find('.weibo-content').html(),'votes':-1}];
		}
		function makeTrans(param){
			var ret='';
			var first=true;
			for(k in param){
				ret+='<div class="translation-content">';
				ret+='<a href="{$space}'+param[k]['uid']+'" target="_blank">'+param[k]['uname']+'</a> ';
				ret+='{$_LANG['translate']}: '+param[k]['content'];
				ret+='<div class="vote-up"><a href="javascript:void(0)" onclick="voteTranslate(this,'+param[k]['tid']+')">{$_LANG['up']}(<span>'+param[k]['votes']+'</span>)</a></div>';
				ret+='</div>';
				if(first){
					if(param[1])
						ret+='<a href="javascript:void(0)" onclick="showMore(this)">{$_LANG['show-all']}</a>';
					ret+='<div class="showMore">';
				}				
				first=false;
			}
			ret+='</div>';
			return ret;
		}
		if(loadedTranslates[wid+lang]==undefined){
			var me=this;
			$(me).parent().parent().find('.weibo-content').html('...');
			$.getJSON('{$url}',{'weibo':wid,'language':lang},function(data){
				loadedTranslates[wid+lang]=data;
				$(me).parent().parent().find('.weibo-content').html(makeTrans(loadedTranslates[wid+lang]));
			});
		}else{
			if(lang=='o')
				$(this).parent().parent().find('.weibo-content').html(loadedTranslates[wid+lang][0]['content']);
			else
				$(this).parent().parent().find('.weibo-content').html(makeTrans(loadedTranslates[wid+lang]));
		}
	});
});
</script>
HTML;
	}
	
	public function displayLanguage($param){
		if(isset($_SESSION['language'])&&$_SESSION['language']=='en'){
			$_LANG['is_a_translate']='This is a translation of the origin.';
			$_LANG['version']='Version: ';
			$_LANG['o']='Original';
		}else{
			$_LANG['is_a_translate']='这篇转发是原文的一个译文。';
			$_LANG['version']='版本：';
			$_LANG['o']='原版';
		}
		$m=M('weibo_translation');
		if(false!=($d=$m->where(array('tid'=>$param))->field('language')->select())){
			echo '<em>'.$_LANG['is_a_translate'].' ('.$this->languages[$d[0]['language']].')</em>';
		}elseif(false!=($d=$m->where(array('origin_id'=>$param))->field('language,count(*) as count')->group('language')->select())){
			echo $_LANG['version'];
			echo '<ul class="translate-versions" weibo="'.$param.'">';
			echo '<li language="o" style="background:#EEE">'.$_LANG['o'].'</li>';
			foreach($d as $f){
				echo '<li language="'.$f['language'].'">'.$this->languages[$f['language']].'('.$f['count'].')</li>';
			}
			echo '</ul>';
		}
	}
	
	public function shareLanguageChoice()
	{
		if(isset($_SESSION['language'])&&$_SESSION['language']=='en'){
			$_lang['o']='Original';
			$_lang['hint_o']='Content will be quoted just as it is';
			$_lang['hint_t']='Your input will be treated as a translation';
		}else{
			$_lang['o']='原文';
			$_lang['hint_o']='将按照原文转发';
			$_lang['hint_t']='您输入的内容将被用作译文';
		}
		
		$lang='';
		foreach($this->languages as $k=>$v){
			$lang.='<option value="'.$k.'">'.$v.'</option>';
		}
		echo '<select name="shareTranslate" onchange="shareTranslateChange(this)"><option value="o">'.$_lang['o'].'</option>'.$lang.'</select>';
		$url=Addons::createAddonUrl('TranslateShare','weiboListStart');
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
	
	public function adminMenu()
	{
		return array(
			'adminSetLanguages'=>'语言选项',
		);
	}
	
	public function adminSetLanguagesSave()
	{
		$fp=fopen($this->cfgFile,'w');
		fwrite($fp,'<?php die();?>'.chr(10));
		$arr=array();
		foreach($_POST['value'] as $k=>$v){
			$arr[$_POST['key'][$k]]=$v;
		}
		fwrite($fp,json_encode($arr));
		fclose($fp);
	}
	
	public function adminSetLanguages()
	{
		$url=Addons::createAddonUrl('TranslateShare','adminSetLanguagesSave');
		echo <<<HTML
<style text="text/css">
input{
	margin-right:50px;
	padding:5px;
}
</style>
<script type="text/javascript">
function remove(me){
	$(me).parent().remove();
}
function add(){
	$('#languageList').append('<div> <label>显示名称：</label><input name="value[]" type="text"/><label>机器名称（2-5个字符）：</label><input name="key[]" type="text"><a href="javascript:void(0)" onclick="remove(this)">删除</a></div>');
}
var saving=false;
function presave(){
	saving=true;
}
function saved(){
	if(saving)
		ui.success('保存完毕');
	saving=false;
}
</script>
<iframe onload="saved()" src="about:blank" name="submFrame" id="submFrame" style="border:0;background:transparent;width:0;height:0"></iframe>
<form onsubmit="presave()" action="{$url}" method="post" target="submFrame">
<div id="languageList">
HTML;
		foreach($this->languages as $k=>$v){
			echo '<div> <label>显示名称：</label><input name="value[]" type="text" value="'.$v.'"/>';
			echo '<label>机器名称（2-5个字符）：</label><input name="key[]" type="text" value="'.$k.'">';
			echo '<a href="javascript:void(0)" onclick="remove(this)">删除</a></div>';
		}
		echo <<<HTML
</div>
<a href="javascript:void(0)" onclick="add()">添加</a><br/>
<input type="submit" value="保存"/>
</form>
HTML;
	}

	public function start()
	{
		return is_writable($this->cfgFile);
	}
	
	public function install()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}weibo_translation` (
				  `tid` int(11) unsigned NOT NULL COMMENT '内容微博ID',
				  `origin_id` int(11) unsigned NOT NULL COMMENT '原微博ID',
				  `language` char(5) NOT NULL,
				  `votes` int(11) NOT NULL COMMENT '顶数',
				  PRIMARY KEY (`tid`)
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
