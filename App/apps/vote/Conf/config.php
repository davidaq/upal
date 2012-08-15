<?php
//由ThinkPHP工具箱生成的配置文件
if (!defined('THINK_PATH')) exit();
$voteConfig = array (
        'LANG_SWITCH_ON' => true,
	    'DEBUG_MODE'     => false,
        'DEFAULT_ACTION' => 'index',
        );
$array = require_once( SITE_PATH.'/config.inc.php' );
$array = array_merge( $voteConfig,$array );
return $array;
?>
