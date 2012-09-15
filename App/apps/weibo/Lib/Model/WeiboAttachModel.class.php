<?php
class WeiboAttachModel extends Model{
    var $tableName = 'weibo_attach';

	//获取用户的微博附件统计信息
	public function getUserAttachCount($uid,$weibo_type=0){

		if(intval($uid)==0) return false;

 		if($weibo_type>0){
			$map['weibo_type']	=	intval($weibo_type);
		}

		$map['uid']	=	intval($uid);

		//获取附件总数
		$count	=	$this->where($map)->count();
		return intval($count);
	}

	//获取用户的微博附件
	public function getUserAttachData($uid,$weibo_type,$row=0){
		//查询关联数据
		if(!$row){
			$attaches = $this->field('attach_id,weibo_id,uid')->where("uid={$uid} AND weibo_type={$weibo_type}")->order("attach_id desc")->findAll();
		}else{
			$attaches = $this->field('attach_id,weibo_id,uid')->where("uid={$uid} AND weibo_type={$weibo_type}")->order("attach_id desc")->findPage($row);
			$backupAttaches = $attaches;
			$attaches = $attaches['data'];
		}
		//获取附件ID
		foreach($attaches as $attach){
			$attach_ids[] = $attach['attach_id'];
			$attach_info[$attach['attach_id']] = $attach['weibo_id'];
		}
		//查询附件信息
		$map['userId']	=	$uid;
		$map['id']		=	array('in',$attach_ids);
		$map['isDel']	=	0;
		if(!$row) {
			$result['data'] = M('Attach')->where($map)->order("id desc")->findAll();
		} else {
			$result = M('Attach')->where($map)->order("id desc")->findPage($row);
			$backupAttaches['data'] = $result['data'];
			$result = $backupAttaches;
		}
		//增加微博ID的信息
		foreach($result['data'] as $k=>$v){
			$result['data'][$k]['weibo_id']	=	$attach_info[$v['id']];
		}
		unset($attaches,$attach_ids,$attach_info);
		return $result;
	}

	// 获取用户的微博附件 - 相册使用
	public function getUserAttachDataNew($uid, $weibo_type, $row = 0) {
		if(!$row) {
			$result = M()->Table(C('DB_PREFIX').'weibo_attach AS w LEFT JOIN '.C('DB_PREFIX').'attach AS a ON w.attach_id = a.id')
						 ->field('a.*, w.weibo_id')
						 ->where("w.uid={$uid} AND w.weibo_type={$weibo_type} AND a.userId={$uid} AND a.isDel=0")
						 ->group('w.attach_id')
						 ->order("w.attach_id DESC")
						 ->findAll();
		} else {
			$result = M()->Table(C('DB_PREFIX').'weibo_attach AS w LEFT JOIN '.C('DB_PREFIX').'attach AS a ON w.attach_id = a.id')
						 ->field('a.*, w.weibo_id')
						 ->where("w.uid={$uid} AND w.weibo_type={$weibo_type} AND a.userId={$uid} AND a.isDel=0")
						 ->group('w.attach_id')
						 ->order("w.attach_id DESC")
						 ->findPage($row);
		}

		return $result;
	}


	//获取微博相册
	public function getWeiboAlbum($uid){
		$count = $this->getUserAttachCount($uid,1);
		//获取微博附件
		if($count > 0){
			$last_weibo = $this->where($map)->order('attach_id desc')->limit(1)->find();
			$last_weibo_attach = M('Attach')->find(intval($last_weibo['attach_id']));
		}

		if(!$last_weibo_attach){
			return false;
		}

		//插入微博相册
		$weibo  =  array(
			  "id" =>  "0",
			  "userId" =>  $uid,
			  "name" => "微博相册",
			  "info" => NULL,
			  "cTime" =>  $last_weibo_attach['uploadTime'],
			  "mTime" =>  $last_weibo_attach['uploadTime'],
			  "coverImageId" => $last_weibo_attach['id'],
			  "coverImagePath" => $last_weibo_attach['savepath'].$last_weibo_attach['savename'],
			  "photoCount" =>  $count,
			  "readCount" =>  0,
			  "status" =>  1,
			  "isHot" =>  0,
			  "rTime" =>  0,
			  "share" =>  0,
			  "privacy" =>  1,
			  "privacy_data" => NULL,
			  "isDel" =>  0
			);

		return $weibo;
	}

	//添加微博关联附件
    public function add($uid, $weibo_id, $weibo_type, $attach_ids){

		$uid	=	intval($uid);
		$weibo_id = intval($weibo_id);
		$weibo_type = intval($weibo_type);

		if(!$uid || !$weibo_id || !$weibo_type || !$attach_ids)
			return false;

		//解析attach_ids
		$attach_ids	=	$this->_parseAttachIds($attach_ids);
		//格式化数据
		if( is_array($attach_ids) && count($attach_ids)>0 ){

			$insert_sql	=	"INSERT INTO __TABLE__ (uid,weibo_id,weibo_type,attach_id) VALUES ";

			foreach($attach_ids as $attach_id){
				$insert_sql	.=	" ($uid,$weibo_id,$weibo_type,$attach_id) ,";
			}

			$insert_sql	=	rtrim($insert_sql,',');

			//插入数据列表
			$result	=	$this->execute($insert_sql);
			return $result;
		}else{
			return false;
		}
	}

	/**
	 * 解析参数
	 *
	 * @param array|Object|string $input
	 * @return array
	 */
	 private function _parseAttachIds($input){
		if(is_string($input)){
			$input = explode(',',$input);
		}
		$output	=	array();
		if(is_array($input) || is_object($input)){
			foreach($input as $v){
				if(intval($v) > 0)
					$output[]	=	intval($v);
			}
		}

		return $output;
	}

	//删除微博的关联附件
    public function del($uid, $weibo_id){

		$uid		= intval($uid);
		$weibo_id	= intval($weibo_id);

		if(!$uid || !$weibo_id)
			return false;

		//查出关联附件数据
		$map['uid']		 = $uid;
		$map['weibo_id'] = $weibo_id;
		$attaches = $this->field('attach_id')->where($map)->findAll();

		if(!$attaches) return false;

		//删除附件及文件
		$ids = getSubByKey($attaches,'attach_id');
		$result = model('Attach')->deleteAttach($ids,true,true);

		if(!$result) return false;

		//删除关联数据
		$result = $this->where($map)->delete();

		return $result;
	}
}