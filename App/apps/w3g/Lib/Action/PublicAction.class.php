<?php
class PublicAction extends Action {

	//刷新操作
	public function jump(){
		$url = $_GET['url'];
		$this->redirect($url);
	}

	public function isRegisterOpen()
	{
	    return strtolower(model('Xdata')->get('register:register_type')) == 'open';
	}

	public function login()
	{
		// 登录验证
		$passport = service('Passport');
		if ($passport->isLogged()) {
			$this->redirect(U('w3g/Index/index'));
		}

		$this->assign('is_register_open', $this->isRegisterOpen() ? '1' : '0');

		$this->display();
	}

	public function doLogin() {
		//使用url+sessionId的方式解决手机不支持cookie的情况
		//http://topic.csdn.net/u/20091116/15/594891e2-9046-40b2-b324-b2220af31c4e.html?70417
		if (empty($_POST['email']) || empty($_POST['password'])) {
			$this->redirect(U('w3g/Public/login'), 3, '用户名和密码不能为空');
		}
		if (!isValidEmail($_POST['email'])) {
			$this->redirect(U('w3g/Public/login'), 3, 'Email格式错误，请重新输入');
		}
		if ($user = service('Passport')->getLocalUser($_POST['email'], $_POST['password'])) {
			if ($user['is_active'] == 0) {
				$this->redirect(U('w3g/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
			}
			$this->setSessionAndCookie($user['uid'], $user['uname'], $user['email'], intval($_POST['remember']) === 1);
            $this->recordLogin($user['uid']);
            $this->redirect(U('w3g/Index/index'));
		}else {
			$this->redirect(U('w3g/Public/login'), 3, '帐号或密码错误，请重新输入');
		}
	}

	public function logout() {
		service('Passport')->logoutLocal('');
		$this->redirect(U('w3g/Public/login'));
	}

	public function setSessionAndCookie($uid, $uname, $email, $remember = false) {
        $_SESSION['mid']    = $uid;
        $_SESSION['uname']  = $uname;
        $remember ?
			cookie('LOGGED_USER',jiami('thinksns.'.$uid),(3600*24*365)) :
			cookie('LOGGED_USER',jiami('thinksns.'.$uid),(3600*2));
    }

    //登录记录
    public function recordLogin($uid) {
        $data['uid']    = $uid;
        $data['ip']     = get_client_ip();
        $data['place']  = convert_ip($data['ip']);
        $data['ctime']  = time();
        M('login_record')->add($data);
    }

	// URL重定向
	function redirect($url,$time=0,$msg='') {
		//多行URL地址支持
		$url = str_replace(array("\n", "\r"), '', $url);
		if(empty($msg))
		$msg    =   "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			// redirect
			if(0===$time) {
				header("Location: ".$url);
			}else {
				header("refresh:{$time};url={$url}");
				// 防止手机浏览器下的乱码
				$str = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
				$str .= $msg;
			}
		}else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time!=0)
			$str   .=   $msg;
		}
		$this->assign('msg', $str);

		$this->display('redirect');
	}


	// 访问正常版
	public function wapToNormal() {
		$_SESSION['wap_to_normal'] = '1';
		cookie('wap_to_normal', '1', 3600*24*365);
		redirect(U('home'));
	}

	public function register()
	{
	    if (!$this->isRegisterOpen())
	        $this->redirect(U('/Public/login'), 3, '站点未开放注册');

	    $this->assign($_GET);
	    $this->display();
	}

	public function doRegister()
	{
	    if ($_POST['password'] != $_POST['re_password'])
	        $this->redirect(U('/Public/register', $_POST), 3, '两次的密码不符');

	    $service = service('UserRegister');
	    $uid     = $service->register($_POST['email'], $_POST['uname'], $_POST['password'], true);
	    if (!$uid)
	        $this->redirect(U('/Public/register', $_POST), 3, $service->getLastError());
	    else
	        $this->redirect(U('/Public/login'), 1, '注册成功');
	}
}