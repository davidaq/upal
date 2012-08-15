<?php
class WeiboOperateAction extends BaseAction{

	protected function _initialize()
	{
		if (!in_array(ACTION_NAME, array('deleteMuleComment'))) {
			parent::_initialize();
			if (!$this->ismember) {
				if (ACTION_NAME == 'transpond') {
					$this->assign('action_name', '转发');
				} else {
					$this->assign('action_name', '发布');
				}
				$this->display('notmember');
				exit;
			}
		}
	}

    //发布
    function publish(){
		if(isSubmitLocked()){
			die('submitlocked');
		} else if (isDuplicateContent(trim($_POST['content'] . $_POST['publish_type_data']))) {
            die('duplicatecontent');
        }
    	$pWeibo = D('GroupWeibo');
        $data['content'] =  $_POST['content'];
        $data['gid']     =  $this->gid;
        $id = $pWeibo ->publish( $this->mid , $data, 0 ,intval( $_POST['publish_type']) , $_POST['publish_type_data']);
        if( $id ){
			//锁定发布
			lockSubmit();

        	if ($_POST['myweibo']) {
				$weibo = $pWeibo->find($id);
        		$from_data = array('app_type'=>'local_app', 'app_name'=>'group', 'gid'=>$this->gid, 'title'=>$this->groupinfo['name'], 'url'=>U('group/Group/index', array('gid'=>$this->gid)));
        		$myweibo_id = D('Weibo', 'weibo')->publish( $this->mid , $weibo, 0 ,0 , null, '', serialize($from_data));
        	}
        	//发布成功后，检测后台是否开启了自动举报功能
        	$weibo_option = model('Xdata')->lget('weibo');
        	if( $weibo_option['openAutoDenounce'] && checkKeyWord( $data['content'] ) ){
        		$map['from'] = 'group';
				$map['aid'] = $id;
				$map['uid'] = '0';
				$map['fuid'] = $this->mid;
				$map['content'] = $data['content'];
				$map['reason'] = '内容中含有需要过滤的敏感词';
				$map['ctime'] = time();
				$map['state'] = '1';
        		M( 'Denounce' )->add( $map );
	        	if ($_POST['myweibo']) {
	        		$map['from'] = 'weibo';
					$map['aid'] = $myweibo_id;
	        		M( 'Denounce' )->add( $map );
	        	}
        		echo '0';exit;
        	}
			X('Credit')->setUserCredit($this->mid,'add_weibo');
        	$data = $pWeibo->getOneLocation($id, $this->gid);
        	$this->assign('data',$data);
        	$this->display();
        }
    }

    // 转发
    function transpond()
    {
    	$pWeibo = D('GroupWeibo');
    	if ($_POST) {
	        $post['gid']         	 = $this->gid;
	        $post['content']         = $_POST['content'];
	        $post['transpond_id']    = intval( $_POST['transpond_id'] );
	        $post['reply_weibo_id']  = $_POST['reply_weibo_id'];
	        if ($id = $pWeibo->transpond($this->mid, $post)) {
	        	$data = $pWeibo->getOneLocation($id, $this->gid);
				X('Credit')->setUserCredit($this->mid,'forward_weibo')
						   ->setUserCredit($data['expend']['uid'],'forwarded_weibo');
        		$this->assign('data',$data);
        		$this->display('publish');
	        }
    	} else {
	    	$intId = intval( $_GET['id'] );
	    	$info = $pWeibo->where( 'weibo_id=' . $intId . ' AND gid=' . $this->gid)->find();
	    	if ($info['transpond_id']) {
	    		$info['transponInfo'] = D('WeiboOperate')->field('weibo_id,uid,content')
	    												 ->where('weibo_id=' . $info['transpond_id'] . ' AND gid=' . $this->gid)
	    												 ->find();
	    	} else {
	    		$info['old_content'] = $info['content'];
	    	}
	    	$info['upcontent'] = intval($_GET['upcontent']);
	    	$this->assign( 'data' , $info );
	    	$this->display();
    	}
    }

    // 分享微博
    function shareWeibo()
    {
    	$pWeibo = D('GroupWeibo');
	    $intId = intval( $_GET['id'] );
	    $info = $pWeibo->where( 'weibo_id=' . $intId . ' AND gid=' . $this->gid)->find();
	    if ($info['transpond_id']) {
	    	$info['transponInfo'] = D('WeiboOperate')->field('weibo_id,uid,content,type,type_data')
	    											 ->where('weibo_id=' . $info['transpond_id'] . ' AND gid=' . $this->gid)
	    											 ->find();
	    	$info['transponInfo']['type_data'] = unserialize($info['transponInfo']['type_data']);
	    } else {
	    	$info['type_data'] = unserialize($info['type_data']);
	    }
	    $this->assign( 'data' , $info );
	    $this->display();
    }

	// 分享到微博
	public function weibo() {
		// 解析参数
		$_GET['param']	= unserialize(urldecode($_GET['param']));
		$active_field	= $_GET['param']['active_field'] == 'title' ? 'title' : 'body';
		$this->assign('has_status', $_GET['param']['has_status']);
		$this->assign('is_success_status', $_GET['param']['is_success_status']);
		$this->assign('status_title', t($_GET['param']['status_title']));

		// 解析模板(统一使用模板的body字段)
		$_GET['data']	= unserialize(urldecode($_GET['data']));
		$content		= model('Template')->parseTemplate(t($_GET['tpl_name']), array($active_field=>$_GET['data']));
		//$content		= preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/u',group_get_content_url, $content);
		$this->assign('content', $content[$active_field]);

		$this->assign('type',$_GET['data']['type']);
		$this->assign('type_data',$_GET['data']['type_data']);
		$this->assign('button_title', t(urldecode($_GET['button_title'])));
		$this->display();
	}

	// 分享到微博
	public function doShare()
	{
	    $data['gid']     = $this->gid;
		$data['content'] = $_POST['content'];
		$type      = intval($_POST['type']);
		$type_data = $_POST['typedata'];

        $id = D('GroupWeibo','group')->publish($this->mid, $data, 0, $type, $type_data, '', $from_data);

        if ($id) {
        	X('Credit')->setUserCredit($this->mid,'share_to_weibo');
        	echo '1';
        } else {
        	echo '0';
        }
	}

    //添加评论
    function addcomment()
    {
        $_POST['comment_content'] = preg_replace('/^\s+|\s+$/', '', $_POST['comment_content']);
		if(isSubmitLocked()){
			die('submitlocked');
		} else if (!$_POST['comment_content']) {
            die('emptycontent');
        } else if (isDuplicateContent($_POST['comment_content'])) {
            die('duplicatecontent');
        }
    	$post['reply_comment_id'] = $_POST['reply_comment_id'];   //回复 评论的ID
    	$post['weibo_id']         = $_POST['weibo_id'];           //回复 微博的ID
    	$post['gid']         	  = $this->gid;           		  //群组的ID
    	$post['content']          = $_POST['comment_content'];    //回复内容
    	$post['transpond']        = $_POST['transpond'];          //是否同是发布一条微博
		echo D('WeiboComment')->doaddcomment($this->mid, $post);
		//锁定发布
		lockSubmit();
		if(intval($_POST['transpond_weibo_id'])){//同时评论给原文作者
			unset($post['reply_comment_id']);
			unset($post['transpond']);
			$post['weibo_id'] = $_POST['transpond_weibo_id'];
			D('WeiboComment')->doaddcomment($this->mid, $post, true);
		}
    }

    //删除评论
    function docomments(){
    	$result = D('WeiboComment')->deleteComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }

    //批量删除评论
    function deleteMuleComment(){
    	$result = D('WeiboComment', 'group')->deleteMuleComments( $_POST['id'] , $this->mid);
    	echo json_encode($result);
    }

    //删除微博
    function delete(){
    	$arrWeiInfo = D('WeiboOperate')->where('weibo_id=' . $_POST['id'] . ' AND gid=' . $this->gid)->field('isdel')->find();
    	if(!$arrWeiInfo['isdel']){
	    	if( D('WeiboOperate')->deleteMini(intval($_POST['id']), $this->gid, $this->mid)){
				X('Credit')->setUserCredit($this->mid, 'delete_weibo');
	    		echo '1';
	    	}
    	}else{
    		echo '1';
    	}
    }
}
?>