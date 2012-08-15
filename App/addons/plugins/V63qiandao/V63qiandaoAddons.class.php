<?php
class V63qiandaoAddons extends NormalAddons
{
	protected $version = '1.0';
	protected $author  = 'small';
	protected $site    = 'http://t.thinksns.com/space/small';
	protected $info    = 'v63签到插件QQ137283358';
	protected $pluginName = 'v63签到';
	protected $sqlfile = 'install.sql';    // 安装时需要执行的sql文件名
	protected $tsVersion  = "2.5";                               // ts核心版本号

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('V63qiandaoHooks');
        return $hooks;
    }

    public function adminMenu(){
        $menu = array(
					'set' 	  => '系统设置'
				);
        return $menu;
    }

    public function start()
    {
        return true;
    }

	public function install()
	{     
	    $data[type][jttype] ='score';
        $data[type][jfsl] ='10';
        model('AddonData')->lput('v63qiandao', $data)?true:false;
		$db_prefix = C('DB_PREFIX');
		$sql = "CREATE TABLE IF NOT EXISTS `ts_v63qiandao` (
                    `id` int(12) NOT NULL auto_increment,
                    `uid` int(12) NOT NULL,
                    `username` varchar(50) NOT NULL,
                    `time` varchar(50) NOT NULL,
                    `num` int(12) NOT NULL,
                    `xq` varchar(12) NOT NULL,
                    `say` varchar(250) NOT NULL,
                    PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
		if (false !== M()->execute($sql)) {
			return true;
		}
	}

	public function uninstall()
	{
		$db_prefix = C('DB_PREFIX');
		$sql = "DROP TABLE IF EXISTS `{$db_prefix}v63qiandao`;";

		if (false !== M()->execute($sql)) {
			return true;
		}
	}
}