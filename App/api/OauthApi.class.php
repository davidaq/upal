<?php
class OauthApi extends Api{
	function access_token(){
		if($_POST['userId'] && $_POST['passwd']){
	    	$username = desdecrypt($_POST['userId'],'12345678');
	    	if( is_numeric($username) ){
	    		$map['uid'] = $username;
	    	}elseif (is_string($username)){
	    		$map['email'] = h($username);
	    	}else{
	    		return;
	    	}
	    	$map['password'] = md5(desdecrypt( h($_REQUEST['passwd']) ,'12345678' ) );
			$user = M('user')->where($map)->field('uid')->find();
			$this->mid = $user['uid'];
    	}
	}
	function request_key(){
		return array($this->getRequestKey());
	}
	private function getRequestKey(){
		return "thinksns";
	}
	function authorize(){
		if($_POST['uid'] && $_POST['passwd']){
			// 杨德升添加
			$isIphone = $_REQUEST['isIphone']==='1';
	    	$username = $isIphone ? $_POST['uid'] : desdecrypt( $_POST['uid'],$this->getRequestKey());
	    	if( is_numeric($username) ){
	    		$map['uid'] = $username;
	    	}elseif (is_string($username)){
	    		$map['email'] = $username;
	    	}else{
	    		$this->verifyError();
	    	}
	    	$map['password'] = $isIphone ? $_POST['passwd'] : md5( desdecrypt($_POST['passwd'],$this->getRequestKey()) );
			$user = M('user')->where($map)->field('uid')->find();
			if($user){
				if( $login = M('login')->where("uid=".$user['uid']." AND type='location'")->find() ){
					$data['oauth_token']         = $login['oauth_token'];
					$data['oauth_token_secret']  = $login['oauth_token_secret'];
					$data['uid']                 = $user['uid'];
				}else{
					$data['oauth_token']         = getOAuthToken($user['uid']);
					$data['oauth_token_secret']  = getOAuthTokenSecret();
					$data['uid']                 = $user['uid'];
					$savedata['type']            = 'location';
					$savedata = array_merge($savedata,$data);
					M('login')->add($savedata);
				}
				return $data;
			}else{
				$this->verifyError();
			}
    	}else{
    		$this->verifyError();
    	}
	}
}