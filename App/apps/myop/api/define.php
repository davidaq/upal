<?php

define('APPS_DIR_NAME',			'apps');
define('MYOP_DIR_NAME',			'myop');

// 所有路径的后面都不带“/”
define('SITE_ROOT', 			substr(dirname(__FILE__), 0, -(strlen(APPS_DIR_NAME) + strlen(MYOP_DIR_NAME) + 6) ));
define('MYOP_ROOT',				SITE_ROOT . '/' . APPS_DIR_NAME . '/' . MYOP_DIR_NAME);
define('API_ROOT',				MYOP_ROOT . '/api');

define('MY_VER',				'0.4');
define('MY_FRIEND_NUM_LIMIT',	2000);
define('SOCIAX_VER',			'2.0');
define('X_RELEASE',				'20100416');