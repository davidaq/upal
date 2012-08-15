<?php
class CommentModel extends Model {
    var $tableName = 'weibo_comment';
    //发布评论
    function addcomment($data){
			$data['comment_ip'] = get_client_ip();
         if( $id= $this->add( $data ) ){
             D('Weibo','weibo')->_removeWeiboCache($data['weibo_id']);
              D('Weibo', 'weibo')->setInc('comment', 'weibo_id='.$data['weibo_id'] );
             return $id;
         }else{
             return false;
         }
    }

    //发布评论同时发布一条微博
    public function doaddcomment($uid, $post, $api=false)
    {
        $data['uid']     = $uid;
        $data['reply_comment_id']   = intval($post['reply_comment_id']);
        $data['weibo_id']   = intval($post['weibo_id']);
        $data['content'] = t(getShort($post['content'],$GLOBALS['ts']['site']['length']));
        $data['content'] = preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/u',getContentUrl, $data['content']);
        $data['ctime']   = time();
		//$data['comment_ip']		  = get_client_ip();
        $miniInfo = D('Weibo', 'weibo')->where('weibo_id='.$data['weibo_id'].' AND isdel=0')->find();
        if( $data['reply_comment_id'] ){
        	$replyInfo = $this->where('comment_id='.$data['reply_comment_id'].' AND isdel=0')->find();
        	$data['reply_uid'] = $replyInfo['uid'];
        }else{
        	$data['reply_uid'] = $miniInfo['uid'];
        	$notify['reply_type'] = 'weibo';
        }
        if ( $comment_id = $this->addcomment( $data ) ){

			//微博回复积分操作
			if($data['uid'] != $data['reply_uid']){
				X('Credit')->setUserCredit($data['uid'],'reply_weibo')
						   ->setUserCredit($data['reply_uid'],'replied_weibo');
			}

            $data['comment'] = $miniInfo['comment'] + 1;
            $return['data'] = $data;
            $return['html'] = '<dl class="comment_list" id="comment_list_c_'.$comment_id.'">
							   <dt><a href="'.U('home/space/index',array('uid'=>$this->mid)).'" class="pic">
            		<img src="'.getUserFace($uid,'s').'" /></a></dt>
					<dd>
                      <div class="msgCnt" style="padding-bottom:0; font-size:12px">' . getUserSpace($uid, 'fn', '', '{uname}') . getUserGroupIcon($uid) . '   '.formatComment( $data['content'] ,true ).' <em>刚刚</em></div>
                      <p class="info"><span class="right"><a href="javascript:void(0)" onclick="ui.confirm(this,\'确认要删除此评论?\')" callback="delComment('.$comment_id.')">删除</a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="reply(\''.getUserName($uid).'\','.$data['weibo_id'].')">回复</a></span></p></dd>
                    </dl>';
            if( $post['transpond'] != 0 ){
            	if($miniInfo['transpond_id']!=0){
            		$transpondData['content']     	   = $data['content'].($data['reply_comment_id']?(" //@".getUserName($replyInfo['uid'])." :".$replyInfo['content']):'')." //@".getUserName($miniInfo['uid']).":".$miniInfo['content'];
            		$transpondData['transpond_id']     = $miniInfo['transpond_id'];
            		$transpondInfo = M('weibo')->where('weibo_id='.$miniInfo['transpond_id'].' AND isdel=0')->find();
            		$transpondData['transpond_uid']     = $transpondInfo['uid'];
            	}else{
            		$transpondData['content']          = $data['content'].($data['reply_comment_id']?(" //@".getUserName($replyInfo['uid'])." :".$replyInfo['content']):'');
            		$transpondData['transpond_id']     = $miniInfo['weibo_id'];
            		$transpondData['transpond_uid']     = $miniInfo['uid'];
                }

            	$id = D('Weibo', 'weibo')->doSaveWeibo($uid,$transpondData,$post['from']);
			    if ($id) {  //当转发的微博uid 与 回复人的uid不一致时发布@到我
			    	if($transpondData['transpond_uid'] != $data['reply_uid']){
			    		D('Weibo', 'weibo')->notifyToAtme($uid, $id, $transpondData['content'], $transpondData['transpond_uid']);
			    	}else{
			    		D('Weibo', 'weibo')->notifyToAtme($uid, $id, $transpondData['content'], $transpondData['transpond_uid'],false);
			    	}
			    }
            }

            //添加统计
            if($api){
            	if($data['reply_uid'] != $miniInfo['uid']){
            		Model('UserCount')->addCount($miniInfo['uid'],'comment');
            	}
            }else{
            	Model('UserCount')->addCount($data['reply_uid'],'comment');
            	if($data['reply_uid'] != $miniInfo['uid']){
            		Model('UserCount')->addCount($miniInfo['uid'],'comment');
            	}
            }
        	Addons::hook('weibo_comment_publish', array($comment_id, $data));

            if($api){
            	return true;
            }else{
            	return json_encode($return);
            }
        }else{
        	return '0';
        }
    }

    //获取评论
    function getComment( $id, $limit = 10, $order = 'comment_id DESC' ){
    	return $this->where("weibo_id={$id} AND isdel=0")->order($order)->findPage($limit);
    }

    //发出的评论
    function getCommentList($type='receive',$person='all',$uid) {
    	if ($type == 'send') { // 发出的评论
	    	if ($person == 'follow') {
	    		$map = "reply_uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	}else if ($person=='other'){
	    		$map = "reply_uid NOT IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	}else{
	    		$map = '1=1';
	    	}
	    	$list = $this->where($map." AND uid=".$uid." AND reply_uid<>$uid".' AND isdel=0')->order('comment_id DESC')->findPage(10);
    	} else { // 收到的评论
    		/*
    		if ($person == 'follow') {
	    		$map = "uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	} else if ($person=='other') {
	    		$map = "uid NOT IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	} else {
	    		$map = 'uid <> '.$uid;
	    	}
	    	*/
    		//$list = $this->where($map." AND reply_uid=".$uid.' AND isdel=0')->order('comment_id DESC')->findPage();



    		$list = $this->field('c.*')
    					 ->table("{$this->tablePrefix}weibo_comment AS c LEFT JOIN {$this->tablePrefix}weibo AS w ON c.weibo_id=w.weibo_id")
    					 ->where("c.isdel=0 AND w.isdel=0 AND (c.reply_uid={$uid} OR w.uid={$uid} ) and c.uid != {$uid}")
    					 ->order('c.comment_id DESC')
    					 ->findPage(10);

    	}


    	// 缓存被评论的微博, 被回复的评论, 评论的发表人, 被回复的用户
    	$ids = getSubBeKeyArray($list['data'], 'comment_id,reply_comment_id,weibo_id,uid,reply_uid');
    	D('Weibo','weibo')->setWeiboObjectCache($ids['weibo_id']);
    	D('User', 'home')->setUserObjectCache(array_merge($ids['uid'], $ids['reply_uid']));
    	$this->setCommentObjectCache(array_merge($ids['comment_id'], $ids['reply_comment_id']));

        foreach ($list['data'] as $key => $value) {
    		$list['data'][$key]['mini'] = D('Weibo', 'weibo')->getOneLocation($value['weibo_id'], '', false);
    		if( !$value['reply_comment_id'] ){
    			$list['data'][$key]['reply_uid']  = $list['data'][$key]['mini']['uid'];
    			$list['data'][$key]['ismini'] = true;
    		}else {
    			$list['data'][$key]['comment'] = $this->getCommentDetail($value['reply_comment_id']);
    		}
    	}

    	return $list;
    }

    //删除评论
    function deleteComments($id,$uid){
    	$pMiniBlog = D('Weibo', 'weibo');
       	$info = $this->where('comment_id='.$id)->find();
       	$webInfo = $pMiniBlog->where('weibo_id='.$info['weibo_id'])->field('uid,comment')->find();
    	if( $info['uid']==$uid || $webInfo['uid']==$uid || $GLOBALS['ts']['isSystemAdmin']){
    		if($info['isdel'] == 0 && $this->setField('isdel',1,'comment_id='.$id.' AND isdel=0')){
    			$pMiniBlog->setDec('comment', 'weibo_id='.$info['weibo_id'] );
    			if($info['uid'] != $info['reply_uid']){//删除自己给自己的评论，不扣积分
					X('Credit')->setUserCredit($info['uid'],'delete_weibo_comment')
								->setUserCredit($info['reply_uid'],'delete_weibo_comment');
    			}
    		}elseif($info['isdel'] == 1){
    			$this->where('comment_id='.$id.' AND isdel=1')->delete();
    		}
            $pMiniBlog->_removeWeiboCache($info['weibo_id']);
    		$r['boolen'] = 1;
    		$r['message'] = L('del_success');
    		$r['count']   = intval( $webInfo['comment'] -1 );
    	}else{
    		$r['boolen'] = 0;
    		$r['message'] = L('del_failed');
    	}
    	return $r;
    }

    //批量删除评论
    function deleteMuleComments($id,$uid) {
    	$pMiniBlog = D('Weibo', 'weibo');
    	$id = is_array($id) ? $id : explode(',', $id);
    	foreach ($id as $k=>$v){
    		$info = $this->where('comment_id='.$v)->find();
    		$webInfo = $pMiniBlog->where('weibo_id='.$info['weibo_id'])->field('uid')->find();
	    	if( $info['uid']==$uid || $webInfo['uid']==$uid ){
	    		if($info['isdel'] == 0 && $this->setField('isdel',1,'comment_id='.$v.' AND isdel=0')){
	    			$pMiniBlog->setDec('comment', 'weibo_id='.$info['weibo_id'] );
	    			if($info['uid'] != $info['reply_uid']){//删除自己给自己的评论，不扣积分
						X('Credit')->setUserCredit($info['uid'],'delete_weibo_comment')
									->setUserCredit($info['reply_uid'],'delete_weibo_comment');
	    			}
	    		}elseif($info['isdel'] == 1){
	    			$this->where('comment_id='.$v.' AND isdel=1')->delete();
	    		}
                $pMiniBlog->_removeWeiboCache($info['weibo_id']);
	    	}
    	}
    	return true;
    }

    public function getCommentDetail($comment_id) {
    	$cache_id = 'weibo_comment_' . $comment_id;
    	if (($res = object_cache_get($cache_id)) === false) {
    		$res = $this->where("`comment_id` = '{$comment_id}' AND `isdel` = 0")->find();
    		object_cache_set($cache_id, $res);
    	}
    	return $res;
    }

    /**
     * 缓存微博评论
     *
     * 缓存的key的格式为: weibo_comment_微博评论ID.
     *
     * @param array $comment_list 微博评论ID列表, 或者微博评论详情列表. 如果为微博评论ID列表时, 本方法会首先获取微博评论详情列表, 然后缓存.
     */
    public function setCommentObjectCache(array $comment_list) {
    	if (!is_array($comment_list[0]) && !is_numeric($comment_list[0]))
    		return false;

    	if (is_numeric($comment_list[0])) { // 给定的是weibo_comment_id的列表. 查询weibo_comment详情
	    	$map['comment_id'] = array('in', $comment_list);
	    	$map['isdel']      = 0;
	    	$comment_list      = $this->where($map)->findAll();
    	}

    	foreach ($comment_list as $v)
	   		object_cache_set("weibo_comment_{$v['comment_id']}", $v);

	   	return $comment_list;
    }
}
