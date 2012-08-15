<?php
date_default_timezone_set('Asia/Chongqing');
include_once( 'qzone/utils.php' );
class qzone{

	var $loginUrl;
	var $error_code;

	function getError(){
		return $this->error_code;
	}

	function getUrl($callback){
		if (is_null($callback)) {
			$callback = U('home/public/qzonecallback');
		}
		//授权登录页
		$redirect = "http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize?oauth_consumer_key=".QZONE_KEY;
		//获取request token
		$result = array();
		$request_token = get_request_token(QZONE_KEY, QZONE_SECRET);
		parse_str($request_token, $result);
		//request token, request token secret 需要保存起来
		//在demo演示中，直接保存在全局变量中.真实情况需要网站自己处理
		$_SESSION['qzone']["keys"]        = $result;

		if ($result["oauth_token"] == "")
		{
			$this->error_code = $result['error_code'];
			return false;
		}

		//302跳转到授权页面
		$redirect .= "&oauth_token=".$result["oauth_token"]."&oauth_callback=".rawurlencode($callback);
		return $redirect;
	}

	//用户资料
	function userInfo(){
		$url    = "http://openapi.qzone.qq.com/user/get_user_info";
		$me = do_get($url, QZONE_KEY, QZONE_SECRET, $_SESSION['qzone']['access_token']['oauth_token'], $_SESSION['qzone']['access_token']['oauth_token_secret'], $_SESSION['qzone']["openid"]);
		$me = json_decode($me);
		$user['id']         =  $_SESSION['qzone']["openid"];
		$user['uname']       = $me->nickname;
		$user['province']    = 0;
		$user['city']        = 0;
		$user['location']    = '';
		$user['userface']    = $me->figureurl_2;
		$user['sex']         = 0;

		//print_r($user);
		return $user;
	}
	//验证用户
	function checkUser(){
		/**
		 * QQ互联登录，授权成功后会回调此地址
		 * 必须要用授权的request token换取access token
		 * 访问QQ互联的任何资源都需要access token
		 * 目前access token是长期有效的，除非用户解除与第三方绑定
		 * 如果第三方发现access token失效，请引导用户重新登录QQ互联，授权，获取access token
		 */

		//授权成功后，会返回用户的openid
		//检查返回的openid是否是合法id
		if (!is_valid_openid($_REQUEST["openid"], $_REQUEST["timestamp"], $_REQUEST["oauth_signature"]))
		{
			return false;
		}
		//tips
		//这里已经获取到了openid，可以处理第三方账户与openid的绑定逻辑
		//但是我们建议第三方等到获取accesstoken之后在做绑定逻辑

		//用授权的request token换取access token
		$access_str = get_access_token(QZONE_KEY, QZONE_SECRET, $_REQUEST["oauth_token"], $_SESSION['qzone']["keys"]["oauth_token_secret"], $_REQUEST["oauth_vericode"]);
		//echo "access_str:$access_str\n";
		$result = array();
		parse_str($access_str, $result);
		//error
		if (isset($result["error_code"]))
		{
			return false;
		}

		//获取access token成功后也会返回用户的openid
		//我们强烈建议第三方使用此openid
		//检查返回的openid是否是合法id
		if (!is_valid_openid($result["openid"], $result["timestamp"], $result["oauth_signature"]))
		{
			return false;
		}

		//将access token，openid保存!!
		//XXX 作为demo,临时存放在session中，网站应该用自己安全的存储系统来存储这些信息
		$_SESSION['qzone']['access_token']['oauth_token']  = $result["oauth_token"];
		$_SESSION['qzone']['access_token']['oauth_token_secret']  = $result["oauth_token_secret"];
		$_SESSION['qzone']["openid"]  = $result["openid"];
		$_SESSION['open_platform_type'] = 'qzone';
		//第三方处理用户绑定逻辑
		//将openid与第三方的帐号做关联
		return true;
	}

}
?>