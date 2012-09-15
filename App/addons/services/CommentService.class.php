<?php
/**
 * 评论服务 [已废弃]
 * @author daniel
 * @deprecated
 */
class CommentService extends Model {
    protected	$tableName	=	'comment';	// 数据库表名
    protected	$list_name	=	'global';	// 默认列表名
	protected	$fields		=	array (0 => 'commentId',1 => 'type',2 => 'appid',3 => 'name',4 => 'uid',5 => 'comment',6 => 'cTime', 7=> 'toId' ,
									   8 => 'status',9=>'quietly','_autoinc' => true,'_pk' => 'commentId');
   	
	public function post($data) {
		//检查是否为空
		if(empty($data['type']) || empty($data['appid']) || empty($data['name']) || empty($data['uid']) || empty($data['comment']))
		   return false;
		   
		$data['cTime'] = time();
		$result = $this->add($data);
		return $result;
	}
	
	public function delete(){
		
	}
	
	public function edit(){
		
	}
	
	public function getCommentByAppId($appId,$type) {
		$map['appid'] = is_array($appId)?array('in',$appId):$appId;
		$map['type']  = $type;
		return $this->where($map)->findAll();
	}
}