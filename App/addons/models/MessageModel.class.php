<?php
/**
 * 私信模型
 *
 * @author daniel <desheng.young@gmail.com>
 */
class MessageModel extends Model
{
    protected $tableName = 'message_content';

    /**
     * 获取消息列表
     *
     * @param string|array $map   查询条件
     * @param string       $field 默认'*'
     * @param string       $order 默认'message_id DESC'
     * @param int          $limit 默认20
     * @return array
     */
    public function getMessageByMap($map = array(), $field = '*', $order = 'message_id DESC', $limit = 20) {
        return $this->where($map)->field($field)->order($order)->findPage($limit);
    }

    /**
     * 私信列表
     *
     * @param int       $uid  用户ID
     * @param int|array $type 0:系统私信 1:一对一私信 2:多人聊天 默认1
     * @return array
     */
    public function getMessageListByUid($uid, $type = 1)
    {
        $uid  = intval($uid);
        $type = is_array($type) ? ' IN (' . implode(',', $type) . ')' : "={$type}";
        $list = M('message_member')->table("`{$this->tablePrefix}message_member` AS `mb`")
                ->join("`{$this->tablePrefix}message_list` AS `li` ON `mb`.`list_id`=`li`.`list_id`")
                ->where("`mb`.`member_uid`={$uid} AND `li`.`type`{$type}")
                ->order('`mb`.`new` DESC,`mb`.`list_ctime` DESC')
                ->findPage();
        $this->_parseMessageList( $list['data'], $uid); // 引用
        return $list;
    }

    private function _parseMessageList(& $list, $current_uid)
    {
        foreach ($list as & $v) {
            $v['last_message'] = unserialize($v['last_message']);
            $v['last_message']['to_uid'] = $this->_parseToUidByMinMax($v['min_max'], $v['last_message']['from_uid']);
        }
    }

    /**
     * 私信列表（API专用）
     *
     * @param int    $uid      用户ID
     * @param string $type     all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
     * @param int    $since_id 范围起始ID 默认0
     * @param int    $max_id   范围结束ID 默认0
     * @param int    $count    单页读取条数  默认20
     * @param int    $page     页码  默认1
     * @param string $order    排序  默认以消息ID倒叙排列
     * @return array
     */
    public function getMessageListByUidForAPI($uid, $type = 1, $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = '`mb`.`new` DESC,`mb`.`list_id` DESC')
    {
        $uid  = intval($uid);
        $type = is_array($type) ? ' IN (' . implode(',', $type) . ')' : "={$type}";
        if ($since_id) {
            $since_id = " AND `li`.`list_id`>{$since_id}";
        }else{
            $since_id = '';
        }
        if ($max_id) {
            $max_id = " AND `li`.`list_id`<{$max_id}";
        }else{
            $max_id ='';
        }
        $limit = ($page-1) * $count . ',' . $count;
        $list = M('message_member')->table("`{$this->tablePrefix}message_member` AS `mb`")
                ->join("`{$this->tablePrefix}message_list` AS `li` ON `mb`.`list_id`=`li`.`list_id`")
                ->where("`mb`.`member_uid`={$uid} AND `li`.`type`{$type} {$since_id} {$max_id}")
                ->order('`mb`.`new` DESC,`mb`.`list_id` DESC')
                ->limit($limit)
                ->findAll();
        $this->_parseMessageList( $list, $uid); // 引用
        foreach ($list as & $_l) {
            $_l['from_uid'] = $_l['last_message']['from_uid'];
            $_l['content']  = h($_l['last_message']['content']);
            $_l['mtime']    = $_l['list_ctime'];
        }

        return $list;
    }
    /**
     * 私信列表（API专用）
     *
     * @param int    $uid      用户ID
     * @param string $type     all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
     * @param int    $since_id 范围起始ID 默认0
     * @param int    $max_id   范围结束ID 默认0
     * @param int    $count    单页读取条数  默认20
     * @param int    $page     页码  默认1
     * @param string $order    排序  默认以消息ID倒叙排列
     * @return array
     */
    public function getMessageListByUidForAPIUnread($uid, $type = 1, $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = '`mb`.`new` DESC,`mb`.`list_id` DESC')
    {
    	$uid  = intval($uid);
    	$type = is_array($type) ? ' IN (' . implode(',', $type) . ')' : "={$type}";
    	if ($since_id) {
    		$since_id = " `li`.`list_id`>{$since_id}";
    	}
    	if ($max_id) {
    		$max_id = " `li`.`list_id`<{$max_id}";
    	}
    	$limit = ($page-1) * $count . ',' . $count;
    	$list = M('message_member')->table("`{$this->tablePrefix}message_member` AS `mb`")
    	->join("`{$this->tablePrefix}message_list` AS `li` ON `mb`.`list_id`=`li`.`list_id`")
    	->where("`mb`.`member_uid`={$uid} AND `li`.`type`{$type} AND `mb`.`new`> 0")
    	->order('`mb`.`list_id` DESC')
    	->limit($limit)
    	->findAll();
    	$this->_parseMessageList( $list, $uid); // 引用
    	foreach ($list as & $_l) {
    		$_l['from_uid'] = $_l['last_message']['from_uid'];
    		$_l['content']  = h($_l['last_message']['content']);
    		$_l['mtime']    = $_l['list_ctime'];
    	}

    	return $list;
    }

    /**
     * 收件箱消息列表
     *
     * @param int    $uid  用户ID
     * @param string $type all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
     * @return array
     */
    /*public function getInboxByUid($uid, $type = 'all') {
        $map['to_uid']      = $uid;
        $map['is_lastest']  = 1;
        $map['deleted_by']  = array('neq', $uid);
        if ($type == 'is_read') {
            $map['is_read'] = 1;
        }else if ($type == 'is_unread') {
            $map['is_read'] = 0;
        }
        return $this->getMessageByMap($map);
    }*/

    /**
     * 收件箱消息列表（API专用）
     *
     * @param int    $uid      用户ID
     * @param string $type     all:全部消息,is_read:阅读过的,is_unread:为阅读  默认'all'
     * @param int    $since_id 范围起始ID 默认0
     * @param int    $max_id   范围结束ID 默认0
     * @param int    $count    单页读取条数  默认20
     * @param int    $page     页码  默认1
     * @param string $order    排序  默认以消息ID倒叙排列
     * @return array
     */
    /*public function getInboxByUidFromApi($uid, $type = 'all', $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = 'message_id DESC') {
        $map['to_uid']      = $uid;
        $map['is_lastest']  = 1;
        $map['deleted_by']  = array('neq', $uid);
        if ($type == 'is_read') {
            $map['is_read'] = 1;
        }else if ($type == 'is_unread') {
            $map['is_read'] = 0;
        }
        if ($since_id) {
            $map['message_id'][] = array('gt', $since_id);
        }
        if ($max_id) {
            $map['message_id'][] = array('lt', $max_id);
        }
        $limit = ($page-1) * $count . ',' . $count;
        return $this->where($map)->order($order)->limit($limit)->findAll();
    }*/

    /**
     * 获取发件箱消息列表
     *
     * @param int $uid 用户列表
     * @return array
     */
    /*public function getOutboxByUid($uid) {
        $map['from_uid']            = $uid;
        $map['is_lastest']          = 1;
        $map['deleted_by']          = array('neq', $uid);
        return $this->getMessageByMap($map);
    }*/

    /**
     * 获取发件箱消息列表（API专用）
     *
     * @param int    $uid      用户ID
     * @param int    $since_id 范围起始ID 默认0
     * @param int    $max_id   范围结束ID 默认0
     * @param int    $count    单页读取条数  默认20
     * @param int    $page     页码  默认1
     * @param string $order    排序  默认'message_id DESC'
     * @return array
     */
    /*public function getOutboxByUidFromApi($uid, $since_id = 0, $max_id = 0, $count = 20, $page = 1, $order = 'message_id DESC') {
        $map['from_uid']            = $uid;
        $map['is_lastest']          = 1;
        $map['deleted_by']          = array('neq', $uid);
        if ($since_id) {
            $map['message_id'][] = array('gt', $since_id);
        }
        if ($max_id) {
            $map['message_id'][] = array('lt', $max_id);
        }
        $limit = ($page-1) * $count . ',' . $count;
        return $this->where($map)->order($order)->limit($limit)->findAll();
    }*/

    /**
     * 私信详细信息
     *
     * @param int     $uid          用户ID
     * @param int     $message_id   私信ID
     * @param boolean $show_cascade 是否获取回话内容
     * @return array
     */
    public function getDetailById($uid, $id, $show_cascade = true) {
        $uid = intval($uid);
        $id = intval($id);
        if ($show_cascade) {
            // 验证该用户是否为该私信成员
            if (!$this->isMember($id, $uid, false)) {
                return false;
            }
            $map['list_id'] = $id;
            $map['is_del']  = 0;
            $res = M('message_content')->where($map)->order('message_id DESC')->findAll();
            $res['content'] = t($res['content']);
            return $res;
        }else {
            // `mb`.`member`={$uid} 可验证当前用户是否依然为该私信成员
            $res = M('message_content')->table("`{$this->tablePrefix}message_content` AS `ct`")
                   ->join("`{$this->tablePrefix}message_member` AS `mb` ON `ct`.`list_id`=`mb`.`list_id` AND `ct`.`from_uid`=`mb`.`member_uid`")
                   ->where("`mb`.`member_uid`={$uid} AND `ct`.`message_id`={$id} AND `ct`.`is_del`=0")
                   ->find();
            $res['content'] = t($res['content']);
            return $res;
        }
    }

    /**
     * 私信内容列表
     *
     * @param int     $list_id      私信列表ID
     * @param int     $uid          用户ID
     * @param int     $since_id     最早会话ID
     * @param int     $lastest_id   最新会话ID
     * @param string  $load         加载方式 new：最新会话 old：之前会话
     * @param int     $count        旧会话加载条数
     * @return array
     */
    public function getMessageByListId($list_id, $uid, $since_id = null, $max_id = null, $count = 20) {
        $list_id  = intval($list_id);
        $uid      = intval($uid);
        $since_id = intval($since_id);
        $max_id   = intval($max_id);
        $count    = intval($count);
        // 验证该用户是否为该私信成员
        if (!$list_id || !$uid || !$this->isMember($list_id, $uid, false)) {
            return false;
        }
        $where = "`list_id`={$list_id} AND `is_del`=0";
        if ($since_id > 0) {
            $where .= " AND `message_id`>{$since_id}";
            $limit  = null;
        } else {
            $max_id > 0 && $where .= " AND `message_id`<{$max_id}";
            // 多查询一条验证，是否还有后续信息
            $limit = intval($count) + 1;
        }
        $res['data']  = M('message_content')->where($where)->order('message_id DESC')->limit($limit)->findAll();
        $res['count'] = count($res['data']);
        foreach($res['data'] as $k=>$v){
            $res['data'][$k]['content'] = t($v['content']);
        }
        if ($since_id > 0) {
            $res['since_id'] = $res['data'][0]['message_id'];
            $res['max_id']   = $res['data'][$res['count'] - 1]['message_id'];
        } else {
            $res['since_id'] = $res['data'][0]['message_id'];
            // 结果数等于查询数，则说明还有后续message
            if ($res['count'] == $limit) {
                // 去除结果的最后一条
                array_pop($res['data']);
                // 计数减一
                $res['count'] --;
                // 取最后一条结果message_id
                $res['max_id'] = $res['data'][$res['count'] - 1]['message_id'];
            } else if ($res['count'] < $limit) {
                // 取最后一条结果message_id设置为0，表示结束
                $res['max_id'] = 0;
            }
        }

        return $res;
    }

    /**
     * 用户未读私信数
     *
     * @param int $uid 用户ID
     * @return array
     */
    public function getUnreadMessageCount($uid) {
        $map['member_uid']  = intval($uid);
        return intval(M('message_member')->where($map)->sum('new'));
    }

    /**
     * 获取私信会话数，可同时返回多条私信的该状态
     *
     * @param int          $uid
     * @param string|array $message_ids 多个私信可以组装成数组，也可以用“,”分隔
     * @return array
     */
    /*public function getSessionCount($uid, $message_ids = 0) {
        $message_ids    = is_array($message_ids) ? implode(',', $message_ids) : $message_ids;
        $prefix = C('DB_PREFIX');
        $where  = "`source_message_id` IN ( $message_ids ) AND `deleted_by` <> $uid";
        $sql    = "SELECT `source_message_id`, count(*) AS count FROM {$prefix}message WHERE $where GROUP BY `source_message_id`";
        $res    = $this->query($sql);

        //格式化为array($message_id => $count)的形式
        foreach ($res as $v) {
            $session_count[$v['source_message_id']] = $v['count'];
        }
        return $session_count;
    }*/

    /**
     * 发送私信
     *
     * @param array $data     私信信息,包括to接受对象、title私信标题、content私信正文
     * @param int   $from_uid 发送私信的用户ID
     * @return array          返回新添加的私信的ID
     */
    public function postMessage($data, $from_uid) {
        $from_uid = intval($from_uid);
        $data['to']       = is_array($data['to']) ? $data['to'] : explode(',', $data['to']);
        // 私信成员
        $data['member']   = array_filter(array_merge(array($from_uid), $data['to']));
        // 发起时间
        $data['mtime'] = time();

        // 添加或更新私信list
        if (false == $data['list_id'] = $this->_addMessageList($data, $from_uid)) {
            return false;
        }
        // 存储私信成员
        if (false === $this->_addMessageMember($data, $from_uid)) {
            return false;
        }
        // 存储内容
        if (false == $this->_addMessage($data, $from_uid)) {
            return false;
        }

        return $data['list_id'];
    }

    /**
     * 回复私信
     *
     * @param int    $id         回复的私信list_id
     * @param string $content    内容
     * @param int    $from_uid   回复者
     * @return mixed 回复失败返回false，回复成功返回本条新回复的message_id
     */
    public function replyMessage($list_id, $content, $from_uid) {
        if (!$this->isMember($list_id, $from_uid, false)) {
            return false;
        }
        $list_id  = intval($list_id);
        $from_uid = intval($from_uid);

        $time = time();

        // 添加新记录
        $data['list_id']  = $list_id;
        $data['content']  = h($content);
        $data['mtime']    = $time;
        $new_message_id = $this->_addMessage($data, $from_uid);
        unset($data);

        if ( !$new_message_id ) {
            return false;
        }else {
            $list_data['list_id']       = $list_id;
            $list_data['last_message'] = serialize(array(
                'from_uid' => $from_uid,
                'content'  => keyWordFilter(t($content))));
            // 获取当前私信列表list的type、min_max
            $list_map['list_id']       = $list_id;
            $list_info = M('message_list')->field('type,member_num,min_max')->where($list_map)->find();
            if (1 == $list_info['type']) { // 一对一
                $list_data['member_num'] = 2;
                // 重置最新记录
                M('message_list')->save($list_data);
                // 重置其他成员信息
                if ($list_info['member_num'] < 2) {
                    $member_data = array(
                        'list_id' => $list_id,
                        'member'  => array_diff(explode('_', $list_info['min_max']), array($from_uid)),
                        'mtime'   => $time
                    );
                    $this->_addMessageMember($member_data, $from_uid);
                } else {
                    // 重置其他成员信息
                    $member_data['new']         = array('exp', '`new`+1');
                    $member_data['message_num'] = array('exp', '`message_num`+1');
                    $member_data['list_ctime']  = $time;
                    M('message_member')->where("`list_id`={$list_id} AND `member_uid`!={$from_uid}")->save($member_data);
                }
            } else { // 多人
                // 重置最新记录
                M('message_list')->save($list_data);
                // 重置其他成员信息
                $member_data['new']         = array('exp', '`new`+1');
                $member_data['message_num'] = array('exp', '`message_num`+1');
                $member_data['list_ctime']  = $time;
                M('message_member')->where("`list_id`={$list_id} AND `member_uid`!={$from_uid}")->save($member_data);
            }
            // 重置回复者的成员信息
            $from_data['message_num'] = array('exp', '`message_num`+1');
            $from_data['ctime']       = $time;
            $from_data['list_ctime']  = $time;
            M('message_member')->where("`list_id`={$list_id} AND `member_uid`={$from_uid}")->save($from_data);
            unset($from_data);
        }

        return $new_message_id;
    }

    /**
     * 设置私信为已读
     *
     * @param array|string $message_ids 多个私信ID可以组成数组，也可以用“,”分隔
     * @param int          $uid 成员的用户ID
     * @return boolean
     */
    public function setMessageIsRead($list_ids = null, $member_uid) {
        if (!$member_uid) {
            return false;
        }
        $list_ids && $map['list_id']    = array('IN', $list_ids);
        $map['member_uid'] = intval($member_uid);
        return false !== M('message_member')->where($map)->setField('new', 0);
    }

    /**
     * 设置用户所有私信为已读
     *
     * @param int          $uid 成员的用户ID
     * @return boolean
     */
    public function setAllIsRead($member_uid) {
        $member_uid = intval($member_uid);
        if ($member_uid <= 0)
            return false;

        $map['member_uid']     = $member_uid;
        return $this->where($map)->setField('new', 0);
    }

    /**
     * 用户删除私信
     *
     * @param int          $uid         用户ID
     * @param array|string $list_ids    多个私信ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */
    public function deleteMessageByListId($member_uid, $list_ids) {
        if (!$list_ids || !$member_uid) {
            return false;
        }
        $member_map['list_id']    = array('IN', $list_ids);
        $member_map['member_uid'] = intval($member_uid);
        if (false == M('message_member')->where($member_map)->delete()) {
            return false;
        } else {
            $list_map['list_id']    = array('IN', $list_ids);
            $res = M('message_list')->setDec('member_num', $list_map);
        }

        if ($list_no_member = M('message_list')->field('`list_id`')->where('`member_num`<=0')->findAll()) {
            $this->deleteMessageList(getSubByKey($list_no_member, 'list_id'));
        }

        // 一对一session处理
        $this->_deleteSessionByListId($member_uid, $list_ids);

        return $res;
    }

    /**
     * 直接删除私信（管理员）
     *
     * @param array|string $list_ids 多个私信ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */
    public function deleteMessageList($list_ids) {
        if (!$list_ids) {
            return false;
        }
        $map['list_id']    = array('IN', $list_ids);
        return false !== M('message_content')->where($map)->delete()
               && false !== M('message_member')->where($map)->delete()
               && false !== M('message_list')->where($map)->delete();
    }

    /**
     * 用户删除会话
     *
     * @param int          $uid         用户ID
     * @param array|string $message_ids 多个会话ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */
    public function deleteSessionById($member_uid, $message_ids) {
        $message_ids = intval($message_ids);
        $member_uid  = intval($member_uid);
        if (!$message_ids || !$member_uid) {
            return false;
        }
        $where = "`message_id`={$message_ids}";
        $list = M('message_content')->field('`list_id`')->where($where)->find();
        if (false === M('message_content')->where($where . " AND `is_del`>0 AND `is_del`!={$member_uid}")->delete()
            || false === M('message_content')->setField('is_del', $member_uid, $where . ' AND `is_del`=0')) {
            return false;
        } else {
            $member_map['list_id']    = $list['list_id'];
            $member_map['member_uid'] = $member_uid;
            $res = M('message_member')->setDec('message_num', $member_map);
        }

        return $res;
    }

    // 一对一删除list同时标记session删除
    private function _deleteSessionByListId($member_uid, $list_ids)
    {
        $member_uid  = intval($member_uid);
        if (!$list_ids || !$member_uid) {
            return false;
        }
        $list_ids = array_map('intval', is_array($list_ids) ? $list_ids : explode(',', $list_ids));

        $map['list_id'] = array('IN', $list_ids);
        $map['type']    = 1;
        $list_ids = M('message_list')->field('list_id')->where($map)->findAll();
        $where = '`list_id` IN (' . implode(',', getSubByKey($list_ids, 'list_id')) . ')';
        if (false === M('message_content')->where($where . " AND `is_del`>0 AND `is_del`!={$member_uid}")->delete()
            || false === M('message_content')->setField('is_del', $member_uid, $where . ' AND `is_del`=0')) {
            return false;
        }
        return true;
    }

    /**
     * 管理员直接删除会话
     *
     * @param int          $uid         用户ID
     * @param array|string $message_ids 多个会话ID可以组成数组，也可以用“,”分隔
     * @return boolean
     */
    public function deleteSessionByAdmin($message_ids) {
        $message_ids = intval($message_ids);
        if (!$message_ids) {
            return false;
        }
        $content_map['message_id'] = $message_ids;
        $list = M('message_content')->field('`list_id`')->where($content_map)->find();
        if (false == M('message_content')->where($content_map)->delete()) {
            return false;
        } else {
            $member_map['list_id']    = $list['list_id'];
            $res = M('message_member')->setDec('message_num', $member_map);
        }

        return $res;
    }

    /*
     * 获取私信成员
     *
     * @param $list_id 私信列表ID
     * @param $field   成员字段
     */
    public function getMessageMembers($list_id, $field = null)
    {
        $list_id = intval($list_id);
        static $_members = array();

        if (!isset($_members[$list_id])) {
            $_members[$list_id] = M('message_member')->field($field)->where("`list_id`={$list_id}")->findAll();
        }

        return $_members[$list_id];
    }

    /*
     * 验证是否为私信成员
     *
     * @param $list_id     私信列表ID
     * @param $uid         用户ID
     * @param $show_detail 是否返回用户当前私信的详细状态
     */
    public function isMember($list_id, $uid, $show_detail = false)
    {
        $list_id = intval($list_id);
        $uid     = intval($uid);
        $show_detail = $show_detail ? 1 : 0;
        static $_is_member = array();

        if (!isset($_is_member[$list_id][$uid][$show_detail])) {
            $map['list_id']    = $list_id;
            $map['member_uid'] = $uid;
            if ($show_detail) {
                $_is_member[$list_id][$uid][$show_detail] = M('message_member')->where($map)->find();
            } else {
                $_is_member[$list_id][$uid][$show_detail] = M('message_member')->getField('member_uid', $map);
            }
        }

        return $_is_member[$list_id][$uid][$show_detail];
    }

    /* 添加私信list
     *
     * @param $data content:私信内容，member:私信成员UID数组，mtime:当前时间
     * @param $from_uid 发布人UID
     */
    private function _addMessageList($data, $from_uid)
    {
        if (!$data['content'] || !is_array($data['member']) || !$from_uid) {
            return false;
        }

        $list['from_uid'] = $from_uid;
        $list['title']    = $data['title'] ? t($data['title']) : t(getShort($data['content'],20));
        $list['member_num'] = count($data['member']);
        $list['type']     = in_array($data['type'], array(1, 2)) ? $data['type'] : (2 == $list['member_num'] ? 1 : 2);
        $list['min_max']  = $this->_getUidMinMax($data['member']);
        $list['mtime']    = $data['mtime'];
        $list['last_message']  = serialize(array(
            'from_uid' => $from_uid,
            'content'  => keyWordFilter(t($data['content'], true))
        ));

        $list_map['type']    = $list['type'];
        $list_map['min_max'] = $list['min_max'];
        if (1 == $list['type'] && $list_id = M('message_list')->getField('list_id', $list_map)) {
            $list_map['list_id'] = $list_id;
            $_list['member_num']   = $list['member_num'];
            $_list['last_message'] = $list['last_message'];
            false === M('message_list')->where($list_map)->data($_list)->save() && $list_id = false;
        } else {
            $list_id = M('message_list')->data($list)->add();
        }

        return $list_id;
    }

    /* 添加私信member
     *
     * @param $data list_id:私信列表ID，member:私信成员UID数组，mtime:当前时间
     * @param $from_uid 发布人UID
     */
    private function _addMessageMember($data, $from_uid)
    {
        if (!$data['list_id'] || !is_array($data['member']) || !$from_uid) {
            return false;
        }

        $member['list_id'] = $data['list_id'];
        $member['member_uid'] = '';
        $member['new']     = 0;
        //$member['message_num'] = '`message_num`+1';
        $member['ctime']   = 0;
        $member['list_ctime'] = $data['mtime'];
        $members = array();
        foreach ($data['member'] as $k => $m) {
            $member['member_uid'] = $m;
            if ($m == $from_uid ) {
                $member['new'] = 0;
                $member['ctime'] = $data['mtime'];
            } else {
                $member['new'] = '`new`+1';
                $member['ctime'] = '`ctime`';
            }

            $members[] = '(' . implode(',', $member) . ')';
            unset($data['member'][$k]);
        }

        $members = implode(',', $members);
        $sql = "REPLACE INTO `{$this->tablePrefix}message_member`(`list_id`,`member_uid`,`new`,`ctime`,`list_ctime`) VALUES {$members}";
        return $this->query($sql);
    }

    // 添加私信message
    private function _addMessage($data, $from_uid)
    {
        if (!$data['list_id'] || !$data['content'] || !$from_uid) {
            return false;
        }
        $message['list_id']  = $data['list_id'];
        $message['from_uid'] = $from_uid;
        $message['content']  = h($data['content']);
        $message['is_del']   = 0;
        $message['mtime']    = $data['mtime'];
        return M('message_content')->data($message)->add();
    }

    private function _getUidMinMaxByListId($list_id)
    {
        $member_uids = $this->getMessageMembers($list_id);
        return $this->_getUidMinMax(getSubByKey($member_uids, 'member_uid'));
    }

    private function _getUidMinMax($uids)
    {
        sort($uids);
        return implode('_', $uids);
    }

    private function _parseToUidByMinMax($min_max_uids, $from_uid)
    {
        $min_max_uids = explode('_', $min_max_uids);
        // 去除当前用户UID
        return array_values(array_diff($min_max_uids, array($from_uid)));
    }
}
