<?php
import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction{
	function index(){
		if($_POST){
			// 推荐的热门话题过滤空话题
			if ($_POST['hotTopic']) {
				foreach ($_POST['hotTopic'] as $k => $v) {
					if (empty($v)) {
						unset($_POST['hotTopic'][$k]);
						unset($_POST['hotTopicNote'][$k]);
					}
				}
			} else {
				$_POST['hotTopic'] = array();
				$_POST['hotTopicNote'] = array();
			}
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '3';
			$data[] = '应用 - 应用配置 - 微博 - 微博配置';
			$data[] = model('Xdata')->lget('weibo');
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$data[] = $_POST;
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			$res = model('Xdata')->lput('weibo', $_POST);
			$this->assign('jumpUrl', U('weibo/Admin/index'));
			if ($res)
				$this->success('保存成功');
			else
				$this->error('保存失败');
		}else{
			$res = model('Xdata')->lget('weibo');
			$res['recommendTopic'] = D('Topic','weibo')->getHot('recommend');
			$res['autoTopic'] =  D('Topic','weibo')->getHot('auto');
			$this->assign($res);
			$this->display();
		}
	}
	public function topics()
	{
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if ( !empty($_POST) ) {
            $_SESSION['admin_topics_search'] = serialize($_POST);
        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
            $_POST = unserialize($_SESSION['admin_search']);
        }else {
            unset($_SESSION['admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
		$data['list'] = D('Topics', 'weibo')->topicsList($_POST);
		$this->assign($data);
        $this->assign($_POST);
		$this->display();
	}
	public function editTopicsTab()
	{
		$topics = D('Topics', 'weibo')->getTopics($_GET['k'], $_GET['id']);
		if (!$topics) {
			$name = html_entity_decode(urldecode($_GET['k']), ENT_QUOTES);
			$topics['topic_id'] = D('Topic', 'weibo')->getTopicId($name);
			$topics['name'] = t($name);
		}
		$topics['description'] = urldecode($_GET['description']);
		$this->assign($topics);
		$this->display();
	}
	public function utf8_strlen($string = null) {
		// 将字符串分解为单元
		preg_match_all("/./us", $string, $match);
		// 返回单元个数
		return count($match[0]);
	}
 
	public function doEditTopics()
	{
		$name = h($_POST['name']);
		$note = h($_POST['note']);
		$content = h($_POST['content']);
		if($this->utf8_strlen($name) > 100){
			$this->error("话题字数不能超过100字，请重输");
		}
		if($this->utf8_strlen($note) > 100){
			$this->error("注释字数不能超过100字，请重输");
		}
		if($this->utf8_strlen($content) > 100){
			$this->error("内容字数不能超过100字，请重输");
		}
		$data['topic_id'] = D('Topic', 'weibo')->getTopicId($_POST['name']);
		if ($data['topic_id'] <= 0) {
			$this->error('话题错误');
		}
		$options['allow_exts']	=	'jpg,jpeg,png,gif';
		if (!$_POST['nopic']) {
			$info	=	X('Xattach')->upload('topics', $options);
			if ($info['status']) {
				$data['pic'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
			}
		} else {
			$data['pic'] = '';
		}
		$res = preg_match('/^(?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*)/',h($_POST['link']));
		if(!$res && trim($_POST['link']) != ""){
			$this->error('外链格式错误');
		}
		$data['domain'] = $_POST['domain'] ? h(t($_POST['domain'])) : md5($_POST['name']);
		//$data['domain'] = h(t($_POST['domain']));
		$data['note'] = h(t($_POST['note']));
		$data['content'] = h(t($_POST['content']));
		$data['link'] = h(t($_POST['link']));
		// $data['recommend'] = (string)intval($_POST['recommend']);
		$data['ctime']   = time();
		if (intval($_POST['topics_id'])) {
			$data['topics_id'] = intval($_POST['topics_id']);
			$res = D('Topics', 'weibo')->save($data);
			if (false !== $res) {
				1 == $data['recommend'] && $this->_resetHotTopic();
				S('_weibo_topic_model_hot_topic_recommend3600', NULL);
				$this->assign('jumpUrl', U('weibo/Admin/topics'));
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		} else {
			if (!D('Topics', 'weibo')->getField('topics_id', "topic_id='{$data['topic_id']}' AND isdel=0")) {
				$res = D('Topics', 'weibo')->add($data);
				if (false !== $res) {
					1 == $data['recommend'] && $this->_resetHotTopic();
					S('_weibo_topic_model_hot_topic_recommend3600', NULL);
					$this->success('添加成功');
				} else {
					$this->error('添加失败');
				}
			} else {
				$this->error("专题“{$data['name']}”已存在");
			}
		}
	}
	// 删除专题
	public function deleteTopics()
	{
		$res = D('Topics', 'weibo')->deleteTopics($_POST['topics_id']);
		$this->_resetHotTopic();
		S('_weibo_hot_topic_recommend_3600', NULL);
		echo false !== $res ? 1 : 0;
	}
	// 专题推荐到话题
	public function doRecommendTopics()
	{
		$res = D('Topics', 'weibo')->recommendTopics($_POST['topics_id'], 'recommend' == $_POST['type'] ? true : false);
		$this->_resetHotTopic();
		S('_weibo_hot_topic_recommend_3600', NULL);
		echo false !== $res ? 1 : 0;		
	}
	function weibolist($map){
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if ( !empty($_POST) ) {
            $_SESSION['admin_search'] = serialize($_POST);
        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
            $_POST = unserialize($_SESSION['admin_search']);
        }else {
            unset($_SESSION['admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
        $_POST['wid']     && $map['weibo_id'] = array('in',t($_POST['wid']));
        $_POST['uid']     && $map['uid'] = array('in',t($_POST['uid']));
        $_POST['content'] && $map['content'] = array('like','%'.t($_POST['content']).'%');
        $order = ( $_POST['orderkey'] && $_POST['ordertype'] )?$_POST['orderkey'].' '.$_POST['ordertype']:'weibo_id DESC';
		if($_GET['recycle'] == 1){
			$map['isdel'] = 1;
			$data['recycle'] = 1;
		}else{
			$map['isdel'] = 0;
			$data['recycle'] = 0;
		}
		$data['list'] = D('Weibo')->order($order)->where($map)->findpage(20);
		if( is_array($map) && sizeof($map)=='1' )unset($map);
		$this->assign($_GET);
		$this->assign($data);
        $this->assign($_POST);
		$this->display('list');
	}
	public function topiclist(){
			$data = D('weibo_topic')->findpage(20);
			$this->assign('list',$data);
		    $this->display();		
	}
	public function searchlist(){
			$name = t($_POST['name']);		
			$lock = intval($_POST['lock']);
			$map['name'] = array('LIKE', '%'.$name.'%');
			$map['lock'] = $lock;
			$data = M('weibo_topic')->where($map)->findpage(20);
			// $name = '%'.$name.'%';
			// $data = D('weibo_topic')->where("`name` like `'$name'` and `status` = $status")->findpage(20);
			// $data = D('weibo_topic')->where("`name` like `'$name'` and `status` = $status")->findAll();
	        $this->assign('list',$data);
			$this->display('topiclist');
	}
	public function setRecommend(){
        $topic_id = intval($_GET['id']);
        $data = D('weibo_topic')->where("topic_id = ".$topic_id)->find();
        if($data['status'] == 1){
        	$map['status'] = 0;
        }else{
        	$map['status'] = 1;
        }       
        $res =  D('weibo_topic')->where("topic_id = ".$topic_id)->save($map);
        if($res){
           echo 1;
         }else{
           echo 0;
          
      }
    }
	public function setShield(){
        $topic_id = intval($_GET['id']);
        $data = D('weibo_topic')->where("topic_id = ".$topic_id)->find();
        if($data['lock'] == 1){
        	$map['lock'] = 0;
        }else{
        	$map['lock'] = 1;
        }       
        $res =  D('weibo_topic')->where("topic_id = ".$topic_id)->save($map);
        if($res){
           echo 1;
         }else{
           echo 0;
          
      }
    }
    public function setManyShield()
	{	
		$topic_id = array();
		$topic_id = $_REQUEST['id'];
		$map['lock'] =1;
	    $res =  D('weibo_topic')->where("topic_id in($topic_id) ")->save($map);
	    if($res){
	      	echo '1';
	    }else{
	      	echo "0";
	    }
	}
	public function fileList()
	{
		if ($_POST) {
	        // 安全过滤
	        $_POST = array_map('t',$_POST);
			$map = $this->_getSearchMap(array('in' => array('id', 'userId', 'extension')));
			$this->assign('isSearch', '1');
		}
		$dao = model('Attach');
		$map['attach_type'] = 'weibo_file';
		$attaches  = $dao->getAttachByMap($map);
		$extensions = $dao->enumerateExtension();
		$this->assign($attaches);
		$this->assign('extensions', $extensions);
		$this->assign($_POST);
		$this->display();
	}
	//微博列表  删除操作
	public function operate(){
		$strType = $_POST['dotype'];
		if($strType=='del'){
			$weibo_ids = explode(',',$_POST['weibo_id']);
			foreach($weibo_ids as $weibo_id){
				$_LOG['uid'] = $this->mid;
				$_LOG['type'] = '2';
				$isDel = M( 'Weibo' )->where( 'weibo_id='.intval($weibo_id) )->find();
				if( $isDel['isdel'] ){
					$data[] = '应用 - 应用配置 - 微博 - 微博回收站';
				}else{
					$data[] = '应用 - 应用配置 - 微博 - 微博列表';
				}
				$data[] = M( 'Weibo' )->where( 'weibo_id='.intval($weibo_id) )->find();
				$_LOG['data'] = serialize($data);
				$_LOG['ctime'] = time();
				M('AdminLog')->add($_LOG);
				$res = D("Operate")->delete(intval($weibo_id),intval($data[1]['uid']));
				unset($data);
			}
			if($res){
				echo 1;
			}else{
				echo 0;
			}
		}
	}
	//微博列表  删除操作
	function restore(){
		$weibo_ids = explode(',',$_POST['weibo_id']);
		foreach($weibo_ids as $weibo_id){
			//$_LOG['uid'] = $this->mid;
			//$_LOG['type'] = '2';
			//$_LOG['data'] = serialize($data);
			//$_LOG['ctime'] = time();
			//M('AdminLog')->add($_LOG);
			$res = M("Weibo")->where( 'weibo_id='.intval($weibo_id))->setField('isdel',0);
			unset($data);
		}
		if($res){
			echo 1;
		}else{
			echo 0;
		}
	}
	//微博广场配置
	public function square()
	{
		if ($_POST) {
			if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '3';
			$data[] = '应用 - 应用配置 - 微博 - 广场配置';
			$data[] = model('Xdata')->lget('square')?model('Xdata')->lget('square'):'';
			$res = model('Xdata')->lput('square', $_POST);
			$data[] = model('Xdata')->lget('square');
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			$this->assign('jumpUrl', U('weibo/Admin/square'));
			if ($res)
				$this->success('保存成功');
			else
				$this->error('保存失败');
		} else {
			$res = model('Xdata')->lget('square');
			$this->assign($res);
			$this->display();
		}
	}
	//名人堂
	public function star(){
		$gid = is_numeric($_GET['gid'])?$_GET['gid']:'';
		$starDao = D('Star');
		$data['top_list'] = $starDao->getGroupList();
		if($gid){
			// 当前分组的信息
			$group = M('weibo_star_group')->find($gid);
			$starDao->getStarGroup($group);
			$this->assign('group',$group);
		    //判断当前分组级别
			$tid = $group['top_group_id'];
			if($tid == 0){
				$tid = $gid;
			}else{
				$this->assign('sid',$gid);
			}
			$this->assign('tid',$tid);
			$data['son_list'] = $starDao->getGroupList($tid);
			$this->assign('gid',$gid);
		}
			$data['list'] = $starDao->setGroup($gid)
										 ->getStars();
			foreach($data['list']['data'] as &$star){
				$starDao->getStarGroup($star);
			}
		$this->assign($data);
		$this->display();
	}
	public function addStarGroup(){
		$title = $_POST['title'];
		$tid   = $_POST['tid'];
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 创建一级分组';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		$res = D('Star')->addGroup($title,$tid);
		echo $res;
	}
	public function editStarGroup(){
		$title = $_POST['title'];
		$gid   = $_POST['gid'];
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 编辑分组名称';
		$map['star_group_id'] = $gid;
		$data[] = M( 'weibo_star_group' )->where( $map )->field('title')->find();
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$log_data['title'] = $_POST['title'];
		$data[] = $log_data;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		$res = D('Star')->editGroup($title,$gid);
		echo $res;
	}
	public function editStarGroupOrder(){
		$tid = intval($_REQUEST['tid'])>0?intval($_REQUEST['tid']):0;
		$group_model = M( 'weibo_star_group' );
		$now_order = $group_model->field('star_group_id,display_order')->where("top_group_id={$tid}")->findAll();
		$new_order = @array_flip($_POST['star_group']);
		$res = 1;
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 编辑分组排序';
		$data[] = M( 'weibo_star_group' )->field('title,display_order')->findall();
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		foreach($now_order as $v){
			if($new_order[$v['star_group_id']] == $v['display_order'])continue;
			$_res = $group_model->where('star_group_id='.$v['star_group_id'])->save(array('display_order'=>intval($new_order[$v['star_group_id']])));
			$res = ($res&&$_res)?$res:0;
		}
		$data[] = M( 'weibo_star_group' )->field('title,display_order')->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		echo $res;
	}
	public function delStarGroup(){
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 删除分组';
		$map['star_group_id'] = $_GET['gid'];
		$data[] = M( 'weibo_star_group' )->where( $map )->find();
		$res = D('Star')->delGroup($_GET['gid']);
		if($res == 1){
			$this->assign('jumpUrl',U('weibo/admin/star'));
			$_LOG['uid'] = $this->mid;
			$_LOG['type'] = '2';
			$_LOG['data'] = serialize($data);
			$_LOG['ctime'] = time();
			M('AdminLog')->add($_LOG);
			$this->success('操作成功！');
		}else{
			$this->error('操作失败！');
		}
	}
	public function addStar(){
		$uid = $_REQUEST['uid'];
		$gid = $_REQUEST['gid'];
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 添加名人';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		$res = D('Star')->addStar($uid,$gid);
		if(is_array($res)){
			echo json_encode($res);
		}else{
			echo $res;
		}
	}
	public function editStarBox(){
		$data['star_id']	= $_REQUEST['id'];
		$data['group_list'] = D('Star')->getAllGroupList();
		$this->assign($data);
		$this->display();
	}
	public function editStar(){
		$star_id = $_POST['star_id'];
		$gid	 = $_POST['gid'];
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '3';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 转移名人';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$map['star_id'] = array('in',explode(',', $star_id));
		$data[] = D( 'Star' )->where( $map )->findall();
		$arr_uid = getSubByKey($data[1],'uid');
		$res = D('Star')->editStar($star_id,$gid);
		$map_2['uid'] = array('in',$arr_uid);
		$map_2['star_group_id'] = array('in',$gid);
		$data[] = D( 'Star' )->where( $map_2 )->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		echo $res;
	}
	public function delStar(){
		$star_id = $_POST['id'];
		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '应用 - 应用配置 - 微博 - 名人堂 - 删除名人';
		if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
		$map['star_id'] = array('in',explode(',', $star_id));
		$data[] = D( 'Star' )->where( $map )->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
		$res = D('Star')->delStar($star_id);
		if($res){
			if(strpos($star_id,',')){
				echo 1;
			}else{
				echo 2;
			}
		}else{
			echo 0;
		}
	}
	/* ------ */
	// 设置“推荐话题重置”标记
	private function _resetHotTopic()
	{
		F('_reset_weibo_hot_topic_', 1);
	}
}