<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// photo数据
	"DROP TABLE IF EXISTS `{$db_prefix}photo`;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_album;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_index;",
	"DROP TABLE IF EXISTS `{$db_prefix}photo_mark;",
	// ts_system_data数据
	"DELETE FROM `{$db_prefix}system_data` WHERE `list` = 'photo'",
	// 模板数据
	"DELETE FROM `{$db_prefix}template` WHERE `name` = 'photo_create_weibo' OR `name` = 'photo_share_weibo' OR `name` = 'album_share_weibo';",
	// 积分规则
	"DELETE FROM `{$db_prefix}credit_setting` WHERE `type` = 'photo';",
);

foreach ($sql as $v)
	M('')->execute($v);