<?php
return  array(
	/**
	'home_addComment'   => array(
		'title' => '{actor} 在您的'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 发表了评论',
		'body'  => $content.' <div class="right alR mr10"><a href="javascript:void(0);return false;" onclick="">回复</a></div>',
	),
	'home_replyComment'	=> array(
		'title'	=> '{actor}: ' . $content,
		'body'	=> '回复我在'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 的评论: '.$my_content.' <div class="right alR mr10"><a href="javascript:void(0);return false;" onclick="">回复</a></div>',
	),
	**/

	'home_addComment'   => array(
		'title' => '{actor} 在您的'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 发表了评论',
		'body'  => $content,
	),
	'home_replyComment'	=> array(
		'title'	=> '{actor}: ' . $content,
		'body'	=> '回复我在'.$app_alias.' <a href="'.$url.'" target="_blank">'.$title.'</a> 的评论: '.$my_content,
	),
);