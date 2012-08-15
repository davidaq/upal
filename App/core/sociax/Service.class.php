<?php
// +----------------------------------------------------------------------
// | OpenSociax [ open your team ! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.sociax.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: genixsoft.net <智士软件>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 服务接口抽象类
 +------------------------------------------------------------------------------
 * @category	core
 * @package		core
 * @author		liuxiaoqing <liuxiaoqing@thinksns.com>
 * @version		$0.1$
 +------------------------------------------------------------------------------
 */
abstract class Service extends Think {
	/* 服务逻辑相关方法 */

    // 执行服务的接口方法
    abstract public function run();

	/* 后台管理相关方法 */

    // 启动服务的接口方法
    //abstract public function _start();
    // 停止服务的接口方法
    //abstract public function _stop();
    // 安装服务的接口方法
    //abstract public function _install();
    // 卸载服务的接口方法
    //abstract public function _uninstall();
}
?>