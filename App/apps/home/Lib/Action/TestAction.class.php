<?php
error_reporting(E_ALL);
set_time_limit(0);
class TestAction extends Action{

	public function testTopFollower(){
		$top_user = D('Follow','weibo')->getTopFollowerUser();
		dump($top_user);
	}

	public function dashboard_new(){
		//方法1
		$time1	=	microtime(true);
		echo '<h1>new_method</h1>';
		//for($i=0;$i<10;$i++){
			$result =	D('Dashboard','weibo')->getDashboardList($this->mid);
		//}
		$time3	=	microtime(true);
		echo 'new:'.($time3-$time1).'<br />';
		$this->display('index');
	}

	public function dashboard_old(){
		//方法1
		$time1	=	microtime(true);
		echo '<h1>old_method</h1>';
		//($i=0;$i<10;$i++){
			$result =	D('Dashboard','weibo')->getHomeList($this->mid);
		//}
		$time3	=	microtime(true);
		echo 'old:'.($time3-$time1).'<br />';
		$this->display('index');
	}

	public function index()
	{
		// 未指定参数时, 加载系统配置
		$config = model('Xdata')->lget('top_follower');
		!isset($hide_auto_friend) && $hide_auto_friend = intval($config['hide_auto_friend']);
		!isset($hide_no_avatar)   && $hide_no_avatar   = intval($config['hide_no_avatar']);

		$uid       = intval($uid);
		$count 	   = intval($count);
		$limit     = 10;       // 查询的结果数
		$following = array(); // 已关注的用户
		$top_user  = array(); // 最终结果

		$cache_id = '_weibo_top_followed_' . $count .'_'. $uid .'_'. intval($hide_auto_friend) . intval($hide_no_avatar);

		// 缓存有效时间: 1 Hour
		$expire   = 1 * 3600;

		//if (($top_user = S($cache_id)) === false) {

			// 隐藏无头像用户时, 为了保证最后结果满足$limit, 查询时使用3倍的$limit
			$limit   += $hide_no_avatar ? $count * 3 : $count;

			$where = 'WHERE `type` = 0 ';
			if ($hide_auto_friend) { // 隐藏默认关注的用户时
				$auto_friend = model('Xdata')->get('register:register_auto_friend');
				$auto_friend = explode(',', $auto_friend);
				if (count($auto_friend) > 1)
					$where .= 'AND `fid` NOT IN ( ' . implode(',', $auto_friend) . ' )';
			}
			$sql = "SELECT `fid` AS `uid`, count(`uid`) AS `count` FROM ts_weibo_follow " .
				   $where . " GROUP BY `fid` " .
				   "ORDER BY `count` DESC LIMIT {$limit}";
			
			$res = M()->query($sql);
			dump($res);
			$res = $res ? $res : array();

			if (!empty($res)) { // 过滤
				$index = 1;
				$noPic = array();
				foreach ($res as $k => $v) {
					if ($index > $count) {
						break;
					} else if ($hide_no_avatar && !hasUserFace($v['uid'])) { // 剔除无头像的用户
						$noPic[] = $v;
						unset($res[$k]);
						continue ;
					} else if ($uid > 0 && in_array($v['uid'], $following)) { // 剔除已关注的用户
						unset($res[$k]);
						continue ;
					}
					$top_user[] = $v;
					++ $index;
				}
			}
			unset($res);
			if(empty($top_user) && !empty($noPic)){
				$top_user = $noPic;
			}

			//S($cache_id,empty($top_user)?array():$top_user,$expire);
		//}

		return $top_user;
	}

	public function getMessage(){
		service('Notify')->getNotifityCount($this->mid,1,0,0,20,1);
	}

	public function getSite(){
		$map['site_id'] = array('gt', 0);
		$sql = ' and status=1';
		D('Site','sitelist')->where($map.$sql)->findAll();
		dump(M('')->getLastSql());
	}

	//更新微博与话题的关联数据
	public function updateWeiboJoinTopicData() {
		send_http_header('utf8');
		dump('开始');
		set_time_limit(0);
		//preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $content, $arr);
		$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
		$radix = 10000;
		$limit = ($page - 1) * $radix.', '.$radix;
		$result = M('weibo')->field('weibo_id, content, type, transpond_id')->where('isdel=0')->limit($limit)->findAll();
//		dump($result);
//		dump($result);exit;
		if(empty($result)) {
			dump('结束');exit;
		} else {
			foreach($result as $value) {
				if(preg_match_all("/#([^#]*[^#^\s][^#]*)#/is", $value['content'], $arr)) {
					$arr = array_unique($arr[1]);
					foreach($arr as $val) {
						$map['name'] = $val;
						$topicId = M('weibo_topic')->where($map)->getField('topic_id');
						$add['weibo_id'] = $value['weibo_id'];
						$add['topic_id'] = $topicId;
						$add['type'] = $value['type'];
						$add['transpond_id'] = $value['transpond_id'];
						M('weibo_topic_link')->add($add);
//						dump(M('weibo_topic_link')->getLastSql());exit;
					}
				}
			}
			$page++;
			$url = U('home/Test/updateWeiboJoinTopicData', array('page'=>$page));
			echo "<script type='text/javascript'>window.location.href=\"$url\"</script>";exit;
		}
	}
}