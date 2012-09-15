<?php
// +----------------------------------------------------------------------
// | OpenSociax [ open your team ! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.sociax.com.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: genixsoft.net <智士软件>
// +----------------------------------------------------------------------
// $Id$
/**
 +------------------------------------------------------------------------------
 * API接口抽象类
 +------------------------------------------------------------------------------
 * @category	core
 * @package		core
 * @author		lengharoan <lenghaoran@thinksns.com>
 * @version		$0.1$
 +------------------------------------------------------------------------------
 */
abstract class Api extends Think {
	var $mid; //当前登录的用户ID
	var $since_id;
	var $max_id;
	var $page;
	var $count;
	var $user_id;
	var $user_name;
	var $id;
	var $data;
	private $_module_white_list = null; // 白名单模块
    public function __construct($location=false)
    {
    	$this->_module_white_list = array('Oauth', 'Sitelist');
    	if ($location == false) {
			if (!$this->mid && !in_array(MODULE_NAME, $this->_module_white_list))
				$this->verifyUser();
    	} else {
    		$this->mid = $_SESSION['mid'];
		}
		$this->data       = $_REQUEST;
		$this->since_id   = $_REQUEST['since_id']   ? intval( $_REQUEST['since_id'] ) : '';
		$this->max_id     = $_REQUEST['max_id']     ? intval( $_REQUEST['max_id'] )   : '';
		$this->page       = $_REQUEST['page']       ? intval( $_REQUEST['page'] )     : 1;
		$this->count      = $_REQUEST['count']      ? intval( $_REQUEST['count'] )    : 20;
		$this->user_id    = $_REQUEST['user_id']    ? intval( $_REQUEST['user_id'])   : 0;
		$this->user_name  = $_REQUEST['user_name']  ? h( $_REQUEST['user_name'])      : '';
		$this->id         = $_REQUEST['id']         ? intval( $_REQUEST['id'])        : 0;
    	//控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }
    //认证用户
    private function verifyUser(){
    	$verifycode['oauth_token'] = h($_REQUEST['oauth_token']);
    	$verifycode['oauth_token_secret'] = h($_REQUEST['oauth_token_secret']);
    	$verifycode['type'] = 'location';
    	if($login = M('login')->where($verifycode)->field('uid,oauth_token,oauth_token_secret')->find() ){
    		$this->mid = $login['uid'];
    		$_SESSION['mid'] = $this->mid;
    	}else{
    		$this->verifyError();
    	}
    }
    //第一次认证成功，生成token和token_sectrect
    private function buildToken($uid){
    }
    //认证失败
	protected  function verifyError(){
		$message['message'] = '认证失败';
		$message['code']    = '00001';
		exit( json_encode( $message ) );
	}
    public function data($data){
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }
		$this->since_id   = $data['since_id']   ? intval( $data['since_id'] ) : '';
		$this->max_id     = $data['max_id']     ? intval( $data['max_id'] )   : '';
		$this->page       = $data['page']       ? intval( $data['page'] )     : 1;
		$this->count      = $data['count']      ? intval( $data['count'] )    : 20;
		$this->user_id    = $data['user_id']    ? intval( $data['user_id'])   : $this->mid;
		$this->user_name  = $data['user_name']  ? h( $data['user_name'])      : '';
		$this->id         = $data['id']         ? intval( $data['id'])        : 0;
        $this->data = $data;
        return $this;
    }
}
?>