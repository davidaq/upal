<?php 
class TopicsModel extends Model{
	var $tableName = 'weibo_topics';

	// 专题列表
	public function topicsList($post = array())
	{
        $post['topics_id'] && $map['topics.topics_id'] = array('in', t($post['topics_id']));
        $post['name']      && $map['topic.name'] 	    = array('like', '%' . t($post['name']) . '%');
        $post['content']   && $map['topics.content']   = array('like', '%' . t($post['content']) . '%');
		is_string($post['recommend']) && $map['topics.recommend'] = (string)intval($post['recommend']);
		$map['topics.isdel'] = 0;
        //$order = ( $post['orderkey'] && $post['ordertype'] ) ? $post['orderkey'] . ' ' . $post['ordertype']:'weibo_id DESC';
        $order = 'topics.recommend ASC,topics.topics_id DESC';

		$list = $this->field('topics.*,topic.name')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where($map)->order($order)->findPage(20);
		return $list;
	}

	// 获取推荐专题列表
	public function getHot()
	{
		$list = $this->field('topic.name,topic.count,topics.domain,topics.note')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where('topics.recommend=1 AND topics.isdel=0')
					 ->order('topics.topics_id DESC')->findAll();
		return $list;
	}

	public function getHotLimit($p=0,$nums=10){
		$limit = $p*$nums.','.$nums;
		$list = $this->field('topic.name,topic.count,topics.domain,topics.note')
					 ->table("{$this->tablePrefix}weibo_topics as topics
					 		  LEFT JOIN {$this->tablePrefix}weibo_topic as topic
					 		  ON topics.topic_id=topic.topic_id")
					 ->where('topics.isdel=0')
					 ->order('topic.count desc')
					 ->limit($limit)
					 ->findAll();
		return $list;
	}
	// 获取专题详细信息
	public function getTopics($name = null, $topics_id = null, $domain = null, $recommend = false)
	{
		if ($name) {
			$name = html_entity_decode(urldecode($name), ENT_QUOTES);
			$map['topic_id'] = D('Topic', 'weibo')->getTopicId($name);
		} else if($topics_id) {
			$map['topics_id'] = intval($topics_id);
		} else if ($domain) {
			$map['domain'] = h(t($domain));
		} else {
			return false;
		}
		//$recommend && $map['recommend'] = '1';
		$map['isdel'] = 0;
		$topics = D('Topics', 'weibo')->where($map)->find();
		if ($topics) {
			$topics['name'] = $name ? t($name) : D('Topic', 'weibo')->getField('name', "topic_id={$topics['topic_id']}");
		}
		return $topics;
	}

	// 删除专题
	public function deleteTopics($topics_id)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('isdel', '1', $map);
		return $res;
	}

	// 推荐专题
	public function recommendTopics($topics_id, $recommend = true)
	{
		$topics_id = is_array($topics_id) ? $topics_id : explode(',', $topics_id);
		$map['topics_id'] = array('IN', $topics_id);
		$res = $this->setField('recommend', $recommend ? '1' : '0', $map);
		return $res;
	}
}