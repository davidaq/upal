<?php
class GroupWeiboModel extends Model{
    var $tableName = 'group_weibo';

	/**
	 *
	 * Description 微博发布
	 * @author Nonant nonant@thinksns.com
	 * @param $uid 发布者用户ID
	 * @param $data 微博主要数据
	 * @param $from 从哪发布的
	 * @param $type 微博类型
	 * @param $type_data   微博类型传来的数据
	 * @return return_type
	 * Create at  2010-9-17 下午05:02:06
	 */
     function publish($uid,$data,$from=0,$type=0,$type_data,$sync, $from_data){
     	$data['content'] =t( $data['content'] );
        $data['gid']     =  intval($data['gid']) ;
     	if($id = $this->doSaveWeibo($uid, $data, $from , $type ,$type_data, $sync, $from_data) ){
     		$this->notifyToAtme($uid, $data['gid'], $id, $data['content'] );
     		return $id;
     	}else{
     		return false;
     	}
    }

    //发布微博
    function doSaveWeibo($uid, $data, $from=0, $type=0, $type_data=null, $sync=null, $from_data=null){
        /*if(!$data['content']){
        	return false;
        }*/
        $save['gid']            = $data['gid'];
        $save['uid']			= $uid;
        $save['transpond_id']	= intval( $data['transpond_id'] );
        $save['from']			= intval( $from );  //0网站 1手机网页版 2 android 3 iphone
        $save['transpond']      = 0;
        $save['isdel']          = 0;
        $save['comment']        = 0;
		$save['ctime']      = time();
		$save['from_data']		= $from_data;

        // 微博内容处理
        if(Addons::requireHooks('weibo_publish_content')){
        	Addons::hook("weibo_publish_content",array(&$save));
        }else{
        	$save['content'] 		= preg_replace('/^\s+|\s+$/i', '', html_entity_decode($data['content'], ENT_QUOTES));
			$save['content'] 		= preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$save['content']);	// 滤掉话题两端的空白
	        $save['content']		= preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/u',getContentUrl, $save['content']);
	        $save['content'] = t(getShort($save['content'], $GLOBALS['ts']['site']['length']));
        }

		// 微博类型钩子
        if(!$data['type'] && $type){
			$addonsData = array();
			Addons::hook("weibo_type",array("typeId"=>$type,"typeData"=>$type_data,"result"=>&$addonsData, 'app'=>'group'));
        	$save = array_merge($save,$addonsData);
        }elseif($data['type']){
			$save['type'] = intval( $data['type'] );
			$save['type_data'] = $data['type_data'];
		}

        $save['ctime']      = time();

        if( $id = $this->add( $save ) ){
        	if( $save['transpond_id']){
        		$this->setInc('transpond','weibo_id=' . $save['transpond_id'] . ' AND gid=' . $save['gid']);
        	}else{
        	    Addons::hook("weibo_publish_after",array('weibo_id'=>$id,'post'=>$save, 'app'=>'group'));
        	}

	        //话题处理
        	D('WeiboTopic', 'group')->addTopic(html_entity_decode($save['content'], ENT_QUOTES), $save['gid']);
        	if($type && $save['type']){
        	    Addons::hook('weibo_type_publish', array("weiboId"=>$id,"type"=>$type,"typeData"=>$type_data, "data"=>$save, 'app'=>'group'));
        	}
        	return $id;
        }else{
        	return false;
        }
    }

    //转发操作
    function transpond($uid, $data, $api=false){

		$post['gid']       	   = intval($data['gid']);
		$post['content']       = t($data['content']);
	    $post['transpond_id']  = intval( $data['transpond_id'] );

	    $transponInfo = $this->field('weibo_id,uid,content,type')->where('weibo_id=' . $post['transpond_id'] . 'AND gid=' . $post['gid'])->find();
	    $post['type'] = $transponInfo['type'];
        if( $data['reply_weibo_id'] ){ //对相应微博ID作出评论
        	foreach ( $data['reply_weibo_id'] as $value ){
				if($value == 0) continue;
				$weiboinfo = $this->field('uid')->where('weibo_id='.$value)->find();
				$comment['gid']       = $post['gid'];
	        	$comment['uid']       = $uid;
	        	$comment['reply_uid'] = $weiboinfo['uid'];
	        	$comment['weibo_id']  = $value;
	        	$comment['content']   = $post['content'];
	        	$comment['ctime']     = time();
	        	D('WeiboComment', 'group')->addcomment( $comment );
	        	D('GroupUserCount','group')->addCount($weiboinfo['uid'],'comment');
        	}
        }

	    $id = $this ->doSaveWeibo( $uid , $post , intval($data['from']) );
	    if($id){
	        Addons::hook("weibo_transpond_after",array('weibo_id'=>$id,'post'=>$post,'data'=>$data, 'app'=>'group'));
	    	$this->notifyToAtme($uid, $post['gid'], $id, $post['content'], $transponInfo['uid']);
	    	return $id;
	    }else{
	    	return false;
	    }
    }

    //给提到我的发通知 @诺南
    function notifyToAtme($uid, $gid, $id, $content, $transpond_uid, $addCount=true)
    {
    	$arrUids= array();
    	if( $transpond_uid ){
    		array_push($arrUids, $transpond_uid);
    	}
    	$arrUids = array_merge($arrUids, getUids($content) );
    	if ($arrUids) {
    		$arrUids = array_unique($arrUids); //去重
    		if ($addCount) {
    			foreach ($arrUids as $v) {
    				if (M('user_blacklist')->where("uid=$v AND fid=$uid")->count() == 0) {
    					$atUids[] = $v;
    				}
    			}
    			D('GroupUserCount', 'group')->addCount($atUids,'atme');
    		}
    		D('WeiboAtme', 'group')->addAtme($arrUids, $gid, $id);
    	}
    }

   	private function checkWeiboType($type,$type_data){
   	    if( $type_data && $type !=0 ){
   	     	$pluginInfo = M('weibo_plugin')->where('plugin_id='.$type)->field('plugin_path')->find();
   	     	$do_type = 'publish';
   	     	include SITE_PATH.'/apps/weibo/Lib/Plugin/'.$pluginInfo['plugin_path'].'/control.php';
   	    	if (!empty($typedata)) {
	   	     	$save['type'] = $type;
		        $save['type_data']  = serialize( $typedata );
	        }
        }else{
        	$save['type']      = 0;
        }
        return $save;
   	}

   	protected  function getOne($id, $gid, $value,$api=false){
   		if($api){
   			return $this->getOneApi($id, $gid, $value);
   		}else{
   			return $this->getOneLocation($id, $gid, $value);
   		}
   	}

    //返回一个站内使用的解析微博
    public function getOneLocation($id, $gid, $value = null){
    	if (!$value && ($value = object_cache_get("group_weibo_{$id}")) === false)
    			$value = $this->where('weibo_id=' . intval($id) . ' AND gid=' . $gid . ' AND isdel=0')->find();

    	if(!$value)
    		return false;

       	$result['id']          = $value['weibo_id'];
        $result['gid']         = $value['gid'];
        $result['uid']         = $value['uid'];
        $result['content']     = $value['content'];
        $result['ctime']       = $value['ctime'];
        $result['comment']     = $value['comment'];
        $result['from']        = $value['from'];
        $result['transpond_id'] = $value['transpond_id'];
        $result['transpond']    = $value['transpond'];
        Addons::requireHooks("weibo_show_detail",array(&$result));

        if( $result['transpond_id'] ){
        	$result['expend']      = $this->getOne($result['transpond_id'], $result['gid']);
        }else{
        	$result['expend']      = $this->__parseTemplate( $value );
        }
        $result['from_data'] = unserialize($value['from_data']);

        return $result;

    }

    //返回一个Api使用的微博信息
    function getOneApi($id,$info,$uid=0){
			if(!$info) $info = $this->where('weibo_id='.$id)->find();
			if(!$info) return false;
       		$info['uname'] = getUserName($info['uid']);
    		$info['face'] =  getUserFace($info['uid']);
    		if( $info['type']==1 && $info['transpond_id']==0 ){
    			$info['type_data'] = unserialize($info['type_data']);
    			$info['type_data']['picurl'] = SITE_URL.'/data/uploads/'.$info['type_data']['picurl'];
    			$info['type_data']['thumbmiddleurl'] = SITE_URL.'/data/uploads/'.$info['type_data']['thumbmiddleurl'];
    			$info['type_data']['thumburl'] = SITE_URL.'/data/uploads/'.$info['type_data']['thumburl'];
    		}
    		$info['transpond_data'] = ($info['transpond_id']!=0)?$this->getOneApi($info['transpond_id']):'';
    		$info['timestamp'] = $info['ctime'];
    		$info['ctime'] = date('Y-m-d H:i',$info['ctime']);
        	$info['from_data'] = unserialize($info['from_data']);
        	$info['favorited'] = D('Favorite','weibo')->isFavorited($id, $uid);
    		return $info;
    }

    private function __parseTemplate( $value ){
        static $rand;
    	if ($rand) {
    		$rand++;
    	}else {
    		$rand = time().$value['transpond_id'];
    	}

    	$typedata = unserialize( $value['type_data'] );
		$type     = $value['type'];
		$template = '';

		Addons::hook('weibo_type_parse_tpl',array('typeId'=>$type,'typeData'=>$typedata,'rand'=>$rand,'result'=>&$template, 'app'=>'group'));
    	return $template;
    }

    //解析类型模板
    private	function templateForType($type){
    	/**
		include(SITE_PATH.'/addons/libs/Io/Dir.class.php');
		$list = new Dir(  );
		foreach( $list->getList(APP_PATH.'/Lib/Plugin/') as $key=>$value ){
			if( is_dir( APP_PATH.'/Lib/Plugin/'.$value ) && is_file( APP_PATH.'/Lib/Plugin/'.$value.'/template.php' )){
				$file[$value] = require APP_PATH.'/Lib/Plugin/'.$value.'/template.php';
			}
		}
		return $file;
		**/
    	$info = M('weibo_plugin')->where('plugin_id='.$type)->field('plugin_path')->find();
		if(!$info) return false;
    	$r =require SITE_PATH.'/apps/weibo/Lib/Plugin/'.$info['plugin_path'].'/template.php';
    	return $r;
    }

    /**
     * 缓存微博列表
     *
     * 缓存的key的格式为: weibo_微博ID.
     *
     * @param array $weibo_list 微博ID列表, 或者微博详情列表. 如果为微博ID列表时, 本方法会首先获取微博详情列表, 然后缓存.
     */
    public function setWeiboObjectCache($weibo_list)
    {
    	if (!is_array($weibo_list))
    		return false;

    	if (!is_array($weibo_list[0]) && !is_numeric($weibo_list[0]))
    		return false;

    	if (is_numeric($weibo_list[0])) { // 给定的是weibo_id的列表. 查询weibo详情
	    	$map['weibo_id'] = array('in', $weibo_list);
	    	$map['isdel']    = 0;
	    	$weibo_list      = $this->where($map)->findAll();
    	}

    	foreach ($weibo_list as $v)
	   		object_cache_set("weibo_{$v['weibo_id']}", $v);

	   	return $weibo_list;
    }

    protected function _doWeiboAndUserCache($weibo_list)
    {
    	if (!is_array($weibo_list) || !is_array($weibo_list[0]))
    		return false;

    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($weibo_list, 'weibo_id,transpond_id,uid');
    	$transpond_list = $this->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);

    	return true;
    }
}