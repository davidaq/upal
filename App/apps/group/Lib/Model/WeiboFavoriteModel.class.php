<?php 
//require_once(SITE_PATH.'/apps/group/Lib/Model/GroupWeiboModel.class.php');
class WeiboFavoriteModel extends Model{
    var $tableName = 'group_weibo_favorite';
    
    
    function getList($uid , $since_id,$max_id,$count=20,$page=1){
    	$limit = ($page-1)*$count.','.$count;
    	$map['_string'] = "weibo_id IN (SELECT weibo_id FROM {$this->tablePrefix}weibo_favorite WHERE uid=$uid)";
		if($since_id){
			$map['weibo_id'] = array('gt',$since_id) ;
		}elseif ($max_id){
			$map['weibo_id'] = array('lt',$max_id);
		}
		
    	$list = M('group_weibo')->where($map)->order('weibo_id DESC')->limit($limit)->findall();
        foreach( $list as $key=>$value){
 			$list[$key] = D('GroupWeibo','group')->getOneApi('',$value);
        }
        return $list;
    }
    
    //收藏微博
    function favWeibo( $id ,$uid ){
    	$data['uid']    = $uid;
    	$data['weibo_id']  = $id;
    	return $this->add($data);
    }
    
    //取消收藏
    function dodelete($id,$uid){
    	return $this->where("weibo_id=$id AND uid=$uid")->delete();
    }
    
    function isFavorite($id, $uid) {
    	$ids = explode(',', $id);
    	if (count($ids) <= 1) {
    		return $this->where("weibo_id=$id AND uid=$uid")->find() ? true : false;
    	}else {
    		$map['weibo_id'] = array('in', $ids);
    		$map['uid']		 = $uid;
    		$res = $this->where($map)->field('weibo_id')->findAll();
    		return getSubByKey($res, 'weibo_id');
    	}
    }
}
?>