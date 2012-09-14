<?php
class WeiboIndexAction extends BaseAction
{
    //加载评论
    public function loadcomment()
    {
        $intMinId = intval($_POST['id']);
        $data['weibo_id'] = $intMinId;
        $data['quick_reply'] = intval($_POST['quick_reply']);
        $data['quick_reply_uname'] = t($_POST['quick_reply_uname']);
        $data['quick_reply_comment_id'] = intval($_POST['quick_reply_comment_id']);
        $data['callback'] = t($_POST['callback']);
        $data['data']  = D('WeiboOperate')->where('weibo_id='.$intMinId)->find();
        $data['privacy'] = D('UserPrivacy','home')->getPrivacy($this->mid,$data['data']['uid']);
        $data['randtime'] = ($data['quick_reply_comment_id'])?$data['quick_reply_comment_id']:$data['data']['weibo_id'] ;
        if(!$data['quick_reply']) $data['list'] =  D('WeiboComment')->getComment($intMinId, $this->gid);
        $this->assign( $data );
        $this->display();
    }

    //加载更多的
    function loadmore(){
    	
    	set_time_limit(0);
    	
    	$data['type'] = $_POST['type'] === 0?$_POST['type']:intval($_POST['type']);
    	
    	$data['list'] = D('WeiboOperate')->getHomeList($this->mid,$this->gid,$data['type'],$_POST['since'],$_POST['limit']);
    	
    	$this->assign($data);
    	$this->display();
    }

    function loadnew(){
//     	// 每120秒刷新一次!
//     	if ( !lockSubmit('120') ) {
//     		exit('<TSAJAX>');
//     	}
    	$data['showfeed'] = intval($_REQUEST['showfeed']);
    	$data['list'] = D('WeiboOperate')->loadNew($this->mid,$this->gid,$_POST['since'],$_POST['limit'],$data['showfeed']);
    	$this->assign($data);

    	// NO unlockSubmit(); !!!

    	$this->display('loadmore');
    }

    //查看最新的
    function countnew(){
    	$data['lastId'] = intval($_POST['lastId']);
    	$data['type']   = h($_POST['type']);
    	$list = D('WeiboOperate')->countNew($this->mid,$this->gid,$data['lastId'],$data['type']);
    	$data['limit'] = $list;
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