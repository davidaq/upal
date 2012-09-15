<?php

class SpaceStyleAddons extends NormalAddons {

    protected $version = '2.0';
    protected $author = '智士软件';
    protected $site = 'http://www.thinksns.com';
    protected $info = '用户自定义风格官方优化版';
    protected $pluginName = '空间换肤-官方优化版';
    protected $tsVersion = "2.5";         // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */

    public function getHooksInfo() {

        $hooks['list'] = array('SpaceStyleHooks');
        return $hooks;
    }

    public function adminMenu() {
        // $menu = array('config' => '皮肤管理');
        // return $menu;
    }

    public function start() {

    }

    public function install() {
        $db_prefix = C('DB_PREFIX');
        $sql = "CREATE TABLE IF NOT EXISTS `{$db_prefix}user_change_style` (
                  `uid` int(11) unsigned NOT NULL,
                  `classname` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
                  `background` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
                  UNIQUE KEY `uid` (`uid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        M()->execute($sql);
        return true;
    }

    public function uninstall() {
        $db_prefix = C('DB_PREFIX');
        $sql = "DROP TABLE `{$db_prefix}user_change_style`;";
		    M()->execute($sql);
        return true;
    }
}