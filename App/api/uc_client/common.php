<?php
/* 字符、数组串编码转换 */
function ts_change_charset($fContents,$from='UTF8',$to='GBK'){
    $from   =  strtoupper($from)=='UTF8'? 'utf-8':$from;
    $to       =  strtoupper($to)=='UTF8'? 'utf-8':$to;
    if( strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents)) ){
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if(is_string($fContents) ) {
        if(function_exists('mb_convert_encoding')){
            return mb_convert_encoding ($fContents, $to, $from);
        }elseif(function_exists('iconv')){
            return iconv($from,$to,$fContents);
        }else{
            return $fContents;
        }
    }
    elseif(is_array($fContents)){
        foreach ( $fContents as $key => $val ) {
            $_key =     ts_change_charset($key,$from,$to);
            $fContents[$_key] = ts_change_charset($val,$from,$to);
            if($key != $_key )
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else{
        return $fContents;
    }
}

function ts_auto_charset($content){
	return ts_change_charset($content, 'UTF8', UC_DBCHARSET);
}

function uc_auto_charset($content){
	return ts_change_charset($content, UC_DBCHARSET, 'UTF8');
}


//添加ThinkSNS与UCenter的用户映射
function ts_add_ucenter_user_ref($uid,$uc_uid,$uc_username=''){
	$uc_ref_data = array(
					   'uid' => $uid,
					   'uc_uid' => $uc_uid,
					   'uc_username'  => $uc_username,
				   );
	M('ucenter_user_link')->add($uc_ref_data);
	return $uc_ref_data;
}

//更新ThinkSNS与UCenter的用户映射
function ts_update_ucenter_user_ref($uid,$uc_uid,$uc_username=''){
	$uid 		 &&	$map['uid']					= intval($uid);
	$uc_uid 	 && $map['uc_uid'] 				= intval($uc_uid);
	$uc_username && $uc_ref_data['uc_username'] = $uc_username;
	if(empty($uc_ref_data['uc_username']))return;
	M('ucenter_user_link')->where($map)->save($uc_ref_data);
}

//获取ThinkSNS与UCenter的用户映射
function ts_get_ucenter_user_ref($uid='',$uc_uid='',$uc_username=''){
	$uid && $map['uid'] 				= intval($uid);
	$uc_uid && $map['uc_uid'] 			= intval($uc_uid);
	$uc_username && $map['uc_username'] = $uc_username;
	if(!$map) return;
	return M('ucenter_user_link')->where($map)->find();
}

class uc_note {

	var $dbconfig = '';
	var $db = '';
	var $tablepre = '';
	var $appdir = '';

	function _serialize($arr, $htmlon = 0) {
		if(!function_exists('xml_serialize')) {
			include_once DISCUZ_ROOT.'./uc_client/lib/xml.class.php';
		}
		return xml_serialize($arr, $htmlon);
	}

	function uc_note() {
		$this->appdir = substr(dirname(__FILE__), 0, -3);
		$this->dbconfig = $this->appdir.'./uc_client/uc_config.inc.php';
		$this->db = $GLOBALS['db'];
		$this->tablepre = $GLOBALS['tablepre'];
	}

	//UC通讯测试
	function test($get, $post) {
		return API_RETURN_SUCCEED;
	}

	//UC同步更新头像到TS - 尚未同步
	function face($get){
		if($get['type'] !== "face"){
			$uc_uid = $get['uid'];
			$uc_user_ref = ts_get_ucenter_user_ref('',$uc_uid);
			$user = M('user')->where("uid={$uc_user_ref['uid']}")->find();
			if($user) {
				echo $user['uid'];
				/*cookie('LOGGED_USER',jiami('thinksns.'.$user['uid']),(3600*2))*/;
			}
		}else{
			$data = 'http://dev.thinksns.com/ts/2.0/public/themes/classic2';
			$face = str_replace("THEME_URL", $data, getUserFace( $get['uid']));
			$data = 'http://dev.thinksns.com/ts/2.0';
			$face = str_replace("SITE_URL", $data, $face);
			echo $face;
		}

	}

	//UC同步删除用户 - 尚未同步
	function deleteuser($get, $post) {
		$uids = $get['ids'];
		!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

		return API_RETURN_SUCCEED;
	}

	//UC同步修改TS用户名 - 已解决GBK问题
	function renameuser($get, $post) {
		if(!API_RENAMEUSER) {
			return API_RETURN_FORBIDDEN;
		}
		$uc_uid = $get['uid'];
		//$usernameold = $get['oldusername'];
		$usernamenew = uc_auto_charset($get['newusername']);
		ts_update_ucenter_user_ref('',$uc_uid,$usernamenew);
		return API_RETURN_SUCCEED;
	}

	function gettag($get, $post) {
		$name = $get['id'];
		if(!API_GETTAG) {
			return API_RETURN_FORBIDDEN;
		}

		$return = array();
		return $this->_serialize($return, 1);
	}

	//UC同步登录TS - 已解决GBK问题
	function synlogin($get, $post) {
		if(!API_SYNLOGIN){
			return API_RETURN_FORBIDDEN;
		}
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

		$uc_uid		= $get['uid'];
		$uc_uname	= uc_auto_charset($get['username']);
		$uc_password=	$get['password'];
		$uc_user_ref = ts_get_ucenter_user_ref('',$uc_uid);
		$user = M('user')->where("uid={$uc_user_ref['uid']}")->find();
		if($user) {
			//检查是否激活，未激活用户不自动登录
			if ($user['is_active'] == 0) {
				exit;
			}
			if($uc_uname != $uc_user_ref['uc_username']){
				ts_update_ucenter_user_ref($uc_user_ref['uid'],$uc_uid,$uc_uname);
			}
			//登录到TS系统
			session_start();
			$user['login_from_dz'] = true;
			$result = service('Passport')->registerLogin($user);
			//由于UC登录没有发送记住登录的状态过来，所以暂时关闭此代码
			//成功登录后，设置Cookie
			//$remember ?
			//cookie('LOGGED_USER',jiami('thinksns.'.$user['uid']),(3600*24*365)) //:
			/*cookie('LOGGED_USER',jiami('thinksns.'.$user['uid']),(3600*2))*/;
		}
	}

	//UC同步退出TS
	function synlogout($get, $post) {
		if(!API_SYNLOGOUT) {
			return API_RETURN_FORBIDDEN;
		}

		//note 同步登出 API 接口
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		session_start();
		service('Passport')->logoutLocal();
	}

	//UC同步更新TS密码 - 已解决GBK问题
	function updatepw($get, $post) {
		if(!API_UPDATEPW) {
			return API_RETURN_FORBIDDEN;
		}
		$uc_username = uc_auto_charset($get['username']);
		$password 	 = $get['password'];
		$uc_user_ref = ts_get_ucenter_user_ref('','',$uc_username);
		M('user')->where("uid={$uc_user_ref['uid']}")->setField('password', md5($password));
		return API_RETURN_SUCCEED;
	}

	//UC同步更新敏感词 - 尚未同步
	function updatebadwords($get, $post) {
		if(!API_UPDATEBADWORDS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/badwords.php';
		$fp = fopen($cachefile, 'w');
		$data = array();
		if(is_array($post)) {
			foreach($post as $k => $v) {
				$data['findpattern'][$k] = $v['findpattern'];
				$data['replace'][$k] = $v['replacement'];
			}
		}
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'badwords\'] = '.var_export($data, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	//尚未同步
	function updatehosts($get, $post) {
		if(!API_UPDATEHOSTS) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/hosts.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	//尚未同步
	function updateapps($get, $post) {
		if(!API_UPDATEAPPS) {
			return API_RETURN_FORBIDDEN;
		}
		$UC_API = $post['UC_API'];

		//note 写 app 缓存文件
		$cachefile = $this->appdir.'./uc_client/data/cache/apps.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);

		//note 写配置文件
		if(is_writeable($this->appdir.'./uc_client/uc_config.inc.php')) {
			$configfile = trim(file_get_contents($this->appdir.'./uc_client/uc_config.inc.php'));
			$configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
			$configfile = preg_replace("/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$UC_API');", $configfile);
			if($fp = @fopen($this->appdir.'./uc_client/uc_config.inc.php', 'w')) {
				@fwrite($fp, trim($configfile));
				@fclose($fp);
			}
		}

		return API_RETURN_SUCCEED;
	}

	//尚未同步
	function updateclient($get, $post) {
		if(!API_UPDATECLIENT) {
			return API_RETURN_FORBIDDEN;
		}
		$cachefile = $this->appdir.'./uc_client/data/cache/settings.php';
		$fp = fopen($cachefile, 'w');
		$s = "<?php\r\n";
		$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
		fwrite($fp, $s);
		fclose($fp);
		return API_RETURN_SUCCEED;
	}

	//积分同步 - 尚未同步
	function updatecredit($get, $post) {
		if(!API_UPDATECREDIT) {
			return API_RETURN_FORBIDDEN;
		}
		$credit = $get['credit'];
		$amount = $get['amount'];
		$uid = $get['uid'];
		return API_RETURN_SUCCEED;
	}

	//积分同步 - 尚未同步
	function getcredit($get, $post) {
		if(!API_GETCREDIT) {
			return API_RETURN_FORBIDDEN;
		}
	}

	//积分同步 - 尚未同步
	function getcreditsettings($get, $post) {
		if(!API_GETCREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		$credits = array();
		return $this->_serialize($credits);
	}

	//积分同步 - 尚未同步
	function updatecreditsettings($get, $post) {
		if(!API_UPDATECREDITSETTINGS) {
			return API_RETURN_FORBIDDEN;
		}
		return API_RETURN_SUCCEED;
	}
}

//note 使用该函数前需要 require_once $this->appdir.'./uc_client/uc_config.inc.php';
function _setcookie($var, $value, $life = 0, $prefix = 1) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	setcookie(($prefix ? $cookiepre : '').$var, $value,
		$life ? $timestamp + $life : 0, $cookiepath,
		$cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
}

function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function _stripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = _stripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}