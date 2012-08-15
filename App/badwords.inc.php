<?php
/*
 * 说明：游客访问的黑/白名单，不需要开放的，可以注释掉
 * 规则：设置的key由APP_NAME/MODULE_NAME/ACTION_NAME组成，只要设置在当前数组中，游客就可以访问
 * 例如：设置成‘blog/Index/news’ => true, 用户就可以访问最新博客页面，否则必须先登录到系统才能访问
 */
return array(
	'badwords' => array(
        //广告
		'ad'	        =>	array('title','content'),
        //插件
		'addons'	    =>	array('pluginName ','author','info'),
        //应用列表
        'app'	        =>	array('app_alias ','description','category','sidebar_title'),
        //地域
        'area'	        =>	array('title'),
        //附件名
        'attach'		=>  array('name'),
        //博客配置
		'blog'			=>  array('name','title','category_title','content'),
		'blog_category' =>	array('name'),
        //评论内容
        'comment'		=>  array('comment'),
        //积分设置
        'credit_setting'=>	array('alias'),
        'credit_type'   =>	array('alias'),
        //举报
        'denounce'	    =>	array('reason','content'),
        //文章配置
        'document'	    =>	array('title','content'),
        //活动配置
		'event'			=>	array('title','contact','explain','address'),
		'event_opts'	=>	array('costExplain'),
		'event_type'	=>	array('name'),
		'event_user'	=>	array('contact'),
        //表情包
		'expression'	=>	array('title'),
        //游戏
		'fgamelist'		=>	array('fgname','fginstruction'),
        //论坛
        'forum'		    =>	array('name','forum_intro'),
        'forum_credit'	=>	array('info'),
        'forum_filter_word'	=>	array('name'),
        'forum_log'	    =>	array('content'),
        'forum_post'	=>	array('title','content'),
        'forum_sign'	=>	array('name'),
        'forum_tclass'	=>	array('name'),
        'forum_template_type'	=>	array('name'),
        'forum_template_widget'	=>	array('label','info','data'),
        'forum_topic'	=>	array('title','info','data'),
        //礼物配置
		'gift'		    =>	array('name'),
		'gift_category' =>	array('name'),
		'gift_user'		=>	array('sendInfo'),
        //群组配置
		'group'			=>	array('name','intro','announce'),
		'group_attachment'=>	array('name'),
		'group_category'=>	array('title'),
		'group_member'	=>	array('reason'),
		'group_post'	=>	array('content'),
		'group_topic'	=>	array('title'),
		'group_topic_category'	=>	array('title'),
		'group_weibo'	=>	array('content'),
		'group_weibo_comment'	=>	array('content'),
		'group_weibo_topic'	=>	array('name'),
        //站内信
		'message_content' 	=>array('content'),
		'message_list'	=>	array('title'),
        //节点
        //'node'		    =>  array('app_name','app_alias','mod_name','mod_alias','act_name','act_alias'),
        //相册配置
		'photo'			=>  array('name'),
		'photo_album'	=>	array('name'),
        //招贴配置
		'poster'	    =>	array('title','content','contact'),
		'poster_small_type'	   =>	array('label','name'),
		'poster_type'	=>	array('explain'),
        //大屏幕
        'screen'	    =>	array('title','keyword'),
        //站点设置
        'sitelist_site'	=>  array('name','description'),
        //调查问卷
		'survey'		=>	array('title','description'),
        //标签
        'tag'			=> 	array('tag_name'),
        //模版管理
		'template'		=>	array('alias','title','body'),
        //用户
		'user_set'		=>	array('fieldname'),
		'user_verified'	=>	array('reason','info'),
		'ucenter_user_link'		=>	array('uc_username'),
        //投票配置
		'vote'			=>	array('title','explain'),
		'vote_opt'		=>	array('name'),
		'vote_user'		=>	array('opts'),
		//微博配置
		'user_group_link'	=>array('user_group_title '),
		'user'			=>	array('uname'),
		'weibo'			=>	array('content'),
		'weibo_comment'	=>	array('content'),//微博评论
		'weibo_follow_group'	=>	array('title'),
		'weibo_topic'	=>	array('name'),
		'weibo_topics'	=>	array('note','content'),
	)
);