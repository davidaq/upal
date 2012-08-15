<?php
class IndexAction extends Action{

	function init(){
		echo './Public/miniblog.js';
	}

    //加载评论
    function loadcomment(){
        $intMinId = intval( $_POST['id'] );
        $data['weibo_id'] = $intMinId;
        $data['quick_reply'] = intval($_POST['quick_reply']);
        $data['quick_reply_uname'] = t($_POST['quick_reply_uname']);
        $data['quick_reply_comment_id'] = intval($_POST['quick_reply_comment_id']);
        $data['callback'] = t($_POST['callback']);
        $data['data']  = D('Operate')->where('weibo_id='.$intMinId)->find();
        $data['privacy'] = D('UserPrivacy','home')->getPrivacy($this->mid,$data['data']['uid']);
        $data['randtime'] = ($data['quick_reply_comment_id'])?$data['quick_reply_comment_id']:$data['data']['weibo_id'] ;
        if(!$data['quick_reply']) $data['list'] =  D('Comment')->getComment($intMinId);
        if($intMinId){
            $data['weibo_id'] = $intMinId;
        }else{
            $map['comment_id'] = $data['quick_reply_comment_id'];
            $data['weibo_id'] = D('Comment')->getField('weibo_id',$map);
        }

        $this->assign( $data );
        $this->display();
    }


    //加载更多的
    function loadmore(){
        $data['showfeed'] = intval($_REQUEST['showfeed']);
        $data['lastId'] = intval($_POST['since']);
        $data['type']   = t($_POST['type']);
        $data['follow_gid'] = intval($_POST['follow_gid']);
        $uid = isset($_POST['hasUid']) ? intval($_POST['hasUid']):$this->mid;
    	$data['list'] = D('Operate')->loadMore($uid,$data['lastId'],$data['type'],$data['follow_gid']);
    	$this->assign($data);
    	$this->display();
    }

    function loadnew(){
    	$data['showfeed'] = intval($_REQUEST['showfeed']);
    	$data['lastId'] = intval($_POST['since']);
    	$data['type']   = t($_POST['type']);
    	$data['follow_gid'] = intval($_POST['follow_gid']);
    	$data['limit']      = intval($_POST['limit']);
    	$uid = isset($_POST['hasUid']) ? intval($_POST['hasUid']):$this->mid;
    	$data['list'] = D('Operate')->loadNew($uid,$_POST['since'],$data['limit'],$data['type'],$data['follow_gid']);
    	$this->assign($data);
    	$this->display('loadmore');
    }

    //查看最新的
    function countnew(){
    	$data['showfeed'] = intval($_REQUEST['showfeed']);
    	$data['lastId'] = intval($_POST['lastId']);
    	$data['type']   = t($_POST['type']);
    	$data['follow_gid'] = intval($_POST['follow_gid']);
    	//重构该处，完全没有用到的功能。
    	//$data['since'] = $list[0]['weibo_id'];
    	$uid = isset($_POST['hasUid']) ? intval($_POST['hasUid']):$this->mid;
    	$data['limit'] = D('Operate')->countNew($uid,$data['lastId'],$data['type'],$data['follow_gid']);
    	$this->assign($data);
    	$this->display();
    }

    //@xxx
    function searchuser(){
    	$name = t($_REQUEST['n']);
    	$list = M('user')->where("uname LIKE '{$name}%'")->field('uid,uname')->findall();
    	exit( json_encode($list));
    }
}
?>