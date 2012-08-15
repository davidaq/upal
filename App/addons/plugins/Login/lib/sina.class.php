<?php
include_once('_OAuth/oauth.php');
include_once( 'sina/weibooauth.php' );
class sina{

	var $loginUrl;
	private $_sina_akey;
	private $_sina_skey;
	var $error_code;

	function getError(){
		return $this->error_code;
	}

	public function __construct() {
		$this->_sina_akey = SINA_WB_AKEY;
		$this->_sina_skey = SINA_WB_SKEY;
	}

    function getUrl($call_back = null) {
		if ( empty($this->_sina_akey) || empty($this->_sina_skey) )
			return false;
		if (is_null($call_back)) {
			$call_back = U('home/public/callback');
		}

		$o = new SinaWeiboOAuth( $this->_sina_akey , $this->_sina_skey  );
        $keys = $o->getRequestToken();
		$this->loginUrl = $o->getAuthorizeURL( $keys['oauth_token'] ,false , $call_back);
        $_SESSION['sina']['keys'] = $keys;
		return $this->loginUrl;
	}

	function getJSON($userid,$passwd){
		$o = new SinaWeiboOAuth( $this->_sina_akey , $this->_sina_skey  );
		$keys = $o->getRequestToken();
		$return = $o->getAuthorizeJSON( $keys['oauth_token'] ,false , $userid,$passwd);
		if($return){
			$return = json_decode($return);
			$o = new SinaWeiboOAuth( $this->_sina_akey , $this->_sina_skey ,$keys['oauth_token'] , $keys['oauth_token_secret']  );
			$access_token = $o->getAccessToken(  $return->oauth_verifier ) ;
			return $access_token;
		}
	}

	//用户资料
	function userInfo(){
		$me = $this->doClient()->verify_credentials();
		$user['id']          = $me['id'];
		$user['uname']       = $me['name'];
		$user['province']    = $me['province'];
		$user['city']        = $me['city'];
		$user['location']    = $me['location'];
		$user['userface']    = str_replace(  $user['id'].'/50/' , $user['id'].'/180/' ,$me['profile_image_url'] );
		$user['sex']         = ($me['gender']=='m')?1:0;
		return $user;
	}

    private function doClient($opt){
		$oauth_token = ( $opt['oauth_token'] )? $opt['oauth_token']:$_SESSION['sina']['access_token']['oauth_token'];
        $oauth_token_secret = ( $opt['oauth_token_secret'] )? $opt['oauth_token_secret']:$_SESSION['sina']['access_token']['oauth_token_secret'];
		return new WeiboClient( $this->_sina_akey , $this->_sina_skey ,  $oauth_token, $oauth_token_secret  );
	}

	//验证用户
    function checkUser(){
        $o = new SinaWeiboOAuth( $this->_sina_akey , $this->_sina_skey , $_SESSION['sina']['keys']['oauth_token'] , $_SESSION['sina']['keys']['oauth_token_secret']  );
        $access_token = $o->getAccessToken(  $_REQUEST['oauth_verifier'] ) ;
		$_SESSION['sina']['access_token'] = $access_token;
        $_SESSION['open_platform_type'] = 'sina';
	}

	//发布一条微博 - 可以发图片微博
	function update($text,$opt){
		return $this->doClient($opt)->update($text);
	}

	//上传一个照片，并发布一条微博
	function upload($text,$opt,$pic){
		if(file_exists($pic)){
			return $this->doClient($opt)->upload($text,$pic);
		}else{
			return $this->doClient($opt)->update($text);
		}
	}

	function saveData($data){
		if(isset($data['id'])){
			return array("sinaId"=>$data['id']);
		}
		return array();
	}

    function transpond($transpondId,$reId,$content='',$opt=null){

		if($reId){
			$this->doClient($opt)->send_comment($reId,$content);
		}

        if($transpondId){
            $result = $this->doClient($opt)->repost($transpondId,$content);
        }
	}

}
?>
