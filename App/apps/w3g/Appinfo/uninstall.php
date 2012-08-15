<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// 无独立数据库数据
);

foreach ($sql as $v) {
	$res = M('')->execute($v);
}