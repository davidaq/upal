<?php
    /**
     * CommentAction
     * 评论Action
     * @uses BaseAction
     * @package
     * @version $id$
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class CommentAction extends Action{
        /**
         * getComment
         * 获取评论数据
         * @access public
         * @return void
         */
        public function getComment(){
        	$map['appid'] = intval($_REQUEST['appid']);
        	$map['type']  = t($_REQUEST['type']);
         	$list = model('GlobalComment')->where($map)->order('id DESC')->findpage(10);
         	$appUid = $this->getInterFaceUid($map['type'], $map['appid']);
        	foreach ($list['data'] as $key=>$value){
        		$list['data'][$key]['uavatar'] = getUserSpace($value['uid'],'null','_blank','{uavatar}');
        		$list['data'][$key]['uspace'] = getUserSpace($value['uid'],'null','_blank','{uname}');
        		$list['data'][$key]['ctime'] = friendlyDate($value['cTime']);
        		$list['data'][$key]['comment'] = formatComment($value['comment']);
        		$list['data'][$key]['uname']   = getUserName($value['uid']);
        		$list['data'][$key]['del_state']   = ($GLOBALS['ts']['isSystemAdmin'])?2:(($appUid==$this->mid || $value['uid']==$this->mid)?1:0);
        		$list['data'][$key]['userGroupIcon']   = getUserGroupIcon($value['uid']);
        	}
        	exit(json_encode($list));
        }

        // 快速回复
        public function quickReply() {
        	$_POST['to_id'] = intval($_POST['to_id']);
        	$this->assign('to_uname', getUserName(M('comment')->where('`id`='.$_POST['to_id'])->getField('uid')));
        	$this->assign('to_id', $_POST['to_id']);
        	$this->assign('callback', t($_POST['callback']));
        	$this->display();
        }

        public function doQuickReply() {
        	$_POST['to_id'] 	= intval($_POST['to_id']);
        	$_POST['comment']	= t(getShort($_POST['comment_content'], $GLOBALS['ts']['site']['length']));
        	if ( $_POST['to_id'] < 0 || empty($_POST['comment']) ) {
        		echo json_encode(array('data'=>array('reply_comment_id'=>0)));
        		return ;
        	}
        	$former_comment = M('comment')->where("`id`='{$_POST['to_id']}'")->find();
        	if ( empty($former_comment) ) {
        		echo json_encode(array('data'=>array('reply_comment_id'=>0)));
        		return ;
        	}

        	// 插入新数据
        	$data['type']	 = $former_comment['type']; // 应用名
        	$data['appid']	 = $former_comment['appid'];
        	$data['appuid']	 = $former_comment['author_uid'];
        	$data['uid']	 = $this->mid;
        	$data['comment'] = $_POST['comment'];
        	$data['cTime']	 = time();
        	$data['toId']	 = $former_comment['id'];
        	$data['status']	 = 0; // 0: 未读 1:已读
        	$data['quietly'] = $former_comment['quietly'];
        	$data['to_uid']	 = $former_comment['uid'];
        	$data['data']	 = $former_comment['data'];
			$data['comment_ip']=get_client_ip();
        	$res = M('comment')->add($data);

        	if($res) {
        		// 应用回调: 增加应用的评论计数
        		$callback_data = unserialize($former_comment['data']);
        		$this->__doAddCallBack( $former_comment['appid'], $callback_data['table'], $callback_data['id_field'], $callback_data['comment_count_field'] );
        		//积分处理
				$setCredit = X('Credit');
				if($data['toId'] > 0 && $this->mid != $data['to_uid']){
					$setCredit->setUserCredit($this->mid,'reply_comment')
							  ->setUserCredit($data['to_uid'],'replied_comment');
				}else if($this->mid != $data['to_uid']){
					$setCredit->setUserCredit($this->mid,'add_comment')
					          ->setUserCredit($data['to_uid'],'is_commented');
				}
        		// 同时发一条微博
        		if ( intval($_POST['with_new_weibo']) ) {
        			$from_data = array('app_type'=>'local_app', 'app_name'=>$data['type'], 'title'=>$callback_data['title'], 'url'=>$callback_data['url']);
        			$from_data = serialize($from_data);
					D('Weibo','weibo')->publish($this->mid,
												array(
													'content' => html_entity_decode(
																	$_POST['comment'] . ($_POST['to_id'] > 0?(' //@'.getUserName($former_comment['uid']) . ' :' . $former_comment['comment']):''),
																	ENT_QUOTES
																 ),
												), 0, 0, '', '', $from_data);
        		}

        		echo json_encode(array('data' => array('reply_comment_id' => $_POST['to_id'])));
        	}else {
        		echo json_encode(array('data' => array('reply_comment_id' => 0)));
        	}
        }

        public function doAddComment() {
        	$_POST['with_new_weibo']		= intval($_POST['with_new_weibo']);
        	$_POST['type']					= t($_POST['type']);
        	$_POST['appid']					= intval($_POST['appid']);
        	$_POST['comment']				= $_POST['comment'];
        	$_POST['to_id']					= intval($_POST['to_id']);
        	$_POST['author_uid']			= intval($_POST['author_uid']);
        	$_POST['title']					= t(html_entity_decode($_POST['title'],ENT_QUOTES));
        	$_POST['url']					= urldecode($_POST['url']);
        	$_POST['table']					= t($_POST['table']);
        	$_POST['id_field']				= t($_POST['id_field']);
        	$_POST['comment_count_field']	= t($_POST['comment_count_field']);
			//$_POST['comment_ip'] = get_client_ip();
	        $app_alias	= getAppAlias($_POST['type']);

        	// 被回复内容
        	$former_comment = array();
        	if ( $_POST['to_id'] > 0 )
        		$former_comment = M('comment')->where("`id`='{$_POST['to_id']}'")->find();

        	// 插入新数据
        	$map['type']	= $_POST['type']; // 应用名
        	$map['appid']	= $_POST['appid'];
        	$map['appuid']	= $_POST['author_uid'];
        	$map['uid']		= $this->mid;
        	$map['comment']	= t(getShort($_POST['comment'], $GLOBALS['ts']['site']['length']));
        	$map['cTime']	= time();
        	$map['toId']	= $_POST['to_id'];
        	$map['status']	= 0; // 0: 未读 1:已读
        	$map['quietly']	= 0;
        	$map['to_uid']	= $former_comment['uid'] ? $former_comment['uid'] : $_POST['author_uid'];
			$map['comment_ip'] = get_client_ip();
        	$map['data']	= serialize(array(
        									'title' 				=> $_POST['title'],
        									'url'					=> $_POST['url'],
        									'table'					=> $_POST['table'],
        									'id_field'				=> $_POST['id_field'],
        									'comment_count_field'	=> $_POST['comment_count_field'],
        								));
        	$res = M('comment')->add($map);

        	// 避免命名冲突
        	unset($map['data']);

        	if ($res) {
        		// 应用回调: 增加应用的评论计数
        		$this->__doAddCallBack( $_POST['appid'], $_POST['table'], $_POST['id_field'], $_POST['comment_count_field'] );
        		//积分处理
				$setCredit = X('Credit');
				if($map['toId'] > 0 && $this->mid != $map['to_uid']){
					$setCredit->setUserCredit($this->mid,'reply_comment')
							  ->setUserCredit($map['to_uid'],'replied_comment');
				}else if($this->mid != $map['to_uid']){
					$setCredit->setUserCredit($this->mid,'add_comment')
					          ->setUserCredit($map['to_uid'],'is_commented');
				}
        		// 发表微博
        		if ($_POST['with_new_weibo']) {
        			$from_data = array('app_type'=>'local_app', 'app_name'=>$_POST['type'], 'title'=>$_POST['title'], 'url'=>$_POST['url']);
        			$from_data = serialize($from_data);
					D('Weibo','weibo')->publish($this->mid,
												array(
													'content' => html_entity_decode(
																	 $_POST['comment'] . ($_POST['to_id'] > 0?(' //@' . getUserName($former_comment['uid']) . ' :' . $former_comment['comment']):''),
																	 ENT_QUOTES
																 ),
												), 0, 0, '', '', $from_data);
        		}
/*
	        	// 给被回复人发送通知
				if ($former_comment['uid']) {
					$data = array(
						'app_alias'	=> $app_alias,
						'url'		=> $_POST['url'],
						'title'		=> $_POST['title'],
						'content'	=> $_POST['comment'],
						'my_content'=> $former_comment['comment'],
					);
					service('Notify')->send($former_comment['uid'], 'home_replyComment', $data, $this->mid);
					unset($data);
				}
				// 给作者发送通知 ( 当被回复人和作者为同一人时, 只发一个通知. 优先被回复. )
				if ($_POST['author_uid'] > 0 && $_POST['author_uid'] != $former_comment['uid']) {
					$data = array(
						'app_alias'	=> $app_alias,
						'url'		=> $_POST['url'],
						'title'		=> $_POST['title'],
						'content'	=> $_POST['comment'],
					);
					service('Notify')->send($_POST['author_uid'], 'home_addComment', $data, $this->mid);
					unset($data);
				}
*/

				// 组装结果集
				$result = $map;
				$result['data']['uavatar']  		= getUserSpace($this->mid,'null','_blank','{uavatar}');
				$result['data']['uspace']   		= getUserSpace($this->mid,'null','_blank','{uname}');
				//$result['data']['comment']  		= $_POST['comment'];
				$result['data']['ctime']    		= L('just_now');
				$result['data']['uname']    		= getUserName($this->mid);
				$result['data']['comment']			= formatComment(t($_POST['comment']));
				$result['data']['id']				= $res;
				$result['data']['userGroupIcon']	= getUserGroupIcon($this->mid);
				$result['data']['del_state']   		= 1;
                echo json_encode( $result );
            }else{
                echo -1;
            }
        }

        public function doDelete() {
        	$_POST['id'] = explode(',', t($_POST['id']));
        	if ( empty($_POST['id']) )
        		return ;
        	if(model('GlobalComment')->deleteComment($_POST['id'])){
        		//积分处理
				$setCredit = X('Credit');
				$setCredit->setUserCredit($this->mid,'delete_comment');
				echo 1;
			}else{
				echo 0;
			}
        }

        // 评论成功后, 回调处理, 增加评论计数
        private function __doAddCallBack($appid, $table,$id_field = 'id', $comment_count_field = 'commentCount') {
        	return $table ? M($table)->setInc($comment_count_field, "`$id_field`='$appid'") : false;
        }

        private function getInterFaceUid($type,$intId){
        	 $info = M('blog')->where("type='{$type}' AND id={$intId}")->field('uid')->find();
        	 return $info['uid'];
        }
}
