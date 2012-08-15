<?php
/**
 * 用户管理中心
 * @author Nonant
 *
 */

class AccountAction extends Action
{
    var $pUser;

    function _initialize(){
        $this->pUser = D('UserProfile');
        $this->pUser->uid = $this->mid;

        // 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        $is_domain_on = $is_domain_on['site_user_domain_on'];

        $menu[] = array( 'act' => 'index',		'name' => L('personal_profile') );
		//$menu[] = array( 'act' => 'avatar',		'name' => L('face_setting') );
        $menu[] = array( 'act' => 'privacy',	'name' => L('private_setting') );
        if ($is_domain_on == 1)
        	$menu[] = array( 'act' => 'domain',	'name' => L('self_domain') );
        $menu[] = array( 'act' => 'security',	'name' => L('account_safe') );
        $menu[] = array( 'act' => 'bind',		'name' => L('outer_bind') );
        $menu[] = array( 'act' => 'credit',		'name' => L('integral_rule') );
        Addons::hook('home_account_tab', array('menu'=>& $menu));
        $this->assign('accountmenu', $menu);
    }

    // 有不存在的ACTION操作的时候执行
    protected function _empty() {
    	if (empty($_POST)) {
    		$this->display('addons');
		}
	}

    //个人资料
    function index(){
        $data['userInfo']         = $this->pUser->getUserInfo();
        $data['userTag']          = D('UserTag')->getUserTagList($this->mid);
        $data['userFavTag']       = D('UserTag')->getFavTageList($this->mid);
        $this->assign( $data );
        $this->setTitle(L('setting').' - '.L('personal_profile'));
        $this->display();
    }

    //更新资料
    function update(){
    	S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
		$nickname = $_REQUEST['nickname'];

		//检查禁止注册的用户昵称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($nickname,$bannedunames)){
					exit(json_encode(array('message'=>'这个昵称禁止注册','boolen'=>0)));
				}
			}
		}

        exit( json_encode($this->pUser->upDate( t($_REQUEST['dotype']) )) );
    }

    //绑定帐号
    function bind(){
   	    $user = M('user')->where('uid='.$this->mid)->field('email')->find();
   	    $replace = substr($user['email'],2,-3);
   	    for ($i=1;$i<=strlen($replace);$i++){
   	    	$replacestring.='*';
   	    }
        $data['email'] = str_replace(  $replace, $replacestring ,$user['email'] );
        $bindData = array();
        Addons::hook('account_bind_after',array('bindInfo'=>&$bindData));
        $data['bind']  = $bindData;
   	    $this->assign($data);
   	    $this->setTitle(L('setting').' - '.L('outer_bind'));
    	$this->display();
    }

    //教育、工作情况
    function addproject(){
        S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
		$pUserProfile = D('UserProfile');
        $pUserProfile->uid = $this->mid;
        $strType = t( $_POST['addtype'] );
        if( $strType =='education' ){
            $data['school'] = msubstr( t(h($_POST['school'])),0,70,'utf-8',false );
            $data['classes']= msubstr( t(h($_POST['classes'])),0,70,'utf-8',false );
            $data['year']   = $_POST['year'];
            if( empty( $data['school'] ) ){
                $return['message']  = L('schoolname_nonull');
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }
        }elseif ($strType == 'career' ){
            $data['company']   = msubstr( t(h($_POST['company'])),0,70,'utf-8',false );
            $data['position']  = msubstr( t(h($_POST['position'])),0,70,'utf-8',false );
            $data['begintime'] = intval( $_POST['beginyear'] ).'-'.intval($_POST['beginmonth']);
            $data['endtime']   = ( $_POST['nowworkflag'] ) ? L('now') : intval( $_POST['endyear'] ).'-'.intval($_POST['endmonth']);
            //2011-03-11 添加
            $date_begin = explode("-", $data['begintime']);
            $date_end = explode("-", $data['endtime']);

            $begin = mktime(0, 0, 0, $date_begin[1], 0, $date_begin[0]);
            $end = mktime(0, 0, 0, $date_end[1], 0, $date_end[0]);

            if( empty( $data['company'] ) ){
                $return['message']  = L('companyname_nonull');
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }

            if($data['endtime'] != L('now') && $begin > $end) {
            	$return['message'] = L('start_time_later');
            	$return['boolen'] = "0";
            	exit(json_encode($return));
            }

            if($data['endtime'] != L('now') && $end > time()) {
                $return['message'] = '结束时间不能超过当前时间';
                $return['boolen'] = 0;
                exit(json_encode($return));
            }
        }
        $data['id'] = $pUserProfile->dosave($strType,$data,'list',true);
        if($data['id']){
            $data['addtype'] = $strType;
            $return['message']  = L('companyname_nonull');
            $return['boolen']   = "1";
            $return['data']   = $data;
            exit( json_encode($return) );
        }
    }

    //个人标签
    function doUserTag(){
    	$strType = h($_REQUEST['type']);
        if( $strType!='deltag' && !$_POST['tagname'] && !$_POST['tagid'] ){
    		echo  json_encode( array('code'=>'3') );
    		exit();
    	}
    	if ($strType=='deltag'){
    		echo D('UserTag')->doDel(intval($_POST['tagid']),$this->mid);
    		exit();
    	}
    	$count = M( 'UserTag' )->where( 'uid='.$this->mid )->count();
    	if( $count >=10 ){
    		echo  json_encode( array('code'=>'2') );
    		exit();
    	}
    	if($strType=='addByname'){
    		$_POST['tagname'] = str_replace('，', ',', $_POST['tagname']);
    		$_POST['tagname'] = str_replace(' ', ',', $_POST['tagname']);
    		echo D('UserTag')->addUserTagByName( $_POST['tagname'] ,$this->mid ,$count);
    	}
    	if ($strType=='addByid'){
    		echo D('UserTag')->addUserTagById( $_POST['tagid'] ,$this->mid);
    	}
    }

    //头像处理
    function avatar(){
        $type = $_REQUEST['t'];
        $pAvatar = D('Avatar');
        $pAvatar ->uid = $this->mid;
        if( $type == 'upload' ){
            echo $pAvatar->upload();
        }elseif ( $type == 'save'){
            $pAvatar->dosave($this->mid);
        }elseif ( $type == 'camera'){
            $pAvatar->getcamera();
        }else{
        	$this->display();
        }
    }

    //邀请
    public function invite() {
    	if($_POST){
    		if( model('Invite')->getReceiveCode( $this->mid ) ){
    			$this->assign('jumpUrl',U('home/Account/invite'));
    			$this->success(L('invite_code_success'));
    			redirect( U('home/Account/invite') );
    		}else{
    			$this->error(L('invite_code_error'));
    		}
    	}else{
            $map['uid'] = $this->mid;
	    	// $invitecode = model('Invite')->getInviteCode( $this->mid );
	    	$receivecount = model('Invite')->getReceiveCount( $this->mid );
            $invitecode = model('Invite')->where($map)->findAll();
			$this->assign('receivecount',$receivecount);
			$this->assign('list',$invitecode);
			$this->setTitle(L('invite'));
	    	$this->display();
    	}
    }

    public function doInvite() {
    	$_POST['email'] = t($_POST['email']);
    	if ( !isValidEmail($_POST['email']) ) {
    		echo -1; //错误的Email格式
    		return ;
    	}

    	$map['email']  = $_POST['email'];
    	$map['is_active'] = 1;
    	if ( $user = M('user')->where($map)->find() ) {
    		echo $user['id']; //被邀请人已存在
    		return ;
    	}
    	unset($map);

    	//添加验证数据 之1
    	$validation = service('Validation')->addValidation($this->mid, $_POST['email'], U('home/Public/inviteRegister'), 'test_invite');
    	if (!$validation) {
    		echo 0;
    		return ;
    	}

    	//发送邀请邮件
    	global $ts;
    	$data['title'] = array(
    		'actor_name'	=> $ts['user']['uname'],
    		'site_name'		=> $ts['site']['site_name'],
    	);
    	$data['body']  = array(
    		'email'			=> $_POST['email'],
    		'actor'			=> '<a href="' . U('home/Space/index',array('uid'=>$ts['user']['uid'])) . '" target="_blank">' . $ts['user']['uname'] . '</a>',
    		'site'			=> '<a href="' . U('home') . '" target="_blank">' . $ts['site']['site_name'] . '</a>',
    	);
    	$tpl_record = model('Template')->parseTemplate('invite_register', $data);
    	unset($data);

    	if ($tpl_record) {
    		//echo -2; //邀请成功

    		//添加验证数据 之2
    		$map['target_url'] = $validation;
    		M('validation')->where($map)->setField('data', serialize(array('tpl_record_id'=>$tpl_record)));
    		echo $validation;
    	}else {
    		echo 0;
    	}
    }

	//邀请已存在的用户
    public function inviteExisted() {
    	$this->assign('uid', intval($_GET['uid']));
    	$this->display();
    }

    //删除资料
    function delprofile(){
        S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
		$intId = intval( $_REQUEST['id'] );
        $pUserProfile = D('UserProfile');
        echo $pUserProfile->delprofile( $intId ,$this->mid );
    }

    //帐号安全
    public function security() {
    	// UCenter帐号同步失败则重新设置
    	if(UC_SYNC){
    		global $ts;
	    	$uc_user_ref = ts_get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		if(uc_user_checkname($ts['user']['uname']))$this->assign('uc_username',$ts['user']['uname']);
	    		if(uc_user_checkemail($ts['user']['email']))$this->assign('uc_email',$ts['user']['email']);
	    		$this->assign('set_ucenter_username',1);
	    	}
    	}

    	$this->setTitle(L('setting').' - '.L('account_safe'));

    	$this->display();
    }

    //隐私设置
    function privacy(){
    	if($_POST){
    		$r = D('UserPrivacy')->dosave($_POST['userset'],$this->mid);
    		$this->success("保存成功");
    	}
    	$userSet = D('UserPrivacy')->getUserSet($this->mid);
    	$blacklist = D('UserPrivacy')->getBlackList($this->mid);
    	$this->assign('userset',$userSet );
    	$this->assign('blacklist',$blacklist );
    	$this->setTitle(L('setting').' - '.L('private_setting'));
    	$this->display();

    }


    //设置黑名单
    function setBlackList(){
    	if( D("UserPrivacy")->setBlackList( $this->mid , t($_POST['type']) , intval($_POST['uid']) ) ){
    		echo '1';
    	}else{
    		echo '0';
    	}
    }
    //设置黑名单
    function setBlack(){
        $uname = t($_POST['blackname']);
        $data = M('user')->where("`uname` = '$uname'")->find();
        $mid = $this->mid;
        if($data['uid'] == $mid){
            $this->error('不能添加自己为黑名单！');
        }
        if(!$data){
            $this->error('没有该用户！');
        }else{
            $map['uid'] = $this->mid;
            $map['fid'] = $data['uid'];
            $map['ctime'] =time();
            D('UserBlacklist')->add($map);
             $this->assign('jumpUrl', U('home/Account/privacy#email'));
            $this->success('设置成功！');
        }
    }
    function release(){
        $fid = $_GET['id'];
        $res = D('UserBlacklist')->where("fid = ".$fid)->delete();
        dump(D('UserBlacklist')->getlastsql());
        if($res){
           echo 1;
         }else{
           echo 0;        
      }
    }

    //个性化域名
    function domain(){
    	// 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        if ($is_domain_on['site_user_domain_on'] != 1)
        	$this->error(L('self_domain_off'));

    	if($_POST){
			S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
			$domain = h($_POST['domain']);

            $dmMap['domain'] = $domain;
            $isExistDomain = M('user')->where($dmMap)->find();
            if(!is_null($isExistDomain)) {
                $this->error('此个性域名已被占用，请重新输入');
            }

    		if( !ereg('^[a-zA-Z][a-zA-Z0-9]+$', $domain)){
    			$this->error(L('domain_english_only'));
    		}

    		if( strlen($domain)<2 ){
    			$this->error(L('domain_short'));
    		}

    		if( strlen($domain)>20 ){
    			$this->error(L('domain_long'));
    		}

			//检查已被禁用的个性化域名
			$audit = model('Xdata')->lget('audit');
			if($audit['banuid']==1){
				$banned_domains = $audit['banneddomains'];
				if(!empty($banned_domains)){
					$banned_domains = explode('|',$banned_domains);
					if( in_array($domain,$banned_domains)){
						$this->error('该个性域名已被禁用');
					}
				}
			}

    		if( M('user')->where("uid!={$this->mid} AND domain='{$domain}'")->count()){
    			$this->error(L('people_used'));
    		}else{
    			M('user')->setField('domain',$domain,'uid='.$this->mid);
    			$this->success(L('setting_success'));
    		}
    	}else{
	    	$user = M('user')->where('uid='.$this->mid)->find();
	    	$data['userDomain'] = $user['domain'];
	    	$this->assign($data);
	    	$this->setTitle(L('setting').' - '.L('self_domain'));
	    	$this->display();
    	}
    }

    //修改密码
    public function doModifyPassword() {
    	if( strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'] ) {
			$this->error(L('password_rule'));
		}
		if ($_POST['password'] == $_POST['oldpassword']) {
			$this->error(L('password_old_sameas_new'));
		}

    	$dao = M('user');
		//$_POST['oldpassword'] = md5($_POST['oldpassword']);
		$map['uid']			  = $this->mid;
		$map['password']	  = md5($_POST['oldpassword']);
		S('S_userInfo_'.$this->mid,null);
    	if ( $dao->where($map)->find() ) {
			include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
			if(UC_SYNC){
				$ucenter_user_ref = ts_get_ucenter_user_ref($this->mid);
				$uc_res = uc_user_edit($ucenter_user_ref['uc_username'],$_POST['oldpassword'],$_POST['password'],'');
				if($uc_res == -8){
					$this->error(L('userprotected_no_right'));
				}
			}
    		//$_POST['password']    = md5($_POST['password']);
			if ( $dao->where($map)->setField('password', md5($_POST['password'])) ) {
				$this->success(L('save_success'));
			}else {
				$this->error(L('save_error'));
			}

    	}else {
    		$this->error(L('oldpassword_wrong'));
    	}
    }

    //修改帐号
    public function modifyEmail() {
		S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
    	$_POST['email']    = t($_POST['email']);
    	$_POST['oldemail'] = t($_POST['oldemail']);
    	if ( !isValidEmail($_POST['email']) || !isValidEmail($_POST['oldemail']) ) {
    		echo -1;
    		return ; //$this->error('Email格式错误');
    	}
    	$map['uid']			= $this->mid;
    	$map['email']		= $_POST['oldemail'];
    	if ( ! M('user')->where($map)->find() ) {
    		echo -2;
    		return ; //原始Email错误
    	}
    	if ( !isEmailAvailable($_POST['email']) ) {
    		echo -3;
    		return ; //$this->error('新Emai已存在');
    	}

    	$opt_email_activate = model('Xdata')->lget('register');

    	// 不需要验证邮件时, 直接修改帐号
		if (!$opt_email_activate['register_email_activate']) {
			if ( M('user')->where($map)->setField('email', $_POST['email']) ) {
				service('Passport')->logoutLocal();
				echo 1;
			}else {
				echo 0;
			}
			unset($opt_email_activate);
			exit;
		}

		unset($opt_email_activate);

		// 邮件验证

    	//添加验证
    	$data = array('oldemail'=>$_POST['oldemail'], 'email'=>$_POST['email']);
    	if ( $url = service('Validation')->addValidation($this->mid, '', U('home/Public/doModifyEmail'), 'modify_account', serialize($data)) ) {
    		// 发送验证邮件
    		global $ts;
    		$body = <<<EOD
<strong>{$ts['user']['uname']}，你好：</strong><br/>

您只需通过点击下面的链接重置您的帐号：<br/>

<a href="$url">$url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;

			if (service('Mail')->send_email($_POST['email'], "重置{$ts['site']['site_name']}帐号", $body)) {
				echo '2';
			}else {
				echo '-4';
			}

    	}else {
    		echo '0';
    	}
    }

    // 设置UCenter帐号
    public function doModifyUCenter() {
    	include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
    	if(UC_SYNC){
	    	$uc_user_ref = ts_get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		$username = $_POST['username'];
	    		$email = $_POST['email'];
	    		$password = $_POST['password'];
	    		if(uc_user_checkname($username) != 1 || !isLegalUsername($username) || M('user')->where("uname='{$username}' AND uid<>{$this->mid}")->count())$this->error('用户名不合法或已经存在，请重新设置用户名');
	    		if(uc_user_checkemail($email) != 1 || M('user')->where("uname='{$email}' AND uid<>{$this->mid}")->count())$this->error('Email不合法或已经存在，请重新设置Email');
	    		global $ts;
	    		if(md5($password) != $ts['user']['password'])$this->error(L('password_error_retype'));
	    		$uc_uid = uc_user_register($username,$password,$email);
	    		if($uc_uid>0){
	    			ts_add_ucenter_user_ref($this->mid,$uc_uid,$username);
					$this->assign('jumpUrl', U('home/Account/security'));
					$this->success(L('ucenter_setting_success'));
	    		}else{
	    			$this->error(L('ucenter_setting_error'));
	    		}
	    	}else{
	    		redirect(U('home/Account/security'));
	    	}
    	}else{
    		redirect(U('home/Account/security'));
    	}
    }

    //积分规则
    public function credit(){
    	$credit = X('Credit');
    	$credit_type  = $credit->getCreditType();
    	$credit_rules = $credit->getCreditRules();

    	$this->assign('credit_type',$credit_type);
    	$this->assign('credit_rules',$credit_rules);
    	$this->setTitle(L('setting').' - '.L('integral_rule'));
    	$this->display();
    }

	public function weiboshow(){
		$this->display();
	}

	public function weiboshare(){
		$this->display();
	}
}
