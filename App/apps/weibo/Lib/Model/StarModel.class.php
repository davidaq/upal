<?php
class StarModel extends Model {
	protected $tableName = 'weibo_star';
	protected $group_list;

	//获取某一父级分组下的子级分组列表   PS：顶级分组的父级为0
	public function getGroupList($tid=0){
		$map['top_group_id'] = intval($tid);
		$list = M('weibo_star_group')->where($map)->order('display_order ASC,star_group_id DESC')->findAll();
		if(empty($this->group_list)){
			if($tid==0){
				$this->group_list = $list;
			}else{
				$this->group_list[] = array('star_group_id'=>$tid,'son_list'=>$list);
			}
		}
		return $list;
	}

	// 获取当前分组的所有子分组
	public function getAllGroupList(){
		if ( ($cache = S('Cache_Star_Group')) != false ) {
			$this->group_list = $cache;
			return $this->group_list;
		}
		if(empty($this->group_list)) {
			$this->getGroupList();
		}
		foreach($this->group_list as &$v){
			$v['son_list'] = $this->getGroupList($v['star_group_id']);
		}
		S('Cache_Star_Group',$this->group_list);
		return $this->group_list;
	}

	// 指定分组
	public function setGroup($gid){
		$this->clearGroupList();

		$gid = intval($gid);
		if($gid){
			$this->getGroupList($gid);
		}
		return $this;
	}

	// 清空分组列表
	public function clearGroupList(){
		$this->group_list = array();
		S('Cache_Star_Group',null);
		return $this;
	}

	// 按分组获取名人列表
	public function getStarsByGroup($limit=6){
		if(empty($this->group_list)){
			$this->getAllGroupList();
		}
		$group_list = $this->group_list;
		$limit = intval($limit);
	
		foreach($group_list as $k=>&$v){
			$v['user_list'] = $this->getStarsCache($v['star_group_id'],$limit);
			if(!empty($v['son_list']) && is_array($v['son_list'])){
				foreach($v['son_list'] as $ks=>&$vs){
					$vs['user_list'] =  $this->getStarsCache($vs['star_group_id'],$limit);
					if(!$vs['user_list'])unset($v['son_list'][$ks]);
				}
			}
			if(!$v['user_list'] && !$v['son_list'])unset($group_list[$k]);
		}

		return $group_list;
	}

	public function getStarsCache($star_group_id,$limit=10, $refresh = false){
		
		//10分钟缓存
		
		if(($cache = S('Cache_Weibo_Star_'.$star_group_id)) === false || true == $refresh){
			
    		S('Cache_Weibo_Star_t_'.$star_group_id,time()); //缓存未设置 先设置缓存设定时间	
    	}else{
    		if(($cacheSetTime =  S('Cache_Weibo_Star_t_'.$star_group_id)) === false || $cacheSetTime+600 <= time()){
    			S('Cache_Weibo_Star_t_'.$star_group_id,time()); //缓存未设置 先设置缓存设定时间	
    		}else{
    			return !empty($limit) ? array_slice($cache,0,$limit) : $cache;
    		}
    	}	
    	
		$map['star_group_id'] = $star_group_id;
		$cache = $this->where($map)->order('star_id DESC')->findAll();
		S('Cache_Weibo_Star_'.$star_group_id,empty($cache) ? array() : $cache);
		
		return !empty($limit) ? array_slice($cache,0,$limit) : $cache;
	}
	// 获取名人列表
	public function getStars($limit=20,$page=true){
		$limit = intval($limit);
		$map = array();

		if(!empty($this->group_list)){
			foreach($this->group_list as $v){
				$_gids[] = $v['star_group_id'];
				if(!empty($v['son_list']) && is_array($v['son_list'])){
					foreach($v['son_list'] as $vs){
						$_gids[] = $vs['star_group_id'];			
					}
				}
			}
			$map['star_group_id'] = array('IN',$_gids);
		}

		$find = $page?'findPage':'findAll';
		$user_list = $this->where($map)->order('star_id DESC')->limit($limit)->$find();

		return $user_list;
	}

	// 获取名人所在分组信息
	public function getStarGroup(&$star) {
		if(!is_array($star)){
			$star = $this->find(intval($star));
		}
		$_star_group = M('weibo_star_group')->find(intval($star['star_group_id']));
		$star['star_group_title'] = $_star_group['title'];
		
		if($_star_group['top_group_id']!=0){		
			$star['top_group_id']    = $_star_group['top_group_id'];
			$star['top_group_title'] = M('weibo_star_group')->getField('title',"star_group_id={$_star_group['top_group_id']}");
		}

		return $star;
	}

	// 添加分组
	public function addGroup($title,$tid=0){
		if(empty($title) || mb_strlen($title,'utf-8')>10 || !is_numeric($tid))return 0;
		$title = h(t($title));

		$groupModel = M('weibo_star_group');

		// 检测父级分组是否存在
		if($tid){
			$tid = $groupModel->getField('star_group_id',"star_group_id={$tid} AND top_group_id=0");
			if(!$tid){
				return -2;
			}			
		}

		// 检测分组是否已经存在
		$data['title'] = $title;
		if($groupModel->where($data)->find()){
			return -1;
		}

		$data['top_group_id'] = $tid;
		$data['ctime'] 		  = time();
		$res = $groupModel->add($data);
		
		//清空缓存
		S('Cache_Star_Group',null);
		
		if(!$res)$res = 0;

		return $res;
	}

	//修改分组名称
	public function editGroup($title,$gid){
		if(empty($title) || mb_strlen($title,'utf-8')>10 || !is_numeric($gid))return 0;
		$title = h(t($title));
		//检测分组是否存在
		$groupModel = M('weibo_star_group');
		$gid = $groupModel->getField('star_group_id',"star_group_id={$gid}");
		if($gid){
			// 检测分组名是否已经存在
			$data['title'] = $title;
			if($groupModel->where($data)->find()){
				return -1;
			}
			$data['star_group_id'] = $gid;
			$data['title'] 		   = $title;
			$res = $groupModel->save($data);
			S('Cache_Star_Group',null);
			if($res){
				return 1;
			}else{
				return 0;
			}
		}else{
			return -2;
		}
	}

	//删除分组
	public function delGroup($gid){
		if(!is_numeric($gid))return 0;
		$groupModel = M('weibo_star_group');

		$group_map = "star_group_id={$gid}";
		$tid = $groupModel->getField('top_group_id',$group_map);
		if($tid==0){
			// 若删除顶级分组 则同时删除该分组下的二级分组
			$group_map .= " OR top_group_id={$gid}";

			$son_map['top_group_id'] = $gid;
			$son_list = $groupModel->field('star_group_id')->where($son_map)->findAll();
			foreach($son_list as &$v){
				$v = $v['star_group_id'];
			}
			$son_list[] = $gid;
			$star_map['star_group_id'] = array('IN',$son_list);
		}else{
			$star_map['star_group_id'] = $gid;
		}
		$res = $groupModel->where($group_map)->delete();
		if($res){
			$this->where($star_map)->delete();
			S('Cache_Star_Group',null);
			return 1;
		}else{
			return 0;
		}
	}

	public function addStar($uid,$gid){
		$uid = t($uid);
		$gid = intval($gid);

		//检测分组是否存在
		$gid = M('weibo_star_group')->getField('star_group_id',"star_group_id={$gid}");
		if($gid){
			$map['star_group_id'] = $gid;
			$data = array(
						'star_group_id' => $gid,
						'ctime'			=> time()
					);
			if(is_numeric($uid)){
				$map['uid']  = $uid;
				$data['uid'] = $uid;
				$star = $this->where($map)->find();
				$isUser = M('user')->where('uid='.$uid)->find();
				if(!$star && $isUser){
					$res['code'] = $this->add($data);
				}else{
					if($star) {
						$res['code'] = -3;
					} else {
						$res['code'] = -4;
					}
				}
			}elseif(strpos($uid,',')){
				$uid = array_unique(explode(',',$uid));
				foreach($uid as $v){
					$map['uid']  = $v;
					$data['uid'] = $v;
					$star = $this->where($map)->find();
					$isUser = M('user')->where('uid='.$v)->find();
					if(!$star && $isUser){
						$res['isJoin'][] = $this->add($data);
					} else {
						if(!$isUser) {
							$res['data'][] = $v;
						}
					}
				}
				if(empty($res['isJoin'])){
					$res['code'] = -3;
				}
				if(!empty($res['data'])) {
					$res['code'] = -5;
				}
			}else{
				$res['code'] = 0;
				return json_encode($res);
			}

			if($res){
				$this->_cleanStarCache();
				return json_encode($res);
			}else{
				$res['code'] = 0;
				return json_encode($res);
			}
		}else{
			$res['code'] = -2;
			return json_encode($res);
		}
	}

	public function editStar($star_id,$gid){
		if(!$gid){
			return 0;
		}elseif(!is_array($gid)){			
			$gid_arr = explode(',',$gid);
		}else{
			$gid_arr = $gid;
		}
		if($star_id){
			$map = array();
			$map['star_id'][] = 'IN';
			$map['star_id'][] = t($star_id);
		}else{
			return 0;
		}
		$star = $this->where($map)->findAll();
		foreach($gid_arr as $gid){
			$gid = intval($gid);
			//检测分组是否存在
			$gid = M('weibo_star_group')->getField('star_group_id',"star_group_id={$gid}");
			if(!$gid)continue;
			$_map['star_group_id'] = $gid;
			foreach($star as $v){
				$_map['uid'] = $v['uid'];
				$_star = $this->where($_map)->find();
				if(!$_star || $v['star_group_id'] == $gid){
					$_map['ctime'] = time();
					$this->add($_map);
				}
			}
		}
		$this->where($map)->delete();
		$this->_cleanStarCache();
		return 1;
	}

	public function delStar($star_id){
		if(is_numeric($star_id)){
			$map['star_id'] = $star_id;
		}elseif(strpos($star_id,',')){
			$map['star_id'][] = 'IN';
			$map['star_id'][] = t($star_id);
		}else{
			return 0;
		}
		$res = $this->where($map)->delete();
		if($res){
			$this->_cleanStarCache();
			return 1;
		}else{
			return 0;
		}
	}
	
	public function getAllStart() {
		$cache_id = '_weibo_star_model_all_star';
		if (($res = F($cache_id)) === false) {
			$res = $this->field('DISTINCT(uid)')->findAll();
			F($cache_id, $res);
		}
		return $res;
	}

	/* ------ */
	// 清理star列表缓存
	private function _cleanStarCache()
	{
		F('_weibo_star_model_all_star', null);

		if(empty($this->group_list)){
			$this->getAllGroupList();
		}
		$group_list = $this->group_list;

		foreach($group_list as $k=>&$v){
			$this->getStarsCache($v['star_group_id'], null, true);
			if(!empty($v['son_list']) && is_array($v['son_list'])){
				foreach($v['son_list'] as $ks=>&$vs){
					$this->getStarsCache($vs['star_group_id'], null, true);
				}
			}
		}
	}
}