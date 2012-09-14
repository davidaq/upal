<?php
class IndexAction extends BaseAction {
	// 个人首页
	public function index($uid = 0) {
	    $data['user_id'] = $uid <= 0 ? $this->mid : $uid;
        $data['page']    = $this->_page;

		// 微博列表
		$weibolist = api('Statuses')->data($data)->friends_timeline();
		$maxWeiboID = $weibolist['0']['weibo_id'];
		$this->assign('maxWeioboID',$maxWeiboID);
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);

		$this->display('index');
	}

	// 微博广场
	public function publicsquare() {
		$data['page'] = $this->_page;
		$weibolist = api('Statuses')->data($data)->public_timeline();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		$this->assign('headtitle', '微博广场');
		$this->display();
	}

	// XX的微博
	public function weibo() {
        // 微博列表
        $data['user_id'] = $this->uid;
        $weibolist	= api('Statuses')->data($data)->user_timeline();
		$weibolist	= $this->__formatByFavorite($weibolist);
		$weibolist	= $this->__formatByContent($weibolist);
        $userinfo	= api('User')->show();
		$this->assign('weibolist', $weibolist);
        $this->assign('hideUsername', 1);
        if($this->mid==$this->uid){
        	$this->assign('headtitle', '我的微博');
        }else {
        	$this->assign('headtitle', $this->profile['status']['uname'].'的微博');
        }
        $this->assign('showuserlist',true);
        $this->display();
	}

	// @提到我的
	public function atMe() {
		$data['page']     = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile[0]);

        // @XX的微博列表
        $weibolist = api('Statuses')->data($data)->mentions();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
        $this->assign('weibolist', $weibolist);

        $this->assign('headtitle', '提到我的');
        $this->display('weibo');
	}

	// 评论我的
	public function replyMe() {
		$data['page']     = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile[0]);

        // 评论的微博列表
        $weibolist = api('Statuses')->data($data)->comments_receive_me();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
        $this->assign('weibolist', $weibolist);

        $this->assign('headtitle', '评论我的');
        $this->display('weibo');
	}

	// 我的收藏
	public function favorite() {
		$data['page']	= $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile[0]);

        // 收藏列表
        $weibolist = api('Favorites')->data($data)->index();
		foreach ($weibolist as $k => $v) {
        	$weibolist[$k]['is_favorite'] = 1;
        }
        $this->assign('weibolist', $weibolist);

        $this->assign('headtitle', '我的收藏');
        $this->display('weibo');
	}

	private function __formatByFavorite($weibolist) {
		$ids = implode(',', getSubByKey($weibolist, 'weibo_id'));
        $favorite = D('Favorite','weibo')->isFavorited($ids, $this->mid);
        foreach ($weibolist as $k => $v) {
        	if ( in_array($v['weibo_id'], $favorite) ) {
        		$weibolist[$k]['is_favorite'] = 1;
        	}else {
        		$weibolist[$k]['is_favorite'] = 0;
        	}
        }
        return $weibolist;
	}

	private function __formatByContent($weibolist) {
		$self_url = urlencode($this->_self_url);
		foreach ($weibolist as $k => $v) {
			$weibolist[$k]['content'] = wapFormatContent($v['content'], false, $self_url);
			if ( isset($v['transpond_data']['content']) ) {
				$weibolist[$k]['transpond_data']['content'] = wapFormatContent($v['transpond_data']['content'], false, $self_url);
			}
		}
		return $weibolist;
	}

	private function __formatByComment($comment) {
		$self_url = urlencode($this->_self_url);
		foreach ($comment as $k => $v) {
			$comment[$k]['content'] = wapFormatComment($v['content'], true, $self_url);
		}
		return $comment;
	}

	// 话题
	public function topic() {
		$topic = D('Topic','weibo')->getHot();
		$this->assign('topic', $topic);

		 $this->assign('headtitle', '热门话题');
		$this->display();
	}


	// 关注列表
	public function following() {
		if($this->uid==$this->mid){
			$this->assign('headtitle', '我的关注');
		}else{
			$this->assign('headtitle', getUserName($this->uid).'的关注');
		}
		$this->__followlist('following',$this->uid);
	}

	// 粉丝列表
	public function followers() {
		if($this->uid==$this->mid){
			$this->assign('headtitle', '我的粉丝');
		}else{
			$this->assign('headtitle', getUserName($this->uid).'的粉丝');
		}
		$this->__followlist('followers',$this->uid);
	}

	// 微博详情
	public function detail() {
		if(intval($_GET['weibo_id'])){
			$data['id']   = intval($_GET['weibo_id']);
		}elseif(intval($_GET['id'])){
			$data['id']   = intval($_GET['id']);
		}
		$detail       = api('Statuses')->data($data)->show();

		$detail['is_favorite'] = api('Favorites')->data($data)->isFavorite() ? 1 : 0;

		$detail['content'] = wapFormatContent($detail['content'], false, urlencode($this->_self_url));

		$this->assign('weibo', $detail);

		$data['page'] = $this->_page;
		$comment      = api('Statuses')->data($data)->comments();
		//$comment	  = $this->__formatByComment($comment);
		$this->assign('comment', $comment);
		$this->display();
	}

	// 图片
	public function image() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
		}
		$weibo = api('Statuses')->data(array('id'=>$weibo_id))->show();

		$image = intval($weibo['transpond_id']) == 0 ? $weibo['type_data'] :  $weibo['transpond_data']['type_data'];
		if (empty($image)) {
			$this->redirect(U('w3g/Index/index'), 3, '无图片信息');
		}

		$this->assign('weibo_id',$weibo_id);
		$this->assign('image', $image);
		$this->display();
	}

	private function __followlist($type,$uid) {
		// 粉丝OR关注列表
		$data['user_id']	=	$uid;
		$followlist = api('Statuses')->data($data)->$type();

		$this->assign('userlist', $followlist);

		$this->assign('type', $type);
		$this->display('followlist');
	}

	public function doFollow() {
        $user_id = intval($_GET['user_id']);
		if ( $user_id <= 0 ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
		}
		$data['user_id'] = $user_id;
		$method = $_GET['type'] == 'follow' ? 'create' : 'destroy';
		switch ($_GET['from']) {
			case 'searchuser':
				$target = U('w3g/Index/doSearchUser',array('key'=>$_REQUEST['key'],'page'=>$_REQUEST['page'],'user'=>'1'));
				break;
			case 'weibo':
				$target = U('w3g/Index/weibo', array('uid'=>$user_id));
				break;
			default:
				$target = U('w3g/Index/'.$_GET['from']);
		}
		if ( api('Friendships')->data($data)->$method() ) {
			//$this->redirect($target, 1, '操作成功');
			$this->redirect($target);
		}else {
			$this->redirect($target, 3, '操作失败');
		}
	}

	public function post() {
		// 自动携带搜索的关键字
		$this->assign('keyword', isset($_REQUEST['key']) ? '#'.$_REQUEST['key'].'# ' : '');

		 $this->assign('headtitle', '发表微博');
		$this->display();
	}

	public function doPost() {
		if ( empty($_POST['content']) && $_FILES['pic'] ) {
			$_POST['content'] = '图片分享';
		}
		if ( empty($_POST['content']) && !$_FILES['pic'] ) {
			$this->redirect(U('w3g/Index/post'), 2, '内容不能为空');
			exit;
		}
		if (isset($_POST['nosplit'])) {
			$this->assign('content', $_POST['content']);
			$this->index();
		}
		$data = array();

		// 字数统计
		$length = mb_strlen($_POST['content'], 'UTF8');
        $parts  = ceil($length/140);
		if (!isset($_POST['split']) && $length > 140) {
			// 自动发一条图片微博
			if(!empty($_FILES['pic']['name'])){
				$data['pic']      = $_FILES['pic'];
				$data['content']  = '图片分享';
				$data['from']     = $this->_type_wap;
				$res = api('Statuses')->data($data)->upload();
			}

			// 提示是否自动拆分
			$this->assign('content', $_POST['content']);
			$this->assign('length', $length);
			$this->assign('parts', $parts);
			$this->display('split');
		}else {
			$api_method = 'update';
			if ($_FILES['pic']) {
				$data['pic']		= $_FILES['pic'];
				$api_method 		= 'upload';
			}
			// 自动拆分成多条
			for ($i = 1; $i <= $parts; $i++) {
				$sub_content      = mb_substr($_POST['content'], 0, 140, 'UTF8');
				$data['content']  = $sub_content;
				$data['from']     = $this->_type_wap;
                $_POST['content'] = mb_substr($_POST['content'], 140, -1, 'UTF8');
				$res = api('Statuses')->data($data)->$api_method();
				if (!$res) {
					$this->redirect(U('w3g/Index/index'), 3, '发布失败，请稍后重试');
					return ;
				}
			}
			$this->redirect(U('w3g/Index/index'));
			//$this->redirect(U('w3g/Index/index'), 1, '发布成功');
		}
	}

	public function comment() {
		$weibo_id 	= intval($_GET['weibo_id']);
		$comment_id	= intval($_GET['comment_id']);
		$uid		= intval($_GET['uid']);
		if ( $weibo_id <= 0 || $uid <= 0 ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
			return ;
		}
		$this->assign('weibo_id', $weibo_id);
		$this->assign('comment_id', $comment_id);
		$this->assign('uname', getUserName($uid));
		$this->display();
	}

	public function doComment() {
		if ( ($weibo_id = intval($_POST['weibo_id'])) <= 0 ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
		}
		if ( empty($_POST['content']) ) {
			$this->redirect(U('w3g/Index/detail',array('weibo_id'=>$weibo_id)), 3, '内容不能为空');
			return ;
		}
		// 仅取前140字
		$_POST['content'] = mb_substr($_POST['content'], 0, 140, 'UTF8');

		$data['weibo_id']			= $weibo_id;
		$data['comment_content'] 	= $_POST['content'];
		$data['from']			 	= $this->_type_wap;
		$data['reply_comment_id']	= intval($_POST['comment_id']);
		$data['transpond']			= intval($_POST['transpond']);
		$res = api('Statuses')->data($data)->comment();
		if ($res) {
			//$this->redirect(U('w3g/Index/detail', array('weibo_id'=>$weibo_id)), 1, '评论成功');
			$this->redirect(U('w3g/Index/detail', array('weibo_id'=>$weibo_id)));
		}else {
			$this->redirect(U('w3g/Index/detail', array('weibo_id'=>$weibo_id)), 3, '评论失败, 请稍后重试');
		}
	}

	public function forward() {
		$weibo_id = intval($_GET['weibo_id']);
		if ( $weibo_id <= 0 ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
			return ;
		}
		$data['id']	= $weibo_id;
		$weibo = api('Statuses')->data($data)->show();
		if (!$weibo) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
			return ;
		}

		$this->assign('weibo', $weibo);
		$this->assign('headtitle', '转发微博');
		$this->display();
	}

	public function doForward() {
		$weibo_id = intval($_POST['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/detail',array('weibo_id'=>$weibo_id)), 3, '参数错误');
			return ;
		}
		if (empty($_POST['content'])) {
			$this->redirect(U('w3g/Index/detail',array('weibo_id'=>$weibo_id)), 3, '内容不能为空');
			return ;
		}

		$data['id']	= $weibo_id;
		$weibo = api('Statuses')->data($data)->show();
		unset($data);
		if ( empty($weibo) ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
			return ;
		}

		// 整合被转发的内容
		if ( $weibo['transpond_id'] != 0 ) {
			$_POST['content'] .= "//@{$weibo['uname']}:{$weibo['content']}";
		}

		// 仅取前140字
		$_POST['content'] = mb_substr($_POST['content'], 0, 140, 'UTF8');

		$data['content']		= $_POST['content'];
		$data['from']			= $this->_type_wap;
		$data['transpond_id']	= $weibo['transpond_id'] ? $weibo['transpond_id'] : $weibo_id;
		if (intval($_POST['isComment']) == 1) {
			$weibo = api('Statuses')->data(array('id'=>$weibo_id))->show();
			$data['reply_data']	= $weibo['weibo_id'];
			if ( !empty($weibo['transpond_data']) ) {
				$data['reply_data']	.= ',' . $weibo['transpond_data']['weibo_id'];
			}
		}
		$res = api('Statuses')->data($data)->repost();
		if ($res) {
			$this->redirect(U('w3g/Index/detail', array('weibo_id'=>$weibo_id)), 1, '转发成功');
		}else {
			$this->redirect(U('w3g/Index/detail', array('weibo_id'=>$weibo_id)), 3, '转发失败, 请稍后重试');
		}
	}

	//搜用户
	public function searchuser(){
		$this->display();
	}

	public function doSearchUser() {

		if ( empty($_REQUEST['key']) ) {
			$this->redirect(U('w3g/Index/search'), 3, '请输入关键字');
			return ;
		}

		$method  = 'searchuser';
		$display = 'searchuser';

		$data['key'] 	= $_REQUEST['key'];
		$data['page']	= $this->_page;
		$res = api('Statuses')->data($data)->$method();

		$userlist = array();
		foreach ($res as $k => $v) {
			$userlist[$k]['user'] = $v;
		}
		$this->assign('userlist', $userlist);
		$this->assign('type','searchuser');
		$this->assign('keyword', t($_REQUEST['key']));
		$this->display($display);
	}

	//搜微博
	public function searchweibo(){
		$this->display();
	}

	public function doSearchWeibo() {
		if ( empty($_REQUEST['key']) ) {
			$this->redirect(U('w3g/Index/search'), 3, '请输入关键字');
			return ;
		}

		$method  = 'search';
		$display = 'searchweibo';

		$data['key'] 	= $_REQUEST['key'];
		$data['page']	= $this->_page;
		$res = api('Statuses')->data($data)->$method();

		$res = $this->__formatByFavorite($res);
		$res = $this->__formatByContent($res);
		$this->assign('weibolist', $res);
		$this->assign('type','searchweibo');
		$this->assign('keyword', t($_REQUEST['key']));
		$this->display($display);
	}

	public function doDelete() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/index', 3, '参数错误'));
			return ;
		}
		if ( !in_array($_GET['from'], array('index','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$target = U('w3g/Index/'.$_GET['from'], array('key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Statuses')->data($data)->destroy();
		if ($res) {
			//$this->redirect($target, 1, '删除成功');
			$this->redirect($target);
		}else {
			$this->redirect($target, 3, '删除失败，请稍后重试');
		}
	}

	public function doFavorite() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('w3g/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Favorites')->data($data)->create();
		if ($res) {
			//$this->redirect($target, 1, '收藏成功');
			$this->redirect($target);
		}else {
			$this->redirect($target, 3, '收藏失败，请稍后重试');
		}
	}

	public function doUnFavorite() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			$this->redirect(U('w3g/Index/index', 3, '参数错误'));
			return ;
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('w3g/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Favorites')->data($data)->destroy();
		if ($res) {
			//$this->redirect($target, 1, '取消成功');
			$this->redirect($target);
		}else {
			$this->redirect($target, 3, '取消失败，请稍后重试');
		}
	}

	public function urlalert() {
		if( !isset($_GET['url']) || !isset($_GET['from_url']) ) {
			$this->redirect(U('w3g/Index/index'), 3, '参数错误');
			return ;
		}
		$this->assign('url', $_GET['url']);
		$this->assign('from_url', $_GET['from_url']);
		$this->display();
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
		exit;
	}

	function countnew(){
		$map="weibo_id>{$_POST['nowMaxID']} AND isdel=0";
    	$map.=" AND ( uid IN (SELECT fid FROM ".C('DB_PREFIX')."weibo_follow WHERE uid=$this->uid) OR uid=$this->uid )";
    	$countnew = M('Weibo')->where($map)->count();
		echo $countnew?$countnew:'0';
	}
}