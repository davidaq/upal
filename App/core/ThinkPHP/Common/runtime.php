<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

// 生成核心编译缓存
function build_runtime() {
    // 加载常量定义文件
    require THINK_PATH.'/Common/defines.php';
    // 加载路径定义文件
    require defined('PATH_DEFINE_FILE')?PATH_DEFINE_FILE:THINK_PATH.'/Common/paths.php';
    // 定义核心编译的文件
    $runtime[]  =  THINK_PATH.'/Common/functions.php'; // 系统函数
    if(version_compare(PHP_VERSION,'5.2.0','<') )
        // 加载兼容函数
        $runtime[]	=	 THINK_PATH.'/Common/compat.php';
    // 核心基类必须加载
    $runtime[]  =  THINK_PATH.'/Core/Think.class.php';
    // 读取核心编译文件列表
    if(is_file(CONFIG_PATH.'core.php')) {
        // 加载项目自定义的核心编译文件列表
        $list   =  include CONFIG_PATH.'core.php';
    }else{
//        if(defined('THINK_MODE')) {
//            // 根据设置的运行模式加载不同的核心编译文件
//            $list   =  include THINK_PATH.'/Mode/'.strtolower(THINK_MODE).'.php';
//        }else{
            // 默认核心
            $list   =  include THINK_PATH.'/Common/core.php';
//        }
    }
    $runtime   =  array_merge($runtime,$list);
    // 加载核心编译文件列表
    foreach ($runtime as $key=>$file){
        if(is_file($file))  require $file;
    }
    // 生成核心编译缓存 去掉文件空白以减少大小
//    if(!defined('NO_CACHE_RUNTIME')) {
//        $compile = defined('RUNTIME_ALLINONE');
//        $content  = compile(THINK_PATH.'/Common/defines.php',$compile);
//        $content .= compile(defined('PATH_DEFINE_FILE')?   PATH_DEFINE_FILE  :   THINK_PATH.'/Common/paths.php',$compile);
//        foreach ($runtime as $file){
//            $content .= compile($file,$compile);
//        }
//        if(defined('STRIP_RUNTIME_SPACE') && STRIP_RUNTIME_SPACE == false ) {
//            file_put_contents(RUNTIME_PATH.'~runtime.php','<?php'.$content);
//        }else{
//            file_put_contents(RUNTIME_PATH.'~runtime.php',strip_whitespace('<?php'.$content));
//        }
//        unset($content);
//    }
}

?>