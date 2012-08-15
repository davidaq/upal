<?php
/**
 * 评论widget
 *
 * @author SamPeng <sampeng87@gmail.com>
 * @author daniel <desheng.young@gmail.com>
 */
class CommentWidget extends Widget {

	/**
	 * 评论Widget
	 *
	 * $data的参数说明:
	 * array(
	 * 	'type'(必须)				    => '应用名',			 // 作用: "同时发一条微博"时, 微博的来源
	 * 	'appid'(必须)				=> '元素的唯一ID',		 // 与'table', 'id_field'对应.
	 * 	'author_uid'(必须)			=> '作者ID',			 // 作用: 用户评论后给作者发提醒通知
	 * 	'title'(必须)				=> '元素标题',	     //
	 * 	'url'(必须)					=> '元素的URL',		 //
	 * 	'table'(必须)				=> '不含前缀的表名',	 // 与'appid', 'id_field'对应
	 * 	'id_field'(必须)			    => '标示ID的字段名',	 // 与'table', 'appid'对应
	 * 	'comment_count_field'(必须)	=> '标示评论数的字段名', // 作用: 用户评论后, 自动增加评论数
	 * )
	 */
	public function render($data) {
		if (! empty ( $data ['url'] ))
			$data ['url'] = urlencode ( $data ['url'] );

		$tpl = $GLOBALS['ts']['user'] ? 'Comment.html' : 'CommentNoLogin.html';
		$content = $this->renderFile ( ADDON_PATH . '/widgets/' . $tpl, $data );
		return $content;
	}

}