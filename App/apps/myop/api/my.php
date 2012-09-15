<?php
define('IN_MYOP', TRUE);
error_reporting(E_ERROR | E_PARSE);

require_once './define.php';
require_once './function.php';
require_once API_ROOT . '/class/My.class.php';
require_once API_ROOT . '/class/APIErrorResponse.class.php';
require_once API_ROOT . '/class/APIResponse.class.php';
require_once API_ROOT . '/class/MyBase.class.php';

//所有URL的后面都不带“/”
define('SITE_PATH', 		getcwd());
define('MYOP_URL',			getmyopurlInApi());
define('UC_URL',			MYOP_URL);
define('SITE_URL',			substr( MYOP_URL, 0, -(strlen(APPS_DIR_NAME) + strlen(MYOP_DIR_NAME) + 2) ));
define('PUBLIC_URL',		SITE_URL . '/public');

//系统配置
$_SITE_CONFIG				= array();
refreshConfig();

//漫游平台的全局变量
$_MY_GLOBAL					= array();
$_MY_GLOBAL['timestamp']	= time();

//个人空间
$space						= array();

$server 	= new My();
$response 	= $server->parseRequest();
echo $server->formatResponse($response);