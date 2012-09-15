<?php
/**
 * 用于ThinkSNS 2.0 漫游注册的工具 [ 修改自uchome_utility.php ]
 *
 * $Id: thinksns_myop_utility.php 2011-01-04 杨德升<desheng.young@gmail.com> $
 */

error_reporting(0);

if ($_POST) {
	$fromManyou = $_POST['fromManyou'];
	if ($fromManyou) {
		require_once './api/define.php';
		
		if (is_file(SITE_ROOT . '/data/thinksns_myop_utility.lock')) {
			echo "LOCK";
		} else {
			echo "OK";
		}
		exit;
	}
	
	require_once './common.php';
	
	$action = $_POST['action'];
	if ($action == 'restore') {
		$my_siteid  = $_POST['my_siteid'];
		$my_sitekey = $_POST['my_sitekeys'][$my_siteid];
		if (!$my_siteid || !$my_sitekey) {
			echo "无效的my_siteid或my_sitekey";
			exit;
		}
		
		$my_siteid	= serialize($my_siteid);
		$my_sitekey	= serialize($my_sitekey);
		$my_status	= serialize('1');
		
		$db_prefix = getDbPrefix();
		doQuery("REPLACE INTO {$db_prefix}system_data (`list`, `key`, `value`) VALUES ('myop', 'my_site_id', '{$my_siteid}'), ('myop', 'my_site_key', '{$my_sitekey}'), ('myop','my_status', '{$my_status}')");
		refreshConfig();
		
		my_show_message("漫游上的站点信息恢复成功 (别忘了更新缓存 和 到 <a href=\"myop.php?my_suffix=%2Fappadmin%2Flist\" target=\"_blank\">管理后台同步MYOP信息</a>)");
	}
} else {
	require_once './common.php';
	
	if ($_GET['q'] == 'forgot') {
		$res = my_site_restore('forgot');
		if ($res['errCode']) {
			$msg = sprintf('操作失败：%s (#%s)', $res['errMessage'], $res['errCode']);
			my_show_message($msg);
		}
		my_forgot($res['sites']);

	} elseif ($_GET['q'] == 'remove') {
		$res = my_site_restore('remove');
		if ($res['errCode']) {
			$msg = sprintf('操作失败：%s (#%s)', $res['errMessage'], $res['errCode']);
			my_show_message($msg);
			exit;
		}

		$db_prefix = getDbPrefix();
		doQuery("DELETE FROM {$db_prefix}system_data WHERE `list` = 'myop' AND `key` IN ('site_key','my_site_id','my_site_key','my_status')");
		refreshConfig();

		$msg = sprintf('操作成功: 将站点标记为无效 (<a href="admincp.php?ac=userapp" target="_blank">你现在可以继续注册新站点了</a>)');
		my_show_message($msg);
		
	} else {
		my_index();
	}
}

function my_header() {
	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>ThinkSNS 漫游平台站点注册工具 | 找回(恢复)站点信息、删除站点信息</title>
		<style type="text/css">
			body {
				width: 70%;
			}
			.msg, .warning {
				padding: 10px;
				border: 1px solid #06c;
				background-color: #c6dff9;
			}
			
			.warning {
				border-color: #ffd700;
				background-color: #ffc;
			}
			.op {
				line-height: 2em;
			}
			.op a { 
				font-size: 1.2em;
			}
		</style>
	</head>
	<body class="sidebars">\n
EOT;
}

function my_footer() {
	$uri = my_get_uri();
	echo <<<EOT
			<hr />
			<div class="warn">
				<h3>注意事项</h3>
				<ul>
					<li>请勿随意公开该文件地址</li>
					<li>使用完毕之后，请<strong>立即删除</strong>该文件</li>
				</ul>
			</div>
			<p><a href="$uri">返回</a> | <a href="http://www.discuz.net/forum-168-1.html" target="_blank">支持论坛</a></p>
	</body>
</html>\n
EOT;
}

function my_index() {
	my_header();
	$siteUrl = getmyopurl();
	
	global $_SITE_CONFIG;
	$my_siteid	= $_SITE_CONFIG['my_site_id'] ? $_SITE_CONFIG['my_site_id'] : '无';
	$my_sitekey = $_SITE_CONFIG['my_site_key'] ? $_SITE_CONFIG['my_site_key'] : '无';
	$my_status	= $_SITE_CONFIG['my_site_id'] ? ($_SITE_CONFIG['my_status'] ? '开启': '关闭') : '无';
	$uri		= my_get_uri();
	echo <<<EOT
		<form method="POST">
			<dl>
				<dt>当前站点地址:</dt>
				<dd>$siteUrl</dd>

				<dt>当前my_siteid:</dt>
				<dd>$my_siteid</dd>

				<dt>当前my_sitekey:</dt>
				<dd>$my_sitekey</dd>

				<dt>当前Manyou状态:</dt>
				<dd>$my_status</dd>
			</dl>
			<p class="op">
				如果您要恢复漫游数据，请<a href="$uri?q=forgot">点这里继续</a> <br />
				如果您要重新注册到漫游平台，请<a href="$uri?q=remove">点这里继续</a>
			</p>
			<p class="warning">以上操作都可能导致漫游中当前站点的应用无法使用、用户信息丢失！！</p>
		</form>
EOT;
	my_footer();
}

function my_forgot($sites) {
	my_header();
	my_title('恢复漫游上的站点信息');
	$table = "<table border=\"1\">
		<tr>
			<th></th>
			<th>my_siteid</th>
			<th>my_sitekey</th>
			<th>sitekey</th>
			<th>创建时间</th>
		</tr>";
	foreach($sites as $site) {
		$my_siteid = $site['my_siteid'];
		$table .= "<tr>
				<td><input type='radio' name='my_siteid' value='$my_siteid' /></td>
				<td>$site[my_siteid]</td>
				<td>$site[my_sitekey]</td>
				<td>$site[sitekey]</td>
				<td>$site[my_created]</td>
				<input type='hidden' name='my_sitekeys[$my_siteid]' value='$site[my_sitekey]' />
			</tr>";
	}
	$table .= "</table>\n";
	echo <<<EOT
		<form method="POST">
			$table
			<p>
				<input type="hidden" name="action" value="restore" />
				<input type="submit" name="submit" value="恢复漫游数据" />
				<input type="reset" name="reset" value="重置" />
			</p>
		</form>
EOT;
	my_footer();
}

function my_title($title = 'index') {
	echo "<h2>$title</h2>\n";
	return true;
}

function my_site_restore($op) {
	$url = 'http://api.manyou.com/uchome.php';
	global $_SITE_CONFIG;
	
	$siteUrl = getmyopurl() . '/';
	$postString = sprintf('action=%s&siteUrl=%s&op=%s', 'webmaster', $siteUrl, $op);
	$response = uc_fopen2($url, 0, $postString, '', false, $_SITE_CONFIG['my_ip']);
	$res = unserialize($response);
	
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}

	if ($res['errCode']) {
		return $res;
	}

	// lock file
	$fp = fopen(SITE_ROOT . '/data/thinksns_myop_utility.lock', 'w');
	if ($fp === false) {
		my_show_message(sprintf('请确保 <strong>%s/data/thinksns_myop_utility.lock</strong> 文件可写!', SITE_ROOT));
	}
	fclose($fp);
	return $res['result'];
}

function my_show_message($msg) {
	my_header();
	printf('<p class="msg">%s</p>', $msg);
	my_footer();
	exit;
}

function my_get_uri() {
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	return $uri;
}
?>
