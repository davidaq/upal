<?php
class UserRegisterService extends Service
{
    /*
     * TODO:
     * 1. 使用插件机制完成与其他平台(如:新浪微博、QQ)的集成
     * 2. 使用插件机制完成与UCenter的集成
     */
    protected $last_error   = null;

    public function getLastError()
    {
        return $this->last_error;
    }

    public function register($email, $uname, $password, $is_init = 0)
    {
        // 检查站点是否关闭注册
        $register_option = model('Xdata')->get('register:register_type');
        if (strtolower($register_option) == 'closed') {
            $this->last_error = '网站关闭注册';
            return false;
        }

        // 检查参数合法性
        if (!isEmailAvailable($email)) {
            $this->last_error = 'Email不合法或已存在';
            return false;
        }
        if (!isUnameAvailable($uname)) {
            $this->last_error = '昵称不合法或已存在';
            return false;
        }
        if (!isValidPassword($password)) {
            $this->last_error = '密码不合法';
            return false;
        }

        // 参数合法. So, continue...

        $uid = $this->_addUser($email, $uname, $password, $is_init);
		if (!$uid) {
		    $this->last_error = '保存失败';
		    return false;
		}

        $this->_addUserToMyopLog($uid);

        $this->_syncUCenter($uid, $email, $uname, $password);

        return $uid;
    }

    private function _addUser($email, $uname, $password, $is_init)
    {
        $user_model          = D('User', 'home');
        $need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));

        $data['email']       = $email;
		$data['uname']	     = t($uname);
		$data['password']    = md5($password);
		$data['ctime']	     = $_SERVER['REQUEST_TIME'];
		$data['is_active']   = $need_email_activate ? '0' : '1';
		$data['is_init']     = $is_init ? '1' : '0';
		return $user_model->add($data);
    }

    private function _addUserToMyopLog($uid)
    {
        $user_log = array(
			'uid'		=> $uid,
			'action'	=> 'add',
			'type'		=> '0',
			'dateline'	=> $_SERVER['REQUEST_TIME'],
		);
		return M('myop_userlog')->add($user_log);
    }

    private function _syncUCenter($uid, $email, $uname, $password)
    {
        if (UC_SYNC) {
			$uc_uid = uc_user_register($uname, $password, $email);
			if ($uc_uid)
				ts_add_ucenter_user_ref($uid, $uc_uid, $uname);
		}
    }

    public function run()
    {
        return ;
    }
}