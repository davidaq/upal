<?php
class FavoriteModel extends Model
{
    var $tableName = 'weibo_favorite';
    static $user_cache = array();
    static $cacheHash = array();
    public function getList($uid, $since_id, $max_id, $count = 20, $page = 1)
    {
    	$limit = ($page-1)*$count.','.$count;
    	$map['_string'] = "weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)";
		if ($since_id) {
			$map['weibo_id'] = array('gt',$since_id);
		} else if ($max_id) {
			$map['weibo_id'] = array('lt',$max_id);
		}
    	$list = M('weibo')->where($map)->order('weibo_id DESC')->limit($limit)->findAll();
    	/*
    	 * 缓存被转发微博的详情, 作者信息, 被转发微博的作者信息
    	 */
    	$ids = getSubBeKeyArray($list, 'weibo_id,transpond_id,uid');
    	$transpond_list = D('Weibo', 'weibo')->setWeiboObjectCache($ids['transpond_id']);
    	// 本页的用户IDs = 作者IDs + 被转发微博的作者IDs
    	$ids['uid'] = array_merge($ids['uid'], getSubByKey($transpond_list, 'uid'));
    	D('User', 'home')->setUserObjectCache($ids['uid']);
    	$weibo_ids = getSubByKey($list, 'weibo_id');
        foreach( $list as $key => $value) {
        	$value['favorited'] = D('Favorite','weibo')->isFavorited($value['weibo_id'], $uid, $weibo_ids);
 			$list[$key] = D('Weibo','weibo')->getOneApi('', $value);
        }
        return $list;
    }
    //收藏微博
    function favWeibo( $id ,$uid ){
    	$data['uid']    = $uid;
    	$data['weibo_id']  = $id;
    	$res = $this->add($data);
    	if($res){
    	    $this->_addFavoriteCache($uid, $id);
    	}
    	return $res;
    }
    //取消收藏
    function dodelete($id,$uid){
    	$res = $this->where("weibo_id=$id AND uid=$uid")->delete();
		if($res){
    	    $this->_delFavoriteCache($uid, $id);
    	}
		return $res;
	}
    /**
     * 检查给定用户是否收藏给定微博
     *
     * @param int 		 $weibo_id 		 微博ID
     * @param int 		 $uid      		 用户ID
     * @param array|null $weibo_id_array $weibo_id所属的微博集合(不为空时会一次性查询, 以减少数据库请求数)
     * @param string     $key            为防止前一次调用对后一次调用的干扰, 为每个$weibo_id_array赋予唯一key
     * @return int 已收藏返回1, 否则返回0
     */
    function isFavorited($weibo_id, $uid,$static = false)
    {
        if($static){
            self::$user_cache = $this->_getFavoriteCache($uid);
            if(empty(self::$user_cache)){
                $list = $this->_setFavoriteCache($uid);
            }else{
                $list = self::$user_cache;
            }
        }else{
            $list = $this->_getFavoriteCache($uid);
        }
        return in_array($weibo_id,$list);
    }
    private function _getListData($uid){
    	if(isset(self::$cacheHash[$uid])){
    		return self::$cacheHash[$uid]['data'];
    	}
       	self::$cacheHash[$uid]['data'] = $this->field('weibo_id')->where("uid=$uid")->findAll();
        return self::$cacheHash[$uid]['data'];
    }
    private function _getFavoriteCache($uid){
        return json_decode(S('favorite_'.$uid));
    }
    private function _addFavoriteCache($uid,$weibo_id){
        $cache = $this->_getFavoriteCache($uid);
        if(false == $cache){
            return $this->_setFavoriteCache($uid);
        }else{
            !in_array($weibo_id,$cache) && $cache[] = $weibo_id;
        }
        return S('favorite_'.$uid,json_encode($cache));
    }
    private function _setFavoriteCache($uid){
        $list = getSubByKey($this->_getListData($uid),'weibo_id');
        S('favorite_'.$uid,json_encode($list));
        return $list;
    }
	private function _delFavoriteCache($uid,$weibo_id){
       S('favorite_'.$uid,Null);
	   return true;
    }
}
?>