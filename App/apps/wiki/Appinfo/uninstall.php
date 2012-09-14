<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	"DROP TABLE IF EXISTS `{$db_prefix}wiki`;",
	"DROP TABLE IF EXISTS `{$db_prefix}wiki_member;",
	"DROP TABLE IF EXISTS `{$db_prefix}wiki_post;",
	"DROP TABLE IF EXISTS `{$db_prefix}wiki_tag;",
);

foreach ($sql as $v)
	M('')->execute($v);
