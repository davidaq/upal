<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> 'w3g',
	// 应用简介 [必填]
	'DESCRIPTION'				=> '微博3G版',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	// 前台展示图标 [必填]（0:不展示，1:展示）
	'APP_SHOW'					=> '1',
	// 应用图标 [必填]
	'ICON_URL'					=> SITE_URL . '/apps/vote/Appinfo/ico_app.png',
	// 应用图标 [必填]
	'LARGE_ICON_URL'			=> SITE_URL . '/apps/vote/Appinfo/ico_app_large.png',
	// 后台入口 [选填]
	'ADMIN_ENTRY'				=> 'Admin/index',
	// 统计入口 [选填]（格式：Model/method）
	'STATISTICS_ENTRY'			=> '',
	// 应用的主页 [选填]
	'HOMEPAGE_URL'				=> '',
	// 应用类型
	'CATEGORY'					=> '工具',
	// 发布日期
	'RELEASE_DATE'				=> '2011-7-29',
	// 最后更新日期
	'LAST_UPDATE_DATE'			=> '2011-7-29',
	
	// 附加链接名称 [选填]
	'SIDEBAR_TITLE'				=> '发表',
	// 附件链接的入口 [选填]（格式：Action/act）
	'SIDEBAR_ENTRY'				=> 'Index/post',
	// 附加链接的图标 [选填]
	'SIDEBAR_ICON_URL'			=> '',
	// 是否在附加链接中展示子菜单 [选填]（0:否 1:是）
	'SIDEBAR_SUPPORT_SUBMENU'	=> '0',
	
	// 作者名 [必填]
	'AUTHOR_NAME'				=> 'King',
	// 作者Email [必填]
	'AUTHOR_EMAIL'				=> 'liuxiaoqing@zhishisoft.com',
	// 作者主页 [选填]
	'AUTHOR_HOMEPAGE_URL'		=> '',
	// 贡献者姓名 [选填]
	'CONTRIBUTOR_NAMES'			=> '刘晓庆、韦新红、范翠娥、曹莹、程丹华',
);
?>