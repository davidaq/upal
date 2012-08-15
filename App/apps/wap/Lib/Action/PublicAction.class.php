<?php
class PublicAction extends Action {

	public function login() {
		// 登录验证
		$passport = service('Passport');
		if ($passport->isLogged()) {
			redirect(U('wap/Index/index'));
		}

	    $this->assign('is_register_open', $this->isRegisterOpen() ? '1' : '0');

		$this->display();
	}

	public function doLogin() {
		if (empty($_POST['email']) || empty($_POST['password'])) {
			redirect(U('wap/Public/login'), 3, '用户名和密码不能为空');
		}
		if (!isValidEmail($_POST['email'])) {
			redirect(U('wap/Public/login'), 3, 'Email格式错误，请重新输入');
		}
		if ($user = service('Passport')->getLocalUser($_POST['email'], $_POST['password'])) {
			if ($user['is_active'] == 0) {
				redirect(U('wap/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
			}
            service('Passport')->registerLogin($user, intval($_POST['remember']) === 1);

            redirect(U('wap/Index/index'));
		} else {
			redirect(U('wap/Public/login'), 3, '帐号或密码错误，请重新输入');
		}
	}

	public function logout() {
		service('Passport')->logoutLocal();
		redirect(U('wap/Public/login'));
	}

	// 访问正常版
	public function wapToNormal() {
		$_SESSION['wap_to_normal'] = '1';
		cookie('wap_to_normal', '1', 3600*24*365);
		redirect(U('home'));
	}

	public function isRegisterOpen()
	{
	    return strtolower(model('Xdata')->get('register:register_type')) == 'open';
	}

	public function register()
	{
	    if (!$this->isRegisterOpen())
	        redirect(U('/Public/login'), 3, '站点未开放注册');

	    $this->assign($_GET);
	    $this->display();
	}

	public function doRegister()
	{
	    if ($_POST['password'] != $_POST['re_password'])
	        redirect(U('/Public/register', $_POST), 3, '两次的密码不符');

	    $service = service('UserRegister');
	    $uid     = $service->register($_POST['email'], $_POST['uname'], $_POST['password'], true);
	    if (!$uid){
	        redirect(U('/Public/register', $_POST), 3, $service->getLastError());
	    }else{
	        //redirect(U('/Public/login'), 1, '注册成功');
			if ($user = service('Passport')->getLocalUser($_POST['email'], $_POST['password'])) {
				if ($user['is_active'] == 0) {
					redirect(U('wap/Public/login'), 3, '帐号尚未激活，请激活后重新登录');
				}

				$result = service('Passport')->registerLogin($user);
				redirect(U('wap/Index/index'));
			} else {
				redirect(U('wap/Public/login'), 3, '帐号或密码错误，请重新输入');
			}
		}
	}
}