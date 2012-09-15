<?php
/**
 * XiamiAddons
 */
class XiamiAddons extends NormalAddons
{
	protected $version = "1.0";
	protected $author  = "流光";
	protected $site    = "http://t.thinksns.com/space/small";
	protected $info    = "控制发布微博的类型插件";
    protected $pluginName = "虾米音乐分享";
    protected $tsVersion = '2.5';

    public function getHooksInfo(){
        //,'VideoHooks','MusicHooks','FileHooks'
        $hooks['list'] = array('XiamiHooks');
        return $hooks;
    }
	/**
	 * 该插件的管理界面的处理逻辑。
	 * 如果return false,则该插件没有管理界面。
	 * 这个接口的主要作用是，该插件在管理界面时的初始化处理
	 * @param string $page
	 */
    public function adminMenu()
    {
	    //return array('config'=>"全局设置");
    }

    public function start()
    {
        return true;
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
