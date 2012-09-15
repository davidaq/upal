<?php
class FollowGroupModel extends Model{
	var $tableName = 'weibo_follow_group';
    const CACHE_PREFIX = "weibo_follow";
	// 获取指定用户所有的关注的分组
	public function getGroupList($uid){
        if(!is_numeric($uid)) throw new ThinkException(L("arg_number_only"));
        
		if(false == ($follow_group_list = F(self::CACHE_PREFIX."list_".$uid)) ){
			
		    $follow_group_list = $this->where("uid={$uid}")->order('ctime ASC')->findAll();
		    if(empty($follow_group_list)){
		    	F(self::CACHE_PREFIX."list_".$uid,array());
		    }else{
		    	F(self::CACHE_PREFIX."list_".$uid,$follow_group_list);
		    }
		}
		return $follow_group_list;
	}
	// 获取指定用户指定关注的人所在分组
	public function getGroupStatus($uid,$fid){
		$map = array(
					'uid' => intval($uid),
					'fid' => intval($fid),
					'type'=> 0
			   );
		$follow_id = M('weibo_follow')->getField('follow_id',$map);
		if($follow_id){
			$follow_group_status = $this->field('link.follow_group_id AS gid,group.title')
										->table("{$this->tablePrefix}weibo_follow_group_link AS link LEFT JOIN {$this->tablePrefix}{$this->tableName} AS `group` ON link.follow_group_id=group.follow_group_id AND link.uid=group.uid")
										->where("link.follow_id={$follow_id} AND group.uid={$uid}")
										->order('group.follow_group_id ASC')
										->findAll();
			if(empty($follow_group_status))$follow_group_status[0] = array('gid'=>0,'title'=> L('no_grouping'));
			return $follow_group_status;
		}else{
			return false;
		}
	}
	// 设置好友的分组状态
	public function setGroupStatus($uid,$fid,$gid,$action=NULL){
	    F(self::CACHE_PREFIX."list_".$uid,null);
	    F(self::CACHE_PREFIX."usergroup_{$uid}_{$gid}",null);
		$map = array(
					'uid' => intval($uid),
					'fid' => intval($fid),
					'type'=> 0
			   );
		$follow_id = M('weibo_follow')->getField('follow_id',$map);
		$gid	   = $this->getField('follow_group_id',"uid={$map['uid']} AND follow_group_id={$gid}");
		if($follow_id && $gid){
			$linkModel = M('weibo_follow_group_link');
			$data = array(
						'follow_group_id' => $gid,
						'follow_id' 	  => $follow_id,
						'uid'			  => $map['uid']
			        );
			if($action == NULL){
				$linkModel->where($data)->delete() || $linkModel->add($data);
			}elseif($action == 'add'){
				$linkModel->where($data)->find() || $linkModel->add($data);
			}elseif($action == 'delete'){
				$linkModel->where($data)->delete();
			}
		}
	}
	// 添加/修改分组
	public function setGroup($uid,$title,$gid=NULL){
	    F(self::CACHE_PREFIX."list_".$uid,null);
	    F(self::CACHE_PREFIX."usergroup_{$uid}_{$gid}",null);
		$uid   = intval($uid);
		$title = h(mStr($title,8,'utf-8',false));
		if(!$title)return 0;
		//查看分组是否已存在
		$map = array(
					'uid'   => $uid,
					'title' => $title
			   );
		$_gid = $this->getField('follow_group_id',$map);
		if(!$_gid){
			if($gid == NULL){
				$data = array(
							'uid'   => $uid,
							'title' => $title,
							'ctime' => time()
						);
				$gid = $this->add($data);
				return $gid;
			}else{
				$gid   = intval($gid);
				if(!$gid)return 0;
				$data = array(
							'follow_group_id'   => $gid,
							'uid'   => $uid,
							'title' => $title
					    );
				$res = $this->save($data);
				return 1;
			}
		}elseif($_gid == $gid){
			return 1;
		}else{
			return 0;
		}
	}
	// 删除某个分组
	public function deleteGroup($uid,$gid){
	    F(self::CACHE_PREFIX."list_".$uid,null);
	    F(self::CACHE_PREFIX."usergroup_{$uid}_{$gid}",null);
		$uid = intval($uid);
		$gid = intval($gid);
		$res = $this->where("uid={$uid} AND follow_group_id={$gid}")->delete();
		if($res){
			// 清除相应分组信息
			M('weibo_follow_group_link')->where("uid={$uid} AND follow_group_id={$gid}")->delete();
			return 1;
		}else{
			return 0;
		}
	}
	// 获取指定用户指定分组下的关注的人的ID
	public function getUsersByGroup($uid,$gid){
		$uid = intval($uid);
		$gid = intval($gid);
		if (($_fid = F(self::CACHE_PREFIX."usergroup_{$uid}_{$gid}")) == false){
		    $follow_group_id_sql = $gid==0?' AND link.follow_group_id IS NULL':" AND link.follow_group_id={$gid}";
		    $fid = $this->field('follow.fid')
		    ->table("{$this->tablePrefix}weibo_follow AS `follow` LEFT JOIN {$this->tablePrefix}weibo_follow_group_link AS link ON follow.follow_id=link.follow_id AND follow.uid=link.uid")
		    ->where("follow.type=0 AND follow.uid={$uid}".$follow_group_id_sql)
		    ->findAll();
		    foreach($fid as $v){
		        $_fid[] = $v['fid'];
		    }
		    F(self::CACHE_PREFIX."usergroup_{$uid}_{$gid}",$_fid);
		}
		return $_fid;
	}
}
?>