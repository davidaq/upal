<?php
if (!defined('SITE_PATH')) exit();

return array(
    // 数据库常用配置
    'DB_TYPE'            => 'mysql',            // 数据库类型
    'DB_HOST'            => '服务器地址',       // 数据库服务器地址
    'DB_NAME'            => '数据库名',         // 数据库名
    'DB_USER'            => '数据库用户名',     // 数据库用户名
    'DB_PWD'             => '数据库密码',       // 数据库密码
    'DB_PORT'            => 3306,               // 数据库端口
    'DB_PREFIX'          => '数据库表前缀',     // 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
    'DB_CHARSET'         => 'utf8',             // 数据库编码
    'DB_FIELDS_CACHE'    => true,               // 启用字段缓存

    //'COOKIE_DOMAIN'    => '.thinksns.com',    // COOKIE域,请替换成你自己的域名 以.开头
	// COOKIE 加密串
	'SECURE_CODE'       =>  'ThinkSNS!Q@W#E$R',	// COOKIE加密串.每个站点都不同,否则有安全漏洞.
    // 默认应用
    'DEFAULT_APPS'       => array('api', 'admin', 'home', 'myop', 'weibo', 'wap', 'w3g'),

    // 是否开启调试模式 (开启AllInOne模式时设置无效, 将自动置为false)
    'APP_DEBUG'          => false,

    // 是否开启URL Rewrite
    'URL_ROUTER_ON'      => true,

    // 是否开启模版缓存
    'TMPL_CACHE_ON'      => false,

    // 缓存相关设置
    //'DATA_CACHE_TYPE'   => 'memcache', //支持APC、Memcached、File等，默认为File
    //'MEMCACHE_HOST'   => '127.0.0.1:11211', //如果不是这个默认地址需要修改

    // 404页面
    'ERROR_PAGE'         => U('home/Public/error404'),
);