<?php
class DashboardModel extends Model{
    var $tableName = 'weibo_dashboard';
    
    //获取首页微博列表
    public function getDashboardList($uid, $since=null, $row=5) {
        $uid = ($uid>0)?intval($uid):$this->mid;
        $row = $row?$row:5;
        //632979
        //先查dashboard是否有用户的相关数据.取出来.
        $dashboard = $this->_getUserDashboard($uid);

        //更新dashboard数据 - 优化更新规则.不应该每次都更新.可以发微博时触发.标记更新规则.
        if($dashboard['last_get_weibo_id'] < $dashboard['last_post_weibo_id']){
            $this->_updateUserDashboard($uid,$dashboard);
            $list = $this->_getDashboardData();
            S('dashboard_data_'.$uid.'_'.$row,$list);
        }

        if(($list=S('dashboard_data_'.$uid.'_'.$row))===false){
            $list = $this->_getDashboardData();
        }
        
        return $list;
    }

    private function _getDashboardData($uid,$row){
        //获取dashboard信息 - 获取dashboard列表数据
        $list  = M('weibo_dashboard_data')->where("uid=".$uid)->order("weibo_id desc")->limit($row)->findAll();

        //更新dashboard的数据
        if($list[0]['weibo_id']!=$dashboard['last_get_weibo_id']){
            unset($data);
            $data['uid'] = $uid;
            $data['last_get_weibo_id'] = $list[0]['weibo_id'];
            $data['last_get_time'] = $list[0]['ctime'];
            M('weibo_dashboard')->where("uid=".$uid)->save($data);
        }

        S('dashboard_data_'.$uid.'_'.$row,$list);
        return $list;
    }
    //获取用户dashboard信息. todo:主动或出发更新dashboard信息
    private function _getUserDashboard($uid){
        $dashboard_cache = S('dashboard_'.$uid);
        if($dashboard_cache){
            return $dashboard_cache;
        }else{
            $dashboard = $this->where("uid=".$uid)->find();
            if(!$dashboard){
                $data['uid'] = $uid;
                $weibo = M('weibo')->where("isdel=0 AND uid=".$uid)->order("ctime desc")->find();
                if($weibo){
                    $data['last_post_weibo_id'] = $weibo['weibo_id'];
                    $data['last_post_time'] = $weibo['ctime'];
                }
                $result = $this->add($data);
                if($result){
                    $sql = "UPDATE ".C('DB_PREFIX')."weibo_dashboard SET 
                        following_weibo_count=(
                            SELECT count(*) 
                            FROM ".C('DB_PREFIX')."weibo_dashboard_data 
                            WHERE uid={$uid}
                        ),
                        my_following=(
                            SELECT count(fid) 
                            FROM ".C('DB_PREFIX')."weibo_follow 
                            WHERE uid={$uid} AND type=0
                        ),
                        my_follower=(
                            SELECT count(uid) 
                            FROM ".C('DB_PREFIX')."weibo_follow 
                            WHERE fid={$uid} AND type=0
                        ),
                        my_weibo_count=(
                            SELECT count(*) 
                            FROM ".C('DB_PREFIX')."weibo 
                            WHERE uid={$uid}
                        )
                        WHERE uid={$uid}";
                    M()->execute($sql);
                    $dashboard = $data;
                }else{
                    return false;
                }
            } 
            S('dashboard_'.$uid,$dashboard);
            return $dashboard;
        }
    }

    private function _updateUserDashboard($uid, $dashboard=null){
        if(!$dashboard){
            $dashboard = $this->__getUserDashboard($uid);
        }
        $since = $dashboard['last_get_weibo_id'];
        $since = $since?$since:0;
        //获取当前信息.
        //$followCount = D('Follow','weibo')->getUserFollowCount($uid);
        $followCount = $dashboard['my_following'];
        if ($followCount) { // 有关注时, 展示关注的用户的微博
            $where =" AND ( uid IN (SELECT fid FROM ".C('DB_PREFIX')."weibo_follow WHERE uid=$uid AND type=0) OR uid={$uid})";
        }else{//无关注时.数据为空.
            $where =" AND uid = ".$uid;
        }
        //更新dashboard_data的数据
        $sql = "INSERT INTO ".C('DB_PREFIX')."weibo_dashboard_data (uid,weibo_id,fid,ctime) 
                        SELECT $uid AS uid,weibo_id,uid AS fid,ctime 
                        FROM `".C('DB_PREFIX')."weibo` 
                        WHERE weibo_id > {$since} ".$where;
        $weibo = M('weibo_dashboard_data')->execute($sql);

        return $weibo;
    }

    //获取首页微博列表
    public function getHomeList($uid, $since=null, $row=5) {
    	$row = $row?$row:5;
	    if ($since) {
			$map="weibo_id < $since AND isdel=0";
		} else {
			$map = '1=1 AND isdel=0';
		}

		if($uid>0){
			$followCount = D('Follow','weibo')->getUserFollowCount($uid);
			if ($followCount) { // 有关注时, 展示关注的用户的微博
				$map.=" AND ( uid IN (SELECT fid FROM ".C('DB_PREFIX')."weibo_follow WHERE uid=$uid AND type=0) OR uid={$uid})";
			}else{//无关注时.数据为空.
				$map.=' AND uid = '.$uid;
			}
		}
    	$list = M('weibo')->field('weibo_id')->where($map)->order('weibo_id DESC')->limit($row)->findAll();
        return $list;
    }
}