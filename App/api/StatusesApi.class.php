<?php
//微博Api接口
class StatusesApi extends Api{
	//获取最新更新的公共微博消息
	function public_timeline(){
		return D('WeiboApi','weibo')->public_timeline( $this->since_id , $this->max_id , $this->count , $this->page ) ;
	}
	//获取当前用户所关注用户的最新微博信息
	function friends_timeline(){
		return D('WeiboApi','weibo')->friends_timeline( $this->mid , $this->since_id , $this->max_id , $this->count , $this->page ) ;
	}
	//获取用户发布的微博信息列表
	function user_timeline() {
		return D('WeiboApi','weibo')->user_timeline( $this->user_id , $this->user_name , $this->since_id , $this->max_id , $this->count , $this->page ) ;
	}
	//获取@当前用户的微博列表
	function mentions(){
		return D('WeiboApi','weibo')->mentions($this->mid , $this->since_id , $this->max_id , $this->count , $this->page);
	}
	//获取当前用户发送及收到的评论列表
	function comments_timeline(){
		return D('WeiboApi','weibo')->getCommentlist($this->mid,'all', $this->since_id , $this->max_id , $this->count , $this->page);
	}
	function show(){
		return D('Weibo','weibo')->getOneApi($this->id, null, $this->mid);
	}
	//获取当前用户发出的评论
	function comments_by_me() {
		return D('WeiboApi','weibo')->getCommentlist($this->mid,'send',$this->since_id , $this->max_id , $this->count , $this->page);
	}
	//获取当前用户收到的评论
	function comments_receive_me() {
		return D('WeiboApi','weibo')->getCommentlist($this->mid,'receive',$this->since_id , $this->max_id , $this->count , $this->page);
	}
	//获取指定微博的评论列表
	function comments(){
		return D('WeiboApi','weibo')->comments($this->id,$this->since_id , $this->max_id , $this->count , $this->page);
	}
	//发布一条微博
	function update(){
		$data['content'] = $this->data['content'];
		$id = D('Weibo','weibo')->publish( $this->mid,$data,$this->data['from'],0,'',array('sina'));
		return (int) $id;
	}
	//上传一张图片并返回图片地址
    function uploadpic(){
    	if( $_FILES['pic'] ){
    		//执行上传操作
    		$savePath =  $this->_getSaveTempPath();
    		$filename = md5( time().'teste' ).'.'.substr($_FILES['pic']['name'],strpos($_FILES['pic']['name'],'.')+1);
	    	if(@copy($_FILES['pic']['tmp_name'], $savePath.'/'.$filename) || @move_uploaded_file($_FILES['pic']['tmp_name'], $savePath.'/'.$filename))
	        {
	        	$result['boolen']    = 1;
	        	$result['type_data'] = 'temp/'.$filename;
	        	$result['picurl']    = SITE_PATH.'/uploads/temp/'.$filename;
	        } else {
	        	$result['boolen']    = 0;
	        	$result['message']   = '上传失败';
	        }
    	}else{
        	$result['boolen']    = 0;
        	$result['message']   = '上传失败';
    	}
		return $result;
    }
    //上传临时文件
    private function _getSaveTempPath(){
        $savePath = SITE_PATH.'/data/uploads/temp';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }
	//发布一个图片微博
	function upload(){
		$uppic = $this->uploadpic();
		$pic = $uppic['boolen']?$uppic['type_data']:h($this->data['pic']);
		$data['content'] = h( $this->data['content'] );
		$id = D('Weibo','weibo')->publish( $this->mid,$data,$this->data['from'],1,$pic,array('sina'));
		return (int) $id;
	}
	//删除一条微博
	function destroy(){
		$result = D('Operate','weibo')->deleteMini($this->id,$this->mid);
		return (int) $result;
	}
	//删除一条评论
	function commentDestroy()
	{
		$result = D('Comment','weibo')->deleteComments($this->id, $this->mid);
		return (int) $result['boolen'];
	}
	//对一个微博发一条评论
	function comment(){
		$post['reply_comment_id'] = intval( $this->data['reply_comment_id'] );  //回复 评论的ID
		$post['weibo_id']         = intval( $this->data['weibo_id'] );          //回复 微博的ID
		$post['content']          = $this->data['comment_content'];         	//回复内容
		$post['transpond']        = intval($this->data['transpond']);           //是否同是发布一条微博
		$post['from']             = intval($this->data['from']);            	//来自哪里
		$id = D('Comment','weibo')->doaddcomment( $this->mid ,$post,true );
		return (int) $id;
	}
	//转发一条微博
	function repost(){
		$post['content']		=  $this->data['content'] ;                  //转发内容
		$post['transpond_id']   = intval( $this->data['transpond_id'] );        //转发的微博ID
		$post['reply_weibo_id'] = explode(',',$this->data['reply_data']);       //给xx同时评论的数组对象(此处传过来的是微博的ID)
		$post['from'] 			= intval($this->data['from']);
		$id = D('Weibo','weibo')->transpond($this->mid,$post);
		return (int) $id;
	}
	//用户关注列表
	function following(){
		return D('WeiboApi','weibo')->following($this->user_id , $this->user_name , $this->since_id , $this->max_id , $this->count , $this->page);
	}
	//用户粉丝列表
	function followers(){
		return D('WeiboApi','weibo')->followers($this->user_id , $this->user_name , $this->since_id , $this->max_id , $this->count , $this->page);
	}
	// 搜索微博
	public function search()
	{
		$result = D('WeiboApi','weibo')->search($this->data['key'], $this->since_id, $this->max_id, $this->count, $this->page);
		if (empty($result))
			$result = array();
		return $result;
	}
	
	
	
	// 搜索用户
	public function searchuser()
	{
		$result = D('WeiboApi','weibo')->searchUser($this->data['key'], $this->mid, $this->since_id, $this->max_id, $this->count, $this->page);
		if (empty($result))
			$result = array();
		$allowed_key = array('ctime', 'domain', 'face', 'followed_count', 'followers_count', 'is_active', 'is_init', 'is_followed', 'location', 'mini', 'sex', 'uid', 'uname');
		foreach ($result as $k => $v) {
			// 剔除敏感信息
			foreach ($v as $k2 => $v2)
				if (!in_array($k2, $allowed_key))
					unset($result[$k][$k2]);
			$result[$k]['timestamp'] = $v['ctime'];
			$result[$k]['ctime']	 = date('Y-m-d H:i:s', $v['ctime']);
			$result[$k]['location']  = (string)$v['location'];
			$result[$k]['sex']       = getSex($v['sex']);
		}
		return $result;
	}
	
	//获取话题
}
?>