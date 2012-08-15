<?php
class TopicModel extends Model{
	var $tableName = 'weibo_topic';

	//添加话题
	function addTopic( $content, $weiboId = false, $data = false ){
		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is",$content,$arr);
		$arr = array_unique($arr[1]);
		foreach($arr as $v){
			$this->addKey($v, $weiboId, $data);
		}
	}

	//添加话题
	private function addKey($key, $weiboId, $data){
		//$map['name'] = trim(t(mStr(preg_replace("/#/",'',trim($key)),150,'utf-8',false)));
		$map['name'] = trim(preg_replace("/#/",'',trim($key)));
		if( $this->where($map)->count() ){
			$this->setInc('count',$map);
			if($weiboId) {
				$this->addWeiboJoinTopic($map['name'], $weiboId, $data, true);
			}
		}else{
			$map['count'] = 1;
			$map['ctime'] = time();
			$topicId = $this->add($map);
			if($weiboId) {
				$this->addWeiboJoinTopic($topicId, $weiboId, $data);
			}
			return $topicId;
		}
	}

	//添加微博与话题的关联
	private function addWeiboJoinTopic($topicNameOrId, $weiboId, $data, $isExist = false) {
		if($isExist) {
			$map['name'] = $topicNameOrId;
			$topicId = $this->where($map)->getField('topic_id');
		} else {
			$topicId = $topicNameOrId;
		}

		$add['weibo_id'] = $weiboId;
		$add['topic_id'] = $topicId;
		if(is_null($data['type'])) {
			$add['type'] = 0;
		} else {
			$add['type'] = $data['type'];
		}
		$add['transpond_id'] = $data['transpond_id'];

		M('weibo_topic_link')->add($add);
	}

	//删除微博与话题关联
	public function deleteWeiboJoinTopic($content, $weiboId) {
		//preg_match_all('/#(.*)#/isU',$info['content'],$topic_arr);
		preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $content, $arr);
		$arr = array_unique($arr[1]);
		$map['name'] = array('IN', $arr);
		$topicIdArr = $this->field('topic_id')->where($map)->findAll();
		$topicIdArr = getSubByKey($topicIdArr, 'topic_id');
		$del['weibo_id'] = $weiboId;
		$del['topic_id'] = array('IN', $topicIdArr);
		M('weibo_topic_link')->where($del)->delete();
		foreach($arr as $v) {
			$topicMap['name'] = $v;
			$this->setDec('count', $topicMap);
		}
	}

	//获取微博与话题关联的统计数 -- 没有作用可以删除 TODO
	public function getWeiboJoinTopicCount($topicId) {
		$map['topic_id'] = $topicId;
		$count = M('weibo_topic_link')->where($map)->count();
		return $count;
	}

	/**
	 * 获取给定话题名的话题ID
	 *
	 * @param string $name 话题名
	 * @return int 话题ID
	 */
	public function getTopicId($name)
	{
		//$topic_length = 20;
		//$map['name'] = trim(t(mStr(preg_replace("/#/",'',$name), $topic_length, 'utf-8', false)));
		$map['name'] = t(preg_replace("/#/",'',$name));
		if (empty($map['name']))
			return 0;

		$info = $this->where($map)->find();
		if ($info['topic_id']) {
			return $info['topic_id'];
		} else {
			$map['count'] = 0;
			$map['ctime'] = time();
			return $this->add($map);
		}
	}

	/**
	 * 获取站点的热门话题，2011/11/26 增加参数 range.如果不指定参数.默认是按照系统配置来计算
	 *
	 * @return array 热门话题
	 */
	public function getHot($type=null, $range=null,$limit=10)
	{
		$hot_list = array();
		$type     = 'recommend' == $type ? 'recommend' : 'auto';
		$expire   = 1 * 3600; // 1 hour
		$range    = !$range ? $expire : intval($range);
		$cache_id = '_weibo_hot_topic_' . $type.'_'.$range;
	

		//首页改用锁机制
		if($hot_list = S($cache_id)){
    		return $hot_list;
    	}

		if ('recommend' == $type) {
			$hot_list = D('Topics', 'weibo')->getHot();
		} else if ('auto' == $type) {
			$map['count'] = array('gt', '0');
			//热门话题时间范围
			if ($range >= 1) {
				$map['ctime'] = array('gt', mktime(0,0,0,date("m"),date("d")+1,date("Y"))-$range*24*3600);
			}
			$map['lock'] = 0;
			$hot_list = $this->where($map)->order('`count` DESC')->limit($limit)->findAll();
		}
		//取热度 - 按照当前值与最大值的比例计算
		$hot_counts = max(getSubByKey($hot_list, 'count'));
		foreach($hot_list as $k=>$v){
			$hot_list[$k]['name']	=	htmlspecialchars($v['name']);
			$hot_list[$k]['rating'] = ceil(($v['count']/$hot_counts)*100);
		}
		S($cache_id, $hot_list, $expire);
		return $hot_list;
	}

	//最新话题
	function getNew($num){
		return $this->order('cTime DESC')->limit($num)->findall();
	}
}