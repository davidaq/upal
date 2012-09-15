<?php
/**
 * 全局评论模型
 * 
 * @author daniel <desheng.young@gmail.com>
 */
class GlobalCommentModel extends Model {
	protected $tableName = 'comment';
	
	/**
	 * 获取评论列表
	 * 
	 * @param string $type  send:已发送 | 其它值:已接收
	 * @param int    $uid
	 * @param string $order
	 * @param int    $limit
	 * @return array
	 */
    public function getCommentList($type, $uid, $order = 'id DESC', $limit = 10) {
    	$map = ( $type == 'send' )?"uid={$uid}":"appuid={$uid} OR to_uid={$uid}";
    	return $this->where($map)->order($order)->findPage($limit);
    }

    /**
     * 全部设置为已读
     * 
     * @param int $uid 用户ID
     * @return boolean
     */
    public function setUnreadCountToZero($uid) {
    	/*$map['to_uid'] = $uid;
    	$map['status'] = 0;
    	$this->where($map)->setField('status', 1);*/
    	$this->where("(appuid={$uid} OR to_uid={$uid}) AND status=0")->setField('status',$uid);
    	$this->where("(appuid={$uid} OR to_uid={$uid}) AND status>0 AND status<>{$uid}")->setField('status',-1);
    }
   
    /**
     * 未读评论数
     *  
     * @param int $uid 用户ID
     * @return boolean
     */
    public function getUnreadCount($uid) {
    	//$map['to_uid'] = $uid;
    	//$map['status'] = 0;
    	$map = "(appuid={$uid} OR to_uid={$uid}) AND status>=0 AND status<>{$uid}";
    	return $this->where($map)->count();
    }

    /**
     * 删除评论
     * 
     * @param array|string|int $ids 多个ID组成数组，或者以“,”分隔
     * @return boolean 
     */
    public function deleteComment($ids,$uid='') {
    	$map['id'] = array('in', $ids);
        $comments = $this->where($map)->findAll();
        if ( empty($comments) )
			return false;
        	
       	// 应用回调: 减少应用的评论计数
        // 已优化: 先统计出哪篇应用需要减几, 然后再减. 这样可以有效减少数据库操作次数
       	$id_array			= array();
       	$id_field_array		= array();
       	$count_field_array	= array();
       	// 统计
       	foreach ($comments as $v) {
       		//如果此条评论不属于某用户则跳过此次循环
       		if( $uid && !$this->isFromUid( $val['id'],$uid ) ){
	       		continue;
	       	}
       		$v['data'] = unserialize($v['data']);
       		$id_array[$v['data']['table']][$v['appid']][]	= 'JUST A FLAG';
       		$id_field_array[$v['data']['table']]			= $v['data']['id_field'];
        	$count_field_array[$v['data']['table']]			= $v['data']['comment_count_field'];
       	}
       	// 减小计数
       	foreach($id_array as $table => $app_data) {
       		foreach($app_data as $app_id => $v) {
       			$count = count($v);
       			M($table)->setDec($count_field_array[$table], "`{$id_field_array[$table]}`='{$app_id}'", $count);
       		}
       	}
       	// 删除评论
       	$this->where($map)->delete();
       	return true;
    }
    
    protected function isFromUid( $cid,$uid ){
    	$map['id'] = $cid;
    	$map['uid'] = $uid;
    	$bool = $this->where($map)->find();
    	if( $bool ){
    		return true;
    	}else{
    		return false;
    	}
    }
}
?>
