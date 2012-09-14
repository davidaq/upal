<?php
class LoginAction extends AdministratorAction {

	//*****日志管理*****
    public function index() {
    	$dao = D('Login');
    	$res = $dao->getLoginList('', true, true);
    	$this->assign($res);
        $this->display();
    }

	//****删除用户登录日志****
    public function doDeleteLog() {
    	$_POST['login_record_id'] = t($_POST['login_record_id']);
    	$_POST['login_record_id'] = explode(',', $_POST['login_record_id']);

    	$_LOG['login_record_id'] = $this->mid;
		$_LOG['type'] = '2';
		
		$data[] = '用户 - 登陆日志管理 ';
		$map['login_record_id'] = array('in',$_POST['login_record_id']);
		$data[] = M('login_record')->where($map)->findAll();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

    	//ts_user
    	$res = D('Login')->deleteLog($_POST['login_record_id']);
    	if($res) {echo 1;		  }
    	else 	 {echo 0; return ;}
    }

    //删除三个月前的日志
    public function doDeleteLogTime(){
        $time = time() - 3*30*24*3600;
        $res = M('login_record')->where('ctime <='.$time)->delete(); 
        if($res){
            echo "1";
        }else{
            echo "0";
        }  
    }

}