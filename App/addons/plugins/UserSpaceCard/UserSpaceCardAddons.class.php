<?php
class UserSpaceCardAddons extends SimpleAddons
{
	protected $version = '1.0';
	protected $author  = '海虾';
	protected $site    = 'http://www.thinksns.com';
	protected $info    = '用户信息小名片';
	protected $pluginName = '小名片';
	protected $sqlfile = '';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

	public function getHooksInfo()
	{
		$this->apply("get_user_space_output","changeSpaceTag");
		$this->apply("public_footer","showSpaceCard");
	}

    //在获取用户空间链接的方法中.get_user_space_output插件位中替换a标签属性
    public function changeSpaceTag($param)
    {
        $param['space_info'] = str_replace('class=',"rel='face' uid='".$param['uid']."' class=",$param['space_info']);
    }

    //在public_footer插件位中增加小名片展示的js代码
    public function showSpaceCard()
	{
		$isLogin = empty($_SESSION['mid']) ? false : true;
		$this->assign('isLogin', $isLogin);
		$this->display('card');
    }
}