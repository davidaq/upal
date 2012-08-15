<?php
class UserVerifiedHooks extends Hooks
{
    static $cache_list=array();
	public function init()
	{
	}

	public function home_account_tab($param)
	{
		$param['menu'][] = array(
			'act' => 'verified',
			'name' => '申请认证',
			'param' => array(
				'addon' => 'UserVerified',
				'hook'  => 'home_account_show'
			)
		);
	}

	public function home_account_show()
	{
    	$verified = M('user_verified')->where("uid={$this->mid}")->find();
    	$verifyruler = model('Xdata')->lget('square', $_POST);
    	$this->assign('verifyruler',$verifyruler);
    	$this->assign('verified', $verified);
    	$this->display('home_account_show');
	}

	public function home_account_do($param)
	{
    	$data['attachment']=$_POST['attach']['0'];
    	$data['uid'] 	  = $this->mid;
    	$data['realname'] = preg_match('/^[\x{2e80}-\x{9fff}]+$|^[a-zA-Z\.·]+$/u', $_POST['realname']) ? $_POST['realname'] : '';
    	$data['phone']	  = preg_match('/^[\d]{11}$/', $_POST['phone']) ? $_POST['phone'] : '';
    	$data['reason']	  = h($_POST['reason']);
    	$data['reasonLength'] = strlen($data['reason']);
    	if($data['reasonLength'] == 0){
    		$this->error('认证资料不能为空');
    	}
    	if (!$data['realname'] || !$data['phone'] || !$data['reason']) {
    		echo 0;
    	}
    	$data['verified'] = '0';
    	if (is_numeric($_POST['id']) && $_POST['id'] > 0) {
    		$data['id'] = $_POST['id'];
    		$res = M('user_verified')->where('uid ='.$this->mid)->save($data);
    	} else {
    		$res = M('user_verified')->add($data);
    	}
    	if (false !== $res) {
    		// echo 1;?    		
    		$this->success('保存成功');
    	} else {
    		$this->error('保存失败');
    		// echo 0;
    	}
	}

	// 用户名图标显示
	public function user_name_end($param)
	{

		$uid  = $param['uid'];
		$html = & $param['html'];
		if(empty(self::$cache_list[$uid])|| !self::$cache_list['uid']){
		    self::$cache_list[$uid] = $this->_getVerifiedCache($uid);
		    if(false === self::$cache_list[$uid]) self::$cache_list[$uid] = $this->_setVerifiedCache($uid);
		}
		$user_verified = self::$cache_list[$uid];
	    if (isset($user_verified) && !empty($user_verified)) {
	    	$html .= '<img class="ts_icon" src="' . SITE_URL . '/addons/plugins/UserVerified/html/icon.gif" title="' . $user_verified['info'] . '" />';
		}
	}

	// 个人空间右侧显示
	public function home_space_right_top($param)
	{
	    $uid = $param['uid'];
	    if(empty(self::$cache_list[$uid])|| !self::$cache_list['uid']){
	        self::$cache_list[$uid] = $this->_getVerifiedCache($uid);
	        if(false === self::$cache_list[$uid]) self::$cache_list[$uid] = $this->_setVerifiedCache($uid);
	    }
	    $user_verified = self::$cache_list[$uid];
		$this->assign('user_verified', $user_verified);
		$this->display('space_verified');
	}

	/* 插件后台管理项 */
	public function verifying()
	{
		$this->verified();
	}

	public function verified()
	{
    	//为使搜索条件在分页时也有效，将搜索条件记录到SESSION中

		if ( !empty($_POST) ) {
			$_SESSION['admin_searchVerifiedUser'] = serialize($_POST);
    		$this->assign('type', 'searchUser');
		}else if ( isset($_GET[C('VAR_PAGE')]) && !is_null($_SESSION['admin_searchVerifiedUser']) ) {
			$_POST = unserialize($_SESSION['admin_searchVerifiedUser']);
    		$this->assign('type', 'searchUser');
		}else {
			unset($_SESSION['admin_searchVerifiedUser']);
		}

		$_POST['uid'] 	   && $map['uid'] 	   = array('IN', t($_POST['uid']));
		$_POST['realname'] && $map['realname'] = array('exp', 'LIKE "%' . t($_POST['realname']) . '%"');
		$_POST['phone']    && $map['phone']    = array('exp', 'LIKE "%' . t($_POST['phone']) . '%"');
		$_POST['reason']   && $map['reason']   = array('exp', 'LIKE "%' . t($_POST['reason']) . '%"');

    	$verified = ('verifying' == $_GET['page']) ? '0' : '1';
		$map['verified'] = "{$verified}";
		$dataList = M('user_verified')->where($map)->findPage();
		$data =$dataList['data'];
		foreach ($data as $v) {
			$attach_id = $v['attachment'];
			$attach = D('attach')->where('id ='.$attach_id)->find();
			$v['attachment'] = $attach['name'];
            $data = $attach['id'];
            $attach_id = array('attach_id' =>$data);
            $v = array_merge($v,$attach_id);
            $data1[] = $v;
		}
		$this->assign('data1',$data1);
    	$this->assign($_POST);
    	$this->assign('verified', $verified);
    	$this->assign($dataList);
		$this->display('verified');
	}
   /*
     * 下载附件
     */
    public function download(){
        $aid    =   intval($_REQUEST['id']);

        $attach   =   M('attach')->where("id={$aid}")->find();
        //$attach   =   model('Xattach')->where("id='$aid'")->find();
        if(!$attach){
            $this->error('附件不存在或者已被删除！');
        }
        //下载函数
        //import("ORG.Net.Http");             //调用下载类
        require_cache('./addons/libs/Http.class.php');
        if(file_exists(UPLOAD_PATH.'/'.$attach['savepath'].$attach['savename'])) {
            //增加下载次数
            //model('Xattach')->setInc('totaldowns',"id={$aid}");    
            //输出文件
            $filename   =   $attach['name'];
            $filename   =   auto_charset($filename,"UTF-8",'GBK');
            //$filename =   'attach_'.$attach['id'].'.'.$attach['extension'];
            Http::download(UPLOAD_PATH.'/'.$attach['savepath'].$attach['savename'],$filename);
        }else{
            $this->error('附件不存在或者已被删除！');
        }
    }
	public function doVerifiedTab()
	{
		if (intval($_GET['uid']) > 0) {
			$verified = M('user_verified')->field('reason')->where("uid={$_GET['uid']}")->find();
			$this->assign('info', $verified['reason']);
		}
		$this->display('doVerifiedTab');
	}

	public function addVerifiedUser()
	{
    	if (intval($_GET['uid']) > 0) {
    		$verified = M('user_verified')->where('uid=' . intval($_GET['uid']))->find();
    		$verified['uid'] = intval($_GET['uid']);
    		$this->assign('verified', $verified);
    		$this->assign('jumpUrl', $_SERVER['HTTP_REFERER']);
    	}
    	$this->display('addVerifiedUser');
	}

	public function setVerifyRuler()
	{
		// $data	=	model('AddonData')->get('verifyruler');
		$data = model('Xdata')->lget('square', $_POST);
		$this->assign($data);
    	$this->display('setVerifyRuler');
	}

	public function saveVerifyRuler()
	{
		 model('Xdata')->lput('square', $_POST);
	}

    public function doVerified()
    {
		$uid = is_array($_POST ['uid']) ? '(' . implode ( ',', $_POST ['uid'] ) . ')' : '(' . $_POST ['uid'] . ')'; // 判读是不是数组
		foreach($uid as $value){
		    $this->_removeVerifiedCache($value);
		}
		$data['info'] = t(urldecode($_POST['info']));
		if (!$data['info']) {
			echo 0;
			exit;
		}
		$data['verified'] = '1';
		$res = M('user_verified')->where('uid IN ' . t($uid))->save($data); // 通过认证
    	if ($res) {
			if (strpos ($_POST['uid'], ',')) {
				echo 1;
				exit;
			} else {
				echo 2;
				exit;
			}

			// 发送通知
			$uids = explode(',', $_POST['uid']);
			$notify_dao = service ( 'Notify' );
			foreach ( $uids as $v ) {
				$notify_dao->sendIn ($v, 'admin_verified');
			}
		} else {
			echo 0;
			exit;
		}
    }

    public function saveVerified()
    {
    	$data = M('user_verified')->create();
    	if (!$data['uid']) {
    		$this->error('请选择用户');
    	} 
    	if (!$data['info']) {
    		$this->error('请填写认证资料');
    	} 

    	$uid = t($_POST['uid']);
        $res_user = M('user')->where('uid ='.$_POST['uid'])->find();
        if(!$res_user){
           $this->error('该用户不存在！');
        }
        $res_search = M('user_verified')->where('uid ='.$_POST['uid'])->find();
        if($res_search['verified'] != $_POST['verified']){
	        $map['uid'] =  t($_POST['uid']);
	        if($res_search['verified'] == 1){
	          $data['verified'] = "0";  
	        }
	        if($res_search['verified'] == 0){
	            $data['verified'] = "1";
	        }
	    }
       $res = M('user_verified')->where($map)->save($data);
       if (false !== $res) {
    		    $this->_removeVerifiedCache($data['uid']);
    		    #http://localhost/ts/index.php?app=admin&mod=Addons&act=admin&pluginid=7&page=addVerifiedUser
    			// $jumpUrl = $_POST['jumpUrl'] ? $_POST['jumpUrl'] : U('admin/User/addVerifiedUser');
    			$jumpUrl = $_POST['jumpUrl'] ? $_POST['jumpUrl'] : U('admin/Addons/admin').'&pluginid=7&page=addVerifiedUser';
    			$this->assign('jumpUrl', $jumpUrl);
    			$this->success();
    		} else {
    			$this->error();
    		}
    }

    public function deleteVerified()
    {
		$uid = is_array($_POST ['uid']) ? '(' . implode ( ',', $_POST ['uid'] ) . ')' : '(' . $_POST ['uid'] . ')'; // 判读是不是数组
		foreach($uid as $value){
		    $this->_removeVerifiedCache($value);
		}
		$res = M('user_verified')->where('uid IN ' . t($uid) )->delete(); // 删除认证
    	if ($res) {
			if (strpos($_POST['uid'], ',') !== FALSE) {
				echo 1;
				exit;
			} else {
				echo 2;
				exit;
			}

			// 发送通知
			$uids = explode(',', $_POST['uid']);
			$notify_dao = service ( 'Notify' );
			$notify_tpl = (1 == $_POST['verified']) ? 'admin_delverified' : 'admin_rejectverified';
			foreach ( $uids as $v ) {
				$notify_dao->sendIn ($v, $notify_tpl, array('reason'=>t(urldecode($_POST['reason']))));
			}
		} else {
			echo 0;
			exit;
		}
    }
    
    public function deleteVerifiedTab()
    {
    	$this->display('deleteVerifiedTab');
    }

    private function _getListData($uid){
        return M('user_verified')->field('uid,realname,phone,reason,info')->where("verified='1' and uid={$uid}")->find();
    }

    private function _getVerifiedCache($uid){
        return unserialize(S('verified_'.$uid));
    }
    private function _removeVerifiedCache($uid){
        return S('verified_'.$uid,null);
    }

    private function _setVerifiedCache($uid){
        $list = $this->_getListData($uid);
        S('verified_'.$uid,serialize($list));
        return $list;
    }
}