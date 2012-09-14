<?php
class WeiboModel extends Model{
    var $tableName = 'weibo';

	/**
	 *
	 +----------------------------------------------------------
	 * Description 微博发布
	 +----------------------------------------------------------
	 * @author Nonant nonant@thinksns.com
	 +----------------------------------------------------------
	 * @param $uid 发布者用户ID
	 * @param $data 微博主要数据
	 * @param $from 从哪发布的
	 * @param $type 微博类型
	 * @param $type_data   微博类型传来的数据
	 +----------------------------------------------------------
	 * @return return_type
	 +----------------------------------------------------------
	 * Create at  2010-9-17 下午05:02:06
	 +----------------------------------------------------------
	 */
     function publish($uid, $data, $from=0, $type=0, $type_data, $sync, $from_data){
     	$data['content'] =t( $data['content'] );
     	if($id = $this->doSaveWeibo($uid, $data, $from , $type ,$type_data, $from_data)){
     		$this->notifyToAtme($uid, $id, $data['content'] );
     		return $id;
     	}else{
     		return false;
     	}
    }

    //发布微博
    function doSaveWeibo($uid, $data, $from=0, $type=0, $type_data=null, $from_data=null){
		//用户不存在时无法发微博、把用户退出处理
		$result = M('User')->find($uid);
		if(!$result){
			//退出登录
			service('Passport')->logoutLocal();
			return false;
		}
		/*if(!$data['content']){
        	return false;
        }*/

        if (!function_exists('getContentUrl'))
        	require_once SITE_PATH . '/apps/weibo/Common/common.php';
        $save['uid']			= $uid;
        $save['transpond_id']	= intval( $data['transpond_id'] );
        $save['from']			= intval( $from );  //0网站 1手机网页版 2 android 3 iphone
        $save['transpond']      = 0;
        $save['isdel']          = 0;
        $save['comment']        = 0;
		$save['pub_ip']	 = get_client_ip();
		$save['ctime']      = time();
		$save['from_data']		= $from_data;
        // 微博内容处理
        if(Addons::requireHooks('weibo_publish_content')){
        	Addons::hook("weibo_publish_content",array(&$save));
        }else{
        	$save['content'] = preg_replace('/^\s+|\s+$/i', '', html_entity_decode($data['content'], ENT_QUOTES));
			$save['content'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$save['content']);	// 滤掉话题两端的空白
	        $save['content'] = preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{2e80}-\x{9fff}\s<\'\"“”‘’]*)?)/u',getContentUrl, $save['content']);
	        $save['content'] = t(getShort($save['content'], $GLOBALS['ts']['site']['length']));
        }

		// 微博类型钩子
        if($type){
			$addonsData = array();
			Addons::hook("weibo_type",array("typeId"=>$type,"typeData"=>$type_data,"result"=>&$addonsData));
        	$save = array_merge($save,$addonsData);
        }elseif($data['type']){
			$save['type'] = intval( $data['type'] );
			$save['type_data'] = $data['type_data'];
		}

		//写入数据库
        if( $id = $this->add( $save ) ){
        	if( $save['transpond_id']){
        		$this->setInc('transpond','weibo_id='.$save['transpond_id']);
        	}else{
        		Addons::hook("weibo_publish_after",array('weibo_id'=>$id,'post'=>$save));
        	}

        	$save['weibo_id'] = $id;
        	$this->_setWeiboCache($id, $save);

		    //修改登录用户缓存信息--添加微博数目
		    $userLoginInfo = S('S_userInfo_'.$uid);
		    if(!empty($userLoginInfo)) {
		    	$userLoginInfo['miniNum'] = strval($userLoginInfo['miniNum'] + 1);
		    	S('S_userInfo_'.$uid, $userLoginInfo);
		    }

        	$weiboInfoWithLink['type'] = $save['type'];
        	$weiboInfoWithLink['transpond_id'] = $save['transpond_id'];
        	D('Topic', 'weibo')->addTopic( html_entity_decode($save['content'],ENT_QUOTES), $id, $weiboInfoWithLink);
        	if($type && $save['type']){
        		Addons::hook('weibo_type_publish', array("weiboId"=>$id,"type"=>$type,"typeData"=>$type_data, "data"=>$save));
        	}

            //更新粉丝的dashboard.更新最新微博ID,最后更新时间.
            //$sql = "UPDATE ".C('DB_PREFIX')."weibo_dashboard SET last_post_weibo_id=$id,last_post_time=".time()." WHERE uid IN (SELECT uid FROM ".C('DB_PREFIX')."weibo_follow WHERE fid={$uid} AND type=0) OR uid={$uid}";
            //M('')->execute($sql);
            //S('dashboard_'.$uid,null);
            
        	return $id;
        }else{
        	return false;
        }
    }

    protected function getWeiboDetail($id){
        if(is_array($id)){
			$id = array_unique(array_filter($id));
            $res = array();
            $no_cache = array();
            foreach($id as $value){
                $detail = $this->_getWeiboCache($value);
                if(empty($detail) || !$detail){
                   $no_cache[] = $value;
                   $res[$value] = false;
                }else{
                    if(!$detail['isdel']){
                        $res[]  = $detail;
                    }
                }
            }

            $no_cache = array_unique(array_filter($no_cache));
            if(!empty($no_cache)){
                $map['weibo_id'] = array('in',$no_cache);
                $noCacheWeiboInDb = $this->where($map)->findAll();
                foreach($noCacheWeiboInDb as $value){
                    if(!$value['isdel']){
                        $res[$value['weibo_id']] = $value;
                    }
                }
                $this->setWeiboObjectCache($noCacheWeiboInDb);
            }
        }else{
            $res = $this->_getWeiboCache($id);
            if(empty($res)||!$res){
                $res = $this->where('weibo_id='.$id.' and isdel=0')->find();
                $this->_setWeiboCache($id, $res);
            }
            if($res['isdel']) return false;
        }
        return $res;
    }

    //转发操作
    function transpond($uid,$data,$api=false){
		$post['content']       = t( $data['content'] );
	    $post['transpond_id']  = intval( $data['transpond_id'] );
        $this->_removeWeiboCache($post['transpond_id']);
	    $transponInfo = $this->field('weibo_id,uid,content,type')->where('weibo_id='.$post['transpond_id'].' AND isdel=0')->find();
	    $post['type'] = $transponInfo['type'];
        if( $data['reply_weibo_id'] ){ //对相应微博ID作出评论
        	foreach ( $data['reply_weibo_id'] as $value ){
				if($value == 0) continue;
				$weiboinfo = $this->field('uid')->where('weibo_id='.$value.' AND isdel=0')->find();
	        	$comment['uid']       = $uid;
	        	$comment['reply_uid'] = $weiboinfo['uid'];
	        	$comment['weibo_id']  = $value;
	        	$comment['content']   = $post['content'];
	        	$comment['ctime']     = time();
	        	D('Comment','weibo')->addcomment( $comment );
	        	model('UserCount')->addCount($weiboinfo['uid'],'comment');
        	}
        }

	    $id = $this ->doSaveWeibo( $uid , $post , intval($data['from']) );
	    if($id){
	    	Addons::hook("weibo_transpond_after",array('weibo_id'=>$id,'post'=>$post,'data'=>$data));
	    	$this->notifyToAtme($uid,$id, $post['content'], $transponInfo['uid']);
	    	return $id;
	    }else{
	    	return false;
	    }
    }

    // 给提到我的发通知 @诺南
    function notifyToAtme($uid,$id,$content,$transpond_uid,$addCount=true){
    	$notify['weibo_id'] = $id;
    	$notify['content'] = $content;
    	$arrUids= array();
    	if( $transpond_uid ){
    		array_push($arrUids, $transpond_uid);
    	}
    	$arrUids = array_merge($arrUids, getUids($content) );
    	if( $arrUids ){
    		$arrUids = array_unique( $arrUids ); //去重
    		if($addCount){
    			foreach ($arrUids as $v){
    				if(M('user_blacklist')->where("uid=$v AND fid=$uid")->count()==0){
    					$atUids[] = $v;
    				}
    			}
    			Model('UserCount')->addCount($atUids,'atme');
    		}
    		D('Atme','weibo')->addAtme($arrUids,$id);
    	}
    }

   	function getOne($id,$value,$api=false, $uid){
   		if($api){
   			return $this->getOneApi($id,$value);
   		}else{
   			return $this->getOneLocation($id, $value, true, $uid);
   		}
   	}

    //返回一个站内使用的解析微博
    public function getOneLocation($id, $value, $show_transpond = true, $uid)
    {
    	if (!$value){
    	    $value = $this->getWeiboDetail($id);
    	}

    	if (!$value)
    		return false;
        $result = $value;

        $uid = empty($uid) ? $value['uid'] : $uid;
        $result['is_favorited'] = isset($value['is_favorited']) ? intval($value['is_favorited']) : D('Favorite','weibo')->isFavorited($value['weibo_id'], $uid);
        Addons::requireHooks("weibo_show_detail",array(&$result));
        if ($show_transpond && $result['transpond_id']){
        	$result['expend']   = $this->getOne($result['transpond_id']);
        } else {
			if (!$result['expend']) {
        		$result['expend'] = $this->__parseTemplate( $value );
			}
        }


        return $result;
    }

    //返回一个Api使用的微博信息
    public function getOneApi($id, $value, $uid = 0)
    {
		if (!$value && is_numeric($id))
			if (($value = object_cache_get("weibo_{$id}")) === false)
    			$value = $this->where('weibo_id="'.$id.'" AND isdel=0')->find();

		if (!$value)
				return false;

		$value['uname'] = getUserName($value['uid']);
    	$value['face']  = getUserFace($value['uid']);
   		if ($value['type'] == 1 && $value['transpond_id'] == 0) {
    		$value['type_data'] 				  = unserialize($value['type_data']);
			if(isset($value['type_data']['thumburl'])){
				$value['type_data']['picurl'] 		  = SITE_URL.'/data/uploads/'.$value['type_data']['picurl'];
    			$value['type_data']['thumbmiddleurl'] = SITE_URL.'/data/uploads/'.$value['type_data']['thumbmiddleurl'];
    			$value['type_data']['thumburl'] 	  = SITE_URL.'/data/uploads/'.$value['type_data']['thumburl'];
    		}else{
				foreach($value['type_data'] as $k=>$v){
					$value['type_data'][$k]['picurl'] 		  = SITE_URL.'/data/uploads/'.$value['type_data'][$k]['picurl'];
    				$value['type_data'][$k]['thumbmiddleurl'] = SITE_URL.'/data/uploads/'.$value['type_data'][$k]['thumbmiddleurl'];
    				$value['type_data'][$k]['thumburl'] 	  = SITE_URL.'/data/uploads/'.$value['type_data'][$k]['thumburl'];
				}
			}
		}

    	$value['transpond_data'] = ($value['transpond_id'] > 0) ? $this->getOneApi($value['transpond_id']) : '';
    	$value['timestamp'] 	 = $value['ctime'];
    	$value['ctime'] 		 = date('Y-m-d H:i',$value['ctime']);
       	$value['from_data'] 	 = unserialize($value['from_data']);
       	$value['content'] 		 = keyWordFilter($value['content']);
		if(isset($value['favorited'])){
       		$value['favorited']   = intval($value['favorited']);
    	}else{
			$value['favorited']   = (int) D('Favorite','weibo')->isFavorited($id,$uid);
		}
		return $value;
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

		Addons::hook('weibo_type_parse_tpl',array('typeId'=>$type,'typeData'=>$typedata,'rand'=>$rand,'result'=>&$template));
    	return $template;
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
        $check_item = $weibo_list{0};
    	if (!is_array($weibo_list))
    		return false;
    	if (!is_array($check_item) && !is_numeric($check_item))
    		return false;
    	if (is_numeric($check_item)) { // 给定的是weibo_id的列表. 查询weibo详情
    	    $weibo_list = array_unique(array_filter($weibo_list));
	    	$map['weibo_id'] = array('in', $weibo_list);
	    	$map['isdel']    = 0;
	    	$weibo_list      = $this->where($map)->findAll();
    	}

    	foreach ($weibo_list as $v){
    	    $cache = $this->_getWeiboCache($v['weibo_id']);
    	    if(!$cache || empty($cache)){
    	        $this->_setWeiboCache($v['weibo_id'], $v);
    	    }
    	}

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

	//微博人气排行榜 - 按一段时间(默认30天)内 微博被转发和评论的权重计算 默认 转发数*5+评论数*5
	public function getAllStarByMaxrating($num=10,$time_range='30', $retweet_weight=5,$comment_weigh=5) {
		$result	=	$this->field("uid,sum(comment*5+transpond*5) as rating")
									->where(array('ctime' => array('gt',time()-$time_range*24*3600)))
									->group("uid")
									->order("rating desc")
									->limit($num)
									->findAll();
		if($result){
			//取热度 - 按照当前值与最大值的比例计算
			$max_rating = max(getSubByKey($result, 'rating'));
			foreach($result as $k=>$v){
				$result[$k]['hot_rating'] = ceil(($v['rating']/$max_rating)*100);
			}
			return $result;
		}else{
			return false;
		}
	}

	public function _getWeiboCache($id){
	    $res = object_cache_get("weibo_".$id);
	    return $res == false?F('weibo_detail_'.$id):$res;
	}

	public function _removeWeiboCache($id){
	    object_cache_set("weibo_".$id,false);
	    return F('weibo_detail_'.$id,null);
	}

	public function _setWeiboCache($id,$data){
	    object_cache_set("weibo_".$id,$data);
	    return F('weibo_detail_'.$id,$data);
	}
}
