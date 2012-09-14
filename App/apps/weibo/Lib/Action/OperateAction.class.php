<?php
class OperateAction extends Action{

    //发布
    function publish(){
		if(isSubmitLocked()){
			die('submitlocked');
		} else if (isDuplicateContent(trim($_POST['content'] . $_POST['publish_type_data']))) {
            if (0 == $_POST['publish_type']) {
                die('duplicatecontent');
            }
        }
		$pWeibo = D('Weibo');
		$data['content'] =  $_POST['content'];
		$id = $pWeibo ->publish( $this->mid , $data, 0 ,intval( $_POST['publish_type']) , $_POST['publish_type_data']);
		if( $id ){
			//锁定发布
			lockSubmit();

        	//发布成功后，检测后台是否开启了自动举报功能
        	$weibo_option = model('Xdata')->lget('weibo');
        	if( $weibo_option['openAutoDenounce'] ){
				if( checkKeyWord( $data['content'] )){
					model('Denounce')->autoDenounce($id,$this->mid,$data['content']);
        			echo '0';exit;
				}
        	}

			//添加积分
			X('Credit')->setUserCredit($this->mid,'add_weibo');

			//输出微博内容
			$data = $pWeibo->getOneLocation( $id );

			$this->assign('data',$data);
        	$this->display();
        }
    }

    //转发
    function transpond(){
    	$pWeibo = D('Weibo');
    	if($_POST){
			if(isSubmitLocked()){
				die('submitlocked');
			} else if (isDuplicateContent(trim($_POST['content']))) {
                die('duplicatecontent');
            }    	
	        $post['content']         = $_POST['content'];
	        $post['transpond_id']    = intval( $_POST['transpond_id'] );
	        $post['reply_weibo_id']  = $_POST['reply_weibo_id'];
	        if( $id = $pWeibo->transpond($this->mid,$post) ){
	        
	         Addons::hook('transpond_post_done',array('origin_id'=>$post['transpond_id'],'weibo_id'=>$id));
				//锁定发布
				lockSubmit();

	        	$data = $pWeibo->getOneLocation($id);
				X('Credit')->setUserCredit($this->mid,'forward_weibo')
						   ->setUserCredit($data['expend']['uid'],'forwarded_weibo');

        		$this->assign('data',$data);
        		$this->display('publish');
	        }
    	}else{
	    	$intId = intval( $_GET['id'] );
	    	$info = $pWeibo->where( 'weibo_id='.$intId)->find();
	    	if( $info['transpond_id'] ){
	    		$info['transponInfo'] = D('Operate')->field('weibo_id,uid,content')->where('weibo_id='.$info['transpond_id'])->find();
	    	}else{
	    		$info['old_content'] = $info['content'];
	    	}
	    	$info['upcontent'] = intval($_GET['upcontent']);
	    	$this->assign( 'data' , $info );
	    	$this->display();
    	}
    }

    //添加评论
    function addcomment(){
        $_POST['comment_content'] = preg_replace('/^\s+|\s+$/', '', $_POST['comment_content']);
		if(isSubmitLocked()){
			die('submitlocked');
		} else if (!$_POST['comment_content']) {
            die('emptycontent');
        } else if (isDuplicateContent($_POST['comment_content'])) {
            die('duplicatecontent');
        }
    	$post['reply_comment_id'] = intval( $_POST['reply_comment_id'] );   //回复 评论的ID
    	$post['weibo_id']         = intval( $_POST['weibo_id'] );           //回复 微博的ID
    	$post['content']          = html_entity_decode(h(t($_POST['comment_content'])));         //回复内容
    	$post['transpond']        = intval($_POST['transpond']);            //是否同是发布一条微博
		$post['comment_ip'] = get_client_ip();
    	$result = D('Comment')->doaddcomment($this->mid, $post);
    	if($result){
			//锁定发布
			lockSubmit();
    	    echo $result;
    	}else{
            $this->error("评论失败");
    	}
		if(intval($_POST['transpond_weibo_id'])){//同时评论给原文作者
			unset($post['reply_comment_id']);
			unset($post['transpond']);
			$post['weibo_id'] = intval($_POST['transpond_weibo_id']);
			D('Comment')->doaddcomment($this->mid, $post, true);
		}
    }

    //删除评论
    function docomments(){
    	$result = D('Comment')->deleteComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }

    //批量删除评论
    function deleteMuleComment(){
    	$result = D('Comment')->deleteMuleComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }

    //删除微博
    function delete(){
    	$arrWeiInfo = D( 'Operate' )->where( 'weibo_id='.intval(($_POST['id'])) )->field('isdel')->find();
    	if( !$arrWeiInfo['isdel'] ){
	    	if( D('Operate')->deleteMini( intval($_POST['id']) , $this->mid ) ){
				X('Credit')->setUserCredit($this->mid,'delete_weibo');
	    		echo '1';
	    	}
    	}else{
    		echo '1';
    	}
    }

    //收藏
    function stow(){
    	if( D('Favorite')->favWeibo( intval( $_POST['id'] ), $this->mid ) ){
    		echo '1';
       	}
    }

 	function unstow(){
    	if( D('Favorite')->dodelete( intval( $_POST['id'] ), $this->mid ) ){
    		echo '1';
       	}
    }

    //关注人
    function follow(){
        if($_POST['type']=='dofollow'){
            $uid = $this->mid;
            $data = D('Follow', 'weibo')->where('uid ='.$uid)->findAll();
            $count = count($data);
            if($count >= $GLOBALS['max_following']){
            echo '14';
            }else{
    		echo D('Follow')->dofollow( $this->mid,intval($_POST['uid']) );
            }
        }else{
    		echo D('Follow')->unfollow( $this->mid,intval($_POST['uid']) );
    	}
    }

    //关注话题
    function followtopic(){
    	$name = $_POST['name'];
    	$topicId = D('Topic')->getTopicId($name);
    	if($topicId){
    		$id = D('Follow')->dofollow($this->mid, $topicId, 1);
    	}
    	echo json_encode(array('code'=>$id,'topicId'=>$topicId,'name'=>h(t(mStr(preg_replace("/#/",'',$name),150,'utf-8',false)))));
    }

    //取消关注话题
    function unfollowtopic(){
        $topicId = intval($_POST['topicId']);
    	if($topicId){
    		$id = D('Follow')->unfollow($this->mid,$topicId,1);
    	}
    	echo $id;
    }

    //上传图片
    function uploadpic(){

    }

    function quickpublish(){
    	$this->assign('text', $_POST['text'] );
    	$this->display();
    }

    //上传临时文件

    // 预同步 (如果已绑定过, 自动同步; 否则展示"开始绑定"按钮)
    function beforeSync() {
    	if ( !in_array($_GET['type'], array('sina')) ) {
    		echo 0;
    	}

    	// 展示"开始绑定"按钮
    	$map['uid']  = $this->mid;
    	$map['type'] = 'sina';
   		if( M('login')->where("uid={$this->mid} AND type='{$_GET['type']}' AND oauth_token<>''")->count() ){
   			M('login')->setField('is_sync',1,$map);
   			echo '1';
   		}else{
   			$_SESSION['weibo_bind_target_url'] = U('home/User/index');
   			$this->assign('url', U('weibo/Operate/bind',array('type'=>$_GET['type'])));
   			$this->display();
   		}
    }

    //绑定帐号
    function bind() {
    	if ( !in_array($_GET['type'], array('sina')) ) {
    		if ($this->isAjax()) {
    			echo 0;
    			exit;
    		}else {
    			$this->error(L('arg_error'));
    		}
    	}
    	include_once SITE_PATH."/addons/plugins/Login/lib/{$_GET['type']}.class.php";
		$platform = new $_GET['type']();
		$call_back_url = U("weibo/Operate/bind{$_GET['type']}CallBack");
		$url = $platform->getUrl($call_back_url);
		redirect($url);
    }

    function bindSinaCallBack() {
    	include_once( SITE_PATH.'/addons/plugins/Login/lib/sina.class.php' );
		$sina = new sina();
    	$sina->checkUser();

    	if ( !in_array($_SESSION['open_platform_type'], array('sina')) ) {
    		if ($this->isAjax()) {
				echo 0;
				exit;
    		}else {
    			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
    			$this->error(L('authorization_failed'));
    		}
		}

		// 检查是否成功获取用户信息
		$userinfo = $sina->userInfo();
		if ( !is_numeric($userinfo['id']) || !is_string($userinfo['uname']) ) {
			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
			$this->error(L('user_information_failed'));
		}

		$syncdata['uid']                = $this->mid;
		$syncdata['type_uid']           = $userinfo['id'];
		$syncdata['type']               = 'sina';
		$syncdata['oauth_token']        = $_SESSION['sina']['access_token']['oauth_token'];
		$syncdata['oauth_token_secret'] = $_SESSION['sina']['access_token']['oauth_token_secret'];
		$syncdata['is_sync']			= '1';
		if ( $info = M('login')->where("type_uid={$userinfo['id']} AND type='sina'")->find() ) {
			// 该新浪用户已在本站存在, 将其与当前用户关联(即原用户ID失效)
			M('login')->where("`login_id`={$info['login_id']}")->save($syncdata);
		}else {
			// 添加同步信息
			M('login')->add($syncdata);
		}

		if ( isset($_SESSION['weibo_bind_target_url']) ) {
			$this->assign('jumpUrl', $_SESSION['weibo_bind_target_url']);
			unset($_SESSION['weibo_bind_target_url']);
		}else {
			$this->assign('jumpUrl', U('home/Account/bind').'#sina');
		}
		$this->success(L('bind_success'));
    }

    /**
     * @deprecated
     */
    function bind_backup(){
    	$type = h($_POST['value']);
    	if($_POST){
	    	include_once( SITE_PATH.'/addons/plugins/Login/lib/sina.class.php' );
			$sina = new sina();
			$weiboAuth =   $sina->getJSON($_POST['username'],$_POST['password']);
			if( $weiboAuth['oauth_token'] ){
				$data['type']     = 'sina';
				$data['type_uid'] =  $weiboAuth['user_id'];
				$data['uid']      = $this->mid;
				if($info = M('login')->where($data)->find()){
					if($info['oauth_token']){
						M('login')->setField('is_sync',1,$data);
					}else{
						$savedata['oauth_token'] 		= $weiboAuth['oauth_token'];
						$savedata['oauth_token_secret'] = $weiboAuth['oauth_token_secret'];
						$savedata['is_sync'] = 1;
						M('login')->where('login_id='.$info['login_id'])->data($savedata)->save();
					}
				}else{
					$data['oauth_token'] 		= $weiboAuth['oauth_token'];
					$data['oauth_token_secret'] = $weiboAuth['oauth_token_secret'];
					$data['is_sync'] = 1;
					M('login')->add($data);
				}
				echo '1';
			}else{
				echo '0';
			}
    	}else{
    		$map['uid'] = $this->mid;
    		$map['type'] = 'sina';
    		if( M('login')->where("uid={$this->mid} AND type='sina' AND oauth_token<>''")->count() ){
    			M('login')->setField('is_sync',1,$map);
    			echo '1';
    		}else{
    			$this->display();
    		}
    	}
    }

    //绑定email
    function bindemail(){
    	$email = $_POST['email'];
    	$passwd = $_POST['passwd'];
		if (!preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)){
			$return['boolen'] = false;
    		$return['message'] = L('email_format_error');
    		exit(json_encode($return));
		}
    	if( M('user')->where("email='{$email}'")->count() ){
    		$return['boolen'] = false;
    		$return['message'] = L('email_exist');
    		exit(json_encode($return));
    	}

    	$data['email']    = $email;
    	$data['password'] = md5($passwd);
    	if( M('user')->where('uid='.$this->mid)->data($data)->save() ){
    		$return['boolen'] = true;
    		exit(json_encode($return));
    	}else{
    		$return['boolen'] = false;
    		$return['message'] = L('bind_failed');
    		exit(json_encode($return));
    	}

    }

    //取消绑定
    function delbind(){
    	if( M('login')->where("uid={$this->mid} AND type='sina'")->delete() ){
    		echo '1';
    	}else{
    		echo '0';
    	}
    }

    function unbind(){
    	$type = h($_POST['value']);
    	echo M("login")->setField('is_sync',0,"uid={$this->mid} AND type='{$type}'" );
    }
}
?>
