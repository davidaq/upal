<?php
/**
 * 调试模式的配置文件
 * 
 * 开启调试模式(APP_DEBUG)时自动加载, 关闭调试模式时不加载
 */
return array(
	/*
	 * 日志记录
	 */
    // 是否记录日志
    'LOG_RECORD'		=> false,
    // 日志记录的级别
    'LOG_RECORD_LEVEL'	=> array('EMERG','ALERT','CRIT','ERR','SQL'),

	/*
	 * 数据库
	 */
	// 是否缓存数据库字段
    'DB_FIELDS_CACHE'	=> true,

	/*
	 * 页面缓存
	 */
	// 是否缓存模版的包含页面
	'TMPL_CACHE_ON'		=> false,

    /*
     * 页面Trace展示
     */
	// 是否显示页面Trace信息
    'SHOW_PAGE_TRACE'	=> true,
	// 是否显示运行时间
    'SHOW_RUN_TIME'		=> true,
	// 是否显示详细的运行时间
    'SHOW_ADV_TIME'		=> true,
	// 是否显示数据库查询和写入次数
    'SHOW_DB_TIMES'		=> true,
	// 是否显示缓存操作次数
    'SHOW_CACHE_TIMES'	=> true,
	// 是否显示内存开销
    'SHOW_USE_MEM'		=> true,
	// 是否检查文件的大小写 (对Windows平台有效)
    'APP_FILE_CASE'		=> true,
);