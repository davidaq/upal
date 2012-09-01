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
			$this->apply('weibo_comment_publish', 'docomment');
			$this->apply('weibo_delete_comment' , 'delcomment');
			$this->apply('weibo_comment_filter_language' , 'setFilter');
		}
		public function setFilter($param)
		{
			$weiboId=$param[0];
			$data=$param[1];
			if(!$data)
				return;
			if(isset($_SESSION['language'])&&'en'==$_SESSION['language']){
				$_LANG['useLang']='Language filter:';
				$_LANG['showAll']='Display all';
			}else{
				$_LANG['useLang']='显示语言';
				$_LANG['showAll']='显示所有';
			}
			echo '<div style="clear:both; color:#666; margin-top:5px;margin-left:32px;">';
			$ctrlId=time().rand(10,99);
			echo $_LANG['useLang'].'<select id="langSel'.$ctrlId.'"><option value="">'.$_LANG['showAll'].'</option>';
			foreach($this->languages as $k=>$v){
				echo '<option value="'.$k.'"'.($k==$r?' selected':'').'>'.$v.'</option>';
			}
			echo '</select></div>';
			echo '<script type="text/javascript">';
			$list=array();
			foreach($data as $f){
				$list[]=$f['comment_id'];
			}
			$m = M('weibo_comment_language');
			$r=$m->where(array('comment_id'=>array('in',$list)))->field('comment_id,language')->select();
			$langS=array();
			foreach($r as $f){
				if(!isset($langS[$f['language']]))
					$langS[$f['language']]=array();
				$langS[$f['language']][]=$f['comment_id'];
			}
			$langS=json_encode($langS);
			echo<<<JS
			$(function(){
				$('#langSel{$ctrlId}').change(function(){
					var langS={$langS};
					var cc=$('#comment_list_{$weiboId} dl.comment_list');
					if($(this).val()==''){
						cc.show();
					}else
					{
						cc.hide();
						for(k in langS[$(this).val()]){
							var cid=langS[$(this).val()][k];
							$('#comment_list_c_'+cid).show();
						}
					}
				});
			});
JS;
			echo '</script>';
		}
		public function delcomment($cid)
		{
			$m = M('weibo_comment_language');
			$m->where(array('comment_id'=>$cid))->delete();
		}
		public function docomment($param)
		{
			$m = M('weibo_comment_language');
			$data['comment_id']=$param[0];
			$data['language']=$_POST['lang'];
			$m->add($data);
		}
		public function display()
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
