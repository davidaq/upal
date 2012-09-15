<?php
define('THINK_PATH', '1');
define('ADDON_PATH', SITE_PATH . '/addons');

require_once SITE_PATH . '/core/ThinkPHP/Core/Think.class.php';
require_once SITE_PATH . '/core/ThinkPHP/Core/Log.class.php';
require_once SITE_PATH . '/core/ThinkPHP/Db/Db.class.php';
require_once SITE_PATH . '/core/ThinkPHP/Db/Driver/DbMysql.class.php';
require_once SITE_PATH . '/core/sociax/Service.class.php';
require_once SITE_PATH . '/core/sociax/Model.class.php';
require_once SITE_PATH . '/core/sociax/functions.php';
require_once SITE_PATH . '/core/sociax/extend.php';

if (!defined('TEMP_PATH')) define('TEMP_PATH' , '');
C(include SITE_PATH . '/core/sociax/convention.php');
C(include SITE_PATH . '/config.inc.php');