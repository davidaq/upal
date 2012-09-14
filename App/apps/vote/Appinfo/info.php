<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> '投票',
	// 应用简介 [必填]
	'DESCRIPTION'				=> '想分享你的投票给你的好友么，快来发起投票吧',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	// 应用图标 [必填]
	'ICON_URL'					=> SITE_URL . '/apps/vote/Appinfo/ico_app.png',
	// 应用图标 [必填]
	'LARGE_ICON_URL'			=> SITE_URL . '/apps/vote/Appinfo/ico_app_large.png',
	// 版本号 [必填]
	'VERSION_NUMBER'			=> '33566',
	// 后台入口 [选填]
	'ADMIN_ENTRY'				=> 'Admin/index',
	// 统计入口 [选填]（格式：Model/method）
	'STATISTICS_ENTRY'			=> 'VoteStatistics/statistics',
	// 应用的主页 [选填]
	'HOMEPAGE_URL'				=> '',
	// 应用类型
	'CATEGORY'					=> '工具',
	// 发布日期
	'RELEASE_DATE'				=> '2008-08-08',
	// 最后更新日期
	'LAST_UPDATE_DATE'			=> '2012-07-12',

	// 附加链接名称 [选填]
	'SIDEBAR_TITLE'				=> '发表',
	// 附件链接的入口 [选填]（格式：Action/act）
	'SIDEBAR_ENTRY'				=> 'Index/addPoll',
	// 附加链接的图标 [选填]
	'SIDEBAR_ICON_URL'			=> '',
	// 是否在附加链接中展示子菜单 [选填]（0:否 1:是）
	'SIDEBAR_SUPPORT_SUBMENU'	=> '0',

	// 作者名 [必填]
	'AUTHOR_NAME'				=> '陈伟川',
	// 作者Email [必填]
	'AUTHOR_EMAIL'				=> '',
	// 作者主页 [选填]
	'AUTHOR_HOMEPAGE_URL'		=> '',
	// 贡献者姓名 [选填]
	'CONTRIBUTOR_NAMES'			=> '赵杰, 杨德升',
);
?>