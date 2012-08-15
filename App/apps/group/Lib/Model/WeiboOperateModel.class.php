<?php
require_once(SITE_PATH.'/apps/group/Lib/Model/GroupWeiboModel.class.php');
class WeiboOperateModel extends GroupWeiboModel{

    var $tableName = 'group_weibo';

   public function getSavePath(){
        $savePath = SITE_PATH.'/data/uploads/miniblog';
        if( !file_exists( $savePath ) ) mk_dir( $savePath  );
        return $savePath;
    }

    //删除一条微博
    public function deleteMini($id, $gid, $uid){
    	if($info = $this->where("weibo_id={$id} AND uid={$uid} AND gid={$gid}")->find()){
    		if($info['isdel'] == 0 && $this->setField('isdel', 1, "weibo_id={$info['weibo_id']} AND gid={$gid} AND isdel=0")){
	    		//关联操作
	    		if ($info['transpond_id']) {
	    			$this->setDec('transpond', 'weibo_id=' . $info['transpond_id'] . ' AND gid=' . $gid);
	    		}
	    		//同时删除@用户的微博数据
	    		D('WeiboAtme', 'group')->where('weibo_id=' . $info['weibo_id'] . ' AND gid=' . $gid)->delete();

	    		//同时删除收藏
	    		D('WeiboFavorite', 'group')->where('weibo_id=' . $info['weibo_id'] . ' AND gid=' . $gid)->delete();

	    		//同时删除评论
	    		D('WeiboComment', 'group')->setField('isdel', 1, 'weibo_id=' . $info['weibo_id'] . ' AND gid=' . $gid);

	    		//同时更新话题微博数
	    		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $info['content'], $topic_arr);
				$topic_arr = array_unique($topic_arr[1]);
				foreach($topic_arr as $v){
					$topic_map['name'] = $v;
					$topic_map['gid']  = $gid;
					M('group_weibo_topic')->setDec('count',$topic_map);
				}
    		}elseif($info['isdel'] == 1 && $this->where('weibo_id=' . $info['weibo_id'] . ' AND gid=' . $gid . ' AND isdel=1')->delete()){
	    		//同时彻底删除评论
	    		D('WeiboComment', 'group')->where('weibo_id=' . $info['weibo_id'] . ' AND gid=' . $gid)->delete();
    		}else{
    			return false;
    		}
    		return true;
    	}else{
    		return false;
    	}
    }

	//搜索话题
    public function doSearch($key, $gid, $type='')
    {
    	$key = addslashes(t($key));
    	$gid = intval($gid);
    	if(!$key){
    		$list['count'] = 0;
    		return $list;
    	}
    	switch ($type){
    		case '':
    			$list = $this->where("content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'location':
    			$user = M('user')->where('uid=' . $GLOBALS['ts']['user']['uid'])->field('province')->find();
    			$list = $this->where("uid IN (SELECT uid FROM {$this->tablePrefix}user WHERE province=".$user['province'].") AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'follow':
    			$list = $this->where("uid IN (SELECT fid FROM {$this->tablePrefix}weibo_follow WHERE uid=" . $GLOBALS['ts']['user']['uid'] . ") AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'original':
    			$list = $this->where("transpond_id=0 AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'image':
    			$list = $this->where("type=1 AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'music':
    			$list = $this->where("type=4 AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;

    		case 'video':
    			$list = $this->where("type=3 AND content LIKE '%{$key}%' AND gid={$gid} AND isdel=0")->order('weibo_id DESC')->findpage(20);
    			break;
    	}

    	/*
    	 * 缓存用户信息, 被转发微博的详情
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);

    	foreach($list['data'] as $key=>$value){
    		$list['data'][$key] = $this->getOne('', '', $value);
    	}
    	return $list;
    }

	//Topic搜索
	function doSearchTopic($map, $order, $uid){
		$map .= trim($map)?' AND isdel = 0':'isdel = 0';
		/*if (model('Denounce')->getIdsDenounce('weibo', 'str')) {
    		$map.=" AND weibo_id NOT IN (" . model('Denounce')->getIdsDenounce('weibo', 'str') . ")";
    	}*/
    	$maskHotTopic = model('Xdata')->get('weibo:maskHotTopic');
		if( $maskHotTopic ){
			$arr_MaskHotTopic = explode('|', trim($maskHotTopic,'|'));
			foreach($arr_MaskHotTopic as $v){
				$map .= " AND content NOT LIKE '%#{$v}#%' ";
			}
		}

		$list = $this->where($map)->order($order)->findPage(20);

		/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);

		foreach($list['data'] as $key=>$value){
			$list['data'][$key] = $this->getOne('', '', $value);
		}
		return $list;
	}

	//获取未取出来的新微博条数
	function countNew($uid, $gid, $lastId,$type = null){
    	$map="gid={$gid} AND weibo_id>{$lastId} AND isdel=0";
    	if(isset($type)){
    	    $map .= " AND type=".intval($type);
    	}
		//获取被举报的微博ID
    	$list = $this->where($map)->order('weibo_id DESC')->count();
    	return $list;
	}

	function loadNew($uid,$gid,$lastId,$limit){
    	$map="gid={$gid} AND weibo_id>{$lastId} AND isdel=0";
		//获取被举报的微博ID
    	$list = $this->where($map)->order('weibo_id DESC')->limit($limit)->findAll();

        foreach( $list as $key=>$value){
 			$result[] = $this->getOne('', '', $value);
        }
        $return['data'] = $result;
        return $return;
	}

    //获取首页微博列表
    function getHomeList($uid, $gid, $type='index', $since, $row=20){
    	$row = $row?$row:10;
		//$followCount = M('weibo_follow')->where("uid=".$uid." AND type=0")->count();

    	if($type=='original'){  //原创
			$map = 'transpond_id=0 AND isdel=0';
    		if($since){
    			$map.=" AND weibo_id<$since";
    		}
    	}else if($type=='index' || $type==''){   // 默认全显
    	    if ($since) {
    			$map="weibo_id < $since AND isdel=0";
    		} else {
    			$map = 'isdel=0';
    		}
    	}else {
    		if ($since) {
    			$map="weibo_id < $since AND isdel=0";
    		}else {
    			$map = 'isdel=0';
    		}
			$map .= " AND type=".$type;
    	}

    	$map = " gid={$gid} AND ".$map;

    	// 去除被举报的微博
    	/*if (($denounce_ids = model('Denounce')->getIdsDenounce('weibo','str')))
    		$map.=" AND `weibo_id` NOT IN ( {$denounce_ids} ) ";*/

    	$list = $this->where($map)->order('weibo_id DESC')->limit($row)->findAll();

    	unset($map);

    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list, 'weibo_id,transpond_id,uid');
    	$this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);

        foreach( $list as $key=>$value){
        	//$value['is_favorited'] = M('group_weibo_favorite')->where("weibo_id={$value['weibo_id']} AND uid={$uid}")->count();
 			$result[] = $this->getOne('', '', $value);
        }
        $return['data'] = $result;

        unset($result, $list);
        return $return;
	}

    //提到我的
    function getAtme($uid, $gid = null, $api = null){
    	// 手动查询总数, 以提高效率
    	$count_sql = "SELECT count(*) AS count FROM {$this->tablePrefix}group_weibo AS w INNER JOIN {$this->tablePrefix}group_weibo_atme AS a ON w.weibo_id = a.weibo_id
    				  WHERE a.uid = {$uid} " . (is_numeric($gid) ? " AND w.gid={$gid} " : '')
    				  . " AND a.uid NOT IN ( SELECT b.fid FROM {$this->tablePrefix}user_blacklist AS b WHERE b.uid = {$uid} )";
    	$count = $this->query($count_sql);
    	$count = $count[0]['count'];

    	$list = $this->where((is_numeric($gid) ? " gid={$gid} AND " : '') . "isdel=0 AND weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}group_weibo_atme WHERE uid=$uid) AND uid NOT IN (SELECT fid FROM {$this->tablePrefix}user_blacklist WHERE uid=$uid)")
    				 ->order('ctime DESC')
    				 ->findPage(10, $count);

    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);

    	foreach ($list['data'] as $key => $value) {
 			$list['data'][$key] = $this->getOneLocation('', '', $value);
        }
        return $list;
    }

    //我收藏的
    function getCollection($uid,$api){
    	/*
    	$list = $this->where("isdel=0 AND weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)")->order('weibo_id DESC')->findPage(10);
    	*/

    	$list = M('group_weibo_favorite')->where("`uid`='{$uid}'")->order('`weibo_id` DESC')->findPage();
    	$favorite_ids = getSubByKey($list['data'], 'weibo_id');
    	$map['weibo_id'] = array('in', $favorite_ids);
    	$map['isdel']	 = '0';
    	$list['data'] = $this->where($map)->order('`weibo_id` DESC')->limit(count($favorite_ids))->findAll();

    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	/*$ids = getSubBeKeyArray($list['data'], 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);*/

    	foreach( $list['data'] as $key=>$value){
        	$value['is_favorited'] = '1';
 			$list['data'][$key] = $this->getOneLocation('',$value);
        }
        return $list;
    }



    //获取手机
    function getMobile($pre,$next,$count=20,$page=1){
    		if($pre){
    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}group_weibo a LEFT JOIN {$this->tablePrefix}group_weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 AND a.weibo_id>$pre UNION SELECT * FROM {$this->tablePrefix}group_weibo WHERE transpond_id=0 AND is_feed=0 AND weibo_id>$pre AND ( type =0 OR type=1) ORDER BY weibo_id ASC  LIMIT $count ");
    			$list = array_reverse($list);

    		}elseif($next){

    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}group_weibo a LEFT JOIN {$this->tablePrefix}group_weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 AND a.weibo_id<$next UNION SELECT * FROM {$this->tablePrefix}group_weibo WHERE transpond_id=0 AND is_feed=0 AND weibo_id<$next AND ( type =0 OR type=1) ORDER BY weibo_id DESC  LIMIT $count ");

    		}else{

    			$list = $this->query("SELECT a.* FROM {$this->tablePrefix}group_weibo a LEFT JOIN {$this->tablePrefix}group_weibo b ON a.transpond_id = b.weibo_id WHERE ( b.type=0 OR b.type=1 ) AND b.is_feed=0 UNION SELECT * FROM {$this->tablePrefix}group_weibo WHERE transpond_id=0 AND is_feed=0 AND ( type =0 OR type=1)  ORDER BY weibo_id DESC  LIMIT $count ");

    		}

    	    foreach($list as $k=>$v){
				$result[$k] = $this->getOneApi('', $v);
	    	}

    	return $result;
    }

    /**
     * 缓存微博列表
     *
     * 缓存的key的格式为: weibo_微博ID.
     *
     * @param array $weibo_list 微博ID列表, 或者微博详情列表. 如果为微博ID列表时, 本方法会首先获取微博详情列表, 然后缓存.
     */
    public function setWeiboObjectCache(array $weibo_list)
    {
    	if (!is_array($weibo_list[0]) && !is_numeric($weibo_list[0]))
    		return false;

    	if (is_numeric($weibo_list[0])) { // 给定的是weibo_id的列表. 查询weibo详情
	    	$map['weibo_id'] = array('in', $weibo_list);
	    	$map['isdel']    = 0;
	    	$weibo_list      = $this->where($map)->findAll();
    	}

    	foreach ($weibo_list as $v)
	   		object_cache_set("group_weibo_{$v['weibo_id']}", $v);

	   	return $weibo_list;
    }
}