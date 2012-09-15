<?php
/**
 * SpaceFollower
 * 个人空间的粉丝和关注列表插件
 * @uses VisitorAddons
 * @package
 * @version 1.0
 * @copyright 2001-2013 SamPeng
 * @author SamPeng <penglingjun@zhishisoft.com>
 * @license PHP Version 5.2
 */
class SpaceFollowAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = 'SamPeng';
	protected $site    = 'http://weibo.com/sampeng';
	protected $info    = '显示空间粉丝和关注列表，提升互动';
	protected $pluginName = '空间粉丝和关注列表';
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子,钩子和本类的方法是怎样的映射关系
     * @access public
     * @return void
     */
	public function getHooksInfo(){
		$this->apply("home_space_right_bottom","_follower");
		$this->apply("home_space_right_bottom","_following");
	}

	//粉丝
	public function _follower($param)
	{
	    $uid = $param['uid'];
	    $data['uname'] = getUserName($uid);
        $data['list'] = D('Follow','weibo')->getList($uid,"follower",0,null,6);
        $this->assign($data);
        $this->display('follower');
	}

	//关注列表
    public function _following($param){
        $uid = $param['uid'];
        $data['uname'] = getUserName($uid);
        $data['list'] = D('Follow','weibo')->getList($uid,"following",0,null,6);
        $this->assign($data);
        $this->display('following');
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