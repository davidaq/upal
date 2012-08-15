<?php
class SquareAppShowAddons extends NormalAddons
{
	protected $version = '1.0';
	protected $author  = '陈伟川';
	protected $site    = 'http://weibo.com/cchhuuaann';
	protected $info    = '广场应用展示，综合站点信息';
	protected $pluginName = '广场应用展示';
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('SquareAppShowHooks');
        return $hooks;
    }

    public function adminMenu(){
        $menu = array('config' => '展示配置');
        return $menu;
    }

    public function start()
    {

    }

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}