<?php
class IndexAction extends BaseAction
{
	// 个人首页
	public function index($uid = 0)
	{
        $data['user_id'] = $uid <= 0 ? $this->mid : $uid;
        $data['page']    = $this->_page;

		// 用户资料
		$profile = api('User')->data($data)->show();
		$this->assign('profile', $profile);

		// 微博列表
		$weibolist = api('Statuses')->data($data)->friends_timeline();
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);

		$this->display('index');
	}

	// 微博广场
	public function publicsquare()
	{
		$data['page'] = $this->_page;
		$weibolist = api('Statuses')->data($data)->public_timeline();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
		$this->assign('weibolist', $weibolist);
		$this->display();
	}

	// XX的微博
	public function weibo()
	{
		$data['user_id']  = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$data['page']     = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // 微博列表
        $weibolist = api('Statuses')->data($data)->user_timeline();
		$weibolist = $this->__formatByFavorite($weibolist);
		$weibolist = $this->__formatByContent($weibolist);
        $this->assign('weibolist', $weibolist);

        $this->assign('hideUsername', '1');
        $this->display();
	}

	// @我
	public function atMe()
	{
		$data['page'] = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // @XX的微博列表
        $weibolist = api('Statuses')->data($data)->mentions();
		$weibolist = $this->__formatByContent($weibolist);

		model ( 'UserCount' )->setZero ( $this->mid, 'atme' );

		$this->assign('weibolist', $weibolist);
        $this->display('weibo');
	}

	// 评论我的
	public function replyMe() {
		$data['page']     = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // 评论的微博列表
        $commentlist = api('Statuses')->data($data)->comments_receive_me();
		$commentlist = $this->__formatByContent($commentlist);
        $this->assign('commentlist', $commentlist);

		model ( 'UserCount' )->setZero ( $this->mid, 'comment' );

        $this->assign('headtitle', '评论我的');
        $this->display('commentlist');
	}

	// 我的收藏
	public function favorite()
	{
		$data['page']	= $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // 收藏列表
        $weibolist = api('Favorites')->data($data)->index();
		foreach ($weibolist as $k => $v) {
        	$weibolist[$k]['favorited'] = 1;
        }
        $this->assign('weibolist', $weibolist);

        $this->display('weibo');
	}

	private function __formatByFavorite($weibolist)
	{
		$ids = implode(',', getSubByKey($weibolist, 'weibo_id'));
        $favorite = D('Favorite','weibo')->isFavorited($ids, $this->mid);
        foreach ($weibolist as $k => $v) {
        	if ( in_array($v['weibo_id'], $favorite) ) {
        		$weibolist[$k]['favorited'] = 1;
        	}else {
        		$weibolist[$k]['favorited'] = 0;
        	}
        }
        return $weibolist;
	}

	private function __formatByContent($weibolist)
	{
		$self_url = urlencode($this->_self_url);
		foreach ($weibolist as $k => $v) {
			$weibolist[$k]['content'] = wapFormatContent($v['content'], true, $self_url);
			if ( isset($v['transpond_data']['content']) ) {
				$weibolist[$k]['transpond_data']['content'] = wapFormatContent($v['transpond_data']['content'], true, $self_url);
			}
		}
		return $weibolist;
	}

	private function __formatByComment($comment)
	{
		$self_url = urlencode($this->_self_url);
		foreach ($comment as $k => $v) {
			$comment[$k]['content'] = wapFormatComment($v['content'], true, $self_url);
		}
		return $comment;
	}

	// 关注列表
	public function following() {
		$this->__followlist('following');
	}

	// 粉丝列表
	public function followers() {
		$this->__followlist('followers');
	}

	// 话题
	public function topic() {
		$topic = D('Topic','weibo')->getHot();
		$this->assign('topic', $topic);
		$this->display();
	}

	// 微博详情
	public function detail() {
		if(intval($_GET['weibo_id'])){
			$data['id']   = intval($_GET['weibo_id']);
		}elseif(intval($_GET['id'])){
			$data['id']   = intval($_GET['id']);
		}
		$detail       = api('Statuses')->data($data)->show();
		$detail['favorited'] = api('Favorites')->data($data)->isFavorite() ? 1 : 0;
		$detail['content'] = wapFormatContent($detail['content'], true, urlencode($this->_self_url));
		$this->assign('weibo', $detail);

		$data['page'] = $this->_page;
		$comment      = api('Statuses')->data($data)->comments();
		$comment	  = $this->__formatByComment($comment);
		$this->assign('comment', $comment);
		$this->display();
	}

	// 图片
	public function image() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$weibo = api('Statuses')->data(array('id'=>$weibo_id))->show();
		$image = intval($weibo['transpond_id']) == 0 ? $weibo['type_data'] :  $weibo['transpond_data']['type_data'];
		if (empty($image)) {
			redirect(U('wap/Index/index'), 3, '无图片信息');
		}

		$this->assign('weibo_id',$weibo_id);
		$this->assign('image', $image);
		$this->display();
	}

	private function __followlist($type) {
		$data['user_id'] = $_GET['uid'] <= 0 ? $this->mid : $_GET['uid'];
		$data['page']    = $this->_page;

		// 用户资料
        $profile = api('User')->data($data)->show();
        $this->assign('profile', $profile);

        // 粉丝OR关注列表
		$followlist = api('Statuses')->data($data)->$type();
		$this->assign('userlist', $followlist);

		$this->assign('type', $type);
		$this->display('followlist');
	}

	public function doFollow() {
        $user_id = intval($_GET['user_id']);
		if ( !in_array($_GET['from'], array('following', 'followers', 'search', 'weibo')) ||
			 !in_array($_GET['type'], array('follow', 'unfollow'))     ||
			 $user_id <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$data['user_id'] = $user_id;
		$method = $_GET['type'] == 'follow' ? 'create' : 'destroy';
		switch ($_GET['from']) {
			case 'search':
				$target = U('wap/Index/doSearch',array('key'=>$_REQUEST['key'],'page'=>$_REQUEST['page'],'user'=>'1'));
				break;
			case 'weibo':
				$target = U('wap/Index/weibo', array('uid'=>$user_id));
				break;
			default:
				$target = U('wap/Index/'.$_GET['from']);
		}
		if ( api('Friendships')->data($data)->$method() ) {
			redirect($target, 1, '操作成功');
		}else {
			redirect($target, 3, '操作失败');
		}
	}

	public function post() {
		// 自动携带搜索的关键字
		$this->assign('keyword', isset($_REQUEST['key']) ? '#'.$_REQUEST['key'].'# ' : '');
		$this->display();
	}

	public function doPost() {
		$_POST['content'] = preg_replace('/^\s+|\s+$/i', '', $_POST['content']);
		if ( empty($_POST['content']) && !empty($_FILES['pic']['name']) ) {
			$_POST['content'] = '图片分享';
		}else if ( empty($_POST['content']) && empty($_FILES['pic']['name']) ) {
			redirect(U('wap/Index/index'), 3, '内容不能为空');
		}
		if (isset($_POST['nosplit'])) {
			$this->assign('content', $_POST['content']);
			$this->index();
		}
		$data = array();

		// 字数统计
		$length = mb_strlen($_POST['content'], 'UTF8');
        $parts  = ceil($length/$GLOBALS['ts']['site']['length']);
		if (!isset($_POST['split']) && $length > $GLOBALS['ts']['site']['length']) {
			if(!empty($_FILES['pic']['name'])) { // 自动发一条图片微博
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
			if ($_FILES['pic']['size']>0) {
				$data['pic']		= $_FILES['pic'];
				$api_method 		= 'upload';
			}
			// 自动拆分成多条
			for ($i = 1; $i <= $parts; $i++) {
				$sub_content      = mb_substr($_POST['content'], 0, $GLOBALS['ts']['site']['length'], 'UTF8');
				$data['content']  = $sub_content;
				$data['from']     = $this->_type_wap;
                $_POST['content'] = mb_substr($_POST['content'], $GLOBALS['ts']['site']['length'], -1, 'UTF8');
				$res = api('Statuses')->data($data)->$api_method();
				if (!$res) {
					redirect(U('wap/Index/index'), 3, '发布失败，请稍后重试');
				}
			}
			redirect(U('wap/Index/index'), 1, '发布成功');
		}
	}

	public function comment() {
		$weibo_id 	= intval($_GET['weibo_id']);
		$comment_id	= intval($_GET['comment_id']);
		$uid		= intval($_GET['uid']);
		if ( $weibo_id <= 0 || $comment_id <= 0 || $uid <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$this->assign('weibo_id', $weibo_id);
		$this->assign('comment_id', $comment_id);
		$this->assign('uname', getUserName($uid));
		$this->display();
	}

	public function doComment() {
		if ( ($weibo_id = intval($_POST['weibo_id'])) <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		if ( empty($_POST['content']) ) {
			redirect(U('wap/Index/detail',array('weibo_id'=>$weibo_id)), 3, '内容不能为空');
		}
		// 仅取前140字
		$_POST['content'] = mb_substr($_POST['content'], 0, $GLOBALS['ts']['site']['length'], 'UTF8');

		$data['weibo_id']			= $weibo_id;
		$data['comment_content'] 	= $_POST['content'];
		$data['from']			 	= $this->_type_wap;
		$data['reply_comment_id']	= intval($_POST['comment_id']);
		$data['transpond']			= intval($_POST['transpond']);
		$res = api('Statuses')->data($data)->comment();
		if ($res) {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 1, '评论成功');
		}else {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 3, '评论失败, 请稍后重试');
		}
	}

	public function forward() {
		$weibo_id = intval($_GET['weibo_id']);
		if ( $weibo_id <= 0 ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$data['id']	= $weibo_id;
		$weibo = api('Statuses')->data($data)->show();
		if (!$weibo) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}

		$this->assign('weibo', $weibo);
		$this->display();
	}

	public function doForward() {
		$weibo_id = intval($_POST['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/detail',array('weibo_id'=>$weibo_id)), 3, '参数错误');
		}
		if (empty($_POST['content'])) {
			redirect(U('wap/Index/detail',array('weibo_id'=>$weibo_id)), 3, '内容不能为空');
		}

		$data['id']	= $weibo_id;
		$weibo = api('Statuses')->data($data)->show();
		unset($data);
		if ( empty($weibo) ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}

		// 整合被转发的内容
		if ( $weibo['transpond_id'] != 0 ) {
			$_POST['content'] .= "//@{$weibo['uname']}:{$weibo['content']}";
		}

		// 仅取前140字
		$_POST['content'] = mb_substr($_POST['content'], 0, $GLOBALS['ts']['site']['length'], 'UTF8');

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
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 1, '转发成功');
		}else {
			redirect(U('wap/Index/detail', array('weibo_id'=>$weibo_id)), 3, '转发失败, 请稍后重试');
		}
	}

	public function doSearch()
	{
		if ( empty($_REQUEST['key']) )
			redirect(U('wap/Index/search'), 3, '请输入关键字');

		if ( isset($_REQUEST['user']) ) {
			$method  = 'searchuser';
			$display = 'searchuser';
		}else {
			$method  = 'search';
			$display = 'searchweibo';
		}

		$data['key'] 	= $_REQUEST['key'];
		$data['page']	= $this->_page;
		$res = api('Statuses')->data($data)->$method();

		if ($display == 'searchuser') {
			$userlist = array();
			foreach ($res as $k => $v) {
				$userlist[$k]['user'] = $v;
			}
			$this->assign('userlist', $userlist);
			$this->assign('type', 'search');
		}else {
			$res = $this->__formatByFavorite($res);
			$res = $this->__formatByContent($res);
			$this->assign('weibolist', $res);
		}
		$this->assign('keyword', $_REQUEST['key']);
		$this->display($display);
	}

	public function doDelete() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$target = U('wap/Index/'.$_GET['from'], array('key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Statuses')->data($data)->destroy();
		if ($res) {
			redirect($target, 1, '删除成功');
		}else {
			redirect($target, 3, '删除失败，请稍后重试');
		}
	}

	public function doFavorite() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('wap/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Favorites')->data($data)->create();
		if ($res) {
			redirect($target, 1, '收藏成功');
		}else {
			redirect($target, 3, '收藏失败，请稍后重试');
		}
	}

	public function doUnFavorite() {
		$weibo_id = intval($_GET['weibo_id']);
		if ($weibo_id <= 0) {
			redirect(U('wap/Index/index', 3, '参数错误'));
		}
		if ( !in_array($_GET['from'], array('index','detail','weibo','doSearch','atMe','favorite')) ) {
			$_GET['from'] = 'index';
		}
		$_GET['key'] = urlencode($_GET['key']);
		$target = U('wap/Index/'.$_GET['from'], array('weibo_id'=>$weibo_id, 'key'=>$_GET['key'],'page'=>$_GET['page']));

		$data['id'] = $weibo_id;
		$res = api('Favorites')->data($data)->destroy();
		if ($res) {
			redirect($target, 1, '取消成功');
		}else {
			redirect($target, 3, '取消失败，请稍后重试');
		}
	}

	public function urlalert() {
		if( !isset($_GET['url']) || !isset($_GET['from_url']) ) {
			redirect(U('wap/Index/index'), 3, '参数错误');
		}
		$this->assign('url', $_GET['url']);
		$this->assign('from_url', $_GET['from_url']);
		$this->display();
	}
}