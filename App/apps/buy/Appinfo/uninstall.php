<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	"DROP TABLE IF EXISTS `{$db_prefix}buy`;",
);

foreach ($sql as $v)
	M('')->execute($v);
