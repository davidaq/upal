<?php
/*
 * tThinkSNS 安装文件,修改自pbdigg.
 */

error_reporting(0);
session_start();
define('THINKSNS_INSTALL', TRUE);
define('THINKSNS_ROOT', str_replace('\\', '/', substr(dirname(__FILE__), 0, -7)));

$_TSVERSION = '2.8';

include 'install_function.php';
include 'install_lang.php';

$timestamp				=	time();
$ip						=	getip();
$installfile			=	't_thinksns_com.sql';
$thinksns_config_file	=	'config.inc.php';

//判断是否安装过
header('Content-Type: text/html; charset=utf-8');
if (file_exists('install.lock'))
{
	exit($i_message['install_lock']);
}
if (!is_readable($installfile))
{
	exit($i_message['install_dbFile_error']);
}
$quit = false;
$msg = $alert = $link = $sql = $allownext = '';

$PHP_SELF = addslashes(htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
set_magic_quotes_runtime(0);
if (!get_magic_quotes_gpc())
{
	addS($_POST);
	addS($_GET);
}
@extract($_POST);
@extract($_GET);
?>
<html>
<head>
<title><?php echo $i_message['install_title']; ?></title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<link href="images/style.css" rel="stylesheet" type="text/css" />
<body>
<div id='content'>
<div id='pageheader'>
	<div id="logo"><img src="images/thinksns.gif" width="260" height="80" border="0" alt="ThinkSNS" /></div>
	<div id="version" class="rightheader">Version <?php echo $_TSVERSION; ?></div>
</div>
<div id='innercontent'>
	<h1>ThinkSNS <?php echo $_TSVERSION, ' ', $i_message['install_wizard']; ?></h1>
<?php
if (!$v)
{
?>
<div class="botBorder">
<p><span class='red'><?php echo $i_message['install_warning'];?></span></p>
</div>
<div class="botBorder">
<?php echo $i_message['install_intro'];?>
</div>
<form method="post" action="install.php?v=1">
<p class="center"><input type="submit" class="submit" value="<?php echo $i_message['install_start'];?>" /></p>
</form>
<?php
}
elseif ($v == '1')
{
?>
<h2><?php echo $i_message['install_license_title'];?></h2>
<p>
<textarea class="textarea" readonly="readonly" cols="50">
<?php echo $i_message['install_license'];?>
</textarea>
</p>
<form action="install.php?v=2" method="post">
<p><label><input type="checkbox" name="agree" value="1" onClick="if(this.checked==true){this.form.next.disabled=''}else{this.form.next.disabled='true'}" checked="checked" /><?php echo $i_message['install_agree'];?></label></p>
<p class="center"><input type="submit" style="width:200px;" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" /></p>
</form>
<?php
}
elseif ($v == '2')
{
if ($agree == 'no')
{
	echo '<script>alert('.$i_message['install_disagree_license'].');history.go(-1)</script>';
}
$dirarray = array (
	'data',
	'_runtime',
	'install',
);
$writeable = array();
foreach ($dirarray as $key => $dir)
{
	if (writable($dir))
	{
		$writeable[$key] = $dir.result(1, 0);
	}
	else
	{
		$writeable[$key] = $dir.result(0, 0);
		$quit = TRUE;
	}
}
?>
<div class="shade">
<div class="settingHead"><?php echo $i_message['install_env'];?></div>
<h5><?php echo $i_message['php_os'];?></h5>
<p><?php echo PHP_OS;result(1, 1);?></p>
<h5><?php echo $i_message['php_version'];?></h5>
<p>
<?php
echo PHP_VERSION;
if (PHP_VERSION < '5.1.2')
{
	result(0, 1);
	$quit = TRUE;
}
else
{
	result(1, 1);
}
?></p>
<h5><?php echo $i_message['file_upload'];?></h5>
<p>
<?php
if (@ini_get('file_uploads'))
{
	echo $i_message['support'],'/',@ini_get('upload_max_filesize');
}
else
{
	echo '<span class="red">'.$i_message['unsupport'].'</span>';
}
result(1, 1);
?></p>
<h5><?php echo $i_message['php_extention'];?></h5>
<p>
<?php
if (extension_loaded('mysql'))
{
	echo 'mysql:'.$i_message['support'];
	result(1, 1);
}
else
{
	echo '<span class="red">'.$i_message['php_extention_unload_mysql'].'</span>';
	result(0, 1);
	$quit = TRUE;
}
?></p>
<p>
<?php
if (extension_loaded('gd'))
{
	echo 'gd:'.$i_message['support'];
	result(1, 1);
}
else
{
	echo '<span class="red">'.$i_message['php_extention_unload_gd'].'</span>';
	result(0, 1);
	$quit = TRUE;
}
?></p>
<p>
<?php
if (extension_loaded('curl'))
{
	echo 'curl:'.$i_message['support'];
	result(1, 1);
}
else
{
	echo '<span class="red">'.$i_message['php_extention_unload_curl'].'</span>';
	result(0, 1);
	$quit = TRUE;
}
?></p>
<p>
<?php
if (extension_loaded('mbstring'))
{
	echo 'mbstring:'.$i_message['support'];
	result(1, 1);
}
else
{
	echo '<span class="red">'.$i_message['php_extention_unload_mbstring'].'</span>';
	result(0, 1);
	$quit = TRUE;
}
?></p>



<h5><?php echo $i_message['mysql'];?></h5>
<p>
<?php
if (function_exists('mysql_connect'))
{
	echo $i_message['support'];
	result(1, 1);
}
else
{
	echo '<span class="red">'.$i_message['mysql_unsupport'].'</span>';
	result(0, 1);
	$quit = TRUE;
}
?></p>


</div>
<div class="shade">
<div class="settingHead"><?php echo $i_message['dirmod'];?></div>
<?php
foreach ($writeable as $value)
{
	echo '<p>'.$value.'</p>';
}

if (is_writable(THINKSNS_ROOT.$thinksns_config_file))
{
	echo '<p>'.$thinksns_config_file.result(1, 0).'</p>';
}
else
{
	echo '<p>'.$thinksns_config_file.result(0, 0).'</p>';
	$quit = TRUE;
}
?>
<!-- <span class='red'><?php echo $i_message['install_dirmod'];?></span> -->
</div>
<p class="center">
	<form method="post" action='install.php?v=3'>
	<input style="width:200px;" type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php if($quit) echo "disabled=\"disabled\"";?>>
	</form>
</p>
<?php
}
elseif ($v == '3')
{
?>
<!-- <h2><?php echo $i_message['install_setting'];?></h2> -->
<form method="post" action="install.php?v=4" id="install" onSubmit="return check(this);">
<div class="shade">
<div class="settingHead"><?php echo $i_message['install_mysql'];?></div>

<h5><?php echo $i_message['install_mysql_host'];?></h5>
<p><?php echo $i_message['install_mysql_host_intro'];?></p>
<p><input type="text" name="db_host" value="localhost" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_username'];?></h5>
<p><input type="text" name="db_username" value="root" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_password'];?></h5>
<p><input type="password" name="db_password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_name'];?></h5>
<p><input type="text" name="db_name" value="thinksns_2_8" size="40" class='input' />
</p>

<h5><?php echo $i_message['install_mysql_prefix'];?></h5>
<p><?php echo $i_message['install_mysql_prefix_intro'];?></p>
<p><input type="text" name="db_prefix" value="ts_" size="40" class='input' /></p>

<h5><?php echo $i_message['site_url'];?></h5>
<p><?php echo $i_message['site_url_intro'];?></p>
<p><input type="text" name="site_url" value="<?php echo "http://".$_SERVER['HTTP_HOST'].rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))),'/');?>" size="40" class='input' /></p>

</div>

<div class="shade">
<div class="settingHead"><?php echo $i_message['founder'];?></div>

<h5><?php echo $i_message['auto_increment'];?></h5>
<p><input type="text" name="first_user_id" value="1" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_email'];?></h5>
<p><input type="text" name="email" value="admin@admin.com" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_password'];?></h5>
<p><input type="text" name="password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_rpassword'];?></h5>
<p><input type="text" name="rpassword" value="" size="40" class='input' /></p>


</div>
<div class="center">
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onClick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>">
</form>
</div>
<script type="text/javascript" language="javascript">
function check(obj)
{
	if (!obj.db_host.value)
	{
		alert('<?php echo $i_message['install_mysql_host_empty'];?>');
		obj.db_host.focus();
		return false;
	}
	else if (!obj.db_username.value)
	{
		alert('<?php echo $i_message['install_mysql_username_empty'];?>');
		obj.db_username.focus();
		return false;
	}
	else if (!obj.db_name.value)
	{
		alert('<?php echo $i_message['install_mysql_name_empty'];?>');
		obj.db_name.focus();
		return false;
	}
	else if (obj.password.value.length < 6)
	{
		alert('<?php echo $i_message['install_founder_password_length'];?>');
		obj.password.focus();
		return false;
	}
	else if (obj.password.value != obj.rpassword.value)
	{
		alert('<?php echo $i_message['install_founder_rpassword_error'];?>');
		obj.rpassword.focus();
		return false;
	}
	else if (!obj.email.value)
	{
		alert('<?php echo $i_message['install_founder_email_empty'];?>');
		obj.email.focus();
		return false;
	}
	return true;
}
</script>
<?php
}
elseif ($v == '4')
{
	if(empty($db_host) || empty($db_username) || empty($db_name) || empty($db_prefix))
	{
		$msg .= '<p>'.$i_message['mysql_invalid_configure'].'<p>';
		$quit = TRUE;
	}
	elseif (!@mysql_connect($db_host, $db_username, $db_password))
	{
		$msg .= '<p>'.mysql_error().'</p>';
		$quit = TRUE;
	}
	if(strstr($db_prefix, '.'))
	{
		$msg .= '<p>'.$i_message['mysql_invalid_prefix'].'</p>';
		$quit = TRUE;
	}

	if (strlen($password) < 6)
	{
		$msg .= '<p>'.$i_message['founder_invalid_password'].'</p>';
		$quit = TRUE;
	}
	elseif ($password != $rpassword)
	{
		$msg .= '<p>'.$i_message['founder_invalid_rpassword'].'</p>';
		$quit = TRUE;
	}
	elseif (!preg_match('/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$/i', $email))
	{
		$msg .= '<p>'.$i_message['founder_invalid_email'].'</p>';
		$quit = TRUE;
	}
	else
	{
		$forbiddencharacter = array ("\\","&"," ","'","\"","/","*",",","<",">","\r","\t","\n","#","$","(",")","%","@","+","?",";","^");
		foreach ($forbiddencharacter as $value)
		{
			if (strpos($username, $value) !== FALSE)
			{
				$msg .= '<p>'.$i_message['forbidden_character'].'</p>';
				$quit = TRUE;
				break;
			}
		}
	}

	if ($quit)
	{
		$allownext = 'disabled="disabled"';
		?>
		<div class="error"><?php echo $i_message['error'];?></div>
		<?php
		echo $msg;
	}
	else
	{

		$config_file_content	=	array();
		$config_file_content['db_host']			=	$db_host;
		$config_file_content['db_name']			=	$db_name;
		$config_file_content['db_username']		=	$db_username;
		$config_file_content['db_password']		=	$db_password;
		$config_file_content['db_prefix']		=	$db_prefix;
		$config_file_content['db_pconnect']		=	0;
		$config_file_content['db_charset']		=	'utf8';
		$config_file_content['dbType']			=	'MySQL';

		$default_manager_account	=	array();
		$default_manager_account['email']		=	$email;
		$default_manager_account['password']	=	md5($password);

		$_SESSION['config_file_content']		=	$config_file_content;
		$_SESSION['default_manager_account']	=	$default_manager_account;
		$_SESSION['first_user_id']				=	$first_user_id;
		$_SESSION['site_url']					=	$site_url;
	}
?>
	<div class="botBorder">
		<p><?php echo $i_message['install_founder_name'], ': ', $email?></p>
		<p><?php echo $i_message['install_founder_password'], ': ', $password;?></p>
	</div>
	<div class="botBorder">

<?php
//写配置文件
$fp = fopen(THINKSNS_ROOT.$thinksns_config_file, 'wb');
$configfilecontent = <<<EOT
<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 数据库常用配置
	'DB_TYPE'			=>	'mysql',			// 数据库类型
	'DB_HOST'			=>	'$db_host',			// 数据库服务器地址
	'DB_NAME'			=>	'$db_name',			// 数据库名
	'DB_USER'			=>	'$db_username',		// 数据库用户名
	'DB_PWD'			=>	'$db_password',		// 数据库密码
	'DB_PORT'			=>	3306,				// 数据库端口
	'DB_PREFIX'			=>	'$db_prefix',		// 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
	'DB_CHARSET'		=>	'utf8',				// 数据库编码
	'DB_FIELDS_CACHE'	=>	true,				// 启用字段缓存

	//'COOKIE_DOMAIN'	=>	'.thinksns.com',	//cookie域,请替换成你自己的域名 以.开头

	//Cookie加密密码
	'SECURE_CODE'       =>  'SECURE_TEST',

	// 默认应用
    'DEFAULT_APPS'		=> array('api', 'admin', 'home', 'myop', 'weibo', 'wap', 'w3g'),

    // 是否开启URL Rewrite
	'URL_ROUTER_ON'		=> false,

    // 是否开启调试模式 (开启AllInOne模式时该配置无效, 将自动置为false)
	'APP_DEBUG'			=> false,
);
EOT;
$configfilecontent = str_replace('SECURE_TEST','SECURE'.rand(10000,20000),$configfilecontent);
chmod(THINKSNS_ROOT.$thinksns_config_file, 0777);
$result_1	=	fwrite($fp, trim($configfilecontent));
@fclose($fp);

if($result_1 && file_exists(THINKSNS_ROOT.$thinksns_config_file)){
?>
	<p><?php echo $i_message['config_log_success']; ?></p>
<?php
}else{
?>
	<p><?php echo $i_message['config_read_failed']; $quit = TRUE;?></p>
<?php
}
?>
	</div>
	<div class="center">
		<form method="post" action="install.php?v=5">
		<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onClick="history.go(-1)">&nbsp;
		<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php echo $allownext;?> >
		</form>
	</div>
<?php
}
elseif ($v == '5')
{
	$db_config	=	$_SESSION['config_file_content'];

	if (!$db_config['db_host'] && !$db_config['db_name'])
	{
		$msg .= '<p>'.$i_message['configure_read_failed'].'</p>';
		$quit = TRUE;
	}
	else
	{
		mysql_connect($db_config['db_host'], $db_config['db_username'], $db_config['db_password']);
		$sqlv = mysql_get_server_info();
		if($sqlv < '4.1')
		{
			$msg .= '<p>'.$i_message['mysql_version_402'].'</p>';
			$quit = TRUE;
		}
		else
		{
			$db_charset	=	$db_config['db_charset'];
			$db_charset = (strpos($db_charset, '-') === FALSE) ? $db_charset : str_replace('-', '', $db_charset);

			mysql_query(" CREATE DATABASE IF NOT EXISTS `{$db_config['db_name']}` DEFAULT CHARACTER SET $db_charset ");

			if (mysql_errno())
			{
				$errormsg = mysql_error();
				$msg .= '<p>'.($errormsg ? $errormsg : $i_message['database_errno']).'</p>';
				$quit = TRUE;
			}
			else
			{
				mysql_select_db($db_config['db_name']);
			}

			//判断是否有用同样的数据库前缀安装过
			$re		=	mysql_query("SELECT COUNT(1) FROM {$db_config['db_prefix']}user");
			$link	=	@mysql_fetch_row($re);

			if( intval($link[0]) > 0 )
			{
				$thinksns_rebuild	=	true;
				$msg .= '<p>'.$i_message['thinksns_rebuild'].'</p>';
				$alert = ' onclick="return confirm(\''.$i_message['thinksns_rebuild'].'\');"';
			}
		}
	}

if ($quit)
{
		$allownext = 'disabled="disabled"';
?>
<div class="error"><?php echo $i_message['error'];?></div>
<?php
	echo $msg;
}
else
{
?>
<div class="botBorder">
<?php
if($thinksns_rebuild){
?>
<p style="color:red;font-size:16px;"><?php echo $i_message['thinksns_rebuild'];?></p>
<?php
}
?>
<p><?php echo $i_message['mysql_import_data'];?></p>
</div>
<?php
}
?>
<div class="center">
	<form method="post" action="install.php?v=6">
	<input type="button" class="submit" name="prev" value="<?php echo $i_message['install_prev'];?>" onClick="history.go(-1)">&nbsp;
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next'];?>" <?php echo $allownext,$alert?>>
	</form>
</div>
<?php
}
elseif ($v == '6')
{
	$db_config	=	$_SESSION['config_file_content'];

	mysql_connect($db_config['db_host'], $db_config['db_username'], $db_config['db_password']);
	if (mysql_get_server_info() > '5.0')
	{
		mysql_query("SET sql_mode = ''");
	}
	$db_config['db_charset'] = (strpos($db_config['db_charset'], '-') === FALSE) ? $db_config['db_charset'] : str_replace('-', '', $db_config['db_charset']);
	mysql_query("SET character_set_connection={$db_config['db_charset']}, character_set_results={$db_config['db_charset']}, character_set_client=binary");
	mysql_select_db($db_config['db_name']);
	$tablenum = 0;

	$fp = fopen($installfile, 'rb');
	$sql = fread($fp, filesize($installfile));
	fclose($fp);
?>
<div class="botBorder">
<h4><?php echo $i_message['import_processing'];?></h4>
<div style="overflow-y:scroll;height:100px;width:715px;padding:5px;border:1px solid #ccc;">
<?php
	$db_charset	=	$db_config['db_charset'];
	$db_prefix	=	$db_config['db_prefix'];
	$sql = str_replace("\r", "\n", str_replace('`'.'ts_', '`'.$db_prefix, $sql));
	foreach(explode(";\n", trim($sql)) as $query)
	{
		$query = trim($query);
		if($query) {
			if(substr($query, 0, 12) == 'CREATE TABLE')
			{
				$name = preg_replace("/CREATE TABLE ([A-Z ]*)`([a-z0-9_]+)` .*/is", "\\2", $query);
				echo '<p>'.$i_message['create_table'].' '.$name.' ... <span class="blue">OK</span></p>';
				@mysql_query(createtable($query, $db_charset));
				$tablenum ++;
			}
			else
			{
				@mysql_query($query);
			}
		}
	}
?>
</div>
</div>
<div class="botBorder">
<h4><?php echo $i_message['create_founder'];?></h4>

<?php
	//设置网站用户起始ID
	if(intval($_SESSION['first_user_id'])>0){
		$admin_id	=	intval($_SESSION['first_user_id']);
		$sql0	=	"ALTER TABLE `{$db_config['db_prefix']}user` AUTO_INCREMENT=".$admin_id.";";
		if( mysql_query($sql0) ){
			echo '<p>'.$i_message['set_auto_increment_success'].'... <span class="blue">OK..'.$admin_id.'</span></p>';
		} else {
			echo '<p>'.$i_message['set_auto_increment_error'].'... <span class="red">ERROR</span></p>';
			$admin_id	=	1;
		}
	}else{
		$admin_id	=	1;
	}
	//添加管理员
	$siteFounder	=	$_SESSION['default_manager_account'];

	$sql1	=	"INSERT INTO `{$db_config['db_prefix']}user` (`uid`, `email`, `password`, `uname`, `sex`, `province`, `city`, `location`, `admin_level`, `commend`, `is_active`, `is_init`, `is_synchronizing`, `cTime`, `identity`, `score`,`myop_menu_num`,`api_key`,`domain`) VALUES (".$admin_id.", '".$siteFounder['email']."', '".$siteFounder['password']."', '管理员', '0', '0', '0', NULL, '1', NULL, '1', '1', '0', ".time().", '1', '0', '10', NULL, '');";

	if( mysql_query($sql1) ){
		echo '<p>'.$i_message['create_founderpower_success'].'... <span class="blue">OK</span></p>';
	} else {
		echo '<p>'.$i_message['create_founderpower_error'].'... <span class="red">ERROR</span></p>';
		$quit	=	true;
	}

	//将管理员添加到漫游的用户记录
	$sql_myop	=	"INSERT INTO `{$db_config['db_prefix']}myop_userlog` (`uid`, `action`, `type`, `dateline`) VALUES (".$admin_id.", 'add', '0', ".time().");";
	if( mysql_query($sql_myop) ){

	} else {
		$quit	=	true;
	}

	//将管理员加入“管理员”用户组
	$sql_user_group	=	"INSERT INTO `{$db_config['db_prefix']}user_group_link` (`user_gorup_link_id`,`user_group_id`,`user_group_title`,`uid`) VALUES ('1', '1', '管理员', ".$admin_id.");";
	if( mysql_query($sql_user_group) ){

	} else {
		$quit	=	true;
	}

	//将管理员设置为默认关注的用户
	$sql_auto_friend = "REPLACE INTO `{$db_config['db_prefix']}system_data` (`list`,`key`,`value`) VALUES ('register', 'register_auto_friend', '".serialize($admin_id)."');";
	if( mysql_query($sql_auto_friend) ){

	} else {
		$quit	=	true;
	}

	if(!$quit){
		//锁定安装
		fopen('install.lock', 'w');
		@unlink('../index.html');
	}else{
		echo '请重新安装';
	}
?>
</div>
<div class="botBorder">
<h4><?php echo $i_message['install_success'];?></h4>
<?php echo $i_message['install_success_intro'];?>
</div>
<iframe src="<?php echo $_SESSION['site_url'];?>/cleancache.php?all" height="0" width="0" style="display: none;"></iframe>
<?php
}
?>
</div>
<div class='copyright'>ThinkSNS <?php echo $_TSVERSION; ?> &#169; copyright 2008-<?php echo date('Y') ?> www.ThinkSNS.com All Rights Reserved</div>
</div>
<div style="display:none;">
<script src="http://s79.cnzz.com/stat.php?id=1702264&web_id=1702264" language="JavaScript" charset="gb2312"></script>
</div>
</body>
</html>