<?php
class LoginHooks extends Hooks {

    private static $validLogin = array("sina" => array ("sina_wb_akey", "sina_wb_skey" ), "qq" => array ("qq_key", "qq_secret" ), "douban" => array ("douban_key", "douban_secret" ),"qzone"=>array("qzone_key","qzone_secret") );
	private static $validPublish = array("sina", "qq");
	private static $validAlias   = array('sina' => '新浪微博', 'qq' => '腾讯微博','douban'=>"豆瓣",'qzone'=>"QQ空间");
	private static $validApply  = array('sina' => 'http://open.weibo.com/', 'qq' => 'http://open.t.qq.com/websites/','douban'=>"http://www.douban.com/service/apidoc/connect",'qzone'=>"http://connect.qq.com");

	public function isSinaLoginAvailable()
	{
	    $config = model('AddonData')->lget('login');
	    if(in_array('sina',$config['open']) && !empty($config['sina_wb_akey']) && !empty($config['sina_wb_skey'])) {
	        echo "1";
	    }else{
	        echo "0";
	    }
	}

	public function sync_bind($param){
        session_start();
        $type = $param['type'];
        $result = &$param['res'];
        $config = model('AddonData')->lget('login');
        $email  = $_POST['email'];
        $uname = t($_POST['uname']);
        if(!in_array($type,$config['open'])){
            $result ['status'] = 0;
            $result ['info'] = "该同步操作管理员已关闭";
            session_write_close();
            return;
        }

        //尝试使用输入的邮箱地址进行获取用户信息。
        $passport = service ( 'Passport' );
        $passwd = $_POST['passwd']?$_POST['passwd']:true;
        $user = $passport->getLocalUser($email,$passwd);
        //如果获取到信息，则是对已有帐号进行绑定
        if($user){
            //对昵称进行覆盖绑定操作
            $user['uname'] = $uname;
            $res = $this->_bindaccunt($type, $result,$user);
        }else{//反之，则是创建新的帐号,或者帐号密码错误
            $res = D('User','home')->getUserByIdentifier($email, 'email');
            if(!$res){
                $res = $this->_register($type, $result);
            }else{
                $result ['status'] = 0;
                $result ['info'] = "登录邮箱密码错误，请检查邮箱密码";
                session_write_close();
                return;
            }
        }

       Session::pause();
	}

    public function no_register_do($param) {
        Session::start();
        $type = $param['type'];
        $result = &$param['res'];
        $config = model('AddonData')->lget('login');
        if(!in_array($type,$config['open'])){
            $result ['status'] = 0;
            $result ['info'] = "该同步操作管理员已关闭";
            Session::pause();
            return;
        }
        switch ($_REQUEST ['connectMod']) {
        case "bind" :
            $this->_bindaccunt ( $type, $result );
            break;
        case "createNew" :
            $this->_register ( $type, $result );
            break;
        default :
            $result ['status'] = 0;
            $result ['info'] = "非法参数";
        }
        Session::pause();
    }

    public function account_bind_after($param)
    {
        Session::start();
        $bindInfo = &$param['bindInfo'];
        $validPublish = self::$validPublish;
        $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
        //检查尅同步的平台的key值是否可用
        $config = model('AddonData')->lget('login');
        foreach ( $validPublish as $v ) {
            if(!in_array($v,$config['publish']) || empty($config[self::$validLogin[$v][0]]) || empty($config[self::$validLogin[$v][1]])) continue;
            $ico = $this->htmlPath.'/html/image/ico_'.$v.'.gif';
            $is_sync = false;
            foreach($bind as $value){
                if($value['type'] == $v && $value['is_sync']) $is_sync = true;
            }
            $bindInfo[] = array('type'=>$v,
                'name'=>self::$validAlias[$v],
                'isBind'=>$is_sync,
                'addon'=>'Login',
                'bind_hook'=>'login_bind_publish_weibo',
                'unbind_hook'=>'unbind',
                'ico'=>$ico);
        }
        Session::pause();
    }

    public function login_bind_publish_weibo($param)
    {
        Session::start();
        $type = $param['type'];
        if($_REQUEST['do'] == 'ajax_bind'){
            $_SESSION ['weibo_bind_target_url'] = U ( 'home/User/index' );
        }else{
            $_SESSION ['weibo_bind_target_url'] = U ( 'home/Account/bind' );
        }
        $this->_loadTypeLogin($type);
        $platform = new $type();
        $call_back_url = Addons::createAddonShow('Login','no_register_display',array('type'=>$type,'do'=>"bind"));
        redirect($platform->getUrl( $call_back_url ));
        Session::pause();
    }

    public function login_ajax_bind_publish_weibo($param)
    {
        $type = $param['type'];
        $type = strtolower($type);
        // 展示"开始绑定"按钮
        $map ['uid'] = $this->mid;
        $map ['type'] = $type;
        if (M ( 'login' )->where ( "uid={$this->mid} AND type='{$type}' AND oauth_token<>''" )->count ()) {
            M ( 'login' )->setField ( 'is_sync', 1, $map );
            S('user_login_'.$this->mid,null);
            echo '1';
            exit ();
        } else {
            Session::start();
            $_SESSION ['weibo_bind_target_url'] = U ( 'home/User/index' );
            $this->_loadTypeLogin($type);
            $platform = new $type ();
            $call_back_url = Addons::createAddonShow('Login','no_register_display',array('type'=>$type,'do'=>"bind"));
            $url = $platform->getUrl ( $call_back_url );
            Session::pause();
            echo '<dl class="pop_sync"><dt></dt>您还未绑定' . $type . '帐号, 请点这里<dd><a class="btn_b" href="' . $url . '">开始绑定</a></dd></dl>';
            exit ();
        }

    }

	//发布框解除同步绑定
    public function login_unbind_publish_weibo()
    {
			$type = h($_POST['type']);
    		echo M("login")->setField('is_sync',0,"uid={$this->mid} AND type='{$type}'" );
    		S('user_login_'.$this->mid,null);
    }

	//资料页删除绑定
    public function unbind()
    {
		if($this->mid > 0){
			$type = h($_POST['type']);
			echo M("login")->where("uid={$this->mid} AND type='{$type}'" )->delete();
			S('user_login_'.$this->mid,null);
		}else{
			echo 0;
		}
	}

	public function home_index_middle_publish() {
        $sync = self::$validPublish;
		//TODO:增加缓存
		$bind = unserialize((S('user_login_'.$this->mid)));
		if(false === $bind){
		    $bind = M ( 'login' )->where ( 'uid=' . $this->mid )->findAll ();
		    S('user_login_'.$this->mid,serialize($bind));
		}

		foreach ( $bind as $v ) {
			$login_bind [$v ['type']] = $v ['is_sync'];
		}
		//检查可同步的平台的key值是否可用
		$config = model('AddonData')->lget('login');
		$validSync = array();
		foreach($sync as $value){
		    if(!in_array($value,$config['publish']) || empty($config[self::$validLogin[$value][0]]) || empty($config[self::$validLogin[$value][1]])){
		        continue;
		    }
		    $validSync[] = $value;
		}

        $this->assign('htmlPath',$this->htmlPath);
		$this->assign('login_bind', $login_bind);
		$this->assign('sync', $validSync);
		$this->assign('alias', self::$validAlias);
		if(!empty($validSync)){
		    $this->display('sync');
		}
	}

    public function weibo_publish_after($param) {
        $id = $param['weibo_id'];
        $data = $param['post'];
        $sync = $_POST['sync'];
        $content = $data['content'];
        if($type_info = unserialize($data['type_data'])){
            if(isset($type_info[0])){
                $temp = array_shift($type_info);
                $type_data = $temp['picurl'];
            }else{
                $type_data = $type_info['picurl'];
            }
			if($data['type']==1){
				if(isset($type_info['picurl'])){
					$pic = UPLOAD_PATH.'/'.$type_info['picurl'];
				}elseif(isset($type_info[0]['picurl'])){
					$pic = UPLOAD_PATH.'/'.$type_info[0]['picurl'];
				}
			}
        }
        if(!empty($sync)){
            $result = array();
            foreach($sync as $key=>$v){
                $sync[$key] = "'{$v}'";
            }
            $where = sprintf("uid=%s and is_sync=1 and type in (%s)",$data['uid'],implode(',',$sync));
            $opt = M('login')->where($where)->field('DISTINCT oauth_token,oauth_token_secret,is_sync,type')->findAll();
            foreach($opt as $v){
                $this->_loadTypeLogin($v['type']);
                $platform = new $v['type']();
                switch($data['type']){
                case 0:
                    $syncData = $platform->update ( $content, $v );
                    break;
                case 1:
                    $syncData = $platform->upload ( $content."  ".U('home/space/detail',array('id'=>$id)), $v, $pic );
                    break;
                default:
                    $syncData = $platform->update ( $content."  ".U('home/space/detail',array('id'=>$id)), $v );
                    return;
                }
                //记录发的新微博到数据库
                if(empty($result)){
                    $result = $platform->saveData($syncData);
                }else{
                    $result = array_merge($result,$platform->saveData($syncData));
                }

            }
            if(!empty($result)){
                $dao = M('login_weibo');
                $result['weiboId'] = $id;
                $dao->add($result);
            }
        }
	}

	public function comment_quick_do($data){
		//检查该微博数据
		if($data['appid'] == "")
		$map['weiboId'] = $data['appid'];
	}

    public function weibo_transpond_after($param)
    {
        Session::start();
        $id   = $param['weibo_id'];
        $post = $param['post'];
        $data = $param['data'];
        $result = array();

        $where = sprintf("uid=%s and is_sync=1",$this->mid);
        $opt = M('login')->where($where)->field('DISTINCT oauth_token,oauth_token_secret,is_sync,type')->findAll();
		//检查该微博数据
		$map['weiboId'] = $post['transpond_id'];

        $data = M('login_weibo')->field("qqId,sinaId")->where($map)->find();
        foreach($opt as $value){
            $type  = $value['type'];
            if(!in_array($type,self::$validPublish)) continue;
            $this->_loadTypeLogin($type);
            $platform = new $type();
            if($data){
                if(!empty($data[$type.'Id'])){
                    $syncData = $platform->transpond($data[$type.'Id'],0,$post['content'],$value);
                }
            }else{
                $post['content'] = $post['content']." ".U('home/space/detail',array('id'=>$post['transpond_id']))." ";
                $syncData = $platform->update ( $post['content'], $value );
            }
            //记录发的新微博到数据库
            if(empty($result)){
                $result = $platform->saveData($syncData);
            }else{
                $result = array_merge($result,$platform->saveData($syncData));
            }
        }

        if(!empty($result)){
            $dao = M('login_weibo');
            if($data){
                $result['weiboId'] = $id;
                $dao->add($result);
            }
        }
        Session::pause();
	}

    public function no_register_display($param) {
        Session::start();
        S('user_login_'.$this->mid,null);
        $type = strtolower($param['type']);
        $result = &$param['res'];
        if ($_GET ['do'] == "bind") {
            $this->_bindPublish ( $type, $param['res'] );
        } else {
            $type = t ( $_GET ['type'] );
           $this->_loadTypeLogin($type);
            $platform = new $type ();
            $platform->checkUser ();
            $userinfo = $platform->userInfo ();
            // 检查是否成功获取用户信息
            if (empty ( $userinfo ['id'] ) || empty ( $userinfo ['uname'] )) {
                $result ['status'] = 0;
                $result ['url'] = SITE_URL;
                $result ['info'] = "获取用户信息失败";
                Session::pause();
                return;
            }
            if ($info = M ( 'login' )->where ( "`type_uid`='" . $userinfo ['id'] . "' AND type='{$type}'" )->find ()) {

                $user = M ( 'user' )->where ( "uid=" . $info ['uid'] )->find ();
                if (empty ( $user )) {

                    // 未在本站找到用户信息, 删除用户站外信息,让用户重新登录
                    M ( 'login' )->where ( "type_uid=" . $userinfo ['id'] . " AND type='{$type}'" )->delete ();
                } else {
                    if ($info ['oauth_token'] == '') {
                        $syncdata ['login_id'] = $info ['login_id'];
                        $syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
                        $syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
                        M ( 'login' )->save ( $syncdata );
                    }
                    service ( 'Passport' )->registerLogin ( $user );
                    $result ['status'] = 1;
                    $result ['url'] = U('home/User/index');
                    $result ['info'] = "同步登录成功";
                    Session::pause();
                    return;
                }
            }
            Session::pause();
            $this->assign ( 'user', $userinfo );
            $this->assign ( 'type', $type );
            $this->display ( "login" );
        }
    }

    public function login_sync_other($param){
        Session::start();
        $regInfo = $param['regInfo'];
        $platform_options = model('AddonData')->lget ( 'login' );
        $data = self::$validLogin;
        $type = strtolower($param['type']);

        $platform = array ();
        $check = array ();
        foreach ( $data[$type] as $v ) {
            $check [] = ! empty ( $platform_options [$v] );
        }
        if (count ( array_filter ( $check ) ) == count ( $data[$type] ) && in_array($type,$platform_options['open'])) {
            $this->_loadTypeLogin($type);
            $object = new $type ();
            $url = Addons::createAddonShow('Login','no_register_display',array('type'=>$type));
            $url = $object->getUrl($url);
			if(!$url){
				dump($type.'-login-error:'.$object->getError());
			}
            redirect($url);
        }

        Session::pause();
    }

    public function login_input_footer($param) {
        Session::start();
        $regInfo = $param['regInfo'];
		$platform_options = model('AddonData')->lget ( 'login' );
		$data = self::$validLogin;
		$platform = array ();
		foreach ( $data as $plateformName => $value ) {
			$check = array ();
			foreach ( $value as $v ) {
				$check [] = ! empty ( $platform_options [$v] );
			}
            if (count ( array_filter ( $check ) ) == count ( $value ) && in_array($plateformName,$platform_options['open'])) {
                $platform [$plateformName] = Addons::createAddonShow('Login','login_sync_other',array('type'=>$plateformName));
            }
        }
        if ($regInfo ['register_type'] == 'open' && ! empty ( $platform )) {
			$html = "<div class='frm alC lh35' style='border-top:1px solid #C9C9C9; margin: 10px 0 0;'>";
			$html .= "<div class='tit'>你也可以通过站外帐号进行登录!";
			$html .= "</div>";
			foreach ( $platform as $key => $vo ) {
				$url = $vo;
                $image = $this->htmlPath."/html/image/btn_{$key}.gif";
				$html .= sprintf ( "<a href='%s'><img src='%s' style='cursor: pointer;margin:0 2px' /></a>", $url, $image );
			}
			$html .= "</div>";
			echo $html;
		}
		Session::pause();
	}

	private function _bindPublish($type, &$result) {
	    Session::start();
		$this->_loadTypeLogin($type);
		$obj = new $type ();
		$obj->checkUser ();
		if (! isset ( self::$validPublish [$_SESSION ['open_platform_type']] )) {
			$result ['status'] = 0;
			$result ['url'] = U ( 'home/Public/displayAddons', array ("class" => __CLASS__, 'type' => "{$type}" ) );
			$result ['info'] = "授权失败";
		}

		// 检查是否成功获取用户信息
        $userinfo = $obj->userInfo ();
		if (!isset($userinfo ['id']) || ! is_string ( $userinfo ['uname'] )) {
			$result ['status'] = 0;
			$result ['url'] = U ( 'home/Public/displayAddons', array ("class" => __CLASS__, 'type' => "{$type}"  ) );
			$result ['info'] = "获取用户信息失败";
			return;
		}

		$syncdata ['uid'] = $this->mid;
		$syncdata ['type_uid'] = $userinfo ['id'];
		$syncdata ['type'] = $type;
		$syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
		$syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
		$syncdata ['is_sync'] = '1';
		S('user_login_'.$this->mid,null);
		if ($info = M ( 'login' )->where ( "type_uid={$userinfo['id']} AND type='" . $type . "'" )->find ()) {
			// 该新浪用户已在本站存在, 将其与当前用户关联(即原用户ID失效)
			M ( 'login' )->where ( "`login_id`={$info['login_id']}" )->save ( $syncdata );
		} else {
			// 添加同步信息
			M ( 'login' )->add ( $syncdata );
		}

		if (isset ( $_SESSION ['weibo_bind_target_url'] )) {
			$result ['url'] = $_SESSION ['weibo_bind_target_url'];
			unset ( $_SESSION ['weibo_bind_target_url'] );
		} else {
			$result ['url'] = U ( 'home/User/index');

		}
		$result ['status'] = 1;
		$result ['info'] = "绑定成功";
		Session::pause();
	}

	private function _bindaccunt($type, &$result,$user) {
		if (! isset ( self::$validLogin [$_POST ['type']] )) {
			$result ['status'] = 0;
			$result ['info'] = "参数错误";
			return;
		}

		$type = $_POST ['type'];
		$this->_loadTypeLogin($type);
		$platform = new $type ();
		$userinfo = $platform->userInfo ();

		// 检查是否成功获取用户信息
		if (empty ( $userinfo ['id'] ) || empty ( $userinfo ['uname'] )) {
			$result ['status'] = 0;
			$result ['jumpUrl'] = SITE_URL;
			$result ['info'] = "获取用户信息失败";
			return;
		}
		//如果该类型的绑定已经进行过，则是系统异常。正确流程并不会进行两次绑定
		$sync['uid'] = $user['uid'];
		$sync['type'] = $type;
		if(M('login')->where($sync)->count()){
		    $result ['status'] = 0;
		    $result ['jumpUrl'] = SITE_URL;
		    $result ['info'] = "该帐号已经绑定了其他新浪微博帐号";
		    return;
		}

		// 更新该用户的昵称数据
		$save['uname'] = $user['uname'];
		$map['uid']    = $user['uid'];
		$res = D('User','home')->where($map)->save($save);

        $syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
	    $syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
		$syncdata ['uid'] = $user ['uid'];
		$syncdata ['type_uid'] = $userinfo ['id'];
		$syncdata ['type'] = $type;
		S('user_login_'. $user['uid'],null);

		if (M ( 'login' )->add ( $syncdata )) {
		    $res = service ( 'Passport' )->loginLocal($user['email'],$_POST['password'],true);
			if($res){
		        $result ['status'] = 1;
		        $result ['jumpUrl'] = U ( 'home/User/index' );
		        $result ['info'] = "绑定成功";
		        return true;
		    }else{
		        $result ['status'] = 0;
		        $result ['jumpUrl'] = SITE_URL;
		        $result ['info'] = "绑定失败";
		        return false;
		    }

		} else {
			$result ['status'] = 0;
			$result ['jumpUrl'] = SITE_URL;
			$result ['info'] = "绑定失败";
			return false;
		}

	}

	private function _register($type, &$result) {
		if (! isset ( self::$validLogin [$type] )) {
			$result ['status'] = 0;
			$result ['info'] = "参数错误";
			return;
		}

		if (! isLegalUsername ( t ( $_POST ['uname'] ) )) {
			$result ['status'] = 0;
			$result ['info'] = "昵称格式不正确";
			return;
		}

        $haveName = M ( 'User' )->where ( "`uname`='" . t ( $_POST ['uname'] ) . "'" )->find ();
		if (is_array ( $haveName ) && sizeof ( $haveName ) > 0) {
			$result ['status'] = 0;
            $result ['info'] = "昵称已被使用";
			return;
		}

		$type = $_POST ['type'];
        $this->_loadTypeLogin($type);
		$platform = new $type ();
        $userinfo = $platform->userInfo ();

		// 检查是否成功获取用户信息
		if (empty ( $userinfo ['id'] ) || empty ( $userinfo ['uname'] )) {
			$result ['status'] = 0;
			$result ['jumpUrl'] = SITE_URL;
			$result ['info'] = "获取用户信息失败";
			return;
		}

		// 初使化用户信息, 激活帐号
		$data ['uname'] = t ( $_POST ['uname'] ) ? t ( $_POST ['uname'] ) : $userinfo ['uname'];
		$data ['province'] = intval ( $userinfo ['province'] );
		$data ['city'] = intval ( $userinfo ['city'] );
		$data ['location'] = $userinfo ['location'];
		$data ['sex'] = intval ( $userinfo ['sex'] );
		$data ['is_active'] = 1;
		$data ['is_init'] = 1;
		$data ['email']   = t($_POST['email']);
		$data ['password'] = md5($_POST['passwd']);
		$data ['ctime'] = time ();
		$data ['is_synchronizing'] = ($type == 'sina') ? '1' : '0'; // 是否同步新浪微博. 目前仅能同步新浪微博


		if ($id = M ( 'user' )->add ( $data )) {
			// 记录至同步登录表
			$syncdata ['uid'] = $id;
			$syncdata ['type_uid'] = $userinfo ['id'];
			$syncdata ['type'] = $type;
			$syncdata ['oauth_token'] = $_SESSION [$type] ['access_token'] ['oauth_token'];
			$syncdata ['oauth_token_secret'] = $_SESSION [$type] ['access_token'] ['oauth_token_secret'];
			M ( 'login' )->add ( $syncdata );

			// 转换头像
			//if ($_POST ['type'] != 'qq' || $_POST['type'] !='qzone') { // 暂且不转换QQ头像: QQ头像的转换很慢, 且会拖慢apache
			//	D ( 'Avatar' )->saveAvatar ( $id, $userinfo ['userface'] );
			//}

			// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
			$userlog = array ('uid' => $id, 'action' => 'add', 'type' => '0', 'dateline' => time () );
			M ( 'myop_userlog' )->add ( $userlog );

			service ( 'Passport' )->loginLocal ( $data['email'],$_POST['passwd'],true );

			$this->registerRelation ( $id );

			redirect ( U ( 'home/Public/followuser' ) );
		} else {
			$result ['status'] = 0;
			$result ['info'] = "同步帐号发生错误";
			return false;
		}
	}

	// 注册的关联操作
	private function registerRelation($uid, $invite_info = null) {
		if (($uid = intval ( $uid )) <= 0)
			return;

		$dao = D ( 'Follow', 'weibo' );

		// 使用邀请码时, 建立与邀请人的关系
		if ($invite_info ['uid']) {
			// 互相关注
			D ( 'Follow', 'weibo' )->dofollow ( $uid, $invite_info ['uid'] );
			D ( 'Follow', 'weibo' )->dofollow ( $invite_info ['uid'], $uid );

			// 添加邀请记录
			model ( 'InviteRecord' )->addRecord ( $invite_info ['uid'], $uid );

			//邀请人积分操作
			X ( 'Credit' )->setUserCredit ( $invite_info ['uid'], 'invite_friend' );
		}

		// 默认关注的好友
		$auto_freind = model ( 'Xdata' )->lget ( 'register' );
		$auto_freind ['register_auto_friend'] = explode ( ',', $auto_freind ['register_auto_friend'] );
		foreach ( $auto_freind ['register_auto_friend'] as $v ) {
			if (($v = intval ( $v )) <= 0)
				continue;
			$dao->dofollow ( $v, $uid );
			$dao->dofollow ( $uid, $v );
		}

		// 开通个人空间
		$data ['uid'] = $uid;
		model ( 'Space' )->add ( $data );

		//注册成功 初始积分
		X ( 'Credit' )->setUserCredit ( $uid, 'init_default' );
	}

	/* 移动客户端外部帐号登录 */
    public function login_on_client()
    {
        $type = $_GET['type'];

        $this->_loadTypeLogin($type);
        $platform = new $type();

        $call_back_url = Addons::createAddonUrl('Login','login_callback_on_client', array('type' => $type));
        redirect($platform->getUrl($call_back_url));
    }

    public function login_callback_on_client()
    {
    	$type = $_GET['type'];
    	switch ($type) {
    		case 'sina':
				$this->_loadTypeLogin($type);
				$sina = new sina();
				$sina->checkUser();
				redirect(Addons::createAddonUrl('Login','login_display_on_client', array('type' => $type)));
    			break;
    		default:
    			;
    	}
    }

	// 外站帐号登录
	public function login_display_on_client(){
		if ( !in_array($_SESSION['open_platform_type'], array('sina', 'douban', 'qq')) ) {
			$this->error('授权失败');
		}

		$type = $_SESSION['open_platform_type'];
		$this->_loadTypeLogin($type);
		$platform = new $type();
		$userinfo = $platform->userInfo();
		// 检查是否成功获取用户信息
		if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
			$this->_loginFailureOnClient('获取用户信息失败');
		}
		if ( $info = M('login')->where("`type_uid`='".$userinfo['id']."' AND type='{$type}'")->find() ) {
			$user = M('user')->where("uid=".$info['uid'])->find();
			if (empty($user)) {
				// 未在本站找到用户信息, 删除用户站外信息,让用户重新登录
				M('login')->where("type_uid=".$userinfo['id']." AND type='{$type}'")->delete();
			}else {
				if ( $info['oauth_token'] == '' ) {
					$syncdata['login_id']        	= $info['login_id'];
					$syncdata['oauth_token']        = $_SESSION[$type]['access_token']['oauth_token'];
					$syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
					M('login')->save($syncdata);
				}

				service('Passport')->registerLogin($user);

				$this->_loginSuccessOnClient($user['uid'], $type);
			}
		}
		$this->assign('user',$userinfo);
		$this->assign('type',$type);
		$this->display('wap_login');
	}

	// 注册新本地帐号
	public function login_register_on_client()
	{
		if ( ! in_array($_POST['type'], array('douban','sina', 'qq')) ) {
			$this->_loginFailureOnClient('参数错误');
		}

		if( !isLegalUsername( t($_POST['uname']) ) ){
			$this->_loginFailureOnClient('昵称格式不正确');
		}

		$haveName = M('User')->where( "`uname`='".t($_POST['uname'])."'")->find();
		if( is_array( $haveName ) && sizeof($haveName)>0 ){
			$this->_loginFailureOnClient('昵称已被使用');
		}

		$type = $_POST['type'];
		$this->_loadTypeLogin($type);
		$platform = new $type();
		$userinfo = $platform->userInfo();

		// 检查是否成功获取用户信息
		if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
			$this->_loginFailureOnClient('获取用户信息失败');
		}

		// 检查是否已加入本站
		$map['type_uid'] = $userinfo['id'];
		$map['type']     = $type;
		if ( ($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find()) ) {
			$this->_loginSuccessOnClient($local_uid, $type);
		}
		// 初使化用户信息, 激活帐号
		$data['uname']        = t($_POST['uname'])?t($_POST['uname']):$userinfo['uname'];
		$data['province']     = intval($userinfo['province']);
		$data['city']         = intval($userinfo['city']);
		$data['location']     = $userinfo['location'];
		$data['sex']          = intval($userinfo['sex']);
		$data['is_active']    = 1;
		$data['is_init']      = 1;
		$data['ctime']      = time();
		$data['is_synchronizing']  = ($type == 'sina') ? '1' : '0'; // 是否同步新浪微博. 目前仅能同步新浪微博

		if ( $id = M('user')->add($data) ) {
			// 记录至同步登录表
			$syncdata['uid']                = $id;
			$syncdata['type_uid']           = $userinfo['id'];
			$syncdata['type']               = $type;
			$syncdata['oauth_token']        = $_SESSION[$type]['access_token']['oauth_token'];
			$syncdata['oauth_token_secret'] = $_SESSION[$type]['access_token']['oauth_token_secret'];
			M('login')->add($syncdata);

			// 转换头像
			if ($_POST['type'] != 'qq') { // 暂且不转换QQ头像: QQ头像的转换很慢, 且会拖慢apache
				D('Avatar')->saveAvatar($id,$userinfo['userface']);
			}

			// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
			$userlog = array(
				'uid'		=> $id,
				'action'	=> 'add',
				'type'		=> '0',
				'dateline'	=> time(),
			);
			M('myop_userlog')->add($userlog);

			service('Passport')->loginLocal($id);

			$this->registerRelation($id);

			$this->_loginSuccessOnClient($id, $type);
		}else{
			$this->_loginFailureOnClient('同步帐号发生错误');
		}
	}

	// 绑定已有帐号
	public function login_bind_on_client()
	{
		if ( ! in_array($_POST['type'], array('douban','sina','qq')) ) {
			$this->_loginFailureOnClient('参数错误');
		}

		$psd  = ($_POST['passwd']) ? $_POST['passwd'] : true;
		$type = $_POST['type'];

		if ( $user = service('Passport')->getLocalUser($_POST['email'], $psd) ) {
			$this->_loadTypeLogin($type);
			$platform = new $type();
			$userinfo = $platform->userInfo();

			// 检查是否成功获取用户信息
			if ( empty($userinfo['id']) || empty($userinfo['uname']) ) {
				$this->_loginFailureOnClient('获取用户信息失败');
			}

			// 检查是否已加入本站
			$map['type_uid'] = $userinfo['id'];
			$map['type']     = $type;
			if ( ($local_uid = M('login')->where($map)->getField('uid')) && (M('user')->where('uid='.$local_uid)->find()) ) {
				$this->_loginSuccessOnClient($local_uid, $type);
			}

			$syncdata['uid']      = $user['uid'];
			$syncdata['type_uid'] = $userinfo['id'];
			$syncdata['type']     = $type;
			if ( M('login')->add($syncdata) ) {
				service('Passport')->registerLogin($user);

				$this->_loginSuccessOnClient($user['uid'], $type);
			}else {
				$this->_loginFailureOnClient('绑定失败');
			}
		}else {
			$this->_loginFailureOnClient('帐号输入有误');
		}
	}

	private function _loginSuccessOnClient($local_uid, $type)
	{
		if( $login = M('login')->where("uid=" . $local_uid . " AND type='location'")->find() ){
			$data['oauth_token']         = $login['oauth_token'];
			$data['oauth_token_secret']  = $login['oauth_token_secret'];
			$data['uid']                 = $local_uid;
			$data['type']                = 'location';
		}else{
			$data['oauth_token']         = getOAuthToken($local_uid);
			$data['oauth_token_secret']  = getOAuthTokenSecret();
			$data['uid']                 = $local_uid;
			$data['type']                = 'location';
			M('login')->add($data);
		}
		redirect(Addons::createAddonUrl('Login', 'login_success_on_client', $data));
	}

	private function _loginFailureOnClient($text = '登录失败')
	{
	    header('Content-type:text/html;charset=utf-8');
		echo $text;
		exit;
	}

	public function login_success_on_client()
	{
	    header('Content-type:text/html;charset=utf-8');
		echo '登录成功，点击进入'.'<a href="'.U('wap').'">我的主页</a>';
		exit;
	}

	// 注册的关联操作
    private function _registerRelation($uid, $invite_info = null)
    {
    	if (($uid = intval($uid)) <= 0)
    		return;

    	$dao = D('Follow','weibo');

    	// 使用邀请码时, 建立与邀请人的关系
    	if ($invite_info['uid']) {
    		// 互相关注
    		D('Follow', 'weibo')->dofollow($uid, $invite_info['uid']);
			D('Follow', 'weibo')->dofollow($invite_info['uid'], $uid);

			// 添加邀请记录
			model('InviteRecord')->addRecord($invite_info['uid'], $uid);

			//邀请人积分操作
			X('Credit')->setUserCredit($invite_info['uid'], 'invite_friend');
    	}

        // 默认关注的好友
		$auto_freind = model('Xdata')->lget('register');
		$auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
		foreach($auto_freind['register_auto_friend'] as $v) {
			if (($v = intval($v)) <= 0)
				continue ;
			$dao->dofollow($v, $uid);
			$dao->dofollow($uid, $v);
		}

		// 开通个人空间
		$data['uid'] = $uid;
		model('Space')->add($data);

		//注册成功 初始积分
		X('Credit')->setUserCredit($uid,'init_default');
	}

	public function login_plugin_login(){
	    $config = model('AddonData')->lget('login');
	    $this->assign('config',$config);
	    $this->assign('data',self::$validLogin);
	    $this->assign('alias',self::$validAlias);
	    $this->assign('applyUrl',self::$validApply);
	    $this->display('sync_admin');
	}

	public function login_plugin_publish(){
	    unset($_POST['unset']);
	    $config = model('AddonData')->lget('login');
	    $temp = array_flip($config['publish']);
        foreach(self::$validLogin as $key=>$value){
            if( in_array($key,self::$validPublish)){
                $item = array('hasKey'=>false,'checked'=>false);
                if(!empty($config[$value[0]]) && !empty($config[$value[1]])){
                    $item['hasKey']= true;
                }
                if(isset($temp[$key])){
                    $item['checked'] = true;
                }
                $data[$key] = $item;
            }
        }

	    $this->assign('data',$data);
	    $this->assign('alias',self::$validAlias);
	    $this->display('sync_publish_admin');
	}

	public function savePublishConfig(){
	    $temp = array();
	    foreach($_POST['open'] as $value){
	        $temp[] = h($value);
	    }
	    $data['publish'] = $temp;
	    $res = model('AddonData')->lput('login', $data);
	    if ($res) {
	        $this->assign('jumpUrl', Addons::adminPage('login_plugin_login'));
	       // $this->success();
	    } else {
	        $this->error();
	    }
	}

	public function saveAdminConfig(){
	    $data = array();
	    foreach($_POST as $key=>$value){
	        if(is_array($value)){
	            foreach($value as $k=>$v){
	                $value[$k] = h($v);
	            }
	            $data[$key] = $value;
	        }else{
	            $data[$key] = h($value);
	        }
	    }
	    $res = model('AddonData')->lput('login', $data);
	    if ($res) {
	        $this->assign('jumpUrl', Addons::adminPage('login_plugin_login'));
	        $this->success();
	    } else {
	        $this->error();
	    }
	}

	private function _loadTypeLogin($type,$config = array()){
	    $config = empty($config)?model('AddonData')->lget('login'):$config;
	    if(isset(self::$validLogin[$type])){
	        foreach(self::$validLogin[$type] as $value){
	            if(empty($config[$value])) {
	                throw new ThinkException(self::$validAlias[$type]."没有设置Key,请勿异常操作");
	            }
	            !defined(strtoupper($value)) && define(strtoupper($value),$config[$value]);
	        }
	        include_once $this->path . "/lib/{$type}.class.php";
	    }
	}
}