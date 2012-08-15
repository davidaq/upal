<?php
/**
 * WeiboTypeAddons
 * 发布的微博类型插件
 * @uses NormalAddons
 * @package
 * @version $id$
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2 {@link www.sampeng.org}
 */
class WeiboTypeAddons extends NormalAddons
{
	protected $version = "1.0";
	protected $author  = "冷浩然,杨德升,陈伟川,彭灵俊";
	protected $site    = "t.thinksns.com";
	protected $info    = "控制发布微博的类型插件";
    protected $pluginName = "微博类型";
    protected $tsVersion = '2.5';

    public function getHooksInfo(){
        //,'VideoHooks','MusicHooks','FileHooks'
        $hooks['list'] = array('PublicTypeHooks', 'ImageHooks', 'VideoHooks', 'MusicHooks','VoteHooks','BlogHooks');
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
	    return array('config'=>"全局设置");
    }

    public function start()
    {
        return true;
    }

    public function install()
    {
        $data['open']=array('1','3','4','7','8');
        $data['file']['size'] = 2048;
        $data['file']['ext']  = 'jpg;gif;png;jpeg;bmp;zip;rar;doc;xls;ppt;docx;xlsx;pptx;pdf';
        $data['image']['size'] = 2048;
        $data['image']['limit'] = 10;
        $data['image']['type'] = 'jpg;gif;png;jpeg';
        model('AddonData')->lput('weibo_type', $data)?true:false;
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
