<?php
class HomeAction extends AdministratorAction
{
	// 统计信息
	public function statistics()
	{
		$statistics = array();

		/*
		 * 重要: 为了防止与应用别名重名，“服务器信息”、“用户信息”、“开发团队”作为key前面有空格
		 */

		// 服务器信息
		$site_version = model('Xdata')->get('siteopt:site_system_version');
		$serverInfo['核心版本']        	= 'ThinkSNS ' . $site_version;
        $serverInfo['服务器系统及PHP版本']	= PHP_OS.' / PHP v'.PHP_VERSION;
        $serverInfo['服务器软件'] 			= $_SERVER['SERVER_SOFTWARE'];
        $serverInfo['最大上传许可']     	= ( @ini_get('file_uploads') )? ini_get('upload_max_filesize') : '<font color="red">no</font>';

        $mysqlinfo = M('')->query("SELECT VERSION() as version");
        $serverInfo['MySQL版本']			= $mysqlinfo[0]['version'] ;

        $t = M('')->query("SHOW TABLE STATUS LIKE '".C('DB_PREFIX')."%'");
        foreach ($t as $k){
            $dbsize += $k['Data_length'] + $k['Index_length'];
        }
        $serverInfo['数据库大小']			= byte_format( $dbsize );
        $statistics[' 服务器信息'] = $serverInfo;
        unset($serverInfo);

        // 用户信息
        $user['当前在线'] = getOnlineUserCount();
        $user['全部用户'] = M('user')->count();
        $user['有效用户'] = M('user')->where('`is_active` = 1 AND `is_init` = 1')->count();
        $statistics[' 用户信息'] = $user;
        unset($user);

        // 应用统计
        $applist = array();
        $res = model('App')->where('`statistics_entry`<>""')->field('app_name,app_alias,statistics_entry')->order('display_order ASC')->findAll();
        foreach ($res as $v) {
        	$d = explode('/', $v['statistics_entry']);
        	$d[1] = empty($d[1]) ? 'index' : $d[1];
        	$statistics[$v['app_alias']] = D($d[0], $v['app_name'])->$d[1]();
        }

        // 开发团队
        $statistics[' 开发团队'] = array(
        	'版权所有'	=> '<a href="http://www.zhishisoft.com" target="_blank">智士软件（北京）有限公司</a>',
            'UI设计'     => '<a href="http://weibo.com/wasdifferent" target="_blank">申川</a>、<a href="http://weibo.com/u/2134567607" target="_blank">马丽稳</a>',
        	'项目经理'	=> '<a href="http://weibo.com/sunan" target="_blank">廖素南</a>',
        	'前端设计'	=> '<a href="http://weibo.com/u/2025142915" target="_blank">牛文涛</a>、<a href="http://weibo.com/u/1087964144" target="_blank">樊翠娥</a>',
        	'开发团队'	=> '<a href="http://weibo.com/sampeng" target="_blank">彭灵俊</a>、<a href="http://weibo.com/thinksns" target="_blank">刘晓庆</a>、<a href="http://weibo.com/cchhuuaann" target="_blank">陈伟川</a>、<a href="http://weibo.com/satan0714" target="_blank">王祚</a>、<a href="http://weibo.com/nonant" target="_blank">冷浩然</a>、<a href="http://weibo.com/mylovehere" target="_blank">韦新红</a>',
        );

        $this->assign('statistics', $statistics);
        $this->display();
	}

	public function update()
	{
		$service = service('System');
		$current_version = $service->getSystemVersion();
		$lastest_version = $service->checkUpdate();

		// 兼容ThinkSNS 2.1 Build 10992的版本号
		foreach ($current_version as $k => $v)
			if ($v <= 0)
				$current_version[$k] = '10992';

		// 自动升级程序仅支持ThinkSNS 2.1 Final(10920或10992)及以上版本
		$system_version = model('Xdata')->get('siteopt:site_system_version');
		$this->assign('system_version', ($system_version == '10920' || $system_version == '10992')
										? 'ThinkSNS 2.1 Final Build '.$system_version
										: $system_version);

		$this->assign('is_support',     ($system_version == '10920' || $system_version == '10992' || $current_version['core'] >= 10992));
		$this->assign('current_version', $current_version);
		$this->assign('lastest_version', $lastest_version);
		$this->display();
	}

	public function doUpdate()
	{
		$_GET['app_name'] = strtolower($_GET['app_name']);
		$apps = model('App')->getAllApp('app_name');
		$apps = getSubByKey($apps, 'app_name');
		$apps[] = 'core';
		if (!in_array($_GET['app_name'], $apps))
			$this->error('参数错误');

		$lastest_version = service('System')->checkUpdate();
		if ($lastest_version['error'])
			$this->error($lastest_version['error_message']);

		$lastest_version = $lastest_version[$_GET['app_name']];
		if (empty($lastest_version))
			$this->error('应用不存在');
		if ($lastest_version['error'])
			$this->error($lastest_version['error_message']);
		if (!$lastest_version['has_update'])
			$this->error($_GET['app_name'] . '已经为最新版本');

		// 升级的SQL文件 (必须)
		// 每个版本必须附带数据升级文件, 并命名为: appname_versionNO.sql, 如: blog_14000.sql/core_14000.sql
		// core的升级文件位于/update/目录
		// app的升级文件位于/apps/app_name/Appinfo/目录
		$sql_files = array();
		foreach ($lastest_version['version_number_list'] as $version_no) {
			if ($lastest_version['current_version_number'] >= $version_no)
				continue ;

			if ($_GET['app_name'] == 'core')
				$path = '/update/core_' . $version_no . '.sql';
			else
				$path = "/apps/{$_GET['app_name']}/Appinfo/{$_GET['app_name']}_{$version_no}.sql";

			if (!is_file(SITE_PATH . $path))
				$this->error("{$path} 不存在");
			else
				$sql_files[] = SITE_PATH . $path;
		}

		// 升级的脚本文件 (可选)
		$before_update_script = '';
		$after_update_script  = '';
		if ($_GET['app_name'] == 'core') {
			$before_update_script = SITE_PATH . '/update/before_update_db.php';
			$after_update_script  = SITE_PATH . '/update/after_update_db.php';
		} else {
			$before_update_script = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/before_update_db.php";
			$after_update_script  = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/after_update_db.php";
		}

		// 执行SQL文件和脚本文件 (TODO: 数据库执行错误时的回滚)
		if (is_file($before_update_script))
			include_once $before_update_script;
		foreach ($sql_files as $file) {
			$res = M('')->executeSqlFile($file);
			if (!empty($res))
				$this->error("SQL错误: {$res['error_code']}");
		}
		if (is_file($after_update_script))
			include_once $after_update_script;

		// 升级完成, 更新版本名称和版本号
		$dao = model('Xdata');
		if ($_GET['app_name'] == 'core') {
			$data['site_system_version'] 		= $lastest_version['lastest_version'];
			$data['site_system_version_number'] = $lastest_version['lastest_version_number'];
			$dao->lput('siteopt', $data);
		} else {
			$dao->put("{$_GET['app_name']}:version_number", $lastest_version['lastest_version_number'], true);
		}

		service('System')->unsetUpdateCache();

		$this->success('升级成功');
	}
}