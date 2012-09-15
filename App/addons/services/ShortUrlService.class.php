<?php
// +----------------------------------------------------------------------
// | ThinkSNS
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.thinksns.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: liuxiaoqing <liuxiaoqing@zhishisoft.com>
// +----------------------------------------------------------------------
//
/**
 * 短地址服务
 */
class ShortUrlService extends Service{
	protected $option	=	array();
	/**
	 * 获取给定URL的短地址
	 * @param string $url 原URL地址
	 * @return string 短URL地址
	 */
	public function __construct(){
		$this->option = model('Xdata')->lget('shorturl');
	}
	public function getShort($url) {
		Addons::hook('get_short_url', array('url' => & $url));
		return $url;
	}
	//运行服务，系统服务自动运行
	public function run(){
		return true;
	}
	//启动服务，未编码
	public function _start(){
		return true;
	}
	//停止服务，未编码
	public function _stop(){
		return true;
	}
	//安装服务，未编码
	public function _install(){
		return true;
	}
	//卸载服务，未编码
	public function _uninstall(){
		return true;
	}
}
?>