<?php
/*
 * ThinkSNS执行与UCenter的同步
 *
 * 本文件在TS核心框架中引入
 */
define('UC_SYNC', 0); // 0:关闭同步 1:开启同步

if(UC_SYNC) {
	include_once SITE_PATH.'/api/uc_client/uc_config.inc.php';
	include_once SITE_PATH.'/api/uc_client/client.php';
}
include_once SITE_PATH.'/api/uc_client/common.php';