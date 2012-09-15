<?php
//站点链接
function getmyopurl() {
	$uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ( $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'] );
	return shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')));
}

//漫游注册
function my_site_register($siteKey, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteLanguage, $siteVersion, $myVersion) {
    global $_MY_GLOBAL;
	
	$siteName 	= urlencode($siteName);
	$postString = sprintf('action=%s&siteKey=%s&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteRealNameEnable=%s&siteRealAvatarEnable=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s', 'siteRegister', $siteKey, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteRealNameEnable, $siteRealAvatarEnable, $siteLanguage, $siteVersion, $myVersion);
	$response 	= uc_fopen2($_MY_GLOBAL['my_register_url'], 0, $postString, '', false, '');
	$res 		= unserialize($response);
	
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}
	return $res;
}

//漫游更新
function my_site_refresh($mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteEnableRealName, $siteEnableRealAvatar, $mySiteKey, $siteKey, $siteLanguage, $siteVersion, $myVersion) {
	global $_MY_GLOBAL;
	
	$key = $mySiteId . $siteName . $siteUrl . $ucUrl . $siteCharset . $siteTimeZone . $siteEnableRealName . $mySiteKey . $siteKey;
	$key = md5($key);

	$siteName = urlencode($siteName);
	$postString = sprintf('action=%s&key=%s&mySiteId=%d&siteName=%s&siteUrl=%s&ucUrl=%s&siteCharset=%s&siteTimeZone=%s&siteEnableRealName=%s&siteEnableRealAvatar=%s&siteKey=%s&siteLanguage=%s&siteVersion=%s&myVersion=%s', 'siteRefresh', $key, $mySiteId, $siteName, $siteUrl, $ucUrl, $siteCharset, $siteTimeZone, $siteEnableRealName, $siteEnableRealAvatar, $siteKey, $siteLanguage, $siteVersion, $myVersion);
	
	$response = uc_fopen2($_MY_GLOBAL['my_register_url'], 0, $postString, '', false, '');
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
	return $res;
}

//漫游关闭
function my_site_close($mySiteId, $mySiteKey) {
	$key		= $mySiteId . $mySiteKey;
	$key		= md5($key);
	$postString = sprintf('action=%s&key=%s&mySiteId=%d', 'siteClose', $key, $mySiteId);
	$response	= uc_fopen2($_MY_GLOBAL['my_register_url'], 0, $postString, '', false, '');
	$res		= unserialize($response);
	if (!$response) {
		$res['errCode'] = 111;
		$res['errMessage'] = 'Empty Response';
		$res['result'] = $response;
	} elseif(!$res) {
		$res['errCode'] = 110;
		$res['errMessage'] = 'Error Response';
		$res['result'] = $response;
	}
	return $res['result'];
}

/**
 *  远程打开URL
 *  @param string $url		打开的url，　如 http://www.baidu.com/123.htm
 *  @param int $limit		取返回的数据的长度
 *  @param string $post		要发送的 POST 数据，如uid=1&password=1234
 *  @param string $cookie	要模拟的 COOKIE 数据，如uid=123&auth=a2323sd2323
 *  @param bool $bysocket	TRUE/FALSE 是否通过SOCKET打开
 *  @param string $ip		IP地址
 *  @param int $timeout		连接超时时间
 *  @param bool $block		是否为阻塞模式
 *  @return			取到的字符串
 */
if (!function_exists('uc_fopen2')) {
function uc_fopen2($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$__times__ = isset($_GET['__times__']) ? intval($_GET['__times__']) + 1 : 1;
	if($__times__ > 2) {
		return '';
	}
	$url .= (strpos($url, '?') === FALSE ? '?' : '&')."__times__=$__times__";
	return uc_fopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
}
}

if (!function_exists('uc_fopen')) {
function uc_fopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	!isset($matches['host']) && $matches['host'] = '';
	!isset($matches['path']) && $matches['path'] = '';
	!isset($matches['query']) && $matches['query'] = '';
	!isset($matches['port']) && $matches['port'] = '';
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;
	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';//note $errstr : $errno \r\n
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//生成站点key
function mksitekey() {
	global $_MY_GLOBAL;	
	//16位
	$sitekey = substr(md5($_SERVER['SERVER_ADDR'].$_SERVER['HTTP_USER_AGENT'].substr($_MY_GLOBAL['timestamp'], 0, 6)), 8, 6).random(10);
	return $sitekey;
}