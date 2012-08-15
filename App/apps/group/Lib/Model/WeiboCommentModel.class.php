<?php
class WeiboCommentModel extends Model {
    var $tableName = 'group_weibo_comment';
    //发布评论
    function addcomment($data){
         if( $id= $this->add($data) ){
              D('GroupWeibo','group')->setInc('comment', 'weibo_id=' . $data['weibo_id'] . ' AND gid=' . $data['gid']);
             return $id;
         }else{
             return false;
         }
         
    }
    
    //发布评论同时发布一条微博
    function doaddcomment($uid,$post,$api=false){
        $data['gid']     = intval($post['gid']);
        $data['uid']     = intval($uid);
        $data['reply_comment_id']   = intval($post['reply_comment_id']);
        $data['weibo_id']   = intval($post['weibo_id']);
        $data['content'] = t(getShort($post['content'], $GLOBALS['ts']['site']['length']));
        $data['ctime']   = time();
        $miniInfo = D('GroupWeibo', 'group')->where('weibo_id=' . $data['weibo_id'] . ' AND gid=' . $data['gid'] . ' AND isdel=0')->find();
        if( $data['reply_comment_id'] ){
        	$replyInfo = $this->where('comment_id=' . $data['reply_comment_id'] . ' AND gid=' . $data['gid'] . ' AND isdel=0')->find();
        	$data['reply_uid'] = $replyInfo['uid'];
        }else{
        	$data['reply_uid'] = $miniInfo['uid'];
        	$notify['reply_type'] = 'weibo';
        }
        if ($comment_id = $this->addcomment($data)){

			//微博回复积分操作
			if($data['uid'] != $data['reply_uid']){
				X('Credit')->setUserCredit($data['uid'],'reply_weibo')
						   ->setUserCredit($data['reply_uid'],'replied_weibo');
			}

            $data['comment'] = $miniInfo['comment'] + 1;
            $return['data'] = $data;
            $return['html'] = '<div class="position_list" id="comment_list_c_'.$comment_id.'"> <a href="'.U('home/space/index',array('uid'=>$this->mid)).'" class="pic">
            		<img class="pic30" src="'.getUserFace($uid,'s').'" /></a>
                      <p class="list_c"><a href="#">'.getUserName($uid).'</a> ' . getUserGroupIcon($uid) . ' : '.formatComment( $data['content'] ,true ).' (刚刚)</p>
                      <div class="alR clear"><a href="javascript:void(0)" onclick="ui.confirm(this,\'确认要删除此评论?\')" callback="delComment('.$comment_id.')">删除</a>&nbsp;&nbsp;<a href="javascript:void(0)" onclick="reply(\''.getUserName($uid).'\','.$data['weibo_id'].')">回复</a></div>
                    </div>';
            if( $post['transpond'] != 0 ){
            	if($miniInfo['transpond_id']!=0){
            		$transpondData['gid']              = $data['gid'];
            		$transpondData['content']     	   = $data['content']." //@".getUserName($miniInfo['uid']).":".$miniInfo['content'];
            		$transpondData['transpond_id']     = $miniInfo['transpond_id'];
            		$transpondInfo = D('GroupWeibo', 'group')->where('weibo_id='.$miniInfo['transpond_id'])->find();
            		$transpondData['transpond_uid']    = $transpondInfo['uid'];
            	}else{
                    $transpondData['gid']              = $data['gid'];
            		$transpondData['content']          = $data['content'];
            		$transpondData['transpond_id']     = $miniInfo['weibo_id'];
            		$transpondData['transpond_uid']    = $miniInfo['uid'];
            	}
            	$id = D('GroupWeibo', 'group')->doSaveWeibo($uid,$transpondData,$post['from']);
			    if ($id ) {  //当转发的微博uid 与 回复人的uid不一致时发布@到我
			    	if ($transpondData['transpond_uid'] != $data['reply_uid']) {
			    		D('GroupWeibo', 'group')->notifyToAtme($uid, $data['gid'], $id, $transpondData['content'], $transpondData['transpond_uid']);
			    	} else {
			    		D('GroupWeibo', 'group')->notifyToAtme($uid, $data['gid'], $id, $transpondData['content'], $transpondData['transpond_uid'],false);
			    	}
			    	
			    }
            }

            // 添加统计
            D('GroupUserCount','group')->addCount($data['reply_uid'],'comment');
            if($data['reply_uid'] != $miniInfo['uid']){
            	D('GroupUserCount','group')->addCount($miniInfo['uid'],'comment');
            }

            if($api){
            	return 1;
            }else{
            	return json_encode($return);
            } 
        }else{
        	return '0';
        }
    }

    // 获取评论
    public function getComment( $id, $gid, $limit = 10, $order = 'comment_id DESC' ){
    	return $this->where('weibo_id=' . intval($id) . ' AND gid=' . $gid .' AND isdel=0')->order($order)->findpage($limit);
    }

    // 收到或发出的评论列表
    public function getCommentList($type='receive', $person='all', $uid, $gid = null){
    	if ($type == 'send') { // 发出的评论
	    	if ($person == 'follow') {
	    		$map = "reply_uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	}else if ($person=='other'){
	    		$map = "reply_uid NOT IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	}else{
	    		$map = '1=1';
	    	}
	    	$map .= $gid ? " AND gid={$gid}" : '';
	    	$list = $this->where($map." AND uid=".$uid." AND reply_uid<>$uid".' AND isdel=0')->order('comment_id DESC')->findpage(10);
    	} else { // 收到的评论
    		if ($person == 'follow') {
	    		$map = "uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	} else if ($person=='other'){
	    		$map = "uid NOT IN (SELECT fid FROM {$this->tablePrefix}weibo_follow where uid={$uid})";
	    	} else {
	    		$map = '1=1';
	    	}
	    	$map .= $gid ? " AND gid={$gid}" : '';
    		$list = $this->where($map." AND reply_uid=".$uid.' AND isdel=0')->order('comment_id DESC')->findpage(10);
    		$list = $this->field('c.*')
    					 ->table("{$this->tablePrefix}group_weibo_comment AS c LEFT JOIN {$this->tablePrefix}group_weibo AS w ON c.weibo_id=w.weibo_id")
    					 ->where("c.isdel=0 AND w.isdel=0 AND (c.reply_uid={$uid} OR w.uid={$uid})")
    					 ->order('comment_id DESC')
    					 ->findPage(10);
    	}

    	// 缓存被评论的微博, 被回复的评论, 评论的发表人, 被回复的用户
    	$ids = getSubBeKeyArray($list['data'], 'comment_id,reply_comment_id,weibo_id,uid,reply_uid');
    	D('GroupWeibo','weibo')->setWeiboObjectCache($ids['weibo_id']);
    	D('User', 'home')->setUserObjectCache(array_merge($ids['uid'], $ids['reply_uid']));
    	$this->setCommentObjectCache(array_merge($ids['comment_id'], $ids['reply_comment_id']));

        foreach ($list['data'] as $key => $value){
    		$list['data'][$key]['mini'] = D('GroupWeibo', 'group')->getOneLocation($value['weibo_id'], $value['gid']);
    		if( !$value['reply_comment_id'] ){
    			$list['data'][$key]['reply_uid']  = $list['data'][$key]['mini']['uid'];
    			$list['data'][$key]['ismini'] = true;
    		}else{
    			$list['data'][$key]['comment'] = $this->getCommentDetail($value['reply_comment_id']);
    		}
    	}    	
    	return $list;
    }

    //删除评论
    function deleteComments($id,$uid){
    	$pMiniBlog = D('GroupWeibo', 'group');
       	$info = $this->where('comment_id='.$id)->find();
       	$webInfo = $pMiniBlog->where('weibo_id='.$info['weibo_id'])->field('uid,comment')->find();
    	if( $info['uid']==$uid || $webInfo['uid']==$uid ){
    		if($info['isdel'] == 0 && $this->setField('isdel',1,'comment_id='.$id.' AND isdel=0')){    		
    			$pMiniBlog->setDec('comment', 'weibo_id='.$info['weibo_id'] );
    			if($info['uid'] != $info['reply_uid']){//删除自己给自己的评论，不扣积分
					X('Credit')->setUserCredit($info['uid'],'delete_weibo_comment')
								->setUserCredit($info['reply_uid'],'delete_weibo_comment');
    			}
    		}elseif($info['isdel'] == 1){
    			$this->where('comment_id='.$id.' AND isdel=1')->delete();
    		}
    		$r['boolen'] = 1;
    		$r['message'] = '删除成功';
    		$r['count']   = intval( $webInfo['comment'] -1 );
    	}else{
    		$r['boolen'] = 0;
    		$r['message'] = '删除失败';
    	}	
    	return $r;
    }
    
    //批量删除评论
    function deleteMuleComments($id,$uid){
    	$pMiniBlog = D('GroupWeibo', 'group');
    	$id = is_array($id) ? $id : explode(',', $id);
    	foreach ($id as $k=>$v){
    		$info = $this->where('comment_id='.$v)->find();
    		$webInfo = $pMiniBlog->field('uid')->where('weibo_id='.$info['weibo_id'])->find();
	    	if( $info['uid'] == $uid || $webInfo['uid'] == $uid ){
	    		if($info['isdel'] == 0 && $this->setField('isdel',1,'comment_id='.$v.' AND isdel=0')){    		
	    			$pMiniBlog->setDec('comment', 'weibo_id='.$info['weibo_id'] );
	    			if($info['uid'] != $info['reply_uid']){//删除自己给自己的评论，不扣积分
						X('Credit')->setUserCredit($info['uid'],'delete_weibo_comment')
									->setUserCredit($info['reply_uid'],'delete_weibo_comment');
	    			}
	    		}elseif($info['isdel'] == 1){
	    			$this->where('comment_id='.$v.' AND isdel=1')->delete();
	    		}
	    	}
    	}
    	return true;
    }

    public function getCommentDetail($comment_id) {
    	$cache_id = 'group_weibo_comment_' . $comment_id;
    	if (($res = object_cache_get($cache_id)) === false) {
    		$res = $this->where("`comment_id` = '{$comment_id}' AND `isdel` = 0")->find();
    		object_cache_set($cache_id, $res);
    	}
    	return $res;
    }
    
    /**
     * 缓存微博评论
     * 
     * 缓存的key的格式为: group_weibo_comment_微博评论ID.
     * 
     * @param array $comment_list 微博评论ID列表, 或者微博评论详情列表. 如果为微博评论ID列表时, 本方法会首先获取微博评论详情列表, 然后缓存.
     */
    public function setCommentObjectCache(array $comment_list) {
    	if (!is_array($comment_list[0]) && !is_numeric($comment_list[0]))
    		return false;
    		
    	if (is_numeric($comment_list[0])) { // 给定的是group_weibo_comment_id的列表. 查询group_weibo_comment详情
	    	$map['comment_id'] = array('in', $comment_list);
	    	$map['isdel']      = 0;
	    	$comment_list      = $this->where($map)->findAll();
    	}
    	
    	foreach ($comment_list as $v)
	   		object_cache_set("group_weibo_comment_{$v['comment_id']}", $v);
	   		
	   	return $comment_list;
    }
}