<?php
// +----------------------------------------------------------------------
// | ThinkSNS
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.thinksns.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: nonant <nonant@thinksns.com>
// +----------------------------------------------------------------------
//

/**
 * 动态服务
 */
class NotifyService extends Service{
    public function __construct($data) {
    }

    /**
     * 获取给定用户的通知统计
     * @param int $mid
     * @return array 格式为:
     *               <code>
     *               array(
     *               	'message'		=> '0', // 未读短消息数
     *               	'notify'		=> '0', // 未读通知数
     *               	'appmessage'	=> '0', // 未读应用消息数
     *               	'comment'		=> '0', // 未读评论总数
     *               	'atme'			=> '0', // 未读的@我的总数
     *               	'total'			=> '0', // 以上未读的总数
     *               	'weibo_comment' => '0', // 未读的微博评论数
     *               	'global_comment'=> '0', // 未读的其它应用评论数
     *                  'group_atme'     => '0', // 群组@我
     *                  'group_comment'     => '0', // 群组评论
     *                  'group_bbs'     => '0', // 群组帖子通知
     *               )
     *               </code>
     */
	public function getCount($uid) {
		$uid = intval($uid);
		if ($uid <= 0) {
			return array(
				'message'		 => 0,
				'notify'		 => 0,
				'appmessage'	 => 0,
				'comment'		 => 0,
				'atme'			 => 0,
				'total'			 => 0,
				'weibo_comment'	 => 0,
				'global_comment' => 0,
			    'group_atme'     => 0,
			    'group_comment'  => 0,
			    'group_bbs'      => 0,
			);
		}
		$weibo_count	= model('UserCount')->where('uid='.$uid)->find();


		global $ts;
		$install_app = $ts['install_apps'];
		foreach($install_app as $value){
		    if( (strtolower($value['app_name']) == 'group')){
		        $group_count    = D('GroupUserCount','group')->getUnreadCount($uid);
		        $return['group_atme']      = $group_count['atme'];
		        $return['group_comment']   = $group_count['comment'];
		        $return['group_bbs']       = $group_count['bbs'];
		    }
		}
		$global_comment = intval(model('GlobalComment')->getUnreadCount($uid));
		$return['message']    		= model('Message')->getUnreadMessageCount($uid);
		$return['notify']     		= M('notify')->where('receive='.$uid.' AND is_read=0')->count();
		$return['appmessage'] 		= M('myop_myinvite')->where('touid='.$uid.' AND is_read=0')->count();
		$return['comment']    		= intval( $weibo_count['comment'] ) + $global_comment;
		$return['atme']       		= intval( $weibo_count['atme'] );
		$return['weibo_comment']	= intval($weibo_count['comment']);
		$return['global_comment']	= $global_comment;
		$return['total']      		= array_sum($return);
		return $return;
	}


	/**
	 * 获取给定用户的通知统计
	 * @param int $mid
	 * @return array 格式为:
	 *               <code>
	 *               array(
	 *               	'message'		=> '0', // 未读短消息数
	 *               	'notify'		=> '0', // 未读通知数
	 *               	'comment'		=> '0', // 未读评论总数
	 *               	'atme'			=> '0', // 未读的@我的总数
	 *               	'total'			=> '0', // 以上未读的总数
	 *               )
	 *               </code>
	 */
	public function getNotifityCount($uid,$type,$since_id,$max_id,$count,$page) {
		$uid = intval($uid);
		if ($uid <= 0) {
			return array(
					     0=>array('type'=>'atme','name'=>'@我的','icon'=>'AtMeIcon','count'=>0,'data'=>''),
					     1=>array('type'=>'comment','name'=>'评论我的','icon'=>'CommentIcon','count'=>0,'data'=>'')
			);
		}
		$weibo_count	= model('UserCount')->where('uid='.$uid)->find();
		$return['0']    		= array('type'=>'atme','name'=>'@我的','icon'=>'AtMeIcon','count'=>intval($weibo_count['atme']),'data'=>'');
		$return['1']       		= array('type'=>'comment','name'=>'评论我的','icon'=>'CommentIcon','count'=>intval($weibo_count['comment']),'data'=>'');
		$message = model('Message')->getMessageListByUidForAPIUnread($uid, $type, $since_id, $max_id, $count, $page);
		foreach ($message as $k => $v) {
			$message[$k] = $this->__formatMessageDetail($v);
		}
		foreach($message as $key=>$value){
			$return[$key+2]['type'] = 'message';
			$return[$key+2]['name'] =$value['from_uname'];
			$return[$key+2]['icon'] = $value['from_face'];
			$return[$key+2]['count'] = $value['new'];
			$return[$key+2]['data'] = $value;
		}
		return $return;
	}

	private function __formatMessageDetail($message) {
		unset($message['deleted_by']);
		$message['from_uname']	= getUserName($message['from_uid']);
		//$message['to_uname']	= getUserName($message['to_uid']);
		$message['from_face']	= getUserFace($message['from_uid']);
		//$message['to_face']		= getUserFace($message['to_uid']);
		$message['timestmap']	= $message['mtime'];
		$message['ctime']		= date('Y-m-d H:i', $message['mtime']);
		return $message;
	}
	/**
	 * 获取通知列表
	 *
	 * @param array|string $map          查询条件, 必须是ThinkPHP格式的map
	 * @param int          $limit        每页显示的数据条数
	 * @param boolean      $mark_is_read 是否标记为已读
	 * @return array
	 */
	public function get($map,$limit=20,$mark_is_read = true) {
		$notifyList = M('Notify')->where($map)->order('ctime DESC')->findpage($limit);

		foreach ($notifyList['data'] as $key=>$value){
			$parseData = $this->_parseTemplate($value);
			$notifyList['data'][$key]['title'] = $parseData['title'];
			$notifyList['data'][$key]['body']  = $parseData['body'];
			$notifyList['data'][$key]['other']  = $parseData['other'];
		}

		if ($mark_is_read)
			M('Notify')->data(array('is_read'=>1))->where($map)->save();

		return $notifyList;
	}

	/**
	 * 用户对用户发送通知
	 * @param string|int|array $receive 接收人ID 多个时以英文的","分割或传入数组
	 * @param string           $type    通知类型, 必须与模版的类型相同, 使用下划线分割应用.
	 * 					   				如$type = "weibo_follow"定位至/apps/weibo/Language/cn/notify.php的"weibo_follow"
	 * @param array            $data
	 * @param int              $from    发送人ID
	 * @return void
	 */
	public function send( $receive , $type , $data  , $from ) {
		return $this->__put( $receive , $type , $data , $from );
	}

	/**
	 * 系统对用户发送通知
	 * @param string|int|array $receive 接收人ID 多个时以英文的","分割或传入数组
	 * @param string           $type    通知类型, 必须与模版的类型相同, 使用下划线分割应用.
	 * 					   				如$type = "weibo_follow"定位至/apps/weibo/Language/cn/notify.php的"weibo_follow"
	 * @param array            $data
	 * @return void
	 */
	public function sendIn( $receive , $type , $data  ) {
		return $this->__put( $receive , $type , $data  , 0 , true );
	}

	/**
	 * 删除通知
	 * @param string|array $ids 通知ID 多个时以英文的","分割
	 * @return boolean
	 */
	public function deleteNotify($ids) {
		$ids = is_array($ids) ? $ids : explode(',', $ids);
		if ( empty($ids) )
			return false;
		$map['notify_id'] = array('in', $ids);
		return M('notify')->where($map)->delete();
	}

	/**
	 * 枚举所有通知类型
	 */
	public function enumerateType() {
		$sql = "SELECT `type` FROM " . C('DB_PREFIX') . "notify GROUP BY `type`";
		$res = M('')->query($sql);
		return getSubByKey($res, 'type');
	}

	/**
	 +----------------------------------------------------------
	 * Description 通知发送处理
	 +----------------------------------------------------------
	 * @author Nonant nonant@thinksns.com
	 +----------------------------------------------------------
	 * @param $type    通知类型
	 * @param $receive 通知接收者的用户ID,类型可为 数字、字符串、数组
	 * @param $title   通知标题
	 * @param $body    通知内容
	 * @param $from    通知发送者UID
	 * @param $system  是否为系统通知
	 +----------------------------------------------------------
	 * @return Boolen
	 +----------------------------------------------------------
	 * Create at  2010-9-13 下午04:24:53
	 +----------------------------------------------------------
	 */
	private function __put($receive,$type,$data,$from=0,$system=false) {
		global $ts;
		$receive = $this->_parseUser( $receive ); if(!$receive) return false;
		$from = ( $system==false &&  $from==0 ) ? $ts['user']['uid'] : $from ;
		$data      = addslashes(serialize( $data ));
		$time       = time();

		//优化大批量发送通知，讲数据切割处理，每次插入100条
		$receive	=	array_chunk($receive, 100)  ;
		foreach ($receive as $receive_chunck){

			foreach ($receive_chunck as $k=>$v){
				if($v==$from) continue;
				$sqlArr[] = "($from,$v,'$type','$data',$time)";
			}

			if( $sqlArr ){
				$sql = "INSERT INTO ".C('DB_PREFIX')."notify (`from`,`receive`,`type`,`data`,`ctime`) values ".implode(',',$sqlArr);
				$result[] = M('Notify')->execute($sql);

			}

			unset($sql,$sqlArr,$receive_chunck);
		}

		return $result;
	}

	//解析传入的用户ID
	private function _parseUser($touid){
		if( is_numeric($touid) ){
			$sendto[] = $touid;
		}elseif ( is_array($touid) ){
			$sendto = $touid;
		}elseif (strpos($touid,',') !== false){
			$touid = array_unique(explode(',',$touid));
			foreach ($touid as $key=>$value){
				$sendto[] = $value;
			}
		}else{
			$sendto = false;
		}
		return $sendto;
	}

	/**
	 * 解析模板
	 */
	private function _parseTemplate($i_data){

		if( false == $i_data['data'] = unserialize($i_data['data'])){
			 $i_data['data'] = unserialize(stripslashes($i_data['data']));
		}
		$replace["{actor}"] = getUserSpace($i_data['from'], 'fn', '_blank', '{uname}') . getUserGroupIcon($i_data['from']);
		if($i_data) extract ( $i_data['data'], EXTR_OVERWRITE );
		unset($i_data['data']);
		extract ( $i_data, EXTR_OVERWRITE );
		$template_type = explode('_',$i_data['type']);
		if(file_exists(SITE_PATH.'/apps/'.$template_type[0].'/Language/cn/notify.php'))
			$template = require( SITE_PATH.'/apps/'.$template_type[0].'/Language/cn/notify.php' );

		$return['title']    = str_replace(array_keys($replace),array_values($replace),$template[$i_data['type']]['title']);
		$return['body']     = str_replace(array_keys($replace),array_values($replace),$template[$i_data['type']]['body']);
		$return['other']    = str_replace(array_keys($replace),array_values($replace),$template[$i_data['type']]['other']);
		return $return;
	}

    //运行服务，系统服务自动运行
	public function run(){

	}

	//启动服务，未编码
	public function _start(){
		return true;
	}

	//停止服务，未编码
	public function _stop(){
		return true;
	}

	//安装服务，未编码
	public function _install(){
		return true;
	}

	//卸载服务，未编码
	public function _uninstall(){
		return true;
	}
}
?>