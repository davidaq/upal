<?php
class ContentAction extends AdministratorAction {

	private function __isValidRequest($field, $array = 'post') {
		$field = is_array($field) ? $field : explode(',', $field);
		$array = $array == 'post' ? $_POST : $_GET;
		foreach ($field as $v){
			$v = trim($v);
			if ( !isset($array[$v]) || $array[$v] == '' ) return false;
		}
		return true;
	}

	/** 内容管理 - 广告管理 **/

	public function ad() {
		$data = M('ad')->order('`display_order` ASC,`ad_id` ASC')->findAll();
		$this->assign('ad', $data);
		$this->assign('place_array', array('中部','头部','左下','右下','底部','右上'));
		$this->display();
	}

	public function addAd() {
		$this->assign('type', 'add');
		$this->display('addAd');
	}

	public function editAd() {
		$map['ad_id'] = intval($_GET['id']);
		$ad = M('ad')->where($map)->find();
		//$this->assign('ad',$ad);

		$ad_content = unserialize($ad['content']);
		if($ad_content){
			$ad['content'] = $ad_content;
			 $ad['type_id'] =3;
			
		} 
		if($ad['display_type'] == 1){
			$ad['type_id'] =1;
		}
		if($ad['display_type'] == 2){
			$ad['type_id'] =2;
		}
		if(empty($ad))
			$this->error('参数错误');
			$this->assign('banner',$ad['content']);
			$this->assign($ad);
			$this->assign('ad_id',$map['ad_id']);
			$this->assign('type', 'edit');
			$this->display('editAd');
	}

	public function doUpdateAd(){
		$map['ad_id'] = $_POST['ad_id'];
		$res = M('ad')->where($map)->find();
		if($res['display_type'] == 1){
			$_POST['content'] = $_POST['content'];
		}
		if($res['display_type'] == 2){
			$_POST['content'] = $_POST['content'];
		}
		if($res['display_type'] == 3){
			$file = X('Xattach')->upload($attach_type='banner');
			if($file['status'] == false){
				foreach ($_POST['bannerOld'] as $key => $value) {
				$content[] = array('img'=>$value,
								  'url'=>$_POST['bannerUrlOld'][$key]);
				}
				$countOld = count($content);
				if($countOld <= 1){
						$this->error('轮播广告至少需要两条');
					}
				$content = serialize($content);
				$_POST['content'] = $content;		
				// 格式化数据
				$_POST['title']			= h(t($_POST['title']));
				$_POST['content'] = $_POST['content'];
			}else{
				foreach ($_POST['bannerUrl'] as $v) {
					if(empty($v)){
						$this->assign('jumpUrl', U('admin/Content/addAd'));
						$this->error('URL不能为空！');
					}
				}
				foreach ($file['info'] as $k=>$v) {
					$url[$k]['img'] = SITE_URL.'/data/uploads/'.$v['savepath'].$v['savename'];
					$url[$k]['url'] = $_POST['bannerUrl'][$k];
				}
			
				foreach ($_POST['bannerOld'] as $key => $value) {
					$url[] = array('img'=>$value,
								  'url'=>$_POST['bannerUrlOld'][$key]);
				}
				$countNew = count($url);
				if($countNew <= 1){
					$this->error('轮播广告至少需要两条');
				}
				$url = serialize($url);
				$_POST['content'] = $url;
			}
			$_POST['place']			= intval($_POST['place']);
			$_POST['is_active']		= intval($_POST['is_active'])   == 0 ? '0' : '1';
			$_POST['is_closable']	= 0; // intval($_POST['is_closable']) == 0 ? '0' : '1';
			$_POST['mtime']			= time();
			if ( !isset($_POST['ad_id']) )
				$_POST['ctime']		= time();

			// 数据检查
			if(empty($_POST['title']))
				$this->error('标题不能为空');
			if($_POST['place'] < 0 || $_POST['place'] > 5)
				$this->error('参数错误');

			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = isset($_POST['ad_id']) ? '3' : '1';
			$data[] = '内容 - 广告管理 ';
			isset($_POST['ad_id']) && $data[] =  M('ad')->where( array( 'ad_id'=>intval($_POST['ad_id']) ) )->find();
			if ( isset($_POST['ad_id']) ) unset( $data['1']['ctime'] );
			unset( $data['1']['display_order'] );
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$data[] = $_POST;
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
		}
		// 提交数据
		$res = isset($_POST['ad_id']) ? M('ad')->save($_POST) : M('ad')->add($_POST);

		//编辑控制
		$aid = $_GET['id'];
		$editdata = M( 'ad' )->where( 'ad_id='.$aid )->find();
		$this->assign('editdata',$editdata);

		if($res) {
			if( !isset($_POST['ad_id']) ) {
				// 为排序方便, 新建完毕后, 将display_order设置为ad_id
				M('ad')->where("`ad_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('admin/Content/addAd'));
			}else {
				$this->assign('jumpUrl', U('admin/Content/ad'));
			}
			F('_action_ad',null);	//删除广告缓存
			$this->success('保存成功');
		}else {
			$this->error('保存失败');
		}
	}
//编辑广告
	public function updataAd(){
		$map['ad_id'] = intval($_GET['id']);
		$ad = M('ad')->where($map)->find();
		$this->assign('ad',$ad);

		$ad_content = unserialize($ad['content']);
		if($ad_content){
			$ad['content'] = $ad_content;
		}
		if(empty($ad))
			$this->error('参数错误');
			$this->assign('banner',$ad['content']);
			$this->assign($ad);
			$this->assign('type', 'edit');
			$this->display('updataAd');
	}
	public function doEditAd() {
		$count = count($_POST['banner']);
		$_POST['display_type'] == intval($_POST['display_type']);
		if( ($_POST['ad_id'] = intval($_POST['ad_id'])) <= 0 )
			unset($_POST['ad_id']);
		// 格式化数据
		$_POST['title']			= h(t($_POST['title']));
		// $bannerUrl = $_POST['bannerUrl'];
		// $bannerUrl = serialize($bannerUrl);

		$file = X('Xattach')->upload($attach_type='banner');
		if($file['status'] == false){
			$_POST['content'] = $_POST['content'];
			if(empty($_POST['content'])){
				$_POST['content']	=$_POST['hide'];
			}
		}else{
			foreach ($_POST['bannerUrl'] as $v) {
				if(empty($v)){
					$this->assign('jumpUrl', U('admin/Content/addAd'));
					$this->error('URL不能为空！');
				}
			}
			foreach ($file['info'] as $k=>$v) {
				$url[$k]['img'] = SITE_URL.'/data/uploads/'.$v['savepath'].$v['savename'];
				$url[$k]['url'] = $_POST['bannerUrl'][$k];
			}
		
			foreach ($_POST['bannerOld'] as $key => $value) {
				$url[] = array('img'=>$value,
							  'url'=>$_POST['bannerUrlOld'][$key]);
			}
			$count = count($url);
			if($count <= 1){
				$this->error('轮播广告至少需要两条');
			}
			$url = serialize($url);
			$_POST['content'] = $url;
		}
		$_POST['place']			= intval($_POST['place']);
		$_POST['is_active']		= intval($_POST['is_active'])   == 0 ? '0' : '1';
		$_POST['is_closable']	= 0; // intval($_POST['is_closable']) == 0 ? '0' : '1';
		$_POST['mtime']			= time();
		if ( !isset($_POST['ad_id']) )
			$_POST['ctime']		= time();

		// 数据检查
		if(empty($_POST['title']))
			$this->error('标题不能为空');
		if($_POST['place'] < 0 || $_POST['place'] > 5)
			$this->error('参数错误');

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = isset($_POST['ad_id']) ? '3' : '1';
		$data[] = '内容 - 广告管理 ';
		isset($_POST['ad_id']) && $data[] =  M('ad')->where( array( 'ad_id'=>intval($_POST['ad_id']) ) )->find();
		if ( isset($_POST['ad_id']) ) unset( $data['1']['ctime'] );
		unset( $data['1']['display_order'] );
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		// 提交数据
		$res = isset($_POST['ad_id']) ? M('ad')->save($_POST) : M('ad')->add($_POST);

		//编辑控制
		$aid = $_GET['id'];
		$editdata = M( 'ad' )->where( 'ad_id='.$aid )->find();
		$this->assign('editdata',$editdata);

		if($res) {
			if( !isset($_POST['ad_id']) ) {
				// 为排序方便, 新建完毕后, 将display_order设置为ad_id
				M('ad')->where("`ad_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('admin/Content/addAd'));
			}else {
				$this->assign('jumpUrl', U('admin/Content/ad'));
			}
			F('_action_ad',null);	//删除广告缓存
			$this->success('保存成功');
		}else {
			$this->error('保存失败');
		}
	}

	public function doDeleteAd() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}
		$map['ad_id'] = array('in', t($_POST['ids']));

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 广告管理 ';
		$data[] =  M('ad')->where( $map )->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		echo M('ad')->where($map)->delete() ? '1' : '0';
		F('_action_ad',null);	//删除广告缓存
	}

	public function deleteBanner(){
		echo "1";
	}
	public function doAdOrder() {
		$_POST['ad_id']  = intval($_POST['ad_id']);
		$_POST['baseid'] = intval($_POST['baseid']);
		if ( $_POST['ad_id'] <= 0 || $_POST['baseid'] <= 0 ) {
			echo 0;
			exit;
		}

		// 获取详情
		$map['ad_id'] = array('in', array($_POST['ad_id'], $_POST['baseid']));
		$res = M('ad')->where($map)->field('ad_id,display_order')->findAll();
		if ( count($res) < 2 ) {
			echo 0;
			exit;
		}

		//转为结果集为array('id'=>'order')的格式
    	foreach($res as $v) {
    		$order[$v['ad_id']] = intval($v['display_order']);
    	}
    	unset($res);

    	//交换order值
    	$res = 		   M('ad')->where('`ad_id`=' . $_POST['ad_id'])->setField(  'display_order', $order[$_POST['baseid']] );
    	$res = $res && M('ad')->where('`ad_id`=' . $_POST['baseid'])->setField( 'display_order', $order[$_POST['ad_id']]  );

    	F('_action_ad',null);	//删除广告缓存

    	if($res) echo 1;
    	else	 echo 0;
	}

	/** 内容管理 - 表情管理 **/

	public function expression() {
		$expression = model('Expression')->getExpressionByMap();
		$this->assign('data', $expression);
		$this->display();
	}

	public function addExpression() {
		$this->assign('type', 'add');
		$this->display('editExpression');
	}

	public function doAddExpression() {
		if (!$this->__isValidRequest('title,type,emotion,filename')) {
			$this->error('数据不完整');
		}

        $_POST = array_map('t',$_POST);

        $_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '内容 - 表情管理 ';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$data[] =  $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$res = model('Expression')->add($_POST);
		if ($res) $this->success('保存成功');
		else	  $this->error('保存失败');
	}

	public function editExpression() {
		$map['expression_id']  = intval($_GET['expression_id']);
		$expression = model('Expression')->getExpressionByMap($map);
		$this->assign('expression', $expression[0]);
		$this->assign('type', 'edit');
		$this->display();
	}

	public function doEditExpression() {
		if (!$this->__isValidRequest('expression_id,title,type,emotion,filename')) {
			$this->error('数据不完整');
		}

        $_POST = array_map('t',$_POST);

        $_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '内容 - 表情管理 ';
		$data[] = model('Expression')->getExpressionByMap( array('expression_id'=>intval($_REQUEST['expression_id'])) );
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$_POST['filepath'] = SITE_URL.'/public/themes/weibo/images/expression/'.$_POST['type'].'/'.$_POST['filename'];
		$data[] =  $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$res = model('Expression')->save($_POST);
		if ($res) {
			$this->assign('jumpUrl', U('admin/Content/expression'));
			$this->success('保存成功');
		}else{
			$this->error('保存失败');
		}
	}

	public function doDeleteExpression() {
		$map['expression_id'] = array('in', t($_POST['expression_id']));

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 表情管理 ';
		$data[] = model('Expression')->getExpressionByMap( array('expression_id'=>intval($_POST['expression_id'])) );
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$res	   = model('Expression')->where($map)->delete();
    	if($res) {echo 1; }
    	else 	 {echo 0; }
	}

	/** 内容 - 模板管理 */

	//模板管理
	public function template() {
		$list   = model('Template')->getTemplate();
		$this->assign($list);
		$action = isset($_GET['action']) ? $_GET['action'] : 'list';
		$this->assign('action', $action);
		$this->display();
	}

	public function addTemplate() {
		$this->assign('type', 'add');
		$this->display('editTemplate');
	}

	public function doAddTemplate() {
		if (! $this->__isValidRequest('name')) $this->error('资料不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

		$_POST['is_cache'] = intval($_POST['is_cache']);

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '内容 - 模板管理 ';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		unset( $_POST['tpl_id'] );
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$res = model('Template')->addTemplate($_POST);
		if ($res) {
			$this->success('保存成功');
		}else {
			$this->error('保存失败');
		}
	}

	public function editTemplate() {
		$tid = intval($_GET['tid']);
		$dao = model('Template');
		$template = M('template')->where("`tpl_id` = $tid")->find();
		if (!$template) $this->error('无此模板');

		$this->assign('template', $template);
		$this->assign('type', 'edit');
		$this->display();
	}

	public function doEditTemplate() {
		if (! $this->__isValidRequest('tpl_id, name')) $this->error('资料不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

		$_POST['tpl_id']   = intval($_POST['tpl_id']);
		$_POST['is_cache'] = intval($_POST['is_cache']);

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '内容 - 模板管理 ';
		$tid = intval($_REQUEST['tpl_id']);
		$data[] = M('template')->where("`tpl_id` = $tid")->find();
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$_POST['ctime'] = time();
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		$res = model('Template')->save($_POST);
		if ($res) {
			$this->assign('jumpUrl', U('admin/Content/template'));
			$this->success('保存成功');
		}else {
			$this->error('保存失败');
		}
	}

	public function doDeleteTemplate() {
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 模板管理 ';
		$tid = intval(t($_POST['ids']));
		$data[] = M('template')->where("`tpl_id` = $tid")->find();
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

    	echo  model('Template')->deleteTemplate( t($_POST['ids']) ) ? '1' : '0';
	}

	/** 内容 - 附件管理 */

	public function attach($map) {
		$dao = model('Attach');
		$attaches   = $dao->getAttachByMap($map);
		$extensions = $dao->enumerateExtension();
		$this->assign($attaches);
		$this->assign('extensions', $extensions);

		$this->assign($_POST);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display('attach');
	}

	public function doSearchAttach() {
        // 安全过滤
        $_POST = array_map('t',$_POST);

		$map = $this->_getSearchMap(array('in' => array('id', 'userId', 'extension')));
		$this->attach($map);
	}

	public function doDeleteAttach() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 附件管理 ';
		$map['id'] = array('in',t($_POST['ids']));
		$data[] = model('Attach')->getAttachByMap($map);
		$data[] = array('isFile'=>intval($_POST['withfile']));
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		$map['attach_id'] = $map['id'];
		unset($map['id']);
		$weibo = M('weibo_attach')->where($map)->findAll();
		unset($map['attach_id']);
		foreach ($weibo as $v) {
			$weibo_id[] = $v['weibo_id'];
		}
		$weibo_id = implode(',', $weibo_id);
		$map['weibo_id'] = array('in',$weibo_id);
		M('weibo')->where($map)->delete();
		echo model('Attach')->deleteAttach( t($_POST['ids']), intval($_POST['withfile']) ) ? '1' : '0';
	}

	/** 内容 - 评论管理 */
	public function comment() {
    	$_GET['from_app']	= ( $_GET['from_app']  == 'other' ) ? 'other' : 'weibo';
    	$limit = 20;

    	if ($_GET['from_app'] == 'weibo') {
	    	if($_GET['recycle'] == 1){
				$map['isdel'] = 1;
	    		$this->assign('recycle', $_GET['recycle']);
			}else{
				$map['isdel'] = 0;
			}
	    	$data = M('weibo_comment')->order('comment_id DESC')->where($map)->findPage($limit);
    	}else {
    		$data = M('comment')->order('id DESC')->findPage($limit);
    	}
	    $this->assign( $this->__formatComment($_GET['from_app'], $data) );
	    $this->assign('from_app', $_GET['from_app']);
    	$this->display();
	}

	private function __formatComment($from_app, $data) {
		foreach($data['data'] as $k => $v) {
			if ($from_app == 'weibo') {
				unset($data['data'][$k]);
				$data['data'][$k]	=  array(
					'comment_id'	=> $v['comment_id'],
					'type'			=> 'weibo',
					'content'		=> $v['content'],
					'uid'			=> $v['uid'],
					'to_uid'		=> $v['reply_uid'],
					'url'			=> U('home/Space/detail',array('id'=>$v['weibo_id'])),
					'ctime'			=> $v['ctime'],
					'comment_ip'	=>$v['comment_ip'],
				);
			}else if ($from_app == 'other') {
				unset($data['data'][$k]);
				$v['data'] = unserialize($v['data']);
				$data['data'][$k]	=  array(
					'comment_id'	=> $v['id'],
					'type'			=> $v['type'],
					'content'		=> $v['comment'],
					'uid'			=> $v['uid'],
					'to_uid'		=> $v['to_uid'],
					'url'			=> $v['data']['url'],
					'ctime'			=> $v['cTime'],
					'comment_ip'	=>$v['comment_ip'],
				);
			}
		}
		return $data;
	}

	public function doDeleteComment() {
		$_POST['from_app']	= $_POST['from_app'] == 'other' ? 'other' : 'weibo';
		$_POST['ids']		= explode(',', t($_POST['ids']));

        if ( empty($_POST['ids']) )
       		return ;

       	if ($_POST['from_app'] == 'weibo') {
       		$dao = D('Comment', 'weibo');
       		$comments = array();

       		$map['comment_id'] = array('in', $_POST['ids']);
       		$res = $dao->where($map)->field('comment_id,uid')->findAll();

       		$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '2';
			$data[] = '内容 - 评论管理  - 微博';
			$data[] = $dao->where($map)->findAll();
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);

       		// 转换成 array('uid'=>$comment) 的形式
       		foreach ($res as $v)
       			$comments[$v['uid']][] = $v['comment_id'];

       		// 循环批量删除
       		foreach ($comments as $uid => $ids)
       			$dao->deleteMuleComments($ids, $uid);

			unset($res);
       		echo 1;

       	}else if ($_POST['from_app'] == 'other') {

       		$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '2';
			$data[] = '内容 - 评论管理  - 其它应用';
			$map['id'] = array('in',$_POST['ids']);
			$data[] = model('GlobalComment')->where($map)->findall();
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);

       		echo model('GlobalComment')->deleteComment($_POST['ids']) ? '1' : '0';

       	}else {
       		echo 0;
       	}
	}

	/** 内容 - 短消息管理 */

	public function message($map) {
		$msg = model('Message')->getMessageByMap($map);
		$this->assign($msg);

		$this->assign($_POST);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display('message');
	}

	public function doSearchMessage() {
        // 安全过滤
        $_POST = array_map('t',$_POST);

		// 标题模糊查询
    	if ( isset($_POST['content']) && $_POST['content'] != '' ) {
    		$_POST['content']	= '%' . $_POST['content'] . '%';
    	}
    	$map = $this->_getSearchMap( array('in'=>array('message_id','from_uid'), 'like'=>array('content')) );
    	$this->message($map);
	}

	public function doDeleteMessage() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 短消息管理';
		$map['message_id'] = array('in',t($_POST['ids']));
		$data[] = model('Message')->getMessageByMap($map);
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		echo model('Message')->deleteSessionByAdmin(t($_POST['ids'])) ? '1' : '0';
	}

	/** 内容 - 通知管理 */

	public function notify($map) {
		$dao    = service('Notify');
		$notify = $dao->get($map,20,false);
		$types  = $dao->enumerateType();
		$this->assign($notify);
		$this->assign('types', $types);

		$this->assign($_POST);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display('notify');
	}

	public function doSearchNotify() {
        // 安全过滤
        $_POST = array_map('t',$_POST);

		$map = $this->_getSearchMap(array('in' => array('notify_id', 'from', 'receive', 'type')));
		$this->notify($map);
	}

	public function doDeleteNotify() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 通知管理';
		$map['notify_id'] = array('in',$_POST['ids']);
		$data[] = M('Notify')->where($map)->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		echo service('Notify')->deleteNotify( t($_POST['ids']) ) ? '1' : '0';
	}

	/** 内容 - 动态管理 */

	public function feed($map) {
		$dao   = service('Feed');
		$feed  = $dao->getFeedByMap($map);
		$types = $dao->enumerateType();
		$this->assign($feed);
		$this->assign('types', $types);

		$this->assign($_POST);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display('feed');
	}

	public function doSearchFeed() {
        // 安全过滤
        $_POST = array_map('t',$_POST);

		$map = $this->_getSearchMap(array('in'=>array('feed_id','uid','type')));
		$this->feed($map);
	}

	public function doDeleteFeed() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '内容 - 动态管理';
		$map['feed_id'] = array('in',$_POST['ids']);
		$data[] = M ( 'feed' )->where($map)->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		echo service('Feed')->deleteFeed( t($_POST['ids']) ) ? '1' : '0';
	}
	/**
	 * 举报管理
	 */
	public function denounce($map){
		$_GET['id'] && $map['id'] = array( 'in',explode( ',', $_GET['id'] ) );
		$_GET['uid'] && $map['uid'] = array( 'in',explode( ',', $_GET['uid'] ) );
		$_GET['fuid'] && $map['fuid'] = array( 'in',explode( ',', $_GET['fuid'] ) );
		$_GET['from'] && $map['from'] = $_GET['from'];
		$map['state'] = $_GET['state']?$_GET['state']:'0';
		$data = model( 'Denounce' )->getFromList($map);
		$data['state'] = $map['state'];
		$this->assign($data);
		if( is_array($map) && sizeof($map)=='1' )unset($map);
		$this->assign($_GET);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display('denounce');
	}

	public function doDeleteDenounce() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '内容 - 举报管理 - 进入回收站';
		$map['id'] = array('in',t($_POST['ids']));
		$data[] = model('Denounce')->where($map)->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

		echo model('Denounce')->deleteDenounce( t($_POST['ids']) ) ? '1' : '0';
	}

	public function doReviewDenounce(){
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}

		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '内容 - 举报管理 - 通过审核';
		$map['id'] = array('in',t($_POST['ids']));
		$data[] = model('Denounce')->where($map)->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		echo model('Denounce')->reviewDenounce( t($_POST['ids']) ) ? '1' : '0';
	}
	/**
	 * 后台日志管理
	 */
	public function adminLog($map){
		$data = M( 'AdminLog' )->where($map)->order('ID DESC')->findpage();
		$this->assign($data);
		$this->assign($_POST);
		$this->assign('isSearch', empty($map)?'0':'1');
		$this->display(adminLog);
	}

	public function showAdminLog(){
		$map['id'] = $_GET['id'];
		$data = M('AdminLog')->where($map)->find();

		$this->assign($data);
		$this->display();
	}

	public function doSearchAdminLog(){
		if(!$_POST['type'])
			unset($_POST['type']);
		// 安全过滤
        $_POST = array_map('t',$_POST);

		$map = $this->_getSearchMap(array('in'=>array('id','uid','type')));
		$this->assign('type',$_POST['type']);
		$this->adminLog($map);
	}

	public function doDeleteAdminLog() {
		if( empty($_POST['ids']) ) {
			echo 0;
			exit ;
		}
		$where['id'] = array('in',t($_POST['ids']));
		echo M( 'AdminLog' )->where( $where )->delete() ? '1' : '0';
	}

	public function lookDetail(){
		$data = M( 'AdminLog' )->where( 'id='.$_POST['ids'] )->find();
		$this->assign($data);
		$this->display();
	}
}