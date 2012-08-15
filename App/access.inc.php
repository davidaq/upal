<?php
/*
 * 说明：游客访问的黑/白名单，不需要开放的，可以注释掉、或删除
 * 规则：设置的key由APP_NAME/MODULE_NAME/ACTION_NAME组成，只要设置在当前数组中，游客就可以访问
 * 例如：设置成‘blog/Index/news’ => true, 用户就可以访问最新博客页面，否则必须先登录到系统才能访问
 */
return array(
	"access"	=>	array(
		//核心模块
		'home/Public/*'				=> true, // 公共模块注册、登录等,不可删除
		'admin/*/*'					=> true, // 管理后台的权限由它自己控制,不可删除
		'home/Index/index'      	=> true, // 默认首页
		'home/Space/*'      		=> true, // 个人空间
		'api/*/*'					=> true, // Api接口
		'wap/*/*'					=> true, // Wap版
        'w3g/*/*'					=> true, // 3G版
		'phptest/*/*'				=> true, // 测试专用,可以删除
		'home/Square/*'				=> true, // 微博广场的权限由管理后台控制
		'home/User/topics'			=> true, // 话题列表

		'home/Widget/renderWidget' 	=> true, // 未登录时渲染插件
		'home/Widget/addonsRequest' => true, // 未登录时下调用钩子相关操作
		'home/Widget/weiboShow'		=> true, // 小工具：微博秀
		'home/Widget/share'			=> true, // 小工具：站外分享
		'home/Widget/webpageComment'=> true, // 小工具：微博评论框

		//博客配置
		'blog/Index/news'			=> true, // 最新博客
		'blog/Index/show'			=> true, // 博客内容
		'blog/Index/personal'		=> true, // 个人博客

		//相册配置
		'photo/Index/photo'			=> true, // 照片展示
		'photo/Index/album'			=> true, // 相册展示
		'photo/Index/photos'		=> true, // 所有照片

		//群组配置
		'group/Index/index'			=> true, // 群组首页
		'group/Index/newIndex'		=> true, // 群组首页
		'group/Index/search'		=> true, // 分类列表
		'group/Group/index'			=> true, // 单群首页
	)
);